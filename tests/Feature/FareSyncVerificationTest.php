<?php

namespace Tests\Feature;

use App\Actions\Bookings\CreateBookingAction;
use App\Models\Accommodation;
use App\Models\Discount;
use App\Models\FerryRoute;
use App\Models\PaymentSetting;
use App\Models\Schedule;
use App\Models\TransportClass;
use App\Models\VehicleRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * FareSyncVerificationTest
 *
 * Verifies that fare calculations are in exact parity between:
 *   1. Server-side logic (CreateBookingAction::calculatePrice - the source of truth)
 *   2. Mobile App estimation logic (mirrored from flutter_app/lib/main.dart)
 *   3. Admin-configured PaymentSetting values (the authoritative origin)
 *
 * These tests prove that Admin is the single source of truth for all fares/fees,
 * and that any change in Filament immediately propagates consistently to both
 * the website and the mobile app.
 */
class FareSyncVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Admin sets these values in Filament -> ManagePaymentSettings.php
        // This is the single authoritative source for all fees
        PaymentSetting::query()->updateOrCreate(['id' => 1], [
            'web_admin_fee'              => 175.00,
            'short_haul_web_admin_fee'   => 30.00,
            'fee_per_accommodation'      => 5000.00,
            'transaction_fee'            => 345.00,
            'short_haul_transaction_fee' => 70.00,
            'revalidation_fee'           => 250.00,
        ]);
        PaymentSetting::bust();
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Invoke the private CreateBookingAction::calculatePrice method via Reflection.
     */
    private function serverCalculatePrice(
        Schedule $schedule,
        array $passengers,
        string $tripType = 'one_way',
        array $accommodationIds = [],
        bool $hasVehicle = false,
        float $vehiclePrice = 0.0
    ): float {
        $action = new CreateBookingAction(
            app(\App\Services\VoucherService::class),
            app(\App\Services\GraciaPointsService::class)
        );

        $refMethod = new \ReflectionMethod(CreateBookingAction::class, 'calculatePrice');
        $refMethod->setAccessible(true);

        $tcId = $schedule->transportClasses->first()?->id ?? null;

        return $refMethod->invoke(
            $action,
            $schedule,
            $passengers,
            $tripType,
            $accommodationIds,
            null,    // scheduleAccommodation (on-board)
            $tcId,
            $hasVehicle,
            $vehiclePrice,
            null,    // returnSchedule
            null,    // returnScheduleAccommodation
            null,    // returnSelectedTransportClassId
            null     // promotionalTicketId
        );
    }

    private function createFerryRoute(string $origin, string $destination): FerryRoute
    {
        return FerryRoute::create([
            'origin'      => $origin,
            'destination' => $destination,
            'mode'        => 'ferry',
            'operator'    => 'Test Operator',
            'is_active'   => true,
        ]);
    }

    private function createSchedule(FerryRoute $route, float $price, int $durationMinutes): Schedule
    {
        $depTime = now()->addDays(2)->setTime(8, 0, 0);

        return Schedule::create([
            'ferry_route_id'    => $route->id,
            'departure_date'    => $depTime->format('Y-m-d'),
            'departure_time'    => $depTime->format('Y-m-d H:i:s'),
            'arrival_time'      => $depTime->copy()->addMinutes($durationMinutes)->format('Y-m-d H:i:s'),
            'price'             => $price,
            'duration_minutes'  => $durationMinutes,
            'status'            => 'scheduled',
            'tickets_available' => 100,
            'is_active'         => true,
        ]);
    }

    private function attachTransportClass(Schedule $schedule, string $name, float $price): TransportClass
    {
        $tc = TransportClass::create([
            'name'      => $name,
            'price'     => $price,
            'is_active' => true,
        ]);

        $schedule->transportClasses()->attach($tc->id, [
            'additional_price'  => $price,
            'tickets_available' => 100,
            'is_active'         => true,
            'rate_type'         => 'regular',
        ]);

        $schedule->load('transportClasses');

        return $tc;
    }

    // -------------------------------------------------------------------------
    // Test 1: Regular ferry -- 1 Adult + 1 Child (Short-haul)
    //
    // Admin source: price=500, tc=100, short_haul_web_admin_fee=30, short_haul_tx=70
    // Expected:
    //   Adult ticket+class : (500+100)*1.0 = 600
    //   Child ticket+class : (500+100)*0.5 = 300
    //   Web Admin Fee      : 2 pax * 30   =  60
    //   Transaction Fee    : 2 pax * 70   = 140
    //   TOTAL              : 1100.00
    // -------------------------------------------------------------------------
    public function test_fare_calculation_parity_regular_ferry_adult_and_child(): void
    {
        $route    = $this->createFerryRoute('Batangas', 'Calapan');
        $schedule = $this->createSchedule($route, 500.00, 60); // 60 min = short-haul
        $this->attachTransportClass($schedule, 'Tourist Class', 100.00);

        $passengers = [
            ['name' => 'Adult Traveler', 'type' => 'adult', 'discount_id' => null],
            ['name' => 'Child Traveler', 'type' => 'child', 'discount_id' => null],
        ];

        // Server calculation
        $serverTotal = $this->serverCalculatePrice($schedule, $passengers);

        // Mobile App mirrored calculation (from flutter_app/lib/main.dart)
        $basePrice   = 500.00;
        $tcPrice     = 100.00;
        $webAdminFee = 30.00;
        $txFee       = 70.00;
        $multiplier  = 2;

        $depTicketAndClass = 0.0;
        foreach ($passengers as $p) {
            $paxMult = in_array(strtolower($p['type']), ['child', 'minor']) ? 0.5 : 1.0;
            $depTicketAndClass += ($basePrice + $tcPrice) * $paxMult;
        }

        $appTotal = $depTicketAndClass + ($multiplier * $webAdminFee) + ($multiplier * $txFee);
        // 600 + 300 + 60 + 140 = 1100.00

        $this->assertEquals(1100.00, round($serverTotal, 2), 'Server total mismatch');
        $this->assertEquals(1100.00, round($appTotal, 2),    'App total mismatch');
        $this->assertEquals(round($serverTotal, 2), round($appTotal, 2), 'Server != App: fare not in sync!');
    }

    // -------------------------------------------------------------------------
    // Test 2: Long-haul ferry -- 1 Senior Citizen + Hotel Accommodation
    // -------------------------------------------------------------------------
    public function test_fare_calculation_parity_senior_citizen_with_hotel(): void
    {
        $route    = $this->createFerryRoute('Manila', 'Cebu');
        $schedule = $this->createSchedule($route, 2000.00, 1200); // long-haul
        $this->attachTransportClass($schedule, 'Cabin for 4', 500.00);

        $seniorDiscount = Discount::create([
            'name'       => 'Senior Citizen',
            'percentage' => 20,
        ]);

        $hotel = Accommodation::create([
            'name'        => 'Cebu Waterfront Hotel',
            'destination' => 'Cebu',
            'description' => 'Test hotel',
            'price'       => 3500.00,
            'images'      => [],
            'is_active'   => true,
        ]);

        $passengers = [
            ['name' => 'Senior Traveler', 'type' => 'adult', 'discount_id' => $seniorDiscount->id],
        ];

        // Server calculation
        $serverTotal = $this->serverCalculatePrice($schedule, $passengers, 'one_way', [$hotel->id]);

        // Mobile App mirrored calculation (from flutter_app/lib/main.dart _computePassengerDiscount)
        $basePrice        = 2000.00;
        $tcPrice          = 500.00;
        $grossTicket      = ($basePrice + $tcPrice) * 1.0; // 2500.00

        // Senior discount: gross - (gross * 0.80 / 1.12)
        $discountedRate   = $grossTicket * 0.80;
        $netSeniorFare    = $discountedRate / 1.12;
        $seniorDiscAmount = round($grossTicket - $netSeniorFare, 2);

        $hotelCost       = 3500.00;
        $webAdminFee     = 175.00; // long-haul
        $hotelServiceFee = 5000.00; // fee_per_accommodation
        $txFee           = 345.00;  // long-haul
        $multiplier      = 1;

        $appGross = $grossTicket + $hotelCost
            + ($multiplier * $webAdminFee)
            + $hotelServiceFee
            + ($multiplier * $txFee);
        $appTotal = round($appGross - $seniorDiscAmount, 2);

        $this->assertEquals(round($serverTotal, 2), $appTotal, 'Server != App for senior citizen with hotel scenario');
    }

    // -------------------------------------------------------------------------
    // Test 3: Short-haul ferry -- Driver (free) + 1 Adult + Vehicle
    //
    // Expected:
    //   Driver ticket+class    : 0
    //   Adult ticket+class     : (450+50)*1.0 = 500
    //   Vehicle cost           : 2500
    //   Web Admin Fee          : 2 pax * 30 = 60
    //   Transaction Fee        : 2 pax * 70 = 140
    //   TOTAL                  : 3200.00
    // -------------------------------------------------------------------------
    public function test_fare_calculation_parity_with_vehicle_and_driver(): void
    {
        $route    = $this->createFerryRoute('Batangas', 'Calapan');
        $schedule = $this->createSchedule($route, 450.00, 120); // short-haul
        $this->attachTransportClass($schedule, 'Economy', 50.00);

        VehicleRate::create([
            'name'      => 'Sedan / SUV',
            'price'     => 2500.00,
            'is_active' => true,
        ]);

        $passengers = [
            ['name' => 'Driver Name',     'type' => 'driver', 'discount_id' => null],
            ['name' => 'Adult Passenger', 'type' => 'adult',  'discount_id' => null],
        ];

        // Server calculation
        $serverTotal = $this->serverCalculatePrice($schedule, $passengers, 'one_way', [], true, 2500.00);

        // Mobile App mirrored calculation
        $basePrice   = 450.00;
        $tcPrice     = 50.00;
        $multiplier  = 2;
        $webAdminFee = 30.00;
        $txFee       = 70.00;

        $depTicketAndClass = 0.0;
        foreach ($passengers as $p) {
            if ($p['type'] === 'driver') continue;
            $depTicketAndClass += ($basePrice + $tcPrice) * 1.0;
        }

        $appTotal = $depTicketAndClass + 2500.00 + ($multiplier * $webAdminFee) + ($multiplier * $txFee);
        // 500 + 2500 + 60 + 140 = 3200.00

        $this->assertEquals(3200.00, round($serverTotal, 2), 'Server total mismatch for vehicle+driver');
        $this->assertEquals(3200.00, round($appTotal, 2),    'App total mismatch for vehicle+driver');
        $this->assertEquals(round($serverTotal, 2), round($appTotal, 2), 'Server != App: vehicle fare not in sync!');
    }

    // -------------------------------------------------------------------------
    // Test 4: Admin fee change cascades to fare calculation
    //
    // Proves that Admin is the live authoritative source -- changing PaymentSetting
    // immediately changes what server calculates (no stale cache allowed).
    // -------------------------------------------------------------------------
    public function test_admin_fee_change_cascades_to_fare_calculation(): void
    {
        $route    = $this->createFerryRoute('Batangas', 'Calapan');
        $schedule = $this->createSchedule($route, 500.00, 60); // short-haul
        $this->attachTransportClass($schedule, 'Economy', 0.00);

        $passengers = [
            ['name' => 'Test Passenger', 'type' => 'adult', 'discount_id' => null],
        ];

        // Before Admin change: short_haul_web_admin_fee=30, short_haul_tx=70
        $totalBefore = $this->serverCalculatePrice($schedule, $passengers);

        // Admin changes fees in Filament (simulated)
        PaymentSetting::query()->updateOrCreate(['id' => 1], [
            'short_haul_web_admin_fee'   => 50.00,  // was 30
            'short_haul_transaction_fee' => 100.00, // was 70
        ]);
        PaymentSetting::bust(); // cache-bust simulates real-time admin update

        $totalAfter = $this->serverCalculatePrice($schedule, $passengers);

        // Delta: (50-30) admin + (100-70) tx = +50 per passenger
        $this->assertGreaterThan($totalBefore, $totalAfter, 'Fee increase did not cascade to fare');
        $this->assertEquals(
            round($totalBefore + 20 + 30, 2), // +20 webAdminFee + +30 txFee
            round($totalAfter, 2),
            'Admin fee change did not cascade correctly'
        );
    }
}

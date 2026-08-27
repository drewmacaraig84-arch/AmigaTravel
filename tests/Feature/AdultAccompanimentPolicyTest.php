<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Booking;
use App\Models\Passenger;
use App\Models\Schedule;
use App\Models\FerryRoute;
use App\Models\PaymentSetting;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdultAccompanimentPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        PaymentSetting::firstOrCreate([], [
            'admin_fee_long_haul' => 50,
            'admin_fee_short_haul' => 25,
            'transaction_fee_long_haul' => 20,
            'transaction_fee_short_haul' => 10,
        ]);
    }

    protected function createBookingWithParty(string $mode, array $passengersConfig): Booking
    {
        $route = FerryRoute::create([
            'origin' => 'Manila',
            'destination' => $mode === 'airline' ? 'Cebu' : 'Coron',
            'mode' => $mode,
            'operator' => $mode === 'airline' ? 'Philippine Airlines' : '2GO Travel',
            'is_active' => true,
        ]);

        $depTime = now()->addDays(5)->setTime(10, 0, 0);

        $schedule = Schedule::create([
            'ferry_route_id' => $route->id,
            'departure_date' => $depTime->format('Y-m-d'),
            'departure_time' => $depTime->format('Y-m-d H:i:s'),
            'arrival_time' => $depTime->copy()->addHours(2)->format('Y-m-d H:i:s'),
            'price' => 1500,
            'duration_minutes' => 120,
            'status' => 'scheduled',
            'tickets_available' => 50,
            'is_active' => true,
        ]);

        $booking = Booking::create([
            'transaction_number' => 'AGT-' . strtoupper(uniqid()),
            'client_name' => 'John Doe',
            'client_email' => 'john@example.com',
            'client_phone' => '09171234567',
            'origin' => $route->origin,
            'destination' => $route->destination,
            'departure_date' => $depTime->format('Y-m-d'),
            'schedule_id' => $schedule->id,
            'schedule_departure_time' => $depTime->format('Y-m-d H:i:s'),
            'schedule_arrival_time' => $depTime->copy()->addHours(2)->format('Y-m-d H:i:s'),
            'schedule_price' => 1500,
            'status' => Booking::STATUS_CONFIRMED,
            'total_price' => count($passengersConfig) * 1500,
        ]);

        Transaction::create([
            'booking_id' => $booking->id,
            'payment_status' => 'paid',
            'total_amount' => $booking->total_price,
            'amount_paid' => $booking->total_price,
        ]);

        foreach ($passengersConfig as $index => $cfg) {
            Passenger::create([
                'booking_id' => $booking->id,
                'item_number' => $index + 1,
                'name' => $cfg['name'],
                'type' => $cfg['type'],
                'birthdate' => $cfg['birthdate'] ?? null,
                'status' => Passenger::STATUS_CONFIRMED,
                'base_fare' => 1500,
            ]);
        }

        return $booking->fresh(['passengers', 'schedule.ferryRoute', 'transaction']);
    }

    public function test_single_adult_with_non_adult_detected_correctly_in_ferry_and_airline_modes(): void
    {
        // 1 Adult + 1 Child in Ferry mode
        $ferryBooking = $this->createBookingWithParty('ferry', [
            ['name' => 'Adult Juan', 'type' => 'adult', 'birthdate' => '1990-01-01'],
            ['name' => 'Child Pedro', 'type' => 'child', 'birthdate' => '2018-05-10'],
        ]);

        $this->assertTrue($ferryBooking->hasSingleAdultWithNonAdults());

        // 1 Adult + 1 Infant in Airline mode
        $airlineBooking = $this->createBookingWithParty('airline', [
            ['name' => 'Adult Maria', 'type' => 'adult', 'birthdate' => '1992-03-15'],
            ['name' => 'Infant Ana', 'type' => 'infant', 'birthdate' => '2025-01-01'],
        ]);

        $this->assertTrue($airlineBooking->hasSingleAdultWithNonAdults());
    }

    public function test_single_adult_cannot_cancel_or_refund_alone(): void
    {
        $booking = $this->createBookingWithParty('ferry', [
            ['name' => 'Adult Juan', 'type' => 'adult', 'birthdate' => '1990-01-01'],
            ['name' => 'Child Pedro', 'type' => 'child', 'birthdate' => '2018-05-10'],
        ]);

        // Attempting to cancel only Adult (item 1)
        $policyAdultOnly = $booking->validatePassengerPartyPolicy([1], 'cancel');
        $this->assertFalse($policyAdultOnly['valid']);
        $this->assertStringContainsString('only one adult accompanying minor/child', $policyAdultOnly['error']);

        // Attempting to cancel only Child (item 2) — single-adult all-or-nothing rule fires first
        $policyChildOnly = $booking->validatePassengerPartyPolicy([2], 'cancel');
        $this->assertFalse($policyChildOnly['valid']);
        $this->assertStringContainsString('only one adult accompanying minor/child', $policyChildOnly['error']);

        // Cancelling both together succeeds
        $policyBoth = $booking->validatePassengerPartyPolicy([1, 2], 'cancel');
        $this->assertTrue($policyBoth['valid']);
        $this->assertNull($policyBoth['error']);
    }

    public function test_single_adult_cannot_rebook_alone(): void
    {
        $booking = $this->createBookingWithParty('airline', [
            ['name' => 'Adult Maria', 'type' => 'adult', 'birthdate' => '1992-03-15'],
            ['name' => 'Child Lucas', 'type' => 'child', 'birthdate' => '2019-06-20'],
        ]);

        // Attempting to rebook only Adult (item 1)
        $policyAdultOnly = $booking->validatePassengerPartyPolicy([1], 'rebook');
        $this->assertFalse($policyAdultOnly['valid']);
        $this->assertStringContainsString('only one adult accompanying minor/child', $policyAdultOnly['error']);

        // Attempting to rebook only Child (item 2)
        $policyChildOnly = $booking->validatePassengerPartyPolicy([2], 'rebook');
        $this->assertFalse($policyChildOnly['valid']);

        // Rebooking both together succeeds
        $policyBoth = $booking->validatePassengerPartyPolicy([1, 2], 'rebook');
        $this->assertTrue($policyBoth['valid']);
        $this->assertNull($policyBoth['error']);
    }

    public function test_multi_adult_party_allows_partial_action_only_if_an_adult_remains(): void
    {
        // 2 Adults + 1 Child
        $booking = $this->createBookingWithParty('ferry', [
            ['name' => 'Adult 1', 'type' => 'adult', 'birthdate' => '1985-01-01'],
            ['name' => 'Adult 2', 'type' => 'adult', 'birthdate' => '1988-02-02'],
            ['name' => 'Child 1', 'type' => 'child', 'birthdate' => '2019-03-03'],
        ]);

        $this->assertFalse($booking->hasSingleAdultWithNonAdults());

        // Selecting Adult 1 only ([1]): Valid, because Adult 2 remains to accompany Child 1
        $policyAdult1 = $booking->validatePassengerPartyPolicy([1], 'cancel');
        $this->assertTrue($policyAdult1['valid']);

        // Selecting Adult 1 and Adult 2 ([1, 2]): Invalid, leaves Child 1 alone on booking with 0 adults
        $policyBothAdults = $booking->validatePassengerPartyPolicy([1, 2], 'cancel');
        $this->assertFalse($policyBothAdults['valid']);
        $this->assertStringContainsString('cannot remain on a booking without an adult', $policyBothAdults['error']);

        // Selecting Child 1 only ([3]): Invalid, non-adult cannot act alone
        $policyChildOnly = $booking->validatePassengerPartyPolicy([3], 'cancel');
        $this->assertFalse($policyChildOnly['valid']);
        $this->assertStringContainsString('without an accompanying adult', $policyChildOnly['error']);

        // Selecting Adult 1 and Child 1 ([1, 3]): Valid, Adult 2 remains
        $policyAdultAndChild = $booking->validatePassengerPartyPolicy([1, 3], 'rebook');
        $this->assertTrue($policyAdultAndChild['valid']);
    }

    public function test_api_cancel_endpoint_blocks_single_adult_split_party(): void
    {
        $booking = $this->createBookingWithParty('ferry', [
            ['name' => 'Adult Juan', 'type' => 'adult', 'birthdate' => '1990-01-01'],
            ['name' => 'Child Pedro', 'type' => 'child', 'birthdate' => '2018-05-10'],
        ]);

        // Trying to start cancellation for only Item 1 (Adult)
        $response = $this->postJson("/api/bookings/{$booking->id}/cancel", [
            'email' => $booking->client_email,
            'action' => 'start',
            'passenger_items' => [1],
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
            ])
            ->assertJsonPath('message', fn ($msg) => str_contains($msg, 'only one adult'));
    }

    public function test_api_rebook_calculation_endpoint_blocks_single_adult_split_party(): void
    {
        $booking = $this->createBookingWithParty('airline', [
            ['name' => 'Adult Maria', 'type' => 'adult', 'birthdate' => '1992-03-15'],
            ['name' => 'Child Lucas', 'type' => 'child', 'birthdate' => '2019-06-20'],
        ]);

        $response = $this->postJson("/api/bookings/{$booking->id}/rebook-calculation", [
            'email' => $booking->client_email,
            'dep_schedule_id' => $booking->schedule_id,
            'is_round_trip' => false,
            'passenger_items' => [1],
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
            ])
            ->assertJsonPath('message', fn ($msg) => str_contains($msg, 'only one adult'));
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Schedule;
use App\Models\FerryRoute;
use App\Models\TransportClass;
use App\Models\Discount;
use App\Models\PaymentSetting;
use App\Actions\Bookings\CreateBookingAction;
use App\Livewire\BookingForm;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

class PassengerFarePricingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forget('payment_settings:current');
        PaymentSetting::updateOrCreate(['id' => 1], [
            'web_admin_fee' => 0,
            'short_haul_web_admin_fee' => 0,
            'fee_per_accommodation' => 0,
            'transaction_fee' => 0,
            'short_haul_transaction_fee' => 0,
        ]);
        Cache::forget('payment_settings:current');
    }

    public function test_ferry_child_gets_50_percent_of_base_fare_and_transport_class(): void
    {
        $route = FerryRoute::create([
            'origin' => 'Batangas',
            'destination' => 'Calapan',
            'mode' => 'ferry',
            'operator' => 'Montenegro',
            'is_active' => true,
        ]);

        $depTime = now()->addDays(2)->setTime(8, 0, 0);

        $schedule = Schedule::create([
            'ferry_route_id' => $route->id,
            'departure_date' => $depTime->format('Y-m-d'),
            'departure_time' => $depTime->format('Y-m-d H:i:s'),
            'arrival_time' => $depTime->copy()->addHours(2)->format('Y-m-d H:i:s'),
            'price' => 500,
            'duration_minutes' => 120,
            'status' => 'scheduled',
            'tickets_available' => 50,
            'is_active' => true,
        ]);

        $tc = TransportClass::create([
            'name' => 'Economy',
            'price' => 100,
            'is_active' => true,
        ]);

        $schedule->transportClasses()->attach($tc->id, [
            'additional_price' => 100,
            'tickets_available' => 50,
            'is_active' => true,
        ]);

        $action = app(CreateBookingAction::class);
        $booking = $action->execute([
            'schedule_id' => $schedule->id,
            'origin' => 'Batangas',
            'destination' => 'Calapan',
            'departure_date' => $depTime->format('Y-m-d'),
            'trip_type' => 'one_way',
            'client_name' => 'Test User',
            'client_email' => 'test@example.com',
            'selected_transport_class_id' => $tc->id,
            'passengers' => [
                ['name' => 'Adult Pax', 'type' => 'adult', 'birthdate' => '1990-01-01', 'discount_id' => null],
                ['name' => 'Child Pax', 'type' => 'child', 'birthdate' => '2018-01-01', 'discount_id' => null],
            ],
        ]);

        // Base + Transport Class = 500 + 100 = 600
        // Adult = 600
        // Child = 50% of 600 = 300
        // Total = 900
        $this->assertEquals(900.0, floatval($booking->total_price));

        $pax = $booking->passengers()->orderBy('item_number')->get();
        $this->assertEquals(600.0, floatval($pax[0]->fare_amount));
        $this->assertEquals(300.0, floatval($pax[1]->fare_amount));
    }

    public function test_airline_minor_child_infant_get_50_percent_and_infant_discounts_are_disabled(): void
    {
        $route = FerryRoute::create([
            'origin' => 'Manila',
            'destination' => 'Cebu',
            'mode' => 'airline',
            'operator' => 'Philippine Airlines',
            'is_active' => true,
        ]);

        $depTime = now()->addDays(3)->setTime(10, 0, 0);

        $schedule = Schedule::create([
            'ferry_route_id' => $route->id,
            'departure_date' => $depTime->format('Y-m-d'),
            'departure_time' => $depTime->format('Y-m-d H:i:s'),
            'arrival_time' => $depTime->copy()->addHours(1)->format('Y-m-d H:i:s'),
            'price' => 2000,
            'duration_minutes' => 60,
            'status' => 'scheduled',
            'tickets_available' => 50,
            'is_active' => true,
        ]);

        $tc = TransportClass::create([
            'name' => 'Economy Plus',
            'price' => 400,
            'is_active' => true,
        ]);

        $schedule->transportClasses()->attach($tc->id, [
            'additional_price' => 400,
            'tickets_available' => 50,
            'is_active' => true,
        ]);

        $seniorDiscount = Discount::create([
            'name' => 'Senior Citizen',
            'percentage' => 20,
            'is_active' => true,
        ]);

        $action = app(CreateBookingAction::class);
        $booking = $action->execute([
            'schedule_id' => $schedule->id,
            'origin' => 'Manila',
            'destination' => 'Cebu',
            'departure_date' => $depTime->format('Y-m-d'),
            'trip_type' => 'one_way',
            'client_name' => 'Test Family',
            'client_email' => 'family@example.com',
            'selected_transport_class_id' => $tc->id,
            'passengers' => [
                ['name' => 'Adult Pax', 'type' => 'adult', 'birthdate' => '1990-01-01', 'discount_id' => null],
                ['name' => 'Child Pax', 'type' => 'child', 'birthdate' => '2016-01-01', 'discount_id' => null],
                ['name' => 'Minor Pax', 'type' => 'minor', 'birthdate' => '2012-01-01', 'discount_id' => null],
                // Infant attempting to apply Senior discount (must be ignored)
                ['name' => 'Infant Pax', 'type' => 'infant', 'birthdate' => '2024-01-01', 'discount_id' => $seniorDiscount->id],
            ],
        ]);

        // Base + Transport Class = 2000 + 400 = 2400
        // Adult = 2400
        // Child = 50% of 2400 = 1200
        // Minor = 50% of 2400 = 1200
        // Infant = 50% of 2400 = 1200 (discount disabled, discount_id cleared)
        // Total = 2400 + 1200 + 1200 + 1200 = 6000
        $this->assertEquals(6000.0, floatval($booking->total_price));

        $pax = $booking->passengers()->orderBy('item_number')->get();
        $this->assertEquals(2400.0, floatval($pax[0]->fare_amount));
        $this->assertEquals(1200.0, floatval($pax[1]->fare_amount));
        $this->assertEquals(1200.0, floatval($pax[2]->fare_amount));
        $this->assertEquals(1200.0, floatval($pax[3]->fare_amount));
        $this->assertNull($pax[3]->discount_id);
    }

    public function test_booking_form_livewire_calculates_50_percent_pricing_correctly(): void
    {
        $route = FerryRoute::create([
            'origin' => 'Manila',
            'destination' => 'Cebu',
            'mode' => 'airline',
            'operator' => 'Philippine Airlines',
            'is_active' => true,
        ]);

        $depTime = now()->addDays(3)->setTime(10, 0, 0);

        $schedule = Schedule::create([
            'ferry_route_id' => $route->id,
            'departure_date' => $depTime->format('Y-m-d'),
            'departure_time' => $depTime->format('Y-m-d H:i:s'),
            'arrival_time' => $depTime->copy()->addHours(1)->format('Y-m-d H:i:s'),
            'price' => 2000,
            'duration_minutes' => 60,
            'status' => 'scheduled',
            'tickets_available' => 50,
            'is_active' => true,
        ]);

        $tc = TransportClass::create([
            'name' => 'Economy Plus',
            'price' => 400,
            'is_active' => true,
        ]);

        $schedule->transportClasses()->attach($tc->id, [
            'additional_price' => 400,
            'tickets_available' => 50,
            'is_active' => true,
        ]);

        $seniorDiscount = Discount::create([
            'name' => 'Senior Citizen',
            'percentage' => 20,
            'is_active' => true,
        ]);

        $component = Livewire::test(BookingForm::class)
            ->set('mode', 'airline')
            ->set('origin', 'Manila')
            ->set('destination', 'Cebu')
            ->set('departure_date', $depTime->format('Y-m-d'))
            ->set('trip_type', 'one_way')
            ->set('selected_schedule_id', $schedule->id)
            ->set('selected_transport_class_id', $tc->id)
            ->set('adults', 1)
            ->set('children', 1)
            ->set('minors', 1)
            ->set('infants', 1)
            ->set('passengers', [
                ['name' => 'Adult Pax', 'first_name' => 'Adult', 'last_name' => 'Pax', 'type' => 'adult', 'birthdate' => '1990-01-01', 'discount_id' => null],
                ['name' => 'Child Pax', 'first_name' => 'Child', 'last_name' => 'Pax', 'type' => 'child', 'birthdate' => '2016-01-01', 'discount_id' => null],
                ['name' => 'Minor Pax', 'first_name' => 'Minor', 'last_name' => 'Pax', 'type' => 'minor', 'birthdate' => '2012-01-01', 'discount_id' => null],
                // Infant with discount attempted (must be ignored)
                ['name' => 'Infant Pax', 'first_name' => 'Infant', 'last_name' => 'Pax', 'type' => 'infant', 'birthdate' => '2024-01-01', 'discount_id' => $seniorDiscount->id],
            ]);

        // Base + Transport Class = 2400
        // Adult (2400) + Child (1200) + Minor (1200) + Infant (1200) = 6000
        $this->assertEquals(6000.0, $component->instance()->calculateTotalPrice());

        $breakdown = $component->instance()->getPriceBreakdown();
        $this->assertEquals(6000.0, $breakdown['departure_ticket']);
    }
}


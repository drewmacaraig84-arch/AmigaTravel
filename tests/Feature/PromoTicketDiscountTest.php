<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Schedule;
use App\Models\FerryRoute;
use App\Models\TransportClass;
use App\Models\PromotionalTicket;
use App\Models\Discount;
use App\Models\PaymentSetting;
use App\Livewire\BookingForm;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Actions\Bookings\CreateBookingAction;

class PromoTicketDiscountTest extends TestCase
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

    protected function createAirlineSchedule(): Schedule
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
            'price' => 2500,
            'duration_minutes' => 60,
            'status' => 'scheduled',
            'tickets_available' => 50,
            'is_active' => true,
        ]);

        $tc = TransportClass::create([
            'name' => 'Economy Class',
            'price' => 0,
            'is_active' => true,
        ]);

        $schedule->transportClasses()->attach($tc->id, [
            'additional_price' => 0,
            'tickets_available' => 50,
            'is_active' => true,
        ]);

        return $schedule;
    }

    public function test_promotional_ticket_allows_student_senior_pwd_discounts_in_livewire(): void
    {
        $schedule = $this->createAirlineSchedule();
        $depDate = $schedule->departure_time->format('Y-m-d');

        $promoTicket = PromotionalTicket::create([
            'schedule_id' => $schedule->id,
            'promo_price' => 1000.00,
            'quantity_available' => 10,
            'quantity_sold' => 0,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(30),
            'is_active' => true,
        ]);

        $discount = Discount::create([
            'name' => 'Senior Citizen',
            'percentage' => 20.00,
        ]);

        $test = Livewire::test(BookingForm::class)
            ->set('step', 5)
            ->set('mode', 'airline')
            ->set('origin', 'Manila')
            ->set('destination', 'Cebu')
            ->set('departure_date', $depDate)
            ->set('selected_schedule_id', $schedule->id)
            ->set('selected_transport_class_id', $schedule->transportClasses->first()->id)
            ->set('adults', 1)
            ->set('passengers', [
                [
                    'type' => 'adult',
                    'first_name' => 'Juan',
                    'middle_name' => 'A',
                    'last_name' => 'Dela Cruz',
                    'name' => 'Juan A Dela Cruz',
                    'birthdate' => '1960-01-01',
                    'discount_id' => $discount->id,
                    'senior_osca_number' => 'OSCA-12345',
                    'use_promo' => true,
                ],
            ])
            ->set('client_name', 'Juan Dela Cruz')
            ->set('client_email', 'juan@example.com')
            ->set('client_phone', '+63 912 345 6789')
            ->set('hasAcceptedTerms', false)
            ->call('submit');

        $test->assertHasNoErrors()
            ->assertSet('showTermsModal', true);

        // Accept terms and privacy and proceed
        $test->set('hasAcceptedTerms', true)
            ->set('hasAcceptedPrivacy', true)
            ->call('confirmTermsAndContinue')
            ->assertRedirect();

        $passenger = \App\Models\Passenger::latest('id')->first();
        $this->assertNotNull($passenger);
        $this->assertEquals($promoTicket->id, $passenger->promotional_ticket_id);
        $this->assertEquals($discount->id, $passenger->discount_id);
        $this->assertTrue((bool)$passenger->is_promo);
        $this->assertEquals(1000.00, (float)$passenger->fare_amount);
        $this->assertEquals(200.00, (float)$passenger->discount_amount); // 20% of 1000
    }

    public function test_create_booking_action_supports_passenger_discounts_on_promo_tickets(): void
    {
        $schedule = $this->createAirlineSchedule();

        $promoTicket = PromotionalTicket::create([
            'schedule_id' => $schedule->id,
            'promo_price' => 800.00,
            'quantity_available' => 5,
            'quantity_sold' => 0,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(30),
            'is_active' => true,
        ]);

        $discount = Discount::create([
            'name' => 'Student',
            'percentage' => 20.00,
        ]);

        $data = [
            'trip_type' => 'one_way',
            'origin' => 'Manila',
            'destination' => 'Cebu',
            'departure_date' => $schedule->departure_time->format('Y-m-d'),
            'schedule_id' => $schedule->id,
            'promotional_ticket_id' => $promoTicket->id,
            'client_name' => 'Maria Santos',
            'client_email' => 'maria@example.com',
            'client_phone' => '09171234567',
            'payment_method' => 'gcash',
            'passengers' => [
                [
                    'type' => 'adult',
                    'name' => 'Maria Santos',
                    'birthdate' => '2004-03-12',
                    'discount_id' => $discount->id,
                    'id_number' => 'STUDENT-2024',
                ],
            ],
        ];

        $action = app(CreateBookingAction::class);
        $booking = $action->execute($data);

        $this->assertNotNull($booking);
        $passenger = $booking->passengers->first();
        $this->assertEquals($discount->id, $passenger->discount_id);
        $this->assertGreaterThan(0, (float)$passenger->discount_amount);
    }
}

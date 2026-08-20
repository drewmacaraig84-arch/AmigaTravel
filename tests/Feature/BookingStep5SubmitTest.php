<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Schedule;
use App\Models\FerryRoute;
use App\Models\TransportClass;
use App\Livewire\BookingForm;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookingStep5SubmitTest extends TestCase
{
    use RefreshDatabase;

    protected function createTestSchedule(): Schedule
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
            'additional_price' => 0,
            'tickets_available' => 50,
            'is_active' => true,
        ]);

        return $schedule;
    }

    public function test_step5_submits_cleanly_with_valid_contact_number_and_opens_terms_modal(): void
    {
        $schedule = $this->createTestSchedule();
        $depDate = $schedule->departure_time->format('Y-m-d');

        $test = Livewire::test(BookingForm::class)
            ->set('step', 5)
            ->set('mode', 'ferry')
            ->set('origin', 'Batangas')
            ->set('destination', 'Calapan')
            ->set('departure_date', $depDate)
            ->set('selected_schedule_id', $schedule->id)
            ->set('selected_transport_class_id', $schedule->transportClasses->first()->id)
            ->set('adults', 1)
            ->set('passengers', [
                [
                    'type' => 'adult',
                    'first_name' => 'John',
                    'middle_name' => 'A',
                    'last_name' => 'Doe',
                    'name' => 'John A Doe',
                    'birthdate' => '1995-05-15',
                    'discount_id' => null,
                ]
            ])
            ->set('client_name', 'John Doe')
            ->set('client_email', 'john@example.com')
            ->set('client_phone', '+63 912 345 6789')
            ->set('hasAcceptedTerms', false)
            ->call('submit');

        if ($test->errors()->isNotEmpty()) {
            fwrite(STDERR, "\nERRORS: " . json_encode($test->errors()->toArray()) . "\n");
        }

        $test->assertHasNoErrors()
            ->assertSet('showTermsModal', true);
    }

    public function test_step5_fails_validation_when_client_phone_is_missing(): void
    {
        $schedule = $this->createTestSchedule();
        $depDate = $schedule->departure_time->format('Y-m-d');

        Livewire::test(BookingForm::class)
            ->set('step', 5)
            ->set('mode', 'ferry')
            ->set('origin', 'Batangas')
            ->set('destination', 'Calapan')
            ->set('departure_date', $depDate)
            ->set('selected_schedule_id', $schedule->id)
            ->set('selected_transport_class_id', $schedule->transportClasses->first()->id)
            ->set('adults', 1)
            ->set('passengers', [
                [
                    'type' => 'adult',
                    'first_name' => 'John',
                    'middle_name' => 'A',
                    'last_name' => 'Doe',
                    'name' => 'John A Doe',
                    'birthdate' => '1995-05-15',
                    'discount_id' => null,
                ]
            ])
            ->set('client_name', 'John Doe')
            ->set('client_email', 'john@example.com')
            ->set('client_phone', '')
            ->set('hasAcceptedTerms', false)
            ->call('submit')
            ->assertHasErrors(['client_phone']);
    }
}

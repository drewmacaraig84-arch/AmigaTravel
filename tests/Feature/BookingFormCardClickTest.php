<?php

namespace Tests\Feature;

use Livewire\Livewire;
use App\Livewire\BookingForm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingFormCardClickTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_form_pre_selects_mode_and_operator_from_card_click(): void
    {
        Livewire::withQueryParams([
            'operator' => '2GO',
            'trip_type' => 'one_way',
            'mode' => 'ferry',
        ])
        ->test(BookingForm::class)
        ->assertSet('mode', 'ferry')
        ->assertSet('operator', '2GO')
        ->assertSet('isOperatorPreselected', true)
        ->assertSet('isModePreselected', true)
        ->assertSet('showOperatorConfirmation', true);
    }

    public function test_booking_form_disables_mode_when_pre_selected(): void
    {
        $response = $this->get('/book/new?operator=Cebu%20Pacific&trip_type=one_way&mode=airline');

        $response->assertStatus(200);
        $response->assertSeeText('Pre-selected from your booking link', false);
    }

    public function test_booking_form_shows_confirmation_modal(): void
    {
        Livewire::withQueryParams([
            'operator' => 'Starlite',
            'trip_type' => 'one_way',
            'mode' => 'ferry',
        ])
        ->test(BookingForm::class)
        ->assertSet('showOperatorConfirmation', true);
    }
}

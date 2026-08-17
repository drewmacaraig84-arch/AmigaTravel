<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PassengerTypeValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_airline_passenger_types_are_allowed_in_voucher_validation(): void
    {
        $response = $this->postJson('/api/vouchers/validate', [
            'voucher_code' => 'INVALID',
            'origin' => 'Manila',
            'destination' => 'Cebu',
            'trip_type' => 'one_way',
            'client_email' => 'traveler@example.com',
            'passengers' => [
                ['type' => 'adult', 'discount_id' => null],
                ['type' => 'child', 'discount_id' => null],
                ['type' => 'minor', 'discount_id' => null],
                ['type' => 'infant', 'discount_id' => null],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Invalid voucher code');
    }
}

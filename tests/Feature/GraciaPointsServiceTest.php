<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\GraciaEarningRule;
use App\Models\GraciaUserBalance;
use App\Models\User;
use App\Services\GraciaPointsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GraciaPointsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_earns_points_for_booking()
    {
        $user = User::factory()->create(['is_app_user' => true]);
        
        $rule = GraciaEarningRule::create([
            'name' => 'Default Rule',
            'spend_threshold_centavos' => 100000, // 1000 PHP
            'points_awarded' => 5,
            'is_active' => true,
        ]);

        \App\Models\PaymentSetting::firstOrCreate(['id' => 1], [
            'web_admin_fee' => 0,
            'transaction_fee' => 0,
        ]);
        \App\Models\PaymentSetting::bust();

        $booking = Booking::create([
            'user_id' => $user->id,
            'transaction_number' => 'TEST-123',
            'origin' => 'Origin',
            'destination' => 'Dest',
            'departure_date' => now(),
            'trip_type' => 'one-way',
            'client_name' => 'Test',
            'client_email' => 'test@test.com',
            'passengers' => [['name' => 'Test', 'age' => 30, 'type' => 'adult', 'gender' => 'Male']],
            'total_price' => 2500, // 2500 PHP -> 250000 centavos
            'status' => 'confirmed',
        ]);

        $service = app(GraciaPointsService::class);
        $service->awardPointsForBooking($booking);

        $balance = GraciaUserBalance::where('user_id', $user->id)->first();
        
        $this->assertNotNull($balance);
        $this->assertEquals(10, $balance->current_points); // 2000 PHP = 10 pts
        $this->assertEquals(50000, $balance->unconverted_spend_centavos); // 500 PHP left
    }

    public function test_reversing_booking()
    {
        $user = User::factory()->create(['is_app_user' => true]);
        
        $rule = GraciaEarningRule::create([
            'name' => 'Default Rule',
            'spend_threshold_centavos' => 100000,
            'points_awarded' => 5,
            'is_active' => true,
        ]);

        \App\Models\PaymentSetting::firstOrCreate(['id' => 1], [
            'web_admin_fee' => 0,
            'transaction_fee' => 0,
        ]);
        \App\Models\PaymentSetting::bust();

        $booking = Booking::create([
            'user_id' => $user->id,
            'transaction_number' => 'TEST-124',
            'origin' => 'Origin',
            'destination' => 'Dest',
            'departure_date' => now(),
            'trip_type' => 'one-way',
            'client_name' => 'Test',
            'client_email' => 'test@test.com',
            'passengers' => [['name' => 'Test', 'age' => 30, 'type' => 'adult', 'gender' => 'Male']],
            'total_price' => 1500, // 1500 PHP
            'status' => 'confirmed',
        ]);

        $service = app(GraciaPointsService::class);
        
        // Give 500 PHP initial unconverted
        GraciaUserBalance::create([
            'user_id' => $user->id,
            'current_points' => 0,
            'unconverted_spend_centavos' => 50000,
        ]);

        $service->awardPointsForBooking($booking);

        $balance = $user->graciaBalance()->first();
        $this->assertEquals(10, $balance->current_points); // 500 + 1500 = 2000 => 10 pts
        $this->assertEquals(0, $balance->unconverted_spend_centavos); // 0 left

        // Reverse
        $service->reversePointsForBooking($booking);

        $balance = $user->graciaBalance()->first();
        $this->assertEquals(0, $balance->current_points); // Reversed 10 pts
        // The original carry-over was 500 PHP. Reversing 1500 PHP worth of points
        // The calculation: Reversed 10 pts = 2000 PHP worth of spend reversed.
        // We added back (2000 - 1500) = +500 to unconverted.
        $this->assertEquals(50000, $balance->unconverted_spend_centavos);
    }
}

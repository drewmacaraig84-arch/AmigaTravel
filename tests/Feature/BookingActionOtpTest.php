<?php

namespace Tests\Feature;

use App\Livewire\BookingLookup;
use App\Mail\BookingActionOtp;
use App\Models\Booking;
use App\Models\FerryRoute;
use App\Models\Passenger;
use App\Models\PaymentSetting;
use App\Models\Schedule;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class BookingActionOtpTest extends TestCase
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

        Storage::fake('public');
    }

    protected function createTestBooking(): Booking
    {
        $route = FerryRoute::create([
            'origin' => 'Batangas',
            'destination' => 'Calapan',
            'mode' => 'ferry',
            'operator' => 'Starlite',
            'is_active' => true,
        ]);

        $depTime = now()->addDays(3)->setTime(8, 0, 0);

        $schedule = Schedule::create([
            'ferry_route_id' => $route->id,
            'departure_date' => $depTime->format('Y-m-d'),
            'departure_time' => $depTime->format('Y-m-d H:i:s'),
            'arrival_time' => $depTime->copy()->addHours(2)->format('Y-m-d H:i:s'),
            'price' => 800,
            'is_active' => true,
        ]);

        $booking = Booking::create([
            'transaction_number' => 'AGT-OTP-' . strtoupper(uniqid()),
            'client_name' => 'Juan Dela Cruz',
            'client_email' => 'juan.delacruz@example.com',
            'client_phone' => '09171234567',
            'origin' => 'Batangas',
            'destination' => 'Calapan',
            'departure_date' => $depTime->format('Y-m-d'),
            'schedule_id' => $schedule->id,
            'schedule_price' => 800,
            'total_price' => 800,
            'status' => 'confirmed',
            'created_at' => now()->subMinutes(10), // past 5-min grace window
        ]);

        Transaction::create([
            'booking_id' => $booking->id,
            'amount' => 800,
            'payment_status' => 'paid',
            'payment_method' => 'GCash',
        ]);

        Passenger::create([
            'booking_id' => $booking->id,
            'item_number' => 1,
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'type' => 'adult',
            'birthdate' => '1990-01-01',
            'price' => 800,
            'status' => 'confirmed',
        ]);

        return $booking->fresh(['passengers', 'transaction']);
    }

    public function test_cancellation_triggers_otp_and_does_not_commit_until_verified(): void
    {
        Mail::fake();

        $booking = $this->createTestBooking();

        $component = Livewire::test(BookingLookup::class)
            ->set('transaction_number', $booking->transaction_number)
            ->call('search')
            ->call('requestCancellation')
            ->set('refund_method', 'GCash')
            ->set('refund_account_number', '09171234567')
            ->set('refund_account_name', 'Juan Dela Cruz')
            ->call('confirmCancellation');

        // Verify OTP modal is shown
        $component->assertSet('showOtpModal', true);
        $component->assertSet('otpAction', 'cancellation');

        // Verify email was dispatched
        Mail::assertSent(BookingActionOtp::class, function ($mail) use ($booking) {
            return $mail->hasTo($booking->client_email) && strlen($mail->otp) === 6;
        });

        // Verify booking has NOT been cancelled yet
        $booking->refresh();
        $this->assertEquals('confirmed', $booking->status);

        // Test invalid OTP
        $component->set('otpCode', '000000')
            ->call('verifyActionOtp')
            ->assertSet('showOtpModal', true)
            ->assertSet('otpError', 'Invalid verification code. Please check your email and try again.');

        $booking->refresh();
        $this->assertEquals('confirmed', $booking->status);

        // Get actual cached OTP
        $cached = Cache::get("booking_action_otp_{$booking->id}");
        $correctOtp = $cached['code'];

        // Test valid OTP
        $component->set('otpCode', $correctOtp)
            ->call('verifyActionOtp')
            ->assertSet('showOtpModal', false);

        // Verify cancellation is now committed
        $booking->refresh();
        $this->assertEquals('cancelled', $booking->status);
        $this->assertEquals('pending', $booking->refund_status);
    }

    public function test_otp_resend_cooldown(): void
    {
        Mail::fake();

        $booking = $this->createTestBooking();

        $component = Livewire::test(BookingLookup::class)
            ->set('transaction_number', $booking->transaction_number)
            ->call('search')
            ->call('requestCancellation')
            ->set('refund_method', 'GCash')
            ->set('refund_account_number', '09171234567')
            ->set('refund_account_name', 'Juan Dela Cruz')
            ->call('confirmCancellation');

        // Initial send
        Mail::assertSent(BookingActionOtp::class, 1);

        // Immediate resend attempt during cooldown
        $component->call('resendActionOtp')
            ->assertSet('otpError', 'Please wait before requesting another code.');

        Mail::assertSent(BookingActionOtp::class, 1);

        // Clear cooldown
        Cache::forget("booking_action_otp_cooldown_{$booking->id}");

        // Now resend succeeds
        $component->call('resendActionOtp');
        Mail::assertSent(BookingActionOtp::class, 2);
    }
}

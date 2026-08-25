<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\PromotionController;
use App\Http\Controllers\Api\DiscountController;
use App\Http\Controllers\Api\AccommodationController;
use App\Models\Inquiry;
use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────────────────────────────────────
// Authentication & Registration — relaxed throttle during testing
// ─────────────────────────────────────────────────────────────────────────────
Route::middleware('throttle:60,1')->group(function () {
    Route::post('/login',                     [AuthController::class, 'apiLogin']);
    Route::post('/register',                  [AuthController::class, 'apiRegister']);
    Route::post('/register/request-otp',      [AuthController::class, 'requestRegisterOtp']);
    Route::post('/register/resend-otp',       [AuthController::class, 'resendRegisterOtp']);
    Route::post('/register/verify-otp',       [AuthController::class, 'verifyRegisterOtp']);
    Route::post('/email-verification/request',[AuthController::class, 'requestEmailVerification']);
    Route::post('/email-verification/verify', [AuthController::class, 'verifyEmail']);
    Route::post('/forgot-password/request-otp',[AuthController::class, 'requestPasswordResetOtp']);
    Route::post('/forgot-password/reset',     [AuthController::class, 'resetPasswordWithOtp']);
});

// ─────────────────────────────────────────────────────────────────────────────
// Public read endpoints — generous throttle (60 requests per minute)
// ─────────────────────────────────────────────────────────────────────────────
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/origins',          [ScheduleController::class, 'origins']);
    Route::get('/destinations',     [ScheduleController::class, 'destinations']);
    Route::get('/available-dates',  [ScheduleController::class, 'availableDates']);
    Route::get('/operators',        [ScheduleController::class, 'operators']);
    Route::post('/schedules',       [ScheduleController::class, 'search']);
    Route::get('/all-schedules',    [ScheduleController::class, 'allSchedules']);
    Route::get('/payment-settings', [BookingController::class, 'paymentSettings']);
    Route::get('/promotions',       [PromotionController::class, 'index']);
    Route::get('/discounts',        [DiscountController::class, 'index']);
    Route::get('/vouchers',         [\App\Http\Controllers\Api\VoucherController::class, 'index']);
    Route::get('/accommodations',   [AccommodationController::class, 'index']);
    Route::get('/tours',            [\App\Http\Controllers\Api\TourController::class, 'index']);
    Route::get('/vehicle-rates',    [BookingController::class, 'vehicleRates']);
    Route::get('/baggage-rules',    [ScheduleController::class, 'baggageRules']);

    Route::get('/services', function () {
        $settings = \App\Models\WebsiteSetting::where('page', 'services')->first();
        $cards = [];
        if ($settings && !empty($settings->content['travel_service_cards'])) {
            $cards = array_values($settings->content['travel_service_cards']);
        }
        if (empty($cards)) {
            $cards = [
                ['title' => '2GO Booking',           'description' => 'Book premier overnight ship accommodation with 2GO.'],
                ['title' => 'Starlite',              'description' => 'Affordable regional transits between Batangas and Calapan.'],
                ['title' => 'Airline Ticketing',     'description' => 'Domestic and international flights via AirAsia, Cebu Pacific, and PAL.'],
                ['title' => 'Tour Packages',         'description' => 'Curated itineraries for local and international destinations.'],
            ];
        }
        return response()->json(['status' => 'success', 'services' => $cards]);
    });

    Route::get('/app-version', function () {
        $pubspecPath = base_path('flutter_app/pubspec.yaml');
        $version = '1.0.0+1';
        if (file_exists($pubspecPath)) {
            $content = file_get_contents($pubspecPath);
            if (preg_match('/^version:\s*(.+)$/m', $content, $matches)) {
                $version = trim($matches[1]);
            }
        }
        return response()->json(['version' => $version, 'force_update' => false]);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Booking write endpoints — moderate throttle (20 per minute) to deter spam
// Booking creation is kept public to support guest bookings.
// Ownership is verified via client_email on all mutating endpoints.
// ─────────────────────────────────────────────────────────────────────────────
Route::middleware(['throttle:20,1', 'sensitive.actions'])->group(function () {
    Route::get('/bookings',               [BookingController::class, 'index']);
    Route::get('/bookings/{transaction_number}', [BookingController::class, 'show']);
    Route::post('/bookings',              [BookingController::class, 'store'])->middleware('throttle:30,1');
    Route::post('/bookings/{id}/proof',   [BookingController::class, 'uploadProof']);
    Route::post('/bookings/{id}/cancel',  [BookingController::class, 'cancel']);
    Route::post('/bookings/{id}/rebook',  [BookingController::class, 'rebook']);
    Route::post('/bookings/{id}/rebook-calculation',  [BookingController::class, 'rebookCalculation']);
    Route::get('/bookings/{id}/eligible-replacements', [BookingController::class, 'eligibleReplacements']);
    Route::post('/bookings/{id}/submit-replacement', [BookingController::class, 'submitReplacement']);
    Route::post('/bookings/{id}/disruption-refund', [BookingController::class, 'submitDisruptionRefund']);

    Route::post('/vouchers/validate', [\App\Http\Controllers\Api\VoucherController::class, 'validateVoucher']);

    Route::post('/support', function (Illuminate\Http\Request $request) {
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
        ]);
        Inquiry::create($data);
        return response()->json(['status' => 'success', 'message' => 'Support request received.']);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Authenticated-only routes (requires valid API token)
// ─────────────────────────────────────────────────────────────────────────────
Route::middleware(['auth:sanctum,api', 'throttle:60,1'])->group(function () {
    Route::post('/profile/update', [AuthController::class, 'updateProfile']);
    Route::get('/gracia-points', [\App\Http\Controllers\Api\GraciaPointsController::class, 'index']);
    Route::post('/vouchers/claim', [\App\Http\Controllers\Api\VoucherController::class, 'claim']);

    // In-app notifications
    Route::get('/notifications',                [\App\Http\Controllers\Api\NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read',     [\App\Http\Controllers\Api\NotificationController::class, 'markRead']);
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Api\NotificationController::class, 'markAllRead']);
    Route::delete('/notifications/{id}',        [\App\Http\Controllers\Api\NotificationController::class, 'destroy']);
    Route::delete('/notifications',             [\App\Http\Controllers\Api\NotificationController::class, 'destroyAll']);

    // Referral program
    Route::get('/referral/my-code',  [\App\Http\Controllers\Api\ReferralController::class, 'myCode']);
    Route::post('/referral/apply',   [\App\Http\Controllers\Api\ReferralController::class, 'applyCode']);
});



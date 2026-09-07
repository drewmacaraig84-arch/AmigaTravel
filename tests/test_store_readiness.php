<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

config([
    'cache.default' => 'array',
    'database.default' => 'sqlite',
    'database.connections.sqlite' => [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
    ],
]);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email');
    $table->string('password');
    $table->boolean('is_app_user')->default(true);
    $table->string('api_token', 80)->nullable();
    $table->string('phone')->nullable();
    $table->string('referral_code')->nullable();
    $table->timestamp('deletion_scheduled_at')->nullable();
    $table->string('deletion_reason')->nullable();
    $table->text('deletion_feedback')->nullable();
    $table->timestamps();
});

Schema::create('bookings', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('user_id')->nullable();
    $table->string('client_email')->nullable();
    $table->string('transaction_number')->nullable();
    $table->string('origin')->nullable();
    $table->string('destination')->nullable();
    $table->date('departure_date')->nullable();
    $table->date('return_date')->nullable();
    $table->string('status')->default('confirmed');
    $table->string('refund_status')->default('none');
    $table->timestamps();
});

Schema::create('vouchers', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('user_id')->nullable();
    $table->string('code')->nullable();
    $table->string('status')->default('active');
    $table->timestamps();
});

Schema::create('user_notifications', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('user_id');
    $table->string('title');
    $table->text('body');
    $table->string('type')->nullable();
    $table->timestamps();
});

Schema::create('gracia_user_balances', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('user_id');
    $table->integer('points')->default(0);
    $table->timestamps();
});

Schema::create('gracia_point_ledgers', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('user_id');
    $table->timestamps();
});

Schema::create('user_login_histories', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('user_id');
    $table->timestamps();
});

Schema::create('personal_access_tokens', function (Blueprint $table) {
    $table->id();
    $table->morphs('tokenable');
    $table->string('name');
    $table->string('token', 64)->nullable();
    $table->text('abilities')->nullable();
    $table->timestamp('last_used_at')->nullable();
    $table->timestamp('expires_at')->nullable();
    $table->timestamps();
});

app()->instance(\Illuminate\Routing\Middleware\ThrottleRequests::class, new class {
    public function handle($request, $next) { return $next($request); }
});

use App\Models\User;
use App\Models\UserNotification;
use App\Models\GraciaUserBalance;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

echo "\n======================================================\n";
echo "       STORE READINESS AUTOMATED COMPLIANCE TEST       \n";
echo "======================================================\n\n";

$passed = 0;
$failed = 0;

function report($name, $ok, $details = '') {
    global $passed, $failed;
    if ($ok) {
        $passed++;
        echo " [PASS] $name\n";
    } else {
        $failed++;
        echo " [FAIL] $name: $details\n";
    }
}

// 1. Check /api/app-version returns store URLs
$response = app()->handle(\Illuminate\Http\Request::create('/api/app-version', 'GET'));
$data = json_decode($response->getContent(), true);
$hasStoreUrls = !empty($data['play_store_url']) && !empty($data['app_store_url']) && !empty($data['app_gallery_url']);
report("API: /app-version returns Play Store, App Store, and AppGallery URLs", $hasStoreUrls, json_encode($data));

// 2. Check DELETE /api/profile/delete rejects unauthenticated requests
$unauthResponse = app()->handle(\Illuminate\Http\Request::create('/api/profile/delete', 'DELETE'));
report("API: DELETE /api/profile/delete rejects unauthenticated requests", $unauthResponse->getStatusCode() === 401, "Status: " . $unauthResponse->getStatusCode());

// 3. Test Multi-Step Account Deletion Flow (Eligibility, Security Verification, 14-Day Grace Period, Cancellation, and Purge)
$testUser = User::create([
    'name' => 'Store Readiness Test User',
    'email' => 'store_test_' . Str::random(8) . '@example.com',
    'password' => Hash::make('password123'),
    'is_app_user' => true,
    'api_token' => Str::random(80),
]);

UserNotification::create([
    'user_id' => $testUser->id,
    'title' => 'Test Welcome',
    'body' => 'Welcome to Amiga Gracia',
    'type' => 'general',
]);

GraciaUserBalance::create([
    'user_id' => $testUser->id,
    'points' => 150,
]);

// 3.1 Eligibility Check (No active bookings -> eligible: true)
$eligRequest = \Illuminate\Http\Request::create('/api/profile/delete-eligibility', 'GET');
$eligRequest->headers->set('Authorization', 'Bearer ' . $testUser->api_token);
$eligRequest->headers->set('Accept', 'application/json');
$eligResponse = app()->handle($eligRequest);
$eligData = json_decode($eligResponse->getContent(), true);

report("API: GET /api/profile/delete-eligibility returns eligible=true for clean account", 
    $eligResponse->getStatusCode() === 200 && ($eligData['eligible'] ?? false) === true, 
    $eligResponse->getContent()
);

// 3.2 Blocked Eligibility Check (Create upcoming confirmed booking -> eligible: false)
$activeBooking = \App\Models\Booking::create([
    'user_id' => $testUser->id,
    'client_email' => $testUser->email,
    'transaction_number' => 'TEST-BK-' . strtoupper(Str::random(6)),
    'origin' => 'Batangas',
    'destination' => 'Calapan',
    'departure_date' => now()->addDays(3)->toDateString(),
    'status' => \App\Models\Booking::STATUS_CONFIRMED,
]);

$eligResponse2 = app()->handle($eligRequest);
$eligData2 = json_decode($eligResponse2->getContent(), true);
report("API: GET /api/profile/delete-eligibility blocks deletion if upcoming trip exists", 
    ($eligData2['eligible'] ?? true) === false && ($eligData2['active_bookings_count'] ?? 0) === 1, 
    $eligResponse2->getContent()
);

// Remove active booking to test the rest of the flow
$activeBooking->delete();

// 3.3 Request Email OTP
$otpRequest = \Illuminate\Http\Request::create('/api/profile/delete/request-otp', 'POST');
$otpRequest->headers->set('Authorization', 'Bearer ' . $testUser->api_token);
$otpRequest->headers->set('Accept', 'application/json');
$otpResponse = app()->handle($otpRequest);
$otpData = json_decode($otpResponse->getContent(), true);

$cachedOtp = \Illuminate\Support\Facades\Cache::get('account_deletion_otp:' . strtolower($testUser->email));
report("API: POST /api/profile/delete/request-otp generates and caches 6-digit OTP", 
    $otpResponse->getStatusCode() === 200 && !empty($cachedOtp) && strlen($cachedOtp) === 6, 
    "Cached OTP: $cachedOtp"
);

// 3.4 Confirm Deletion Rejects Invalid Credentials
$invalidConfirmRequest = \Illuminate\Http\Request::create('/api/profile/delete/confirm', 'POST', [
    'password' => 'wrongpassword',
    'otp' => $cachedOtp,
    'confirmation_text' => 'DELETE',
]);
$invalidConfirmRequest->headers->set('Authorization', 'Bearer ' . $testUser->api_token);
$invalidConfirmRequest->headers->set('Accept', 'application/json');
$invalidConfirmResponse = app()->handle($invalidConfirmRequest);
report("API: POST /api/profile/delete/confirm rejects incorrect password", 
    $invalidConfirmResponse->getStatusCode() === 422, 
    $invalidConfirmResponse->getContent()
);

$invalidTextRequest = \Illuminate\Http\Request::create('/api/profile/delete/confirm', 'POST', [
    'password' => 'password123',
    'otp' => $cachedOtp,
    'confirmation_text' => 'delete', // lowercase should fail
]);
$invalidTextRequest->headers->set('Authorization', 'Bearer ' . $testUser->api_token);
$invalidTextRequest->headers->set('Accept', 'application/json');
$invalidTextResponse = app()->handle($invalidTextRequest);
report("API: POST /api/profile/delete/confirm requires typed uppercase 'DELETE'", 
    $invalidTextResponse->getStatusCode() === 422, 
    $invalidTextResponse->getContent()
);

// 3.5 Confirm Deletion Schedules 14-Day Grace Period
$validConfirmRequest = \Illuminate\Http\Request::create('/api/profile/delete/confirm', 'POST', [
    'password' => 'password123',
    'otp' => $cachedOtp,
    'confirmation_text' => 'DELETE',
    'reason' => 'Testing account deletion',
]);
$validConfirmRequest->headers->set('Authorization', 'Bearer ' . $testUser->api_token);
$validConfirmRequest->headers->set('Accept', 'application/json');
$validConfirmResponse = app()->handle($validConfirmRequest);
$confirmData = json_decode($validConfirmResponse->getContent(), true);

$testUser->refresh();
report("API: POST /api/profile/delete/confirm schedules account for deletion (Option A)", 
    $validConfirmResponse->getStatusCode() === 200 && 
    !empty($testUser->deletion_scheduled_at) && 
    ($confirmData['days_remaining'] ?? 0) === 14, 
    $validConfirmResponse->getContent()
);

// 3.6 Cancel / Restore Account Deletion
$cancelRequest = \Illuminate\Http\Request::create('/api/profile/delete/cancel', 'POST');
$cancelRequest->headers->set('Authorization', 'Bearer ' . $testUser->api_token);
$cancelRequest->headers->set('Accept', 'application/json');
$cancelResponse = app()->handle($cancelRequest);

$testUser->refresh();
report("API: POST /api/profile/delete/cancel restores account from grace period", 
    $cancelResponse->getStatusCode() === 200 && is_null($testUser->deletion_scheduled_at), 
    $cancelResponse->getContent()
);

// 3.7 Background Command: Purge accounts whose 14-day grace period has expired
$testUser->update(['deletion_scheduled_at' => now()->subDays(15)]);
\Illuminate\Support\Facades\Artisan::call('accounts:purge-scheduled');

$userExistsAfterPurge = User::where('id', $testUser->id)->exists();
$notificationsExistAfter = UserNotification::where('user_id', $testUser->id)->exists();
$balanceExistsAfter = GraciaUserBalance::where('user_id', $testUser->id)->exists();

report("CLI: accounts:purge-scheduled purges expired grace-period user", !$userExistsAfterPurge, "User still exists");
report("CLI: accounts:purge-scheduled cleans up user notifications", !$notificationsExistAfter, "Notifications remain");
report("CLI: accounts:purge-scheduled cleans up user points balance", !$balanceExistsAfter, "Points remain");

// 4. AndroidManifest.xml verification
$manifestContent = file_get_contents(__DIR__ . '/../flutter_app/android/app/src/main/AndroidManifest.xml');
$hasMaxSdk = strpos($manifestContent, 'android.permission.READ_EXTERNAL_STORAGE" android:maxSdkVersion="32"') !== false;
$hasDisabledCleartext = strpos($manifestContent, 'android:usesCleartextTraffic="false"') !== false;
report("Android: READ_EXTERNAL_STORAGE has maxSdkVersion='32'", $hasMaxSdk, "Missing maxSdkVersion");
report("Android: usesCleartextTraffic is false", $hasDisabledCleartext, "Cleartext still enabled");

// 5. iOS Info.plist verification
$plistContent = file_get_contents(__DIR__ . '/../flutter_app/ios/Runner/Info.plist');
$hasCamera = strpos($plistContent, '<key>NSCameraUsageDescription</key>') !== false;
$hasPhoto = strpos($plistContent, '<key>NSPhotoLibraryUsageDescription</key>') !== false;
$hasPhotoAdd = strpos($plistContent, '<key>NSPhotoLibraryAddUsageDescription</key>') !== false;
report("iOS: Info.plist has NSCameraUsageDescription", $hasCamera, "Missing camera description");
report("iOS: Info.plist has NSPhotoLibraryUsageDescription", $hasPhoto, "Missing photo library description");
report("iOS: Info.plist has NSPhotoLibraryAddUsageDescription", $hasPhotoAdd, "Missing photo library add description");

// 6. Web Privacy Policy verification
$privacyPolicyContent = file_get_contents(__DIR__ . '/../resources/views/privacy-policy.blade.php');
$hasDeletionAnchor = strpos($privacyPolicyContent, 'id="account-deletion"') !== false;
report("Web: Privacy Policy contains id='account-deletion' for Google Play Data Safety form", $hasDeletionAnchor, "Missing account deletion anchor");

echo "\n------------------------------------------------------\n";
echo "Results: $passed Passed, $failed Failed\n";
echo "------------------------------------------------------\n\n";

exit($failed > 0 ? 1 : 0);

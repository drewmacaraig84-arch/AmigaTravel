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

// 3. Test DELETE /api/profile/delete deletes user, notifications, and balance
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

$authRequest = \Illuminate\Http\Request::create('/api/profile/delete', 'DELETE');
$authRequest->headers->set('Authorization', 'Bearer ' . $testUser->api_token);
$authRequest->headers->set('Accept', 'application/json');

$authResponse = app()->handle($authRequest);
$deleteData = json_decode($authResponse->getContent(), true);

$userExistsAfter = User::where('id', $testUser->id)->exists();
$notificationsExistAfter = UserNotification::where('user_id', $testUser->id)->exists();
$balanceExistsAfter = GraciaUserBalance::where('user_id', $testUser->id)->exists();

report("API: DELETE /api/profile/delete returns success 200", $authResponse->getStatusCode() === 200 && ($deleteData['status'] ?? '') === 'success', "Response: " . $authResponse->getContent());
report("API: DELETE /api/profile/delete permanently deletes user record", !$userExistsAfter, "User still in DB");
report("API: DELETE /api/profile/delete cleans up user notifications", !$notificationsExistAfter, "Notifications still exist");
report("API: DELETE /api/profile/delete cleans up user points balance", !$balanceExistsAfter, "Balance record still exists");

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

<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

config(['cache.default' => 'array']);

use Illuminate\Support\Facades\Cache;
use App\Models\PaymentSetting;
use App\Models\Accommodation;
use App\Models\PromotionalTicket;
use App\Models\Promotion;
use App\Models\Discount;
use App\Models\TransportClass;
use App\Models\Schedule;
use App\Models\VehicleRate;
use App\Models\VehicleBrand;
use App\Models\VehicleModel;
use App\Models\AirlineBaggageRule;
use App\Models\Tour;
use App\Models\TourDate;
use App\Models\GraciaEarningRule;
use App\Models\Operator;

echo "=== STARTING REACTIVITY & CACHE INVALIDATION TESTS ===\n\n";

$passed = 0;
$failed = 0;

function assertTest(bool $condition, string $title) {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] {$title}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$title}\n";
        $failed++;
    }
}

// 1. Test PaymentSetting Invalidation
echo "1. Testing PaymentSetting...\n";
Cache::put('payment_settings:current', ['test' => 'old_value'], 3600);
Cache::put('api:payment_settings', ['test' => 'old_value'], 3600);
assertTest(Cache::has('payment_settings:current'), "Cache::has('payment_settings:current') initially true");
PaymentSetting::bust();
assertTest(!Cache::has('payment_settings:current'), "PaymentSetting::bust() clears 'payment_settings:current'");
assertTest(!Cache::has('api:payment_settings'), "PaymentSetting::bust() clears 'api:payment_settings'");

// 2. Test Accommodation Invalidation
echo "\n2. Testing Accommodation...\n";
Cache::put('api:accommodations:all', ['test' => 'data'], 3600);
Cache::put('api:accommodations_v3', ['test' => 'data'], 3600);
Cache::put('api:accommodations:boracay', ['test' => 'data'], 3600);
Accommodation::bust();
assertTest(!Cache::has('api:accommodations:all'), "Accommodation::bust() clears 'api:accommodations:all'");
assertTest(!Cache::has('api:accommodations_v3'), "Accommodation::bust() clears 'api:accommodations_v3'");

// 3. Test PromotionalTicket Invalidation
echo "\n3. Testing PromotionalTicket...\n";
Cache::put('api:promotions', ['promo' => 'old'], 3600);
Cache::put('website_settings:promotions', ['promo' => 'old'], 3600);
PromotionalTicket::bust();
assertTest(!Cache::has('api:promotions'), "PromotionalTicket::bust() clears 'api:promotions'");
assertTest(!Cache::has('website_settings:promotions'), "PromotionalTicket::bust() clears 'website_settings:promotions'");

// 4. Test Promotion Invalidation
echo "\n4. Testing Promotion...\n";
Cache::put('api:promotions', ['banner' => 'old'], 3600);
Promotion::bust();
assertTest(!Cache::has('api:promotions'), "Promotion::bust() clears 'api:promotions'");

// 5. Test Discount Invalidation
echo "\n5. Testing Discount...\n";
Cache::put('discounts:all:keyed', ['disc' => 20], 3600);
Cache::put('api:discounts', ['disc' => 20], 3600);
Cache::put('catalog:discounts_v3', ['disc' => 20], 3600);
Discount::bust();
assertTest(!Cache::has('discounts:all:keyed'), "Discount::bust() clears 'discounts:all:keyed'");
assertTest(!Cache::has('api:discounts'), "Discount::bust() clears 'api:discounts'");
assertTest(!Cache::has('catalog:discounts_v3'), "Discount::bust() clears 'catalog:discounts_v3'");

// 6. Test TransportClass Invalidation
echo "\n6. Testing TransportClass...\n";
Cache::put('catalog:transport_classes_v3', ['class' => 'VIP'], 3600);
TransportClass::bust();
assertTest(!Cache::has('catalog:transport_classes_v3'), "TransportClass::bust() clears 'catalog:transport_classes_v3'");

// 7. Test VehicleRate Invalidation
echo "\n7. Testing VehicleRate...\n";
Cache::put('api:vehicle_rates', ['rate' => 1000], 3600);
Cache::put('api:vehicle_rates_v3', ['rate' => 1000], 3600);
VehicleRate::bust();
assertTest(!Cache::has('api:vehicle_rates'), "VehicleRate::bust() clears 'api:vehicle_rates'");
assertTest(!Cache::has('api:vehicle_rates_v3'), "VehicleRate::bust() clears 'api:vehicle_rates_v3'");

// 8. Test VehicleBrand Invalidation
echo "\n8. Testing VehicleBrand...\n";
Cache::put('catalog:vehicle_brands_v3', ['brand' => 'Toyota'], 3600);
VehicleBrand::bust();
assertTest(!Cache::has('catalog:vehicle_brands_v3'), "VehicleBrand::bust() clears 'catalog:vehicle_brands_v3'");

// 9. Test AirlineBaggageRule Invalidation
echo "\n9. Testing AirlineBaggageRule...\n";
Cache::put('baggage_rules_json_v1', ['bag' => 500], 3600);
Cache::put('baggage_rules:local', ['bag' => 500], 3600);
Cache::put('baggage_rules:international', ['bag' => 800], 3600);
AirlineBaggageRule::bust();
assertTest(!Cache::has('baggage_rules_json_v1'), "AirlineBaggageRule::bust() clears 'baggage_rules_json_v1'");
assertTest(!Cache::has('baggage_rules:local'), "AirlineBaggageRule::bust() clears 'baggage_rules:local'");
assertTest(!Cache::has('baggage_rules:international'), "AirlineBaggageRule::bust() clears 'baggage_rules:international'");

// 10. Test Tour Invalidation
echo "\n10. Testing Tour...\n";
Cache::put('api:tours', ['tour' => 'Boracay Package'], 3600);
Cache::put('api:tours:all', ['tour' => 'Boracay Package'], 3600);
Tour::bust();
assertTest(!Cache::has('api:tours'), "Tour::bust() clears 'api:tours'");
assertTest(!Cache::has('api:tours:all'), "Tour::bust() clears 'api:tours:all'");

// 11. Test GraciaEarningRule Invalidation
echo "\n11. Testing GraciaEarningRule...\n";
Cache::put('gracia:active_rule', ['rule' => 100], 3600);
GraciaEarningRule::bust();
assertTest(!Cache::has('gracia:active_rule'), "GraciaEarningRule::bust() clears 'gracia:active_rule'");

// 12. Test PreventApiCaching Middleware
echo "\n12. Testing PreventApiCaching Middleware...\n";
$request = \Illuminate\Http\Request::create('/api/payment-settings', 'GET');
$middleware = new \App\Http\Middleware\PreventApiCaching();
$response = $middleware->handle($request, function ($req) {
    return response()->json(['status' => 'success']);
});
$cacheControl = $response->headers->get('Cache-Control');
$pragma = $response->headers->get('Pragma');
assertTest(str_contains($cacheControl, 'no-cache') && str_contains($cacheControl, 'no-store'), "Middleware attaches Cache-Control: no-cache, no-store");
assertTest($pragma === 'no-cache', "Middleware attaches Pragma: no-cache");

echo "\n============================================\n";
echo "TEST RESULTS: {$passed} Passed, {$failed} Failed\n";
echo "============================================\n";

exit($failed > 0 ? 1 : 0);

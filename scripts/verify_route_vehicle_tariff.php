<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\StarliteScheduleIngestionService;
use App\Services\VehicleBookingPolicyService;
use App\Models\Schedule;
use App\Models\FerryRoute;

echo "===============================================================\n";
echo "       STARLITE FERRIES ROUTE VEHICLE TARIFF VERIFICATION      \n";
echo "===============================================================\n\n";

$passCount = 0;
$failCount = 0;

function assertTest(string $description, bool $condition, string $detail = '') {
    global $passCount, $failCount;
    if ($condition) {
        echo " [PASS] {$description}\n";
        $passCount++;
    } else {
        echo " [FAIL] {$description} | Detail: {$detail}\n";
        $failCount++;
    }
}

// 1. Test route support detection
echo "--- 1. Route Rolling Cargo Eligibility ---\n";
assertTest(
    "Batangas to Calapan supports vehicle rolling cargo",
    StarliteScheduleIngestionService::isVehicleSupportedForRoute('Batangas', 'Calapan') === true
);
assertTest(
    "Batangas to Caticlan supports vehicle rolling cargo",
    StarliteScheduleIngestionService::isVehicleSupportedForRoute('Batangas', 'Caticlan') === true
);
assertTest(
    "Batangas to Roxas Capiz supports vehicle rolling cargo",
    StarliteScheduleIngestionService::isVehicleSupportedForRoute('Batangas', 'Roxas') === true
);
assertTest(
    "Roxas Mindoro to Caticlan supports vehicle rolling cargo",
    StarliteScheduleIngestionService::isVehicleSupportedForRoute('Roxas Mindoro', 'Caticlan') === true
);
assertTest(
    "Cebu to Nasipit supports vehicle rolling cargo",
    StarliteScheduleIngestionService::isVehicleSupportedForRoute('Cebu', 'Nasipit') === true
);
assertTest(
    "Odiongan to Caticlan DOES NOT support vehicle rolling cargo (passenger only)",
    StarliteScheduleIngestionService::isVehicleSupportedForRoute('Odiongan', 'Caticlan') === false
);
assertTest(
    "Batangas to Odiongan DOES NOT support vehicle rolling cargo (passenger only)",
    StarliteScheduleIngestionService::isVehicleSupportedForRoute('Batangas', 'Odiongan') === false
);

// 2. Test official tariff price calculations
echo "\n--- 2. Route-Specific Tariff Pricing (April 13, 2026 PDF) ---\n";

// Batangas - Calapan
$p1 = StarliteScheduleIngestionService::calculateVehiclePriceForRoute('Motorcycle', 'Batangas', 'Calapan');
assertTest("Batangas-Calapan Motorcycle tariff is ₱1,440.00", $p1 === 1440.0, "Got: {$p1}");

$p2 = StarliteScheduleIngestionService::calculateVehiclePriceForRoute('Below 3 meters', 'Batangas', 'Calapan');
assertTest("Batangas-Calapan Below 3m tariff is ₱2,160.00", $p2 === 2160.0, "Got: {$p2}");

$p3 = StarliteScheduleIngestionService::calculateVehiclePriceForRoute('3 to 3.9 meters (Small Car)', 'Batangas', 'Calapan');
assertTest("Batangas-Calapan Small Car tariff is ₱3,100.00", $p3 === 3100.0, "Got: {$p3}");

$p4 = StarliteScheduleIngestionService::calculateVehiclePriceForRoute('4 to 4.9 meters (Regular Car / SUV)', 'Batangas', 'Calapan');
assertTest("Batangas-Calapan Regular Car/SUV tariff is ₱3,840.00", $p4 === 3840.0, "Got: {$p4}");

// Batangas - Caticlan
$p5 = StarliteScheduleIngestionService::calculateVehiclePriceForRoute('Motorcycle', 'Batangas', 'Caticlan');
assertTest("Batangas-Caticlan Motorcycle tariff is ₱7,020.00", $p5 === 7020.0, "Got: {$p5}");

$p6 = StarliteScheduleIngestionService::calculateVehiclePriceForRoute('3 to 3.9 meters (Small Car)', 'Batangas', 'Caticlan');
assertTest("Batangas-Caticlan Small Car tariff is ₱15,030.00", $p6 === 15030.0, "Got: {$p6}");

$p7 = StarliteScheduleIngestionService::calculateVehiclePriceForRoute('4 to 4.9 meters (Regular Car / SUV)', 'Batangas', 'Caticlan');
assertTest("Batangas-Caticlan Regular Car/SUV tariff is ₱16,650.00", $p7 === 16650.0, "Got: {$p7}");

// Cebu - Nasipit
$p8 = StarliteScheduleIngestionService::calculateVehiclePriceForRoute('Motorcycle', 'Cebu', 'Nasipit');
assertTest("Cebu-Nasipit Motorcycle tariff is ₱4,030.00", $p8 === 4030.0, "Got: {$p8}");

$p9 = StarliteScheduleIngestionService::calculateVehiclePriceForRoute('4 to 4.9 meters (Regular Car / SUV)', 'Cebu', 'Nasipit');
assertTest("Cebu-Nasipit Regular Car/SUV tariff is ₱14,900.00", $p9 === 14900.0, "Got: {$p9}");

// 3. Test Brand / Model dynamic resolution to tiers
echo "\n--- 3. Brand / Model Mapping to Starlite Tiers ---\n";
$m1 = StarliteScheduleIngestionService::calculateVehiclePriceForRoute('Toyota Wigo', 'Batangas', 'Calapan');
assertTest("Toyota Wigo maps to Small Car on Batangas-Calapan -> ₱3,100.00", $m1 === 3100.0, "Got: {$m1}");

$m2 = StarliteScheduleIngestionService::calculateVehiclePriceForRoute('Toyota Fortuner', 'Batangas', 'Calapan');
assertTest("Toyota Fortuner maps to Regular Car/SUV on Batangas-Calapan -> ₱3,840.00", $m2 === 3840.0, "Got: {$m2}");

$m3 = StarliteScheduleIngestionService::calculateVehiclePriceForRoute('Toyota Fortuner', 'Batangas', 'Caticlan');
assertTest("Toyota Fortuner on Batangas-Caticlan -> ₱16,650.00", $m3 === 16650.0, "Got: {$m3}");

$m4 = StarliteScheduleIngestionService::calculateVehiclePriceForRoute('Honda Click 125', 'Batangas', 'Calapan');
assertTest("Honda Click 125 maps to Motorcycle on Batangas-Calapan -> ₱1,440.00", $m4 === 1440.0, "Got: {$m4}");

$m5 = StarliteScheduleIngestionService::calculateVehiclePriceForRoute('Bajaj RE', 'Batangas', 'Calapan');
assertTest("Bajaj RE maps to Below 3m on Batangas-Calapan -> ₱2,160.00", $m5 === 2160.0, "Got: {$m5}");

// 4. Test unsupported route fallback
echo "\n--- 4. Unsupported Route Safe Fallback ---\n";
$unsupportedRate = StarliteScheduleIngestionService::calculateVehiclePriceForRoute('Toyota Wigo', 'Odiongan', 'Caticlan', 0.0);
assertTest("Odiongan to Caticlan returns fallback 0.0", $unsupportedRate === 0.0, "Got: {$unsupportedRate}");

// 5. Test compiled cache data
echo "\n--- 5. Compiled Route Vehicle Rates Cache Dictionary ---\n";
$allRates = StarliteScheduleIngestionService::getAllRouteVehicleRates();
assertTest("getAllRouteVehicleRates returns array with 15+ pairs", count($allRates) >= 15, "Count: " . count($allRates));
assertTest("getAllRouteVehicleRates has Batangas|Calapan supported = true", isset($allRates['Batangas|Calapan']) && $allRates['Batangas|Calapan']['supported'] === true);
assertTest("getAllRouteVehicleRates has Odiongan|Caticlan supported = false", isset($allRates['Odiongan|Caticlan']) && $allRates['Odiongan|Caticlan']['supported'] === false);

// 6. Policy validator check
echo "\n--- 6. Policy Service Validation Check ---\n";
$policyService = app(VehicleBookingPolicyService::class);
assertTest("Policy service earliest date is at least 3 days ahead", $policyService->getEarliestVehicleBookingDate() >= now('Asia/Manila')->addDays(3)->format('Y-m-d'));

echo "\n===============================================================\n";
echo "Results: {$passCount} PASSED, {$failCount} FAILED\n";
echo "===============================================================\n";

if ($failCount > 0) {
    exit(1);
}

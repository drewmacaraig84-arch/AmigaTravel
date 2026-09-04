<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Livewire\BookingForm;
use App\Models\FerryRoute;
use App\Models\Schedule;
use App\Models\ScheduleAccommodation;
use App\Models\VehicleRate;
use App\Services\StarliteScheduleIngestionService;

echo "======================================================\n";
echo "       VERIFICATION OF TARIFFS, VEHICLES & SCHEDULES\n";
echo "======================================================\n\n";

$pass = true;

// 1. Check Red Routes
echo "1. Checking that all RED routes & departures are excluded:\n";

// A. Odiongan routes (all red)
$odionganCount = FerryRoute::where('operator', 'Starlite')
    ->where(function ($q) {
        $q->where('origin', 'like', '%Odiongan%')
          ->orWhere('destination', 'like', '%Odiongan%');
    })->count();
echo "   Odiongan routes count: {$odionganCount} " . ($odionganCount === 0 ? "[PASS]" : "[FAIL]") . "\n";
if ($odionganCount !== 0) $pass = false;

// B. Nasipit routes (red column in fare sheet)
$nasipitCount = FerryRoute::where('operator', 'Starlite')
    ->where(function ($q) {
        $q->where('origin', 'like', '%Nasipit%')
          ->orWhere('destination', 'like', '%Nasipit%');
    })->count();
echo "   Nasipit routes count: {$nasipitCount} " . ($nasipitCount === 0 ? "[PASS]" : "[FAIL]") . "\n";
if ($nasipitCount !== 0) $pass = false;

// C. Red LCT and Fastcraft schedules on Batangas-Calapan
$calapanRouteIds = FerryRoute::where('operator', 'Starlite')
    ->where(function ($q) {
        $q->where(fn($s) => $s->where('origin', 'Batangas')->where('destination', 'Calapan'))
          ->orWhere(fn($s) => $s->where('origin', 'Calapan')->where('destination', 'Batangas'));
    })->pluck('id');

$redCalapanLctCount = Schedule::whereIn('ferry_route_id', $calapanRouteIds)
    ->where(function ($q) {
        $q->where('vehicle_name', 'like', '%SPRINT%')
          ->orWhere('vehicle_name', 'like', '%PACIFIC%')
          ->orWhere('vehicle_name', 'like', '%ARCHER%')
          ->orWhere('service_name', 'like', '%LCT%')
          ->orWhere('service_name', 'like', '%FASTCRAFT%');
    })->count();
echo "   Red LCT/Fastcraft schedules on Batangas-Calapan count: {$redCalapanLctCount} " . ($redCalapanLctCount === 0 ? "[PASS]" : "[FAIL]") . "\n";
if ($redCalapanLctCount !== 0) $pass = false;

// D. Red Caticlan departures: 1:00 PM on Batangas->Caticlan and 7:30 AM on Caticlan->Batangas
$btgCatRoute = FerryRoute::where('operator', 'Starlite')->where('origin', 'Batangas')->where('destination', 'Caticlan')->first();
$red1pmCount = $btgCatRoute ? Schedule::where('ferry_route_id', $btgCatRoute->id)->whereRaw("TIME(departure_time) BETWEEN '12:55:00' AND '13:05:00'")->count() : 0;
echo "   Red 1:00 PM Batangas->Caticlan departures: {$red1pmCount} " . ($red1pmCount === 0 ? "[PASS]" : "[FAIL]") . "\n";
if ($red1pmCount !== 0) $pass = false;

$catBtgRoute = FerryRoute::where('operator', 'Starlite')->where('origin', 'Caticlan')->where('destination', 'Batangas')->first();
$red730amCount = $catBtgRoute ? Schedule::where('ferry_route_id', $catBtgRoute->id)->whereRaw("TIME(departure_time) BETWEEN '07:25:00' AND '07:35:00'")->count() : 0;
echo "   Red 7:30 AM Caticlan->Batangas departures: {$red730amCount} " . ($red730amCount === 0 ? "[PASS]" : "[FAIL]") . "\n\n";
if ($red730amCount !== 0) $pass = false;

// 2. Check Accommodations and Prices on Active Schedules from exact user tariff sheets
echo "2. Checking Accommodations and Prices on Active Schedules against Tariff Sheets:\n";
$testRoutes = [
    // Image 2: Batangas to Calapan VV
    ['Batangas', 'Calapan', 680.00, [
        'Reclining Seat' => 680.00,
        'Economy Bed Bunk' => 680.00,
        'Tourist Bed Bunk' => 680.00,
    ]],
    // Image 2: Batangas to Caticlan VV
    ['Batangas', 'Caticlan', 2170.00, [
        'Reclining Seat' => 2170.00,
        'Economy Bed Bunk' => 2270.00,
        'Tourist Bed Bunk' => 2790.00,
        'Cabin' => 3720.00,
        'VIP Room (2-3 pax)' => 8300.00,
        'VIP Room (5 pax)' => 14400.00,
    ]],
    // Image 2: Cebu to Surigao & VV
    ['Cebu', 'Surigao', 1550.00, [
        'Reclining Seat' => 1550.00,
        'Economy Bed Bunk' => 1650.00,
        'Tourist Bed Bunk' => 1960.00,
        'Cabin' => 2380.00,
        'VIP Room (2-3 pax)' => 7700.00,
    ]],
    // Image 2: Cebu-Dapitan VV
    ['Cebu', 'Dapitan', 1130.00, [
        'Reclining Seat' => 1130.00,
        'Economy Bed Bunk' => 1440.00,
        'Tourist Bed Bunk' => 1860.00,
        'Cabin' => 2270.00,
        'VIP Room (2-3 pax)' => 7700.00,
    ]],
    // Image 1: Batangas to Sibuyan (Magdiwang) VV
    ['Batangas', 'Sibuyan (Magdiwang), Romblon', 1240.00, [
        'Reclining Seat' => 1240.00,
        'Economy Bed Bunk' => 1240.00,
        'Tourist Bed Bunk' => 1860.00,
        'Cabin' => 3200.00,
        'VIP Room (2-3 pax)' => 9600.00,
    ]],
    // Image 1: Batangas to Romblon, Romblon VV
    ['Batangas', 'Romblon, Romblon', 1240.00, [
        'Reclining Seat' => 1240.00,
        'Economy Bed Bunk' => 1240.00,
        'Tourist Bed Bunk' => 1860.00,
        'Cabin' => 2790.00,
        'VIP Room (2-3 pax)' => 8300.00,
    ]],
    // Image 3: Batangas to Cajidiocan, Romblon VV
    ['Batangas', 'Cajidiocan, Romblon', 1550.00, [
        'Reclining Seat' => 1550.00,
        'Economy Bed Bunk' => 1550.00,
        'Tourist Bed Bunk' => 2170.00,
        'Cabin' => 3410.00,
        'VIP Room (2-3 pax)' => 9900.00,
    ]],
    // Image 3: Roxas Mindoro to Caticlan VV
    ['Roxas Mindoro', 'Caticlan', 1340.00, [
        'Reclining Seat' => 1340.00,
        'Economy Bed Bunk' => 1340.00,
        'Tourist Bed Bunk' => 1550.00,
        'Cabin' => 1750.00,
        'VIP Room (2-3 pax)' => 4400.00,
    ]],
];

foreach ($testRoutes as [$orig, $dest, $expectedBase, $expectedAccs]) {
    $route = FerryRoute::where('operator', 'Starlite')->where('origin', $orig)->where('destination', $dest)->first();
    if (! $route) {
        echo "   Route {$orig} -> {$dest} NOT FOUND! [FAIL]\n";
        $pass = false;
        continue;
    }
    $sched = Schedule::where('ferry_route_id', $route->id)->first();
    if (! $sched) {
        echo "   No schedules found for {$orig} -> {$dest}! [FAIL]\n";
        $pass = false;
        continue;
    }

    $baseMatches = (float) $sched->price === (float) $expectedBase;
    echo "   {$orig} -> {$dest} Base Fare: ₱{$sched->price} " . ($baseMatches ? "[PASS]" : "[FAIL (expected ₱{$expectedBase})]") . "\n";
    if (! $baseMatches) $pass = false;

    $accs = ScheduleAccommodation::where('schedule_id', $sched->id)->pluck('price', 'name')->all();
    foreach ($expectedAccs as $accName => $expectedPrice) {
        $actualPrice = $accs[$accName] ?? null;
        $accMatches = $actualPrice !== null && (float) $actualPrice === (float) $expectedPrice;
        echo "     * Accommodation {$accName}: ₱" . ($actualPrice ?? 'MISSING') . " " . ($accMatches ? "[PASS]" : "[FAIL (expected ₱{$expectedPrice})]") . "\n";
        if (! $accMatches) $pass = false;
    }
}
echo "\n";

// 3. Check Vehicle Rates Resolution via Service and BookingForm
echo "3. Checking Actual Route-Specific Vehicle Rates from Tariff Sheets:\n";
$vehicleTestRoutes = [
    'Batangas' => [
        // Image 2
        'Calapan' => ['Motorcycle' => 1440.00, 'Below 3 meters' => 2160.00, '3 to 3.9 meters (Small Car)' => 3100.00, '4 to 4.9 meters (Regular Car / SUV)' => 3840.00],
        // Image 2
        'Caticlan' => ['Motorcycle' => 7020.00, 'Below 3 meters' => 7200.00, '3 to 3.9 meters (Small Car)' => 15030.00, '4 to 4.9 meters (Regular Car / SUV)' => 16650.00],
        // Image 1
        'Sibuyan (Magdiwang), Romblon' => ['Motorcycle' => 5900.00, 'Below 3 meters' => 9730.00, '3 to 3.9 meters (Small Car)' => 15620.00, '4 to 4.9 meters (Regular Car / SUV)' => 15620.00],
        // Image 1
        'Romblon, Romblon' => ['Motorcycle' => 4860.00, 'Below 3 meters' => 8070.00, '3 to 3.9 meters (Small Car)' => 14590.00, '4 to 4.9 meters (Regular Car / SUV)' => 14590.00],
        // Image 3
        'Cajidiocan, Romblon' => ['Motorcycle' => 5900.00, 'Below 3 meters' => 9730.00, '3 to 3.9 meters (Small Car)' => 15620.00, '4 to 4.9 meters (Regular Car / SUV)' => 15620.00],
    ],
    'Cebu' => [
        // Image 2
        'Surigao' => ['Motorcycle' => 3310.00, 'Below 3 meters' => 5380.00, '3 to 3.9 meters (Small Car)' => 10140.00, '4 to 4.9 meters (Regular Car / SUV)' => 12310.00],
        // Image 2
        'Dapitan' => ['Motorcycle' => 1650.00, 'Below 3 meters' => 5170.00, '3 to 3.9 meters (Small Car)' => 8380.00, '4 to 4.9 meters (Regular Car / SUV)' => 9000.00],
    ],
    'Roxas Mindoro' => [
        // Image 3
        'Caticlan' => ['Motorcycle' => 2880.00, 'Below 3 meters' => 4320.00, '3 to 3.9 meters (Small Car)' => 5760.00, '4 to 4.9 meters (Regular Car / SUV)' => 7200.00],
    ],
];

foreach ($vehicleTestRoutes as $orig => $destinations) {
    foreach ($destinations as $dest => $expectedVehicles) {
        $rates = StarliteScheduleIngestionService::getVehicleRatesForRoute($orig, $dest);
        if (! $rates) {
            echo "   Vehicle rates for {$orig} -> {$dest}: NONE FOUND! [FAIL]\n";
            $pass = false;
            continue;
        }

        echo "   Vehicle Rates for {$orig} -> {$dest}:\n";
        foreach ($expectedVehicles as $vKey => $vExpectedPrice) {
            $vActualPrice = (float) ($rates[$vKey] ?? 0);
            $vMatches = $vActualPrice === (float) $vExpectedPrice;
            echo "     * {$vKey}: ₱{$vActualPrice} " . ($vMatches ? "[PASS]" : "[FAIL (expected ₱{$vExpectedPrice})]") . "\n";
            if (! $vMatches) $pass = false;
        }

        // Test Livewire BookingForm dynamic catalog resolution
        $form = new BookingForm();
        $form->origin = $orig;
        $form->destination = $dest;
        $catalog = $form->vehicleRateCatalog();

        $formMotorcycle = $catalog->firstWhere('name', 'Motorcycle');
        $formSuv = $catalog->firstWhere('name', '4 to 4.9 meters (Regular Car / SUV)');

        $motorcycleOk = $formMotorcycle && (float) $formMotorcycle->price === (float) $expectedVehicles['Motorcycle'];
        $suvOk = $formSuv && (float) $formSuv->price === (float) $expectedVehicles['4 to 4.9 meters (Regular Car / SUV)'];

        echo "     -> BookingForm Catalog Resolution: Motorcycle=₱" . ($formMotorcycle?->price ?? 'N/A') . " (" . ($motorcycleOk ? 'OK' : 'MISMATCH') . "), SUV=₱" . ($formSuv?->price ?? 'N/A') . " (" . ($suvOk ? 'OK' : 'MISMATCH') . ")\n";
        if (! $motorcycleOk || ! $suvOk) $pass = false;
    }
}

echo "\n======================================================\n";
echo "OVERALL VERIFICATION RESULT: " . ($pass ? "ALL CHECKS PASSED!" : "SOME CHECKS FAILED!") . "\n";
echo "======================================================\n";

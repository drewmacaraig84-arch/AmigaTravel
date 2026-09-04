<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FerryRoute;
use App\Models\Operator;
use App\Models\Schedule;
use App\Models\ScheduleAccommodation;
use App\Models\TransportClass;
use App\Models\Vehicle;
use App\Models\VehicleRate;
use App\Services\LocationCodeResolver;
use App\Services\StarliteScheduleIngestionService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

echo "======================================================\n";
echo "  STARLITE FARES, VEHICLES & SCHEDULES SYNCHRONIZATION\n";
echo "======================================================\n\n";

// 1. Ensure the 4 official Starlite vehicle categories exist in vehicle_rates
echo "1. Ensuring official Starlite vehicle categories in vehicle_rates...\n";
$officialVehicleRates = [
    ['name' => 'Motorcycle', 'price' => 1440.00, 'sort_order' => 1],
    ['name' => 'Below 3 meters', 'price' => 2160.00, 'sort_order' => 2],
    ['name' => '3 to 3.9 meters (Small Car)', 'price' => 3100.00, 'sort_order' => 3],
    ['name' => '4 to 4.9 meters (Regular Car / SUV)', 'price' => 3840.00, 'sort_order' => 4],
];

foreach ($officialVehicleRates as $rateData) {
    VehicleRate::updateOrCreate(
        ['name' => $rateData['name']],
        [
            'price' => $rateData['price'],
            'sort_order' => $rateData['sort_order'],
            'is_active' => true,
        ]
    );
}
echo "   Vehicle categories synchronized.\n\n";

// 2. Ensure Starlite Operator exists
echo "2. Ensuring Starlite operator...\n";
$operator = Operator::firstOrCreate(
    ['name' => 'Starlite'],
    [
        'mode' => 'ferry',
        'logo_path' => 'operators/Starlite_Logo.png',
        'is_active' => true,
    ]
);
echo "   Operator ID: {$operator->id}\n\n";

// 3. Purge RED routes and schedules
echo "3. Purging RED-marked routes (Odiongan routes, LCT, Fastcraft, Red departures)...\n";
// A. Odiongan routes
$odionganRoutes = FerryRoute::where('operator', 'Starlite')
    ->where(function ($q) {
        $q->where('origin', 'like', '%Odiongan%')
          ->orWhere('destination', 'like', '%Odiongan%');
    })->get();

$purgedSchedsCount = 0;
foreach ($odionganRoutes as $r) {
    $schedIds = Schedule::where('ferry_route_id', $r->id)->pluck('id');
    if ($schedIds->isNotEmpty()) {
        DB::table('schedule_accommodations')->whereIn('schedule_id', $schedIds)->delete();
        DB::table('schedule_transport_class')->whereIn('schedule_id', $schedIds)->delete();
        $purgedSchedsCount += Schedule::whereIn('id', $schedIds)->delete();
    }
    $r->delete();
    echo "   Deleted red route: {$r->origin} -> {$r->destination}\n";
}

// B. LCT and Fastcraft schedules on Batangas-Calapan
$calapanRoutes = FerryRoute::where('operator', 'Starlite')
    ->where(function ($q) {
        $q->where(function ($sub) {
            $sub->where('origin', 'Batangas')->where('destination', 'Calapan');
        })->orWhere(function ($sub) {
            $sub->where('origin', 'Calapan')->where('destination', 'Batangas');
        });
    })->pluck('id');

if ($calapanRoutes->isNotEmpty()) {
    $redCalapanScheds = Schedule::whereIn('ferry_route_id', $calapanRoutes)
        ->where(function ($q) {
            $q->where('vehicle_name', 'like', '%SPRINT%')
              ->orWhere('vehicle_name', 'like', '%PACIFIC%')
              ->orWhere('vehicle_name', 'like', '%ARCHER%')
              ->orWhere('service_name', 'like', '%LCT%')
              ->orWhere('service_name', 'like', '%FASTCRAFT%');
        })->pluck('id');

    if ($redCalapanScheds->isNotEmpty()) {
        DB::table('schedule_accommodations')->whereIn('schedule_id', $redCalapanScheds)->delete();
        DB::table('schedule_transport_class')->whereIn('schedule_id', $redCalapanScheds)->delete();
        $purgedSchedsCount += Schedule::whereIn('id', $redCalapanScheds)->delete();
        echo "   Deleted " . count($redCalapanScheds) . " red LCT/Fastcraft schedules on Batangas-Calapan.\n";
    }
}

// C. Red Caticlan departures: 1:00 PM on Batangas->Caticlan and 7:30 AM on Caticlan->Batangas
$btgCatRoute = FerryRoute::where('operator', 'Starlite')->where('origin', 'Batangas')->where('destination', 'Caticlan')->first();
if ($btgCatRoute) {
    $red1pm = Schedule::where('ferry_route_id', $btgCatRoute->id)
        ->whereRaw("TIME(departure_time) BETWEEN '12:55:00' AND '13:05:00'")
        ->pluck('id');
    if ($red1pm->isNotEmpty()) {
        DB::table('schedule_accommodations')->whereIn('schedule_id', $red1pm)->delete();
        DB::table('schedule_transport_class')->whereIn('schedule_id', $red1pm)->delete();
        $purgedSchedsCount += Schedule::whereIn('id', $red1pm)->delete();
        echo "   Deleted " . count($red1pm) . " red 1:00 PM departures on Batangas -> Caticlan.\n";
    }
}

$catBtgRoute = FerryRoute::where('operator', 'Starlite')->where('origin', 'Caticlan')->where('destination', 'Batangas')->first();
if ($catBtgRoute) {
    $red730am = Schedule::where('ferry_route_id', $catBtgRoute->id)
        ->whereRaw("TIME(departure_time) BETWEEN '07:25:00' AND '07:35:00'")
        ->pluck('id');
    if ($red730am->isNotEmpty()) {
        DB::table('schedule_accommodations')->whereIn('schedule_id', $red730am)->delete();
        DB::table('schedule_transport_class')->whereIn('schedule_id', $red730am)->delete();
        $purgedSchedsCount += Schedule::whereIn('id', $red730am)->delete();
        echo "   Deleted " . count($red730am) . " red 7:30 AM departures on Caticlan -> Batangas.\n";
    }
}

echo "   Total red schedules purged: {$purgedSchedsCount}\n\n";

// 4. Ensure Starlite transport classes exist
echo "4. Ensuring Starlite transport classes in transport_classes table...\n";
$accommodationTiers = [
    ['name' => 'Reclining Seat', 'code' => 'reclining-seat', 'sort_order' => 1, 'has_bed' => false],
    ['name' => 'Economy Bed Bunk', 'code' => 'economy-bed-bunk', 'sort_order' => 2, 'has_bed' => true],
    ['name' => 'Tourist Bed Bunk', 'code' => 'tourist-bed-bunk', 'sort_order' => 3, 'has_bed' => true],
    ['name' => 'Cabin', 'code' => 'cabin', 'sort_order' => 4, 'has_bed' => true],
    ['name' => 'VIP Room (2-3 pax)', 'code' => 'vip-room-2-3-pax', 'sort_order' => 5, 'has_bed' => true],
    ['name' => 'VIP Room (5 pax)', 'code' => 'vip-room-5-pax', 'sort_order' => 6, 'has_bed' => true],
];

$tcMap = [];
foreach ($accommodationTiers as $t) {
    $tc = TransportClass::updateOrCreate(
        [
            'operator' => 'Starlite',
            'name' => $t['name'],
        ],
        [
            'operator_id' => $operator->id,
            'code' => $t['code'],
            'mode' => 'ferry',
            'has_bed' => $t['has_bed'],
            'sort_order' => $t['sort_order'],
            'is_active' => true,
        ]
    );
    $tcMap[$t['name']] = $tc;
}
echo "   Transport classes ensured.\n\n";

// 5. Synchronize pricing & accommodations for all active Starlite routes
echo "5. Synchronizing accommodations and fares for all active Starlite routes (bulk mode)...\n";

$resolver = new LocationCodeResolver();
$fareMatrix = StarliteScheduleIngestionService::STARLITE_FARE_MATRIX;
$ingestionService = new StarliteScheduleIngestionService($resolver);

$activeRoutes = FerryRoute::where('operator', 'Starlite')->get();
$totalAccommodationsAttached = 0;
$totalSchedulesUpdated = 0;
$now = now();

foreach ($activeRoutes as $route) {
    $fareConfig = $ingestionService->lookupFareMatrix($route->origin, $route->destination);
    $basePrice = (float) ($fareConfig['base_price'] ?? 680.00);
    $accommodations = $fareConfig['accommodations'] ?? [];

    if (empty($accommodations)) {
        continue;
    }

    $schedIds = Schedule::where('ferry_route_id', $route->id)->pluck('id')->all();
    $schedCount = count($schedIds);
    if ($schedCount === 0) {
        continue;
    }

    echo "   Route {$route->id}: {$route->origin} -> {$route->destination} ({$schedCount} schedules, base ₱{$basePrice})\n";

    // Bulk update schedule base price
    DB::table('schedules')->where('ferry_route_id', $route->id)->update(['price' => $basePrice]);

    // Clean out existing accommodations and pivots for these schedules in chunks
    foreach (array_chunk($schedIds, 250) as $chunk) {
        DB::table('schedule_accommodations')->whereIn('schedule_id', $chunk)->delete();
        DB::table('schedule_transport_class')->whereIn('schedule_id', $chunk)->delete();
    }

    $allAcc = [];
    $allPivot = [];

    foreach ($schedIds as $sId) {
        foreach ($accommodations as $acc) {
            $allAcc[] = [
                'schedule_id' => $sId,
                'name' => $acc['name'],
                'price' => (float) $acc['price'],
                'tickets_available' => $acc['tickets'] ?? 50,
                'has_bed' => (bool) ($acc['has_bed'] ?? false),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $tc = $tcMap[$acc['name']] ?? null;
            if ($tc) {
                $allPivot[] = [
                    'schedule_id' => $sId,
                    'transport_class_id' => $tc->id,
                    'additional_price' => (float) $acc['price'],
                    'tickets_available' => $acc['tickets'] ?? 50,
                    'rate_type' => 'regular',
                    'is_promo' => false,
                    'has_bed' => (bool) ($acc['has_bed'] ?? false),
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }
    }

    foreach (array_chunk($allAcc, 250) as $chunk) {
        DB::table('schedule_accommodations')->insert($chunk);
        $totalAccommodationsAttached += count($chunk);
    }

    foreach (array_chunk($allPivot, 250) as $chunk) {
        DB::table('schedule_transport_class')->insert($chunk);
    }

    $totalSchedulesUpdated += $schedCount;
}

echo "\n   Updated {$totalSchedulesUpdated} schedules with {$totalAccommodationsAttached} accommodation entries.\n\n";

// 6. Bust Caches
echo "6. Busting caches...\n";
Schedule::bust();
VehicleRate::bust();
TransportClass::bust();
\Illuminate\Support\Facades\Cache::forget('api:vehicle_rates_v3');
\Illuminate\Support\Facades\Cache::forget('api:vehicle_rates');
echo "   All caches busted successfully.\n\n";

echo "======================================================\n";
echo "  STARLITE SYNCHRONIZATION COMPLETED SUCCESSFULLY!\n";
echo "======================================================\n";

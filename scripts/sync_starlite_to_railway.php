<?php

/**
 * Sync Starlite schedules to Railway production database.
 * Uses bulk queries to avoid per-row round trips over high-latency connection.
 * WRITE-ONLY: Never deletes existing data.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// ── Configure Railway Production ──
Config::set('database.connections.railway', [
    'driver'    => 'mysql',
    'host'      => 'kodama.proxy.rlwy.net',
    'port'      => 34553,
    'database'  => 'railway',
    'username'  => 'root',
    'password'  => 'CvPVaydCTsLSQigiGlbOaEYYhcqiYVsk',
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix'    => '',
    'strict'    => true,
    'engine'    => null,
]);

$db = DB::connection('railway');

echo "=== Connecting to Railway Production Database ===" . PHP_EOL;
$info = $db->selectOne('SELECT DATABASE() as db');
echo "Connected to: {$info->db}" . PHP_EOL;

if ($info->db !== 'railway') {
    die("Safety check failed: Not connected to 'railway'. Aborting." . PHP_EOL);
}

$beforeCount = $db->table('schedules')->where('is_active', 1)->count();
echo "Active schedules BEFORE sync: {$beforeCount}" . PHP_EOL;

// ── Step 1: Get the operator ──
echo PHP_EOL . "=== Step 1: Ensure Starlite operator exists ===" . PHP_EOL;
$operator = $db->table('operators')->where('name', 'Starlite')->first();
if (!$operator) {
    $opId = $db->table('operators')->insertGetId([
        'name'      => 'Starlite',
        'mode'      => 'ferry',
        'logo_path' => 'operators/Starlite_Logo.png',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "Created Starlite operator (ID: {$opId})" . PHP_EOL;
} else {
    $opId = $operator->id;
    echo "Found Starlite operator (ID: {$opId})" . PHP_EOL;
}

// ── Step 2: Fetch existing Starlite routes ──
echo PHP_EOL . "=== Step 2: Fetch existing Starlite routes ===" . PHP_EOL;
$existingRoutes = $db->table('ferry_routes')
    ->where(function($q) use ($opId) {
        $q->where('operator', 'Starlite')->orWhere('operator_id', $opId);
    })
    ->get()
    ->keyBy(fn($r) => $r->origin . '|' . $r->destination);

echo "Found " . $existingRoutes->count() . " existing Starlite routes" . PHP_EOL;

// ── Step 3: Pre-fetch ALL existing Starlite schedule departure_times ──
echo PHP_EOL . "=== Step 3: Pre-fetch existing schedule keys (one query) ===" . PHP_EOL;
$existingScheduleKeys = collect();
$routeIds = $existingRoutes->pluck('id')->toArray();

if (!empty($routeIds)) {
    $existingScheduleKeys = $db->table('schedules')
        ->whereIn('ferry_route_id', $routeIds)
        ->select('ferry_route_id', 'departure_time')
        ->get()
        ->map(fn($s) => $s->ferry_route_id . '|' . $s->departure_time)
        ->flip(); // flip to use isset() for O(1) lookups
}
echo "Found " . $existingScheduleKeys->count() . " existing schedule entries" . PHP_EOL;

// ── Route + schedule data ──
$routesData = [
    ['origin' => 'Batangas', 'destination' => 'Calapan', 'schedules' => [
        ['service_name' => 'Starlite Eagle', 'vehicle_name' => 'MV Starlite Eagle', 'plate_no' => 'STE-101', 'dep_time' => '08:00:00', 'duration' => 120, 'price' => 680.00],
        ['service_name' => 'Starlite Pioneer', 'vehicle_name' => 'MV Starlite Pioneer', 'plate_no' => 'STP-102', 'dep_time' => '14:00:00', 'duration' => 120, 'price' => 680.00],
        ['service_name' => 'Starlite Saturn', 'vehicle_name' => 'MV Starlite Saturn', 'plate_no' => 'STS-103', 'dep_time' => '20:00:00', 'duration' => 120, 'price' => 680.00],
    ], 'accommodations' => [
        ['name' => 'Reclining Seat', 'description' => 'Air-conditioned reclining seat accommodation.', 'price' => 680.00, 'has_bed' => false, 'sort_order' => 1],
        ['name' => 'Economy Bed Bunk', 'description' => 'Air-conditioned bunk bed accommodation.', 'price' => 680.00, 'has_bed' => true, 'sort_order' => 2],
        ['name' => 'Tourist Bed Bunk', 'description' => 'Comfortable tourist class bed accommodation.', 'price' => 680.00, 'has_bed' => true, 'sort_order' => 3],
    ]],
    ['origin' => 'Calapan', 'destination' => 'Batangas', 'schedules' => [
        ['service_name' => 'Starlite Eagle', 'vehicle_name' => 'MV Starlite Eagle', 'plate_no' => 'STE-101', 'dep_time' => '08:00:00', 'duration' => 120, 'price' => 680.00],
        ['service_name' => 'Starlite Pioneer', 'vehicle_name' => 'MV Starlite Pioneer', 'plate_no' => 'STP-102', 'dep_time' => '14:00:00', 'duration' => 120, 'price' => 680.00],
        ['service_name' => 'Starlite Saturn', 'vehicle_name' => 'MV Starlite Saturn', 'plate_no' => 'STS-103', 'dep_time' => '20:00:00', 'duration' => 120, 'price' => 680.00],
    ], 'accommodations' => [
        ['name' => 'Reclining Seat', 'description' => 'Air-conditioned reclining seat accommodation.', 'price' => 680.00, 'has_bed' => false, 'sort_order' => 1],
        ['name' => 'Economy Bed Bunk', 'description' => 'Air-conditioned bunk bed accommodation.', 'price' => 680.00, 'has_bed' => true, 'sort_order' => 2],
        ['name' => 'Tourist Bed Bunk', 'description' => 'Comfortable tourist class bed accommodation.', 'price' => 680.00, 'has_bed' => true, 'sort_order' => 3],
    ]],
    ['origin' => 'Batangas', 'destination' => 'Caticlan', 'schedules' => [
        ['service_name' => 'MV Starlite Archer', 'vehicle_name' => 'MV Starlite Archer', 'plate_no' => 'STA-201', 'dep_time' => '18:00:00', 'duration' => 540, 'price' => 2170.00],
    ], 'accommodations' => [
        ['name' => 'Reclining Seat', 'description' => 'Comfortable reclining seat.', 'price' => 2170.00, 'has_bed' => false, 'sort_order' => 1],
        ['name' => 'Economy Bed Bunk', 'description' => 'Air-conditioned lower/upper bunk.', 'price' => 2270.00, 'has_bed' => true, 'sort_order' => 2],
        ['name' => 'Tourist Bed Bunk', 'description' => 'Spacious tourist class bed.', 'price' => 2790.00, 'has_bed' => true, 'sort_order' => 3],
        ['name' => 'Cabin', 'description' => 'Shared 4-berth cabin with privacy.', 'price' => 3720.00, 'has_bed' => true, 'sort_order' => 4],
        ['name' => 'VIP Room (2-3 pax)', 'description' => 'Exclusive VIP room with en-suite bath.', 'price' => 8300.00, 'has_bed' => true, 'sort_order' => 5],
    ]],
    ['origin' => 'Caticlan', 'destination' => 'Batangas', 'schedules' => [
        ['service_name' => 'MV Starlite Archer', 'vehicle_name' => 'MV Starlite Archer', 'plate_no' => 'STA-201', 'dep_time' => '18:00:00', 'duration' => 540, 'price' => 2170.00],
    ], 'accommodations' => [
        ['name' => 'Reclining Seat', 'description' => 'Comfortable reclining seat.', 'price' => 2170.00, 'has_bed' => false, 'sort_order' => 1],
        ['name' => 'Economy Bed Bunk', 'description' => 'Air-conditioned lower/upper bunk.', 'price' => 2270.00, 'has_bed' => true, 'sort_order' => 2],
        ['name' => 'Tourist Bed Bunk', 'description' => 'Spacious tourist class bed.', 'price' => 2790.00, 'has_bed' => true, 'sort_order' => 3],
        ['name' => 'Cabin', 'description' => 'Shared 4-berth cabin with privacy.', 'price' => 3720.00, 'has_bed' => true, 'sort_order' => 4],
        ['name' => 'VIP Room (2-3 pax)', 'description' => 'Exclusive VIP room with en-suite bath.', 'price' => 8300.00, 'has_bed' => true, 'sort_order' => 5],
    ]],
    ['origin' => 'Batangas', 'destination' => 'Roxas City, Capiz', 'schedules' => [
        ['service_name' => 'MV Starlite Annapolis', 'vehicle_name' => 'MV Starlite Annapolis', 'plate_no' => 'STA-301', 'dep_time' => '16:00:00', 'duration' => 780, 'price' => 2580.00],
    ], 'accommodations' => [
        ['name' => 'Reclining Seat', 'description' => 'Comfortable reclining seat.', 'price' => 2580.00, 'has_bed' => false, 'sort_order' => 1],
        ['name' => 'Economy Bed Bunk', 'description' => 'Air-conditioned bunk bed.', 'price' => 2580.00, 'has_bed' => true, 'sort_order' => 2],
        ['name' => 'Tourist Bed Bunk', 'description' => 'Spacious tourist class bed.', 'price' => 3200.00, 'has_bed' => true, 'sort_order' => 3],
        ['name' => 'Cabin', 'description' => 'Shared cabin.', 'price' => 3820.00, 'has_bed' => true, 'sort_order' => 4],
        ['name' => 'VIP Room (2-3 pax)', 'description' => 'Exclusive private VIP room.', 'price' => 11500.00, 'has_bed' => true, 'sort_order' => 5],
    ]],
    ['origin' => 'Roxas City, Capiz', 'destination' => 'Batangas', 'schedules' => [
        ['service_name' => 'MV Starlite Annapolis', 'vehicle_name' => 'MV Starlite Annapolis', 'plate_no' => 'STA-301', 'dep_time' => '16:00:00', 'duration' => 780, 'price' => 2580.00],
    ], 'accommodations' => [
        ['name' => 'Reclining Seat', 'description' => 'Comfortable reclining seat.', 'price' => 2580.00, 'has_bed' => false, 'sort_order' => 1],
        ['name' => 'Economy Bed Bunk', 'description' => 'Air-conditioned bunk bed.', 'price' => 2580.00, 'has_bed' => true, 'sort_order' => 2],
        ['name' => 'Tourist Bed Bunk', 'description' => 'Spacious tourist class bed.', 'price' => 3200.00, 'has_bed' => true, 'sort_order' => 3],
        ['name' => 'Cabin', 'description' => 'Shared cabin.', 'price' => 3820.00, 'has_bed' => true, 'sort_order' => 4],
        ['name' => 'VIP Room (2-3 pax)', 'description' => 'Exclusive private VIP room.', 'price' => 11500.00, 'has_bed' => true, 'sort_order' => 5],
    ]],
    ['origin' => 'Cebu', 'destination' => 'Surigao', 'schedules' => [
        ['service_name' => 'MV Starlite Stella Maris', 'vehicle_name' => 'MV Starlite Stella Maris', 'plate_no' => 'SSM-401', 'dep_time' => '20:00:00', 'duration' => 480, 'price' => 1550.00],
    ], 'accommodations' => [
        ['name' => 'Reclining Seat', 'description' => 'Comfortable reclining seat.', 'price' => 1550.00, 'has_bed' => false, 'sort_order' => 1],
        ['name' => 'Economy Bed Bunk', 'description' => 'Air-conditioned bunk bed.', 'price' => 1650.00, 'has_bed' => true, 'sort_order' => 2],
        ['name' => 'Tourist Bed Bunk', 'description' => 'Spacious tourist class bed.', 'price' => 1960.00, 'has_bed' => true, 'sort_order' => 3],
        ['name' => 'Cabin', 'description' => 'Shared 4-berth cabin.', 'price' => 2380.00, 'has_bed' => true, 'sort_order' => 4],
        ['name' => 'VIP Room (2-3 pax)', 'description' => 'VIP room with bath.', 'price' => 7700.00, 'has_bed' => true, 'sort_order' => 5],
    ]],
    ['origin' => 'Surigao', 'destination' => 'Cebu', 'schedules' => [
        ['service_name' => 'MV Starlite Stella Maris', 'vehicle_name' => 'MV Starlite Stella Maris', 'plate_no' => 'SSM-401', 'dep_time' => '20:00:00', 'duration' => 480, 'price' => 1550.00],
    ], 'accommodations' => [
        ['name' => 'Reclining Seat', 'description' => 'Comfortable reclining seat.', 'price' => 1550.00, 'has_bed' => false, 'sort_order' => 1],
        ['name' => 'Economy Bed Bunk', 'description' => 'Air-conditioned bunk bed.', 'price' => 1650.00, 'has_bed' => true, 'sort_order' => 2],
        ['name' => 'Tourist Bed Bunk', 'description' => 'Spacious tourist class bed.', 'price' => 1960.00, 'has_bed' => true, 'sort_order' => 3],
        ['name' => 'Cabin', 'description' => 'Shared 4-berth cabin.', 'price' => 2380.00, 'has_bed' => true, 'sort_order' => 4],
        ['name' => 'VIP Room (2-3 pax)', 'description' => 'VIP room with bath.', 'price' => 7700.00, 'has_bed' => true, 'sort_order' => 5],
    ]],
    ['origin' => 'Cebu', 'destination' => 'Dapitan', 'schedules' => [
        ['service_name' => 'MV Starlite Salve Regina', 'vehicle_name' => 'MV Starlite Salve Regina', 'plate_no' => 'SSR-501', 'dep_time' => '21:00:00', 'duration' => 480, 'price' => 1130.00],
    ], 'accommodations' => [
        ['name' => 'Reclining Seat', 'description' => 'Comfortable reclining seat.', 'price' => 1130.00, 'has_bed' => false, 'sort_order' => 1],
        ['name' => 'Economy Bed Bunk', 'description' => 'Air-conditioned bunk bed.', 'price' => 1440.00, 'has_bed' => true, 'sort_order' => 2],
        ['name' => 'Tourist Bed Bunk', 'description' => 'Spacious tourist class bed.', 'price' => 1860.00, 'has_bed' => true, 'sort_order' => 3],
        ['name' => 'Cabin', 'description' => 'Shared 4-berth cabin.', 'price' => 2270.00, 'has_bed' => true, 'sort_order' => 4],
        ['name' => 'VIP Room (2-3 pax)', 'description' => 'VIP room with bath.', 'price' => 7700.00, 'has_bed' => true, 'sort_order' => 5],
    ]],
    ['origin' => 'Dapitan', 'destination' => 'Cebu', 'schedules' => [
        ['service_name' => 'MV Starlite Salve Regina', 'vehicle_name' => 'MV Starlite Salve Regina', 'plate_no' => 'SSR-501', 'dep_time' => '21:00:00', 'duration' => 480, 'price' => 1130.00],
    ], 'accommodations' => [
        ['name' => 'Reclining Seat', 'description' => 'Comfortable reclining seat.', 'price' => 1130.00, 'has_bed' => false, 'sort_order' => 1],
        ['name' => 'Economy Bed Bunk', 'description' => 'Air-conditioned bunk bed.', 'price' => 1440.00, 'has_bed' => true, 'sort_order' => 2],
        ['name' => 'Tourist Bed Bunk', 'description' => 'Spacious tourist class bed.', 'price' => 1860.00, 'has_bed' => true, 'sort_order' => 3],
        ['name' => 'Cabin', 'description' => 'Shared 4-berth cabin.', 'price' => 2270.00, 'has_bed' => true, 'sort_order' => 4],
        ['name' => 'VIP Room (2-3 pax)', 'description' => 'VIP room with bath.', 'price' => 7700.00, 'has_bed' => true, 'sort_order' => 5],
    ]],
    ['origin' => 'Roxas Mindoro', 'destination' => 'Caticlan', 'schedules' => [
        ['service_name' => 'MV Starlite Sprint 1', 'vehicle_name' => 'MV Starlite Sprint 1', 'plate_no' => 'SSS-601', 'dep_time' => '08:00:00', 'duration' => 240, 'price' => 750.00],
        ['service_name' => 'MV Starlite Pacific', 'vehicle_name' => 'MV Starlite Pacific', 'plate_no' => 'SSP-602', 'dep_time' => '14:00:00', 'duration' => 240, 'price' => 750.00],
    ], 'accommodations' => [
        ['name' => 'Reclining Seat', 'description' => 'Comfortable reclining seat.', 'price' => 750.00, 'has_bed' => false, 'sort_order' => 1],
        ['name' => 'Economy Bed Bunk', 'description' => 'Air-conditioned bunk bed.', 'price' => 850.00, 'has_bed' => true, 'sort_order' => 2],
        ['name' => 'Tourist Bed Bunk', 'description' => 'Spacious tourist class bed.', 'price' => 1050.00, 'has_bed' => true, 'sort_order' => 3],
    ]],
    ['origin' => 'Caticlan', 'destination' => 'Roxas Mindoro', 'schedules' => [
        ['service_name' => 'MV Starlite Sprint 1', 'vehicle_name' => 'MV Starlite Sprint 1', 'plate_no' => 'SSS-601', 'dep_time' => '08:00:00', 'duration' => 240, 'price' => 750.00],
        ['service_name' => 'MV Starlite Pacific', 'vehicle_name' => 'MV Starlite Pacific', 'plate_no' => 'SSP-602', 'dep_time' => '14:00:00', 'duration' => 240, 'price' => 750.00],
    ], 'accommodations' => [
        ['name' => 'Reclining Seat', 'description' => 'Comfortable reclining seat.', 'price' => 750.00, 'has_bed' => false, 'sort_order' => 1],
        ['name' => 'Economy Bed Bunk', 'description' => 'Air-conditioned bunk bed.', 'price' => 850.00, 'has_bed' => true, 'sort_order' => 2],
        ['name' => 'Tourist Bed Bunk', 'description' => 'Spacious tourist class bed.', 'price' => 1050.00, 'has_bed' => true, 'sort_order' => 3],
    ]],
];

// ── Step 4: Ensure vehicles exist ──
echo PHP_EOL . "=== Step 4: Ensure vehicles exist ===" . PHP_EOL;
$vehicleMap = []; // plate_no => id
$allPlates = [];
foreach ($routesData as $rd) {
    foreach ($rd['schedules'] as $s) {
        $allPlates[$s['plate_no']] = $s;
    }
}
$existingVehicles = $db->table('vehicles')->whereIn('vehicle_id', array_keys($allPlates))->get()->keyBy('vehicle_id');
foreach ($allPlates as $plate => $sData) {
    if (isset($existingVehicles[$plate])) {
        $vehicleMap[$plate] = $existingVehicles[$plate]->id;
    } else {
        $vId = $db->table('vehicles')->insertGetId([
            'vehicle_id'  => $plate,
            'name'        => $sData['vehicle_name'],
            'type'        => 'ferry',
            'operator'    => 'Starlite',
            'operator_id' => $opId,
            'is_active'   => true,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
        $vehicleMap[$plate] = $vId;
        echo "  Created vehicle: {$sData['vehicle_name']} (ID: {$vId})" . PHP_EOL;
    }
}
echo "Vehicles ready: " . count($vehicleMap) . PHP_EOL;

// ── Step 5: Ensure routes exist ──
echo PHP_EOL . "=== Step 5: Ensure routes exist ===" . PHP_EOL;
$routeMap = []; // origin|destination => id
foreach ($routesData as $rd) {
    $key = $rd['origin'] . '|' . $rd['destination'];
    if (isset($existingRoutes[$key])) {
        $routeMap[$key] = $existingRoutes[$key]->id;
    } else {
        $firstPlate = $rd['schedules'][0]['plate_no'];
        $rId = $db->table('ferry_routes')->insertGetId([
            'origin'      => $rd['origin'],
            'destination'  => $rd['destination'],
            'mode'        => 'ferry',
            'vehicle_id'  => $vehicleMap[$firstPlate],
            'operator'    => 'Starlite',
            'operator_id' => $opId,
            'trip_type'   => 'local',
            'is_active'   => true,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
        $routeMap[$key] = $rId;
        echo "  Created route: {$rd['origin']} -> {$rd['destination']} (ID: {$rId})" . PHP_EOL;
    }
}
echo "Routes ready: " . count($routeMap) . PHP_EOL;

// ── Step 6: Ensure transport classes exist ──
echo PHP_EOL . "=== Step 6: Ensure transport classes exist ===" . PHP_EOL;
$existingTC = $db->table('transport_classes')->where('operator', 'Starlite')->get()->keyBy('code');
$tcMap = []; // name => id
$allAccNames = [];
foreach ($routesData as $rd) {
    foreach ($rd['accommodations'] as $acc) {
        $code = \Illuminate\Support\Str::slug($acc['name']);
        $allAccNames[$acc['name']] = ['code' => $code, 'data' => $acc];
    }
}
foreach ($allAccNames as $name => $info) {
    $code = $info['code'];
    $acc = $info['data'];
    if (isset($existingTC[$code])) {
        $tcMap[$name] = $existingTC[$code]->id;
    } else {
        $tcId = $db->table('transport_classes')->insertGetId([
            'operator'    => 'Starlite',
            'operator_id' => $opId,
            'code'        => $code,
            'name'        => $name,
            'description' => $acc['description'] ?? null,
            'price'       => $acc['price'] ?? 0,
            'is_active'   => true,
            'sort_order'  => $acc['sort_order'] ?? 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
        $tcMap[$name] = $tcId;
        echo "  Created transport class: {$name} (ID: {$tcId})" . PHP_EOL;
    }
}
echo "Transport classes ready: " . count($tcMap) . PHP_EOL;

// ── Step 7: Generate missing schedules in bulk ──
echo PHP_EOL . "=== Step 7: Generate missing schedules (bulk insert) ===" . PHP_EOL;

$startDate = Carbon::today();
$endDate = Carbon::parse('2026-12-31');
$now = Carbon::now();
$totalCreated = 0;
$scheduleBatch = [];
$batchSize = 200;

// Track new schedule IDs for accommodation/pivot inserts
$newScheduleIds = [];

foreach ($routesData as $rd) {
    $routeKey = $rd['origin'] . '|' . $rd['destination'];
    $routeId = $routeMap[$routeKey];

    foreach ($rd['schedules'] as $sData) {
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $depTime = Carbon::parse($date->format('Y-m-d') . ' ' . $sData['dep_time']);
            $arrTime = $depTime->copy()->addMinutes($sData['duration']);
            $lookupKey = $routeId . '|' . $depTime->format('Y-m-d H:i:s');

            if (isset($existingScheduleKeys[$lookupKey])) {
                continue; // Already exists, skip
            }

            $scheduleBatch[] = [
                'ferry_route_id'     => $routeId,
                'service_name'       => $sData['service_name'],
                'vehicle_name'       => $sData['vehicle_name'],
                'plate_no'           => $sData['plate_no'],
                'departure_time'     => $depTime->format('Y-m-d H:i:s'),
                'arrival_time'       => $arrTime->format('Y-m-d H:i:s'),
                'duration_minutes'   => $sData['duration'],
                'price'              => $sData['price'],
                'availability_label' => 'Available',
                'seat_rows'          => 15,
                'seat_columns'       => json_encode(['A', 'B', 'C', 'D', 'E', 'F']),
                'is_active'          => true,
                'created_at'         => $now->format('Y-m-d H:i:s'),
                'updated_at'         => $now->format('Y-m-d H:i:s'),
            ];

            // Mark in the key set so duplicates within our own batch are caught
            $existingScheduleKeys[$lookupKey] = true;

            // Track which route this belongs to for accommodation inserts
            $newScheduleIds[] = [
                'route_key' => $routeKey,
                'dep_time'  => $depTime->format('Y-m-d H:i:s'),
            ];

            if (count($scheduleBatch) >= $batchSize) {
                $db->table('schedules')->insert($scheduleBatch);
                $totalCreated += count($scheduleBatch);
                echo "  Inserted batch: {$totalCreated} schedules so far..." . PHP_EOL;
                $scheduleBatch = [];
            }
        }
    }
}

// Flush remaining
if (!empty($scheduleBatch)) {
    $db->table('schedules')->insert($scheduleBatch);
    $totalCreated += count($scheduleBatch);
}
echo "Total new schedules inserted: {$totalCreated}" . PHP_EOL;

// ── Step 8: Insert accommodations and pivot for new schedules ──
if ($totalCreated > 0) {
    echo PHP_EOL . "=== Step 8: Insert accommodations & transport class pivots ===" . PHP_EOL;

    // Re-fetch new schedule IDs (those just created)
    // We'll do this by querying schedules created_at = $now
    $newSchedules = $db->table('schedules')
        ->where('created_at', '>=', $now->format('Y-m-d H:i:s'))
        ->whereIn('ferry_route_id', array_values($routeMap))
        ->select('id', 'ferry_route_id', 'departure_time')
        ->get();

    echo "Found {$newSchedules->count()} newly created schedules" . PHP_EOL;

    // Build a reverse map: route_id => accommodations
    $routeAccMap = [];
    foreach ($routesData as $rd) {
        $routeKey = $rd['origin'] . '|' . $rd['destination'];
        $routeId = $routeMap[$routeKey];
        $routeAccMap[$routeId] = $rd['accommodations'];
    }

    $accBatch = [];
    $pivotBatch = [];

    foreach ($newSchedules as $sched) {
        $accs = $routeAccMap[$sched->ferry_route_id] ?? [];
        foreach ($accs as $acc) {
            $accBatch[] = [
                'schedule_id'       => $sched->id,
                'name'              => $acc['name'],
                'description'       => $acc['description'] ?? null,
                'price'             => $acc['price'] ?? 0,
                'tickets_available' => 50,
                'has_bed'           => $acc['has_bed'] ?? false,
                'is_active'         => true,
                'sort_order'        => $acc['sort_order'] ?? 1,
                'created_at'        => $now->format('Y-m-d H:i:s'),
                'updated_at'        => $now->format('Y-m-d H:i:s'),
            ];

            if (isset($tcMap[$acc['name']])) {
                $pivotBatch[] = [
                    'schedule_id'        => $sched->id,
                    'transport_class_id' => $tcMap[$acc['name']],
                    'additional_price'   => $acc['price'] ?? 0,
                    'tickets_available'  => 50,
                    'description'        => $acc['description'] ?? null,
                    'has_bed'            => $acc['has_bed'] ?? false,
                    'is_active'          => true,
                    'created_at'         => $now->format('Y-m-d H:i:s'),
                    'updated_at'         => $now->format('Y-m-d H:i:s'),
                ];
            }
        }

        // Flush in chunks
        if (count($accBatch) >= 500) {
            $db->table('schedule_accommodations')->insert($accBatch);
            echo "  Inserted " . count($accBatch) . " accommodations..." . PHP_EOL;
            $accBatch = [];
        }
        if (count($pivotBatch) >= 500) {
            $db->table('schedule_transport_class')->insert($pivotBatch);
            echo "  Inserted " . count($pivotBatch) . " transport class pivots..." . PHP_EOL;
            $pivotBatch = [];
        }
    }

    // Flush remaining
    if (!empty($accBatch)) {
        $db->table('schedule_accommodations')->insert($accBatch);
        echo "  Inserted final " . count($accBatch) . " accommodations" . PHP_EOL;
    }
    if (!empty($pivotBatch)) {
        $db->table('schedule_transport_class')->insert($pivotBatch);
        echo "  Inserted final " . count($pivotBatch) . " transport class pivots" . PHP_EOL;
    }
}

// ── Final Summary ──
echo PHP_EOL . "=== FINAL SUMMARY ===" . PHP_EOL;
$afterCount = $db->table('schedules')->where('is_active', 1)->count();
echo "Active schedules AFTER sync: {$afterCount}" . PHP_EOL;
echo "New schedules added: {$totalCreated}" . PHP_EOL;

$summary = $db->table('schedules')
    ->join('ferry_routes', 'schedules.ferry_route_id', '=', 'ferry_routes.id')
    ->where('ferry_routes.operator', 'Starlite')
    ->where('schedules.is_active', 1)
    ->selectRaw('ferry_routes.origin, ferry_routes.destination, count(*) as cnt, MIN(schedules.departure_time) as min_dep, MAX(schedules.departure_time) as max_dep')
    ->groupBy('ferry_routes.origin', 'ferry_routes.destination')
    ->get();

foreach ($summary as $row) {
    echo "{$row->origin} -> {$row->destination}: {$row->cnt} departures ({$row->min_dep} to {$row->max_dep})" . PHP_EOL;
}

echo PHP_EOL . "=== DONE! Production sync complete. ===" . PHP_EOL;

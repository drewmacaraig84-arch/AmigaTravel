<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

$isDryRun = !in_array('--force', $argv);

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

echo "=== FAST BATCH IMPORT: 2GO SCHEDULES TO RAILWAY (" . ($isDryRun ? "DRY-RUN" : "LIVE") . ") ===\n\n";

$csvPath = base_path('2go_schedules/2GO_All_3_Routes_Combined.csv');
if (!file_exists($csvPath)) {
    die("File not found: {$csvPath}\n");
}

$lines = file($csvPath, FILE_IGNORE_NEW_LINES);
$header = str_getcsv(array_shift($lines));

$rows = [];
foreach ($lines as $line) {
    if (trim($line) === '') continue;
    $rows[] = array_combine($header, str_getcsv($line));
}

echo "Total CSV rows to import: " . count($rows) . "\n";

// 1. Resolve Operator
$operator = $db->table('operators')->where('name', '2GO')->first();
if (!$operator) {
    die("Error: Operator 2GO not found in Railway DB.\n");
}
$operatorId = $operator->id;
echo "Operator: 2GO (ID: {$operatorId})\n";

// 2. Resolve Routes
$routeMap = [];
$dbRoutes = $db->table('ferry_routes')->where('operator', '2GO')->get();
foreach ($dbRoutes as $r) {
    $key = strtolower(trim($r->origin)) . '->' . strtolower(trim($r->destination));
    $routeMap[$key] = $r->id;
}

// 3. Resolve Vehicles
$vehicleMap = [];
$dbVehicles = $db->table('vehicles')->where('operator', '2GO')->get();
foreach ($dbVehicles as $v) {
    $vehicleMap[strtolower(trim($v->name))] = $v->id;
    if ($v->vehicle_id) {
        $vehicleMap[strtolower(trim($v->vehicle_id))] = $v->id;
    }
}

// 4. Resolve Transport Classes
$tcMap = [];
$dbTcs = $db->table('transport_classes')->where('operator', '2GO')->get();
foreach ($dbTcs as $tc) {
    $tcMap[strtolower(trim($tc->name))] = $tc->id;
}

if (!isset($tcMap['tourist class'])) {
    // Check if Tourist Class exists across ferry mode
    $genericTc = $db->table('transport_classes')->where('name', 'like', '%Tourist%')->first();
    if ($genericTc) {
        $tcMap['tourist class'] = $genericTc->id;
    }
}

// Pre-create any missing vehicles
$uniqueVessels = array_unique(array_filter(array_column($rows, 'Vehicle Tail No')));
foreach ($uniqueVessels as $vessel) {
    $vKey = strtolower(trim($vessel));
    if (!isset($vehicleMap[$vKey])) {
        echo "Creating missing vehicle on Railway: {$vessel}\n";
        if (!$isDryRun) {
            $newVId = $db->table('vehicles')->insertGetId([
                'type' => 'ferry',
                'name' => $vessel,
                'vehicle_id' => $vessel,
                'operator' => '2GO',
                'operator_id' => $operatorId,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $vehicleMap[$vKey] = $newVId;
        }
    }
}

// Ensure the 3 routes exist
$requiredRoutes = [
    'manila->cebu' => ['origin' => 'Manila', 'destination' => 'Cebu'],
    'manila->bacolod' => ['origin' => 'Manila', 'destination' => 'Bacolod'],
    'manila->butuan (nasipit)' => ['origin' => 'Manila', 'destination' => 'Butuan (nasipit)'],
];

foreach ($requiredRoutes as $key => $rInfo) {
    if (!isset($routeMap[$key])) {
        echo "Creating route on Railway: {$rInfo['origin']} -> {$rInfo['destination']}\n";
        if (!$isDryRun) {
            $newRId = $db->table('ferry_routes')->insertGetId([
                'origin' => $rInfo['origin'],
                'destination' => $rInfo['destination'],
                'mode' => 'ferry',
                'operator' => '2GO',
                'operator_id' => $operatorId,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $routeMap[$key] = $newRId;
        }
    }
}

// Get all existing schedules for these routes into memory
$targetRouteIds = array_values(array_filter(array_intersect_key($routeMap, $requiredRoutes)));
$existingSchedules = $db->table('schedules')
    ->whereIn('ferry_route_id', $targetRouteIds)
    ->select('id', 'ferry_route_id', 'departure_time')
    ->get();

$existingScheduleLookup = [];
foreach ($existingSchedules as $es) {
    // Key by route_id + minute-granularity departure
    $ts = Carbon::parse($es->departure_time)->format('Y-m-d H:i');
    $existingScheduleLookup["{$es->ferry_route_id}_{$ts}"] = $es->id;
}

echo "Existing schedules in lookup for target routes: " . count($existingScheduleLookup) . "\n";

$newSchedules = 0;
$existingSchedulesMatched = 0;
$now = now()->toDateTimeString();

if ($isDryRun) {
    echo "\n[DRY RUN SUMMARY]\n";
    foreach ($rows as $r) {
        $rKey = strtolower(trim($r['Origin'])) . '->' . strtolower(trim($r['Destination']));
        $routeId = $routeMap[$rKey] ?? null;
        $depDt = Carbon::createFromFormat('d/m/Y h:i A', "{$r['Departure Date']} {$r['Departure Time']}");
        $lookupKey = "{$routeId}_" . $depDt->format('Y-m-d H:i');
        if (isset($existingScheduleLookup[$lookupKey])) {
            $existingSchedulesMatched++;
        } else {
            $newSchedules++;
        }
    }
    echo "  New schedules to create: {$newSchedules}\n";
    echo "  Existing schedules matched (will update/attach): {$existingSchedulesMatched}\n";
    echo "\nPass --force to execute live batch import.\n";
    exit(0);
}

// LIVE EXECUTION
echo "\n--- Executing Batch Insert & Attach ---\n";

foreach ($rows as $i => $r) {
    $rKey = strtolower(trim($r['Origin'])) . '->' . strtolower(trim($r['Destination']));
    $routeId = $routeMap[$rKey];
    $depDt = Carbon::createFromFormat('d/m/Y h:i A', "{$r['Departure Date']} {$r['Departure Time']}");
    $arrDt = Carbon::createFromFormat('d/m/Y h:i A', "{$r['Arrival Date']} {$r['Arrival Time']}");

    $lookupKey = "{$routeId}_" . $depDt->format('Y-m-d H:i');
    $schedId = $existingScheduleLookup[$lookupKey] ?? null;

    $price = floatval($r['Rate']);
    $addPrice = floatval($r['Additional Price'] ?? 0);
    $rate = $price;
    $vName = trim($r['Vehicle Tail No']);
    $tClass = trim($r['Transport Class']);
    $tcKey = strtolower($tClass);
    $tcId = $tcMap[$tcKey] ?? ($tcMap['tourist class'] ?? 68);
    $tickets = intval($r['Tickets Available'] ?? 50) ?: 50;
    $hasBed = strtolower($r['Has Bed'] ?? 'yes') === 'yes' ? 1 : 0;
    $rateCode = $r['Rate Code'] ?? 'REG';

    if (!$schedId) {
        // Create Schedule
        $schedId = $db->table('schedules')->insertGetId([
            'ferry_route_id' => $routeId,
            'vehicle_name' => $vName,
            'departure_time' => $depDt->format('Y-m-d H:i:s'),
            'arrival_time' => $arrDt->format('Y-m-d H:i:s'),
            'price' => 0.00,
            'is_active' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $existingScheduleLookup[$lookupKey] = $schedId;
        $newSchedules++;
    } else {
        // Update vehicle and arrival time
        $db->table('schedules')->where('id', $schedId)->update([
            'vehicle_name' => $vName,
            'arrival_time' => $arrDt->format('Y-m-d H:i:s'),
            'is_active' => 1,
            'updated_at' => $now,
        ]);
        $existingSchedulesMatched++;
    }

    // Attach / Update schedule_transport_class
    $tcExists = $db->table('schedule_transport_class')
        ->where('schedule_id', $schedId)
        ->where('transport_class_id', $tcId)
        ->first();

    if (!$tcExists) {
        $db->table('schedule_transport_class')->insert([
            'schedule_id' => $schedId,
            'transport_class_id' => $tcId,
            'additional_price' => $rate,
            'tickets_available' => $tickets,
            'rate_type' => 'regular',
            'is_promo' => 0,
            'rate_code' => $rateCode,
            'has_bed' => $hasBed,
            'is_active' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    } else {
        $db->table('schedule_transport_class')->where('id', $tcExists->id)->update([
            'additional_price' => $rate,
            'tickets_available' => $tickets,
            'has_bed' => $hasBed,
            'is_active' => 1,
            'updated_at' => $now,
        ]);
    }

    // Attach / Update schedule_accommodations
    $accExists = $db->table('schedule_accommodations')
        ->where('schedule_id', $schedId)
        ->where('name', $tClass)
        ->first();

    if (!$accExists) {
        $db->table('schedule_accommodations')->insert([
            'schedule_id' => $schedId,
            'name' => $tClass,
            'rate_code' => $rateCode,
            'price' => $rate,
            'tickets_available' => $tickets,
            'has_bed' => $hasBed,
            'is_active' => 1,
            'sort_order' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    } else {
        $db->table('schedule_accommodations')->where('id', $accExists->id)->update([
            'price' => $rate,
            'tickets_available' => $tickets,
            'has_bed' => $hasBed,
            'is_active' => 1,
            'updated_at' => $now,
        ]);
    }

    if (($i + 1) % 25 === 0 || ($i + 1) === count($rows)) {
        echo "  Processed " . ($i + 1) . "/" . count($rows) . " rows...\n";
    }
}

echo "\nSync Results:\n";
echo "  New Schedules Created:  {$newSchedules}\n";
echo "  Existing Updated:       {$existingSchedulesMatched}\n";

// Clear Railway Cache Table
if ($db->getSchemaBuilder()->hasTable('cache')) {
    $db->table('cache')->truncate();
    echo "✓ Cleared Railway production cache table.\n";
}

// Final counts on Railway
echo "\nFinal Schedule Counts on Railway for 2GO:\n";
foreach ($requiredRoutes as $key => $rInfo) {
    $rId = $routeMap[$key];
    $cnt = $db->table('schedules')->where('ferry_route_id', $rId)->where('is_active', 1)->count();
    echo "  {$rInfo['origin']} -> {$rInfo['destination']}: {$cnt} active schedules\n";
}

echo "\n=== COMPLETED SUCCESSFULLY ===\n";

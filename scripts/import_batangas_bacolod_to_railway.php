<?php
/**
 * Import 2GO Batangas -> Bacolod schedules directly to Railway DB
 *
 * Usage:
 *   php scripts/import_batangas_bacolod_to_railway.php          (dry-run)
 *   php scripts/import_batangas_bacolod_to_railway.php --force  (live import)
 */

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

echo "=== IMPORT 2GO BATANGAS -> BACOLOD (" . ($isDryRun ? "DRY-RUN" : "LIVE") . ") ===\n\n";

$csvPath = base_path('2go_schedules/2GO_Batangas_Bacolod.csv');
if (!file_exists($csvPath)) {
    die("File not found: {$csvPath}\n");
}

$lines = array_values(array_filter(file($csvPath, FILE_IGNORE_NEW_LINES)));
$header = str_getcsv(array_shift($lines));
$header = array_map(fn($h) => trim(strtolower(str_replace([' ', '.', '_', '-'], '', $h))), $header);

// 1. Resolve Operator
$operator = $db->table('operators')->where('name', '2GO')->first();
$operatorId = $operator?->id ?? 1;

// 2. Resolve / Create Route Batangas -> Bacolod
$route = $db->table('ferry_routes')
    ->where('operator', '2GO')
    ->whereRaw('LOWER(origin) = ?', ['batangas'])
    ->whereRaw('LOWER(destination) = ?', ['bacolod'])
    ->first();

if (!$route) {
    echo "Route Batangas -> Bacolod not found, creating...\n";
    if (!$isDryRun) {
        $anyVehicle = $db->table('vehicles')->where('operator', '2GO')->first();
        $routeId = $db->table('ferry_routes')->insertGetId([
            'origin'      => 'Batangas',
            'destination' => 'Bacolod',
            'mode'        => 'ferry',
            'operator'    => '2GO',
            'operator_id' => $operatorId,
            'vehicle_id'  => $anyVehicle?->id,
            'is_active'   => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
        echo "Created route ID: {$routeId}\n";
    } else {
        $routeId = 0;
        echo "Will create route ID on live run.\n";
    }
} else {
    $routeId = $route->id;
    echo "Found route ID: {$routeId} (Batangas -> Bacolod)\n";
}

// 3. Resolve Transport Class
$tc = $db->table('transport_classes')
    ->where('operator', '2GO')
    ->where(function ($q) {
        $q->where('name', 'Tourist Class')->orWhere('name', 'like', '%Tourist%');
    })->first();
if (!$tc) {
    $tc = $db->table('transport_classes')->where('name', 'like', '%Tourist%')->first();
}
$tcId = $tc?->id ?? 14;

// 4. Resolve / Create Vehicle
$vehicleMap = [];
$dbVehicles = $db->table('vehicles')->where('operator', '2GO')->get();
foreach ($dbVehicles as $v) {
    $vehicleMap[strtolower(trim($v->name))] = $v->id;
    if ($v->vehicle_id) $vehicleMap[strtolower(trim($v->vehicle_id))] = $v->id;
}

// Load existing schedules
$existingLookup = [];
if ($routeId) {
    $existing = $db->table('schedules')->where('ferry_route_id', $routeId)->get();
    foreach ($existing as $e) {
        $key = Carbon::parse($e->departure_time)->format('Y-m-d H:i');
        $existingLookup[$key] = $e->id;
    }
}

$now = now()->toDateTimeString();
$created = 0;
$updated = 0;

foreach ($lines as $idx => $line) {
    if (trim($line) === '') continue;
    $cols = str_getcsv($line);
    if (count($cols) < count($header)) {
        $cols = array_pad($cols, count($header), '');
    }
    $row = array_combine(array_slice($header, 0, count($cols)), array_slice($cols, 0, count($header)));

    $vessel = trim($row['vehicletailno'] ?? 'MV St. Michael the Archangel', " \t\n\r\0\x0B.");
    $depDate = trim($row['departuredate'] ?? '');
    $depTime = trim($row['departuretime'] ?? '');
    $arrDate = trim($row['arrivaldate'] ?? ($row['arivaldate'] ?? $depDate));
    $arrTime = trim($row['arrivaltime'] ?? '');
    $tClass = trim($row['transportclass'] ?? 'Tourist Class');
    $rateRaw = $row['rate'] ?? '0';
    $addRaw = $row['additionalprice'] ?? '0';
    $price = floatval(preg_replace('/[^0-9.]/', '', $addRaw ?: $rateRaw));
    $tickets = (int)preg_replace('/[^0-9]/', '', $row['ticketsavailable'] ?? '50') ?: 50;
    $hasBed = strtolower($row['hasbed'] ?? 'yes') === 'yes' ? 1 : 0;
    $rateCode = $row['ratecode'] ?? 'REG';

    $depDt = Carbon::createFromFormat('d/m/Y g:i a', "$depDate $depTime");
    $arrDt = Carbon::createFromFormat('d/m/Y g:i a', "$arrDate $arrTime");

    $vk = strtolower(trim($vessel));
    if (!isset($vehicleMap[$vk])) {
        echo "Creating vehicle: {$vessel}\n";
        if (!$isDryRun) {
            $newVId = $db->table('vehicles')->insertGetId([
                'type'        => 'ferry',
                'name'        => $vessel,
                'vehicle_id'  => $vessel,
                'operator'    => '2GO',
                'operator_id' => $operatorId,
                'is_active'   => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
            $vehicleMap[$vk] = $newVId;
        } else {
            $vehicleMap[$vk] = 9999;
        }
    }

    $depKey = $depDt->format('Y-m-d H:i');
    echo "Row " . ($idx + 2) . ": {$vessel} | Dep: {$depKey} | Arr: " . $arrDt->format('Y-m-d H:i') . " | ₱" . number_format($price, 2) . "\n";

    if ($isDryRun) continue;

    $schedId = $existingLookup[$depKey] ?? null;
    if (!$schedId) {
        $schedId = $db->table('schedules')->insertGetId([
            'ferry_route_id' => $routeId,
            'vehicle_name'   => $vessel,
            'departure_time' => $depDt->format('Y-m-d H:i:s'),
            'arrival_time'   => $arrDt->format('Y-m-d H:i:s'),
            'price'          => 0.00,
            'is_active'      => 1,
            'created_at'     => $now,
            'updated_at'     => $now,
        ]);
        $existingLookup[$depKey] = $schedId;
        $created++;
    } else {
        $db->table('schedules')->where('id', $schedId)->update([
            'vehicle_name' => $vessel,
            'arrival_time' => $arrDt->format('Y-m-d H:i:s'),
            'is_active'    => 1,
            'updated_at'   => $now,
        ]);
        $updated++;
    }

    // Transport class
    $tcPivot = $db->table('schedule_transport_class')
        ->where('schedule_id', $schedId)
        ->where('transport_class_id', $tcId)
        ->first();

    if (!$tcPivot) {
        $db->table('schedule_transport_class')->insert([
            'schedule_id'        => $schedId,
            'transport_class_id' => $tcId,
            'additional_price'   => $price,
            'tickets_available'  => $tickets,
            'rate_type'          => 'regular',
            'is_promo'           => 0,
            'rate_code'          => $rateCode,
            'has_bed'            => $hasBed,
            'is_active'          => 1,
            'created_at'         => $now,
            'updated_at'         => $now,
        ]);
    } else {
        $db->table('schedule_transport_class')->where('id', $tcPivot->id)->update([
            'additional_price'  => $price,
            'tickets_available' => $tickets,
            'has_bed'           => $hasBed,
            'is_active'         => 1,
            'updated_at'        => $now,
        ]);
    }

    // Accommodations
    $acc = $db->table('schedule_accommodations')
        ->where('schedule_id', $schedId)
        ->where('name', $tClass)
        ->first();

    if (!$acc) {
        $db->table('schedule_accommodations')->insert([
            'schedule_id'       => $schedId,
            'name'              => $tClass,
            'rate_code'         => $rateCode,
            'price'             => $price,
            'tickets_available' => $tickets,
            'has_bed'           => $hasBed,
            'is_active'         => 1,
            'sort_order'        => 0,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);
    } else {
        $db->table('schedule_accommodations')->where('id', $acc->id)->update([
            'price'             => $price,
            'tickets_available' => $tickets,
            'has_bed'           => $hasBed,
            'is_active'         => 1,
            'updated_at'        => $now,
        ]);
    }
}

echo "\nSummary:\n";
echo "  Created: {$created}\n";
echo "  Updated: {$updated}\n";

if (!$isDryRun) {
    if ($db->getSchemaBuilder()->hasTable('cache')) {
        $db->table('cache')->truncate();
        echo "✓ Cleared Railway production cache table.\n";
    }
    $finalCount = $db->table('schedules')->where('ferry_route_id', $routeId)->where('is_active', 1)->count();
    echo "Final active schedules on Batangas -> Bacolod: {$finalCount}\n";
}

echo "=== FINISHED ===\n";

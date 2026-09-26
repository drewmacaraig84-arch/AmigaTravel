<?php
/**
 * Import newly uploaded 2GO Schedules to Railway Production DB:
 * 1. Butuan (nasipit) -> Manila (13 rows) from media_1790392936046.csv
 * 2. Bacolod -> Manila (41 rows) from media_1790392936176.csv
 *
 * Usage:
 *   php scripts/import_additional_2go_to_railway.php          (dry-run)
 *   php scripts/import_additional_2go_to_railway.php --force  (live execution)
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

echo "=== IMPORT NEW 2GO SCHEDULES TO RAILWAY (" . ($isDryRun ? "DRY-RUN" : "LIVE") . ") ===\n\n";

$files = [
    [
        'label'    => 'Butuan via Nasipit -> Manila',
        'path'     => 'C:\Users\macar\.gemini\antigravity-ide\brain\26f81cef-cdc9-45eb-aac9-ff6ab3ce0a0e\.user_uploaded\media_1790392936046.csv',
        'route_id' => 33, // Butuan (nasipit) => Manila
    ],
    [
        'label'    => 'Bacolod -> Manila',
        'path'     => 'C:\Users\macar\.gemini\antigravity-ide\brain\26f81cef-cdc9-45eb-aac9-ff6ab3ce0a0e\.user_uploaded\media_1790392936176.csv',
        'route_id' => 24, // Bacolod => Manila
    ],
];

// Helper to sanitize dates
function sanitizeDate(string $d): string {
    $d = preg_replace('/\s*([\\/\\-])\s*/', '$1', trim($d));
    $d = preg_replace('/(\d{1,2}\/\d{1,2}\/)2+(\d{4})/', '$1$2', $d);
    return $d;
}

function parseDep(string $date, string $time): Carbon {
    $d = sanitizeDate($date);
    $t = trim($time);
    foreach (['d/m/Y g:i A', 'd/m/Y h:i A', 'd/m/Y g:ia', 'd/m/Y h:ia', 'm/d/Y g:i A', 'm/d/Y h:i A'] as $fmt) {
        try {
            return Carbon::createFromFormat($fmt, "$d $t");
        } catch (\Throwable) {}
    }
    return Carbon::parse("$d $t");
}

function parseArr(string $date, string $time, Carbon $dep): Carbon {
    $d = sanitizeDate($date);
    $t = trim($time);
    $candidates = [];
    foreach (['d/m/Y g:i A', 'd/m/Y h:i A', 'd/m/Y g:ia', 'd/m/Y h:ia', 'm/d/Y g:i A', 'm/d/Y h:i A'] as $fmt) {
        try {
            $c = Carbon::createFromFormat($fmt, "$d $t");
            if (!in_array($c->timestamp, array_column($candidates, 'ts'))) {
                $candidates[] = ['dt' => $c, 'ts' => $c->timestamp];
            }
        } catch (\Throwable) {}
    }

    foreach ($candidates as $item) {
        $arr = clone $item['dt'];
        if ($arr->lt($dep)) $arr->addDay();
        $diffDays = $dep->diffInDays($arr, false);
        if ($diffDays >= 0 && $diffDays <= 5) {
            return $arr;
        }
    }

    if (!empty($candidates)) {
        $arr = clone $candidates[0]['dt'];
        if ($arr->lt($dep)) $arr->addDay();
        return $arr;
    }

    $arr = Carbon::parse("$d $t");
    if ($arr->lt($dep)) $arr->addDay();
    return $arr;
}

// 1. Resolve Operator
$operator = $db->table('operators')->where('name', '2GO')->first();
$operatorId = $operator?->id ?? 1;

// 2. Resolve Transport Class
$tc = $db->table('transport_classes')
    ->where('operator', '2GO')
    ->where(function ($q) {
        $q->where('name', 'Tourist Class')->orWhere('name', 'like', '%Tourist%');
    })->first();
if (!$tc) {
    $tc = $db->table('transport_classes')->where('name', 'like', '%Tourist%')->first();
}
$tcId = $tc?->id ?? 14;

// 3. Resolve Vehicles
$vehicleMap = [];
$dbVehicles = $db->table('vehicles')->where('operator', '2GO')->get();
foreach ($dbVehicles as $v) {
    $vehicleMap[strtolower(trim($v->name))] = $v->id;
    if ($v->vehicle_id) $vehicleMap[strtolower(trim($v->vehicle_id))] = $v->id;
}

$now = now()->toDateTimeString();

foreach ($files as $fileConfig) {
    echo "====================================================\n";
    echo "Processing: {$fileConfig['label']}\n";
    echo "====================================================\n";

    if (!file_exists($fileConfig['path'])) {
        echo "File not found: {$fileConfig['path']}\n";
        continue;
    }

    $content = file_get_contents($fileConfig['path']);
    $content = str_replace(["\r\n", "\r"], "\n", $content);
    $lines = array_values(array_filter(explode("\n", trim($content))));

    $header = str_getcsv(array_shift($lines));
    $header = array_map(fn($h) => trim(strtolower(str_replace([' ', '.', '_', '-'], '', $h))), $header);

    $routeId = $fileConfig['route_id'];

    // Load existing schedules for this route
    $existing = $db->table('schedules')->where('ferry_route_id', $routeId)->get();
    $existingLookup = [];
    foreach ($existing as $e) {
        $key = Carbon::parse($e->departure_time)->format('Y-m-d H:i');
        $existingLookup[$key] = $e->id;
    }

    $created = 0;
    $updated = 0;

    $lastDep = null;

    foreach ($lines as $idx => $line) {
        if (trim($line) === '') continue;
        $cols = str_getcsv($line);
        if (count($cols) < count($header)) {
            $cols = array_pad($cols, count($header), '');
        }
        $row = array_combine(array_slice($header, 0, count($cols)), array_slice($cols, 0, count($header)));

        // Extract columns flexibly
        $vessel = $row['vehicletailno'] ?? ($row['vehicle'] ?? '2GO Vessel');
        $vessel = trim($vessel, " \t\n\r\0\x0B.");
        $depDate = $row['departuredate'] ?? '';
        $depTime = $row['departuretime'] ?? '';
        $arrDate = $row['arrivaldate'] ?? ($row['arivaldate'] ?? $depDate);
        $arrTime = $row['arrivaltime'] ?? '';
        $tClass = $row['transportclass'] ?? 'Tourist Class';
        $rateRaw = $row['rate'] ?? '0';
        $addRaw = $row['additionalprice'] ?? '0';
        $price = floatval(preg_replace('/[^0-9.]/', '', $addRaw ?: $rateRaw));
        $tickets = (int)preg_replace('/[^0-9]/', '', $row['ticketsavailable'] ?? '50') ?: 50;
        $hasBed = strtolower($row['hasbed'] ?? 'yes') === 'yes' ? 1 : 0;
        $rateCode = $row['ratecode'] ?? 'REG';

        // Parse initial dates
        $depDt = parseDep($depDate, $depTime);

        // If departure went backwards in time relative to previous row, adjust month if typod
        if ($lastDep && $depDt->lt($lastDep)) {
            $cand = $depDt->copy()->addMonth();
            if ($cand->gte($lastDep) && $lastDep->diffInDays($cand) <= 14) {
                $depDt = $cand;
            }
        }
        $lastDep = $depDt->copy();

        $arrDt = parseArr($arrDate, $arrTime, $depDt);

        // Ensure vehicle exists
        $vk = strtolower(trim($vessel));
        if (!isset($vehicleMap[$vk])) {
            echo "Creating vehicle on Railway: {$vessel}\n";
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

        // schedule_transport_class
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

        // schedule_accommodations
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

    echo "\nRoute {$routeId} Results:\n";
    echo "  Created: {$created}\n";
    echo "  Updated: {$updated}\n\n";
}

if (!$isDryRun) {
    if ($db->getSchemaBuilder()->hasTable('cache')) {
        $db->table('cache')->truncate();
        echo "✓ Cleared Railway production cache table.\n";
    }
}

echo "=== FINISHED ===\n";

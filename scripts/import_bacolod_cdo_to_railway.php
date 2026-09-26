<?php
/**
 * Import Bacolod -> Cagayan De Oro 2GO Schedules directly into Railway production DB.
 * Handles:
 *   - bare \r line endings
 *   - 'Arival Date' header typo
 *   - spaced dates like "14/ 11/ 2026"
 *   - 5-digit year typos like "22026"
 *   - ambiguous DMY vs MDY dates (Excel locale flip)
 *
 * Usage:
 *   php scripts/import_bacolod_cdo_to_railway.php           (dry-run)
 *   php scripts/import_bacolod_cdo_to_railway.php --force   (live insert)
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
echo "=== 2GO BACOLOD -> CAGAYAN DE ORO IMPORT (" . ($isDryRun ? "DRY-RUN" : "LIVE") . ") ===\n\n";

// ── CSV parsing ─────────────────────────────────────────────────────────────
// The uploaded file uses bare \r (classic Mac) line endings.
$csvPath = 'C:\Users\macar\.gemini\antigravity-ide\brain\26f81cef-cdc9-45eb-aac9-ff6ab3ce0a0e\.user_uploaded\media_1790390056357.csv';

if (!file_exists($csvPath)) {
    die("CSV file not found: {$csvPath}\n");
}

$content = file_get_contents($csvPath);
// Normalise all line-endings to \n
$content = str_replace(["\r\n", "\r"], "\n", $content);
$lines   = array_filter(explode("\n", trim($content)));
$lines   = array_values($lines);

$header = str_getcsv(array_shift($lines));
// Normalise header names
$header = array_map(fn($h) => trim(strtolower(str_replace([' ', '.', '_', '-'], '', $h))), $header);

// ── Date helpers ─────────────────────────────────────────────────────────────
function sanitizeDate(string $d): string
{
    $d = preg_replace('/\s*([\\/\\-])\s*/', '$1', trim($d));       // "14/ 11/ 2026" -> "14/11/2026"
    $d = preg_replace('/(\d{1,2}\/\d{1,2}\/)2+(\d{4})/', '$1$2', $d); // "22026" -> "2026"
    return $d;
}

$lastDep = null;

function parseDepDate(string $date, string $time): Carbon
{
    global $lastDep;
    $d = sanitizeDate($date);
    $t = trim($time);

    if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $d, $m)) {
        $p1 = (int)$m[1]; $p2 = (int)$m[2]; $y = (int)$m[3];

        $canDmy = ($p2 >= 1 && $p2 <= 12 && $p1 >= 1 && $p1 <= 31);
        $canMdy = ($p1 >= 1 && $p1 <= 12 && $p2 >= 1 && $p2 <= 31);

        if ($canDmy && $canMdy && $lastDep) {
            foreach (['d/m/Y', 'm/d/Y'] as $fmt) {
                try {
                    $candidate = Carbon::createFromFormat("$fmt g:i A", "$d $t");
                    $diff = $lastDep->diffInDays($candidate, false);
                    if ($diff >= -2 && $diff <= 14) {
                        return $candidate;
                    }
                } catch (\Throwable) {}
            }
        }
    }

    // Fallback: try formats in order
    foreach (['d/m/Y g:i A', 'd/m/Y h:i A', 'm/d/Y g:i A', 'm/d/Y h:i A'] as $fmt) {
        try {
            return Carbon::createFromFormat($fmt, sanitizeDate($date) . ' ' . trim($time));
        } catch (\Throwable) {}
    }
    return Carbon::parse(sanitizeDate($date) . ' ' . trim($time));
}

function parseArrDate(string $date, string $time, Carbon $dep): Carbon
{
    $d = sanitizeDate($date);
    $t = trim($time);

    $candidates = [];
    foreach (['d/m/Y g:i A', 'd/m/Y h:i A', 'm/d/Y g:i A', 'm/d/Y h:i A'] as $fmt) {
        try {
            $c = Carbon::createFromFormat($fmt, "$d $t");
            if (!in_array($c->timestamp, array_column($candidates, 'ts'))) {
                $candidates[] = ['dt' => $c, 'ts' => $c->timestamp];
            }
        } catch (\Throwable) {}
    }

    // Among valid candidates, prefer the one where arrival is within 0-5 days after departure
    foreach ($candidates as $item) {
        $arr = clone $item['dt'];
        if ($arr->lt($dep)) $arr->addDay();      // same-night trip
        $diffDays = $dep->diffInDays($arr, false);
        if ($diffDays >= 0 && $diffDays <= 5) {
            return $arr;
        }
    }

    // Fallback: use first candidate, nudge to >= dep
    if (!empty($candidates)) {
        $arr = clone $candidates[0]['dt'];
        if ($arr->lt($dep)) $arr->addDay();
        return $arr;
    }

    $arr = Carbon::parse("$d $t");
    if ($arr->lt($dep)) $arr->addDay();
    return $arr;
}

// ── Parse CSV rows ───────────────────────────────────────────────────────────
$rows = [];
foreach ($lines as $line) {
    if (trim($line) === '') continue;
    $cols = str_getcsv($line);
    if (count($cols) < count($header)) {
        $cols = array_pad($cols, count($header), '');
    }
    $rows[] = array_combine(
        array_slice($header, 0, count($cols)),
        array_slice($cols, 0, count($header))
    );
}

echo "Parsed rows from CSV: " . count($rows) . "\n\n";

// ── Resolve Operator ─────────────────────────────────────────────────────────
$operator = $db->table('operators')->where('name', '2GO')->first();
if (!$operator) {
    die("ERROR: Operator '2GO' not found in Railway DB.\n");
}
$operatorId = $operator->id;
echo "Operator: 2GO (ID: {$operatorId})\n";

// ── Resolve / create route ───────────────────────────────────────────────────
$routeOrigin = 'Bacolod';
$routeDest   = 'Cagayan De Oro';

$route = $db->table('ferry_routes')
    ->where('operator', '2GO')
    ->whereRaw('LOWER(origin) = ?', [strtolower($routeOrigin)])
    ->whereRaw('LOWER(destination) = ?', [strtolower($routeDest)])
    ->first();

if (!$route) {
    echo "Route not found, will create: {$routeOrigin} -> {$routeDest}\n";
    if (!$isDryRun) {
        // Grab a vehicle ID from any existing 2GO vehicle
        $anyVehicle = $db->table('vehicles')->where('operator', '2GO')->first();
        $routeId = $db->table('ferry_routes')->insertGetId([
            'origin'      => $routeOrigin,
            'destination' => $routeDest,
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
    }
} else {
    $routeId = $route->id;
    echo "Route found: {$routeOrigin} -> {$routeDest} (ID: {$routeId})\n";
}

// ── Resolve / create Tourist Class ──────────────────────────────────────────
$tc = $db->table('transport_classes')
    ->where('operator', '2GO')
    ->where(function ($q) {
        $q->where('name', 'Tourist Class')->orWhere('name', 'like', '%Tourist%');
    })->first();

if (!$tc) {
    $tc = $db->table('transport_classes')
        ->where('name', 'like', '%Tourist%')
        ->first();
}
$tcId = $tc?->id ?? null;
echo "Transport Class: " . ($tc?->name ?? 'Tourist Class (will use fallback)') . " (ID: " . ($tcId ?? 'none') . ")\n\n";

// ── Resolve / create vehicles ────────────────────────────────────────────────
$vehicleMap = [];
$dbVehicles = $db->table('vehicles')->where('operator', '2GO')->get();
foreach ($dbVehicles as $v) {
    $vehicleMap[strtolower(trim($v->name))] = $v->id;
    if ($v->vehicle_id) $vehicleMap[strtolower(trim($v->vehicle_id))] = $v->id;
}

$uniqueVessels = array_unique(array_column($rows, 'vehicletailno'));
$uniqueVessels = array_filter($uniqueVessels);

foreach ($uniqueVessels as $vessel) {
    $vk = strtolower(trim($vessel));
    if (!isset($vehicleMap[$vk])) {
        echo "Will create vehicle: {$vessel}\n";
        if (!$isDryRun) {
            $newVId = $db->table('vehicles')->insertGetId([
                'type'        => 'ferry',
                'name'        => $vessel,
                'vehicle_id'  => $vessel,
                'operator'    => '2GO',
                'operator_id' => $operatorId,
                'is_active'   => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
            $vehicleMap[$vk] = $newVId;
            echo "  Created vehicle ID: {$newVId}\n";
        }
    }
}

// ── Load existing schedules for this route ───────────────────────────────────
$existingLookup = [];
if ($routeId) {
    $existing = $db->table('schedules')
        ->where('ferry_route_id', $routeId)
        ->select('id', 'departure_time')
        ->get();
    foreach ($existing as $e) {
        $key = Carbon::parse($e->departure_time)->format('Y-m-d H:i');
        $existingLookup[$key] = $e->id;
    }
}
echo "\nExisting schedules on this route: " . count($existingLookup) . "\n\n";

// ── Process rows ─────────────────────────────────────────────────────────────
$now     = now()->toDateTimeString();
$created = 0;
$updated = 0;
$errors  = [];

// Header key mapping (handles typos)
$colMap = [
    'depdate' => ['departuredate'],
    'deptime' => ['departuretime'],
    'arrdate' => ['arrivaldate', 'arivaldate', 'arrdate', 'ardate'],
    'arrtime' => ['arrivaltime'],
    'vessel'  => ['vehicletailno'],
    'tc'      => ['transportclass'],
    'rate'    => ['rate'],
    'addprice'=> ['additionalprice'],
    'tickets' => ['ticketsavailable'],
    'hasbed'  => ['hasbed'],
    'ratecode'=> ['ratecode'],
];

function getCol(array $row, array $keys): ?string
{
    foreach ($keys as $k) {
        if (isset($row[$k]) && trim((string)$row[$k]) !== '') {
            return trim((string)$row[$k]);
        }
    }
    return null;
}

foreach ($rows as $i => $r) {
    $rowNum = $i + 2;
    try {
        $depDateRaw = getCol($r, $colMap['depdate']) ?? '';
        $depTimeRaw = getCol($r, $colMap['deptime']) ?? '';
        $arrDateRaw = getCol($r, $colMap['arrdate']) ?? $depDateRaw;
        $arrTimeRaw = getCol($r, $colMap['arrtime']) ?? '';
        $vessel     = getCol($r, $colMap['vessel'])  ?? '2GO Vessel';
        $tClass     = getCol($r, $colMap['tc'])      ?? 'Tourist Class';
        $rateRaw    = getCol($r, $colMap['rate'])    ?? '0';
        $addRaw     = getCol($r, $colMap['addprice'])  ?? '0';
        $tickets    = (int)preg_replace('/[^0-9]/', '', getCol($r, $colMap['tickets']) ?? '50') ?: 50;
        $hasBed     = strtolower(getCol($r, $colMap['hasbed']) ?? 'yes') === 'yes' ? 1 : 0;
        $rateCode   = getCol($r, $colMap['ratecode']) ?? 'REG';
        $price      = floatval(preg_replace('/[^0-9.]/', '', $addRaw ?: $rateRaw));

        $depDt = parseDepDate($depDateRaw, $depTimeRaw);
        $lastDep = $depDt;
        $arrDt = parseArrDate($arrDateRaw, $arrTimeRaw, $depDt);

        $depKey = $depDt->format('Y-m-d H:i');

        echo "Row {$rowNum}: {$vessel} | Dep: " . $depDt->format('Y-m-d H:i') . " | Arr: " . $arrDt->format('Y-m-d H:i') . " | ₱" . number_format($price, 2) . "\n";

        if ($isDryRun) continue;

        // ── Schedule ────────────────────────────────────────────────────────
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

        // ── Transport class pivot ────────────────────────────────────────────
        if ($tcId) {
            $pivot = $db->table('schedule_transport_class')
                ->where('schedule_id', $schedId)
                ->where('transport_class_id', $tcId)
                ->first();

            if (!$pivot) {
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
                $db->table('schedule_transport_class')->where('id', $pivot->id)->update([
                    'additional_price'  => $price,
                    'tickets_available' => $tickets,
                    'has_bed'           => $hasBed,
                    'is_active'         => 1,
                    'updated_at'        => $now,
                ]);
            }
        }

        // ── Accommodation ────────────────────────────────────────────────────
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

    } catch (\Throwable $e) {
        $errors[] = "Row {$rowNum}: " . $e->getMessage();
        echo "  ERROR Row {$rowNum}: " . $e->getMessage() . "\n";
    }
}

// ── Summary ──────────────────────────────────────────────────────────────────
echo "\n";
if ($isDryRun) {
    echo "=== DRY-RUN COMPLETE (nothing written) ===\n";
    echo "Re-run with --force to execute live import.\n";
} else {
    echo "=== IMPORT COMPLETE ===\n";
    echo "  Schedules created: {$created}\n";
    echo "  Schedules updated: {$updated}\n";
    if ($errors) {
        echo "  Errors (" . count($errors) . "):\n";
        foreach ($errors as $err) echo "    - {$err}\n";
    }

    // Bust cache
    if ($db->getSchemaBuilder()->hasTable('cache')) {
        $db->table('cache')->truncate();
        echo "✓ Cleared Railway production cache.\n";
    }

    $finalCount = $db->table('schedules')->where('ferry_route_id', $routeId)->where('is_active', 1)->count();
    echo "\nFinal active schedules on Bacolod -> Cagayan De Oro: {$finalCount}\n";
}

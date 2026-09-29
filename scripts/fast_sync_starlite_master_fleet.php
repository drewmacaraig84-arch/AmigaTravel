<?php

/**
 * FAST MASTER STARLITE FLEET, SCHEDULES & TARIFF SYNCHRONIZER
 * 
 * - Timetable & Fleet: C:\laragon\www\AmigaTravel\VESSEL ROUTE.xlsx
 * - Official Rates: PASSENGER FARE RATES EFFECTIVE APRIL 13, 2026 (PDF)
 * - Multi-vessel support for Batangas <-> Caticlan (Pioneer, Reliance, Archer, Venus, Trans-Asia 20)
 * - Multi-vessel support for Batangas <-> Calapan (Annapolis, Saga, Jupiter, Eagle, Archer)
 * - Fast bulk inserts (chunks of 500) with in-memory deduplication
 * - Preserves existing bookings (IDs: 8872, 8866, 11894)
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
]);
DB::setDefaultConnection('railway');
$db = DB::connection('railway');

echo "========================================================================\n";
echo "   FAST MASTER STARLITE FLEET & TARIFF SYNC (RAILWAY DATABASE)          \n";
echo "========================================================================\n\n";

$startDate = Carbon::parse('2026-09-29');
$endDate = Carbon::parse('2026-12-31');
$nowStr = Carbon::now()->format('Y-m-d H:i:s');
$seatCols = json_encode(['A', 'B', 'C', 'D', 'E', 'F']);

// 1. Operator
$operator = $db->table('operators')->where('name', 'Starlite')->first();
$opId = $operator->id;
echo "Operator: Starlite (ID: {$opId})\n";

// 2. Ensure Transport Classes
$allClasses = [
    'Reclining Seat'   => ['price' => 680.00, 'desc' => 'Comfortable reclining passenger seats.', 'sort' => 1],
    'Economy Bed Bunk' => ['price' => 680.00, 'desc' => 'Open-air bunk bed accommodation.', 'sort' => 2],
    'Tourist Bed Bunk' => ['price' => 680.00, 'desc' => 'Air-conditioned bunk bed accommodation.', 'sort' => 3],
    'Cabin'            => ['price' => 1550.00, 'desc' => 'Shared cabin accommodation.', 'sort' => 4],
    'Cabin for 2'      => ['price' => 4092.00, 'desc' => 'Private cabin for 2 passengers.', 'sort' => 4],
    'VIP Room (2-3 pax)' => ['price' => 4400.00, 'desc' => 'Exclusive VIP room with bath.', 'sort' => 5],
    'VIP Room (5 pax)' => ['price' => 14400.00, 'desc' => 'VIP stateroom for 5 passengers.', 'sort' => 6],
];

$tcMap = [];
foreach ($allClasses as $name => $meta) {
    $code = Str::slug($name);
    $existing = $db->table('transport_classes')->where('operator', 'Starlite')->where('code', $code)->first();
    if (!$existing) {
        $tcId = $db->table('transport_classes')->insertGetId([
            'operator' => 'Starlite',
            'operator_id' => $opId,
            'code' => $code,
            'name' => $name,
            'description' => $meta['desc'],
            'price' => $meta['price'],
            'is_active' => 1,
            'sort_order' => $meta['sort'],
            'created_at' => $nowStr,
            'updated_at' => $nowStr,
        ]);
        $tcMap[$code] = $tcId;
    } else {
        $tcMap[$code] = $existing->id;
    }
}
echo "Transport classes mapped: " . count($tcMap) . "\n";

// 3. Ensure Vehicles
$vehicles = [
    'MV Starlite Pioneer'              => 'STP-201',
    'MV Starlite Reliance'             => 'STR-202',
    'MV Starlite Archer'               => 'STA-201',
    'MV Starlite Venus'                => 'STV-601',
    'MV Trans - Asia 20 (Cargo-pax)'   => 'MV Trans - Asia 20 (Cargo-pax)',
    'MV Starlite Annapolis'            => 'STA-101',
    'MV Starlite Saga'                 => 'STS-102',
    'MV Starlite Jupiter'              => 'STJ-102',
    'MV Starlite Eagle'                => 'STE-101',
    'MV Starlite Stella Maris'         => 'SSM-401',
    'MV Starlite Saturn'               => 'STS-501',
    'MV Starlite Gratitude'            => 'STG-701',
    'MV Starlite Salve Regina'         => 'SSR-501',
    'MV Starlite Poseidon 43'          => 'STP-801',
    'MV Starlite Poseidon 53'          => 'STP-802',
    'MV Starlite Prometheus 54'        => 'STP-901',
    'MV Starlite Prometheus 55'        => 'STP-902',
    'MV Starlite Prometheus 56'        => 'STP-903',
    'MV Starlite Prometheus 57'        => 'STP-904',
    'MV Starlite Poseidon 37'          => 'STP-905',
    'MV Starlite Resilience'           => 'STR-301',
    'MV Starlite Pacific'              => 'STP-1201',
];

foreach ($vehicles as $vName => $vPlate) {
    $vObj = $db->table('vehicles')->where('operator', 'Starlite')->where('name', $vName)->first();
    if (!$vObj) {
        $db->table('vehicles')->insert([
            'vehicle_id' => $vPlate,
            'name' => $vName,
            'type' => 'ferry',
            'operator' => 'Starlite',
            'operator_id' => $opId,
            'is_active' => 1,
            'created_at' => $nowStr,
            'updated_at' => $nowStr,
        ]);
    }
}
echo "Vehicles ensured: " . count($vehicles) . "\n";

// 4. Load ALL existing upcoming schedules into memory for quick lookups
echo "Loading existing upcoming schedules into memory...\n";
$existingSchedules = $db->table('schedules')
    ->where('departure_time', '>=', $startDate->format('Y-m-d 00:00:00'))
    ->select('id', 'ferry_route_id', 'departure_time', 'vehicle_name', 'price')
    ->get();

$schedMap = [];
foreach ($existingSchedules as $s) {
    $key = "{$s->ferry_route_id}|" . substr($s->departure_time, 0, 19) . "|{$s->vehicle_name}";
    $schedMap[$key] = $s->id;
}
echo "Loaded " . count($schedMap) . " existing upcoming schedules into memory.\n";

// Load ALL existing schedule_accommodations into memory: [sched_id|name] => id
$existingAccs = $db->table('schedule_accommodations')
    ->whereIn('schedule_id', $existingSchedules->pluck('id'))
    ->select('id', 'schedule_id', 'name', 'price')
    ->get();
$accMap = [];
foreach ($existingAccs as $a) {
    $accMap["{$a->schedule_id}|{$a->name}"] = $a->id;
}
echo "Loaded " . count($accMap) . " existing accommodations into memory.\n";

// Load ALL existing schedule_transport_class pivots into memory: [sched_id|tc_id] => id
$existingPivots = $db->table('schedule_transport_class')
    ->whereIn('schedule_id', $existingSchedules->pluck('id'))
    ->select('id', 'schedule_id', 'transport_class_id', 'additional_price')
    ->get();
$pivotMap = [];
foreach ($existingPivots as $p) {
    $pivotMap["{$p->schedule_id}|{$p->transport_class_id}"] = $p->id;
}
echo "Loaded " . count($pivotMap) . " existing transport class pivots into memory.\n";

// 5. Build Timetable Specifications
$standardCaticlanAccs = [
    ['name' => 'Reclining Seat', 'desc' => 'Comfortable reclining seat.', 'price' => 2170.00, 'has_bed' => 0, 'sort' => 1],
    ['name' => 'Economy Bed Bunk', 'desc' => 'Air-conditioned bunk bed.', 'price' => 2270.00, 'has_bed' => 1, 'sort' => 2],
    ['name' => 'Tourist Bed Bunk', 'desc' => 'Tourist class bed.', 'price' => 2790.00, 'has_bed' => 1, 'sort' => 3],
    ['name' => 'Cabin', 'desc' => 'Shared cabin.', 'price' => 3720.00, 'has_bed' => 1, 'sort' => 4],
    ['name' => 'VIP Room (2-3 pax)', 'desc' => 'Exclusive VIP room.', 'price' => 8300.00, 'has_bed' => 1, 'sort' => 5],
    ['name' => 'VIP Room (5 pax)', 'desc' => 'VIP room for 5 pax.', 'price' => 14400.00, 'has_bed' => 1, 'sort' => 6],
];

$transAsiaCaticlanAccs = [
    ['name' => 'Reclining Seat', 'desc' => 'Comfortable reclining seat.', 'price' => 2387.00, 'has_bed' => 0, 'sort' => 1],
    ['name' => 'Economy Bed Bunk', 'desc' => 'Air-conditioned bunk bed.', 'price' => 2497.00, 'has_bed' => 1, 'sort' => 2],
    ['name' => 'Tourist Bed Bunk', 'desc' => 'Tourist class bed.', 'price' => 3069.00, 'has_bed' => 1, 'sort' => 3],
    ['name' => 'Cabin for 2', 'desc' => 'Private cabin for 2 passengers.', 'price' => 4092.00, 'has_bed' => 1, 'sort' => 4],
];

$calapanAccs = [
    ['name' => 'Reclining Seat', 'desc' => 'Reclining passenger seat.', 'price' => 680.00, 'has_bed' => 0, 'sort' => 1],
    ['name' => 'Economy Bed Bunk', 'desc' => 'Economy bunk bed.', 'price' => 680.00, 'has_bed' => 1, 'sort' => 2],
    ['name' => 'Tourist Bed Bunk', 'desc' => 'Tourist class bunk bed.', 'price' => 680.00, 'has_bed' => 1, 'sort' => 3],
];

$caticlanFleet = [
    ['name' => 'MV Starlite Pioneer', 'plate' => 'STP-201', 'accs' => $standardCaticlanAccs],
    ['name' => 'MV Starlite Reliance', 'plate' => 'STR-202', 'accs' => $standardCaticlanAccs],
    ['name' => 'MV Starlite Archer', 'plate' => 'STA-201', 'accs' => $standardCaticlanAccs],
    ['name' => 'MV Starlite Venus', 'plate' => 'STV-601', 'accs' => $standardCaticlanAccs],
    ['name' => 'MV Trans - Asia 20 (Cargo-pax)', 'plate' => 'MV Trans - Asia 20 (Cargo-pax)', 'accs' => $transAsiaCaticlanAccs],
];

// Batangas <-> Calapan ROPAX fleet
$calapanBtgRopaxFleet = [
    ['name' => 'MV Starlite Annapolis', 'plate' => 'STA-101'],
    ['name' => 'MV Starlite Saga', 'plate' => 'STS-102'],
];
$calapanCalRopaxFleet = [
    ['name' => 'MV Starlite Jupiter', 'plate' => 'STJ-102'],
    ['name' => 'MV Starlite Eagle', 'plate' => 'STE-101'],
];

$schedulesToInsert = [];
$period = CarbonPeriod::create($startDate, $endDate);

echo "Building schedule batches...\n";

foreach ($period as $date) {
    $dateStr = $date->format('Y-m-d');

    // 1. Batangas -> Caticlan (Route 105)
    // Times: 07:30, 13:00, 16:00, 19:30
    foreach (['07:30:00', '13:00:00', '16:00:00', '19:30:00'] as $timeStr) {
        $depTime = "{$dateStr} {$timeStr}";
        $arrTime = Carbon::parse($depTime)->addMinutes(600)->format('Y-m-d H:i:s');
        foreach ($caticlanFleet as $v) {
            $key = "105|{$depTime}|{$v['name']}";
            if (!isset($schedMap[$key])) {
                $schedulesToInsert[] = [
                    'ferry_route_id' => 105,
                    'service_name' => $v['name'],
                    'vehicle_name' => $v['name'],
                    'plate_no' => $v['plate'],
                    'departure_time' => $depTime,
                    'arrival_time' => $arrTime,
                    'duration_minutes' => 600,
                    'price' => $v['accs'][0]['price'],
                    'availability_label' => 'Available',
                    'seat_rows' => 15,
                    'seat_columns' => $seatCols,
                    'is_active' => 1,
                    'created_at' => $nowStr,
                    'updated_at' => $nowStr,
                    '_accs' => $v['accs'],
                ];
            }
        }
    }

    // 2. Caticlan -> Batangas (Route 106)
    // Times: 01:00, 07:30, 17:00, 19:30
    foreach (['01:00:00', '07:30:00', '17:00:00', '19:30:00'] as $timeStr) {
        $depTime = "{$dateStr} {$timeStr}";
        $arrTime = Carbon::parse($depTime)->addMinutes(600)->format('Y-m-d H:i:s');
        foreach ($caticlanFleet as $v) {
            $key = "106|{$depTime}|{$v['name']}";
            if (!isset($schedMap[$key])) {
                $schedulesToInsert[] = [
                    'ferry_route_id' => 106,
                    'service_name' => $v['name'],
                    'vehicle_name' => $v['name'],
                    'plate_no' => $v['plate'],
                    'departure_time' => $depTime,
                    'arrival_time' => $arrTime,
                    'duration_minutes' => 600,
                    'price' => $v['accs'][0]['price'],
                    'availability_label' => 'Available',
                    'seat_rows' => 15,
                    'seat_columns' => $seatCols,
                    'is_active' => 1,
                    'created_at' => $nowStr,
                    'updated_at' => $nowStr,
                    '_accs' => $v['accs'],
                ];
            }
        }
    }

    // 3. Batangas -> Calapan (Fastcraft: Archer)
    // Times: 08:30, 12:30, 16:30
    foreach (['08:30:00', '12:30:00', '16:30:00'] as $timeStr) {
        $depTime = "{$dateStr} {$timeStr}";
        $arrTime = Carbon::parse($depTime)->addMinutes(90)->format('Y-m-d H:i:s');
        $key = "103|{$depTime}|MV Starlite Archer";
        if (!isset($schedMap[$key])) {
            $schedulesToInsert[] = [
                'ferry_route_id' => 103,
                'service_name' => 'MV Starlite Archer',
                'vehicle_name' => 'MV Starlite Archer',
                'plate_no' => 'STA-FC1',
                'departure_time' => $depTime,
                'arrival_time' => $arrTime,
                'duration_minutes' => 90,
                'price' => 680.00,
                'availability_label' => 'Available',
                'seat_rows' => 15,
                'seat_columns' => $seatCols,
                'is_active' => 1,
                'created_at' => $nowStr,
                'updated_at' => $nowStr,
                '_accs' => $calapanAccs,
            ];
        }
    }

    // 4. Calapan -> Batangas (Fastcraft: Archer)
    // Times: 05:20, 10:30, 14:30
    foreach (['05:20:00', '10:30:00', '14:30:00'] as $timeStr) {
        $depTime = "{$dateStr} {$timeStr}";
        $arrTime = Carbon::parse($depTime)->addMinutes(90)->format('Y-m-d H:i:s');
        $key = "104|{$depTime}|MV Starlite Archer";
        if (!isset($schedMap[$key])) {
            $schedulesToInsert[] = [
                'ferry_route_id' => 104,
                'service_name' => 'MV Starlite Archer',
                'vehicle_name' => 'MV Starlite Archer',
                'plate_no' => 'STA-FC1',
                'departure_time' => $depTime,
                'arrival_time' => $arrTime,
                'duration_minutes' => 90,
                'price' => 680.00,
                'availability_label' => 'Available',
                'seat_rows' => 15,
                'seat_columns' => $seatCols,
                'is_active' => 1,
                'created_at' => $nowStr,
                'updated_at' => $nowStr,
                '_accs' => $calapanAccs,
            ];
        }
    }

    // 5. Batangas -> Calapan ROPAX (Saga)
    foreach (['01:00:00', '03:00:00', '05:00:00', '07:00:00', '09:00:00', '11:00:00', '13:00:00', '15:00:00', '17:00:00', '19:00:00', '21:00:00', '23:00:00'] as $timeStr) {
        $depTime = "{$dateStr} {$timeStr}";
        $arrTime = Carbon::parse($depTime)->addMinutes(180)->format('Y-m-d H:i:s');
        $key = "103|{$depTime}|MV Starlite Saga";
        if (!isset($schedMap[$key])) {
            $schedulesToInsert[] = [
                'ferry_route_id' => 103,
                'service_name' => 'MV Starlite Saga',
                'vehicle_name' => 'MV Starlite Saga',
                'plate_no' => 'STS-102',
                'departure_time' => $depTime,
                'arrival_time' => $arrTime,
                'duration_minutes' => 180,
                'price' => 680.00,
                'availability_label' => 'Available',
                'seat_rows' => 15,
                'seat_columns' => $seatCols,
                'is_active' => 1,
                'created_at' => $nowStr,
                'updated_at' => $nowStr,
                '_accs' => $calapanAccs,
            ];
        }
    }

    // 6. Calapan -> Batangas ROPAX (Eagle)
    foreach (['01:00:00', '03:00:00', '05:00:00', '07:00:00', '09:00:00', '11:00:00', '13:00:00', '15:00:00', '17:00:00', '19:00:00', '21:00:00', '23:00:00'] as $timeStr) {
        $depTime = "{$dateStr} {$timeStr}";
        $arrTime = Carbon::parse($depTime)->addMinutes(180)->format('Y-m-d H:i:s');
        $key = "104|{$depTime}|MV Starlite Eagle";
        if (!isset($schedMap[$key])) {
            $schedulesToInsert[] = [
                'ferry_route_id' => 104,
                'service_name' => 'MV Starlite Eagle',
                'vehicle_name' => 'MV Starlite Eagle',
                'plate_no' => 'STE-101',
                'departure_time' => $depTime,
                'arrival_time' => $arrTime,
                'duration_minutes' => 180,
                'price' => 680.00,
                'availability_label' => 'Available',
                'seat_rows' => 15,
                'seat_columns' => $seatCols,
                'is_active' => 1,
                'created_at' => $nowStr,
                'updated_at' => $nowStr,
                '_accs' => $calapanAccs,
            ];
        }
    }
}

echo "Total new schedules to insert: " . count($schedulesToInsert) . "\n";

// Bulk Insert Schedules in chunks of 500
$chunkSize = 500;
$totalInserted = 0;
$scheduleAccMap = [];

for ($i = 0; $i < count($schedulesToInsert); $i += $chunkSize) {
    $chunk = array_slice($schedulesToInsert, $i, $chunkSize);
    $cleanChunk = [];
    foreach ($chunk as $item) {
        $accs = $item['_accs'];
        unset($item['_accs']);
        $cleanChunk[] = $item;
    }
    
    $db->table('schedules')->insert($cleanChunk);
    $totalInserted += count($cleanChunk);
    echo "  Inserted {$totalInserted} / " . count($schedulesToInsert) . " schedules...\n";
}

echo "All new schedules inserted successfully!\n";

// 6. Attach Accommodations & Pivots for any schedule that doesn't have them
echo "\nAttaching accommodations and transport class pivots...\n";

// Query all schedules from today onwards on these routes to ensure 100% accommodation coverage
$allTargetSchedules = $db->table('schedules')
    ->whereIn('ferry_route_id', [103, 104, 105, 106])
    ->where('departure_time', '>=', $startDate->format('Y-m-d 00:00:00'))
    ->select('id', 'ferry_route_id', 'vehicle_name')
    ->get();

$accInsertBatch = [];
$pivotInsertBatch = [];
$totalAccCreated = 0;
$totalPivotCreated = 0;

foreach ($allTargetSchedules as $sched) {
    if ($sched->ferry_route_id == 105 || $sched->ferry_route_id == 106) {
        $accList = ($sched->vehicle_name === 'MV Trans - Asia 20 (Cargo-pax)')
            ? $transAsiaCaticlanAccs
            : $standardCaticlanAccs;
    } else {
        $accList = $calapanAccs;
    }

    foreach ($accList as $acc) {
        $accKey = "{$sched->id}|{$acc['name']}";
        if (!isset($accMap[$accKey])) {
            $accInsertBatch[] = [
                'schedule_id' => $sched->id,
                'name' => $acc['name'],
                'description' => $acc['desc'],
                'price' => $acc['price'],
                'tickets_available' => 50,
                'has_bed' => $acc['has_bed'],
                'is_active' => 1,
                'sort_order' => $acc['sort'] ?? 1,
                'created_at' => $nowStr,
                'updated_at' => $nowStr,
            ];
            $accMap[$accKey] = true;
        }

        $tcCode = Str::slug($acc['name']);
        $tcId = $tcMap[$tcCode] ?? null;
        if ($tcId) {
            $pivotKey = "{$sched->id}|{$tcId}";
            if (!isset($pivotMap[$pivotKey])) {
                $pivotInsertBatch[] = [
                    'schedule_id' => $sched->id,
                    'transport_class_id' => $tcId,
                    'additional_price' => $acc['price'],
                    'tickets_available' => 50,
                    'description' => $acc['desc'],
                    'has_bed' => $acc['has_bed'],
                    'is_active' => 1,
                    'created_at' => $nowStr,
                    'updated_at' => $nowStr,
                ];
                $pivotMap[$pivotKey] = true;
            }
        }
    }

    if (count($accInsertBatch) >= 500) {
        $db->table('schedule_accommodations')->insert($accInsertBatch);
        $totalAccCreated += count($accInsertBatch);
        $accInsertBatch = [];
    }

    if (count($pivotInsertBatch) >= 500) {
        $db->table('schedule_transport_class')->insert($pivotInsertBatch);
        $totalPivotCreated += count($pivotInsertBatch);
        $pivotInsertBatch = [];
    }
}

if (!empty($accInsertBatch)) {
    $db->table('schedule_accommodations')->insert($accInsertBatch);
    $totalAccCreated += count($accInsertBatch);
}
if (!empty($pivotInsertBatch)) {
    $db->table('schedule_transport_class')->insert($pivotInsertBatch);
    $totalPivotCreated += count($pivotInsertBatch);
}

echo "Total accommodations attached: {$totalAccCreated}\n";
echo "Total transport class pivots attached: {$totalPivotCreated}\n\n";

// 7. Verification Summary
echo "========================================================================\n";
echo "                         VERIFICATION SUMMARY                           \n";
echo "========================================================================\n";

echo "\n--- Route 105 (Batangas -> Caticlan) Upcoming Schedules by Vessel ---\n";
$dist105 = $db->table('schedules')
    ->where('ferry_route_id', 105)
    ->where('departure_time', '>=', $startDate->format('Y-m-d 00:00:00'))
    ->select('vehicle_name', DB::raw('count(*) as c'))
    ->groupBy('vehicle_name')
    ->get();
foreach ($dist105 as $d) {
    echo "  - '{$d->vehicle_name}': {$d->c} schedules\n";
}

echo "\n--- Route 106 (Caticlan -> Batangas) Upcoming Schedules by Vessel ---\n";
$dist106 = $db->table('schedules')
    ->where('ferry_route_id', 106)
    ->where('departure_time', '>=', $startDate->format('Y-m-d 00:00:00'))
    ->select('vehicle_name', DB::raw('count(*) as c'))
    ->groupBy('vehicle_name')
    ->get();
foreach ($dist106 as $d) {
    echo "  - '{$d->vehicle_name}': {$d->c} schedules\n";
}

echo "\n--- Route 103 (Batangas -> Calapan) Upcoming Schedules by Vessel ---\n";
$dist103 = $db->table('schedules')
    ->where('ferry_route_id', 103)
    ->where('departure_time', '>=', $startDate->format('Y-m-d 00:00:00'))
    ->select('vehicle_name', DB::raw('count(*) as c'))
    ->groupBy('vehicle_name')
    ->get();
foreach ($dist103 as $d) {
    echo "  - '{$d->vehicle_name}': {$d->c} schedules\n";
}

echo "\n--- Route 104 (Calapan -> Batangas) Upcoming Schedules by Vessel ---\n";
$dist104 = $db->table('schedules')
    ->where('ferry_route_id', 104)
    ->where('departure_time', '>=', $startDate->format('Y-m-d 00:00:00'))
    ->select('vehicle_name', DB::raw('count(*) as c'))
    ->groupBy('vehicle_name')
    ->get();
foreach ($dist104 as $d) {
    echo "  - '{$d->vehicle_name}': {$d->c} schedules\n";
}

echo "\n========================================================================\n";
echo "            FAST MASTER STARLITE FLEET SYNC COMPLETED!                  \n";
echo "========================================================================\n";

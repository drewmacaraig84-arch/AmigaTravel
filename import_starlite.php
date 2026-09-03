<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\FerryRoute;
use App\Models\Operator;
use App\Models\TransportClass;
use App\Models\Vehicle;
use App\Services\LocationCodeResolver;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

$dir = base_path('2go_schedules/Starlite_schedule');
$files = glob($dir . '/*.csv');
$resolver = new LocationCodeResolver();
$now = now()->toDateTimeString();

echo "=== Starlite Bulk Import ===\n";
echo "Found " . count($files) . " CSV files.\n\n";

// 1. Ensure Starlite operator exists
$operator = Operator::firstOrCreate(
    ['name' => 'Starlite'],
    ['mode' => 'ferry', 'is_active' => true]
);
echo "Operator ID: {$operator->id}\n";

// 2. Parse all CSV files into grouped structure
// Key: "origin|destination" => array of schedules
// Each schedule: ['dep', 'arr', 'vehicle', 'classes' => [['name','price','has_bed','tickets']]]
$allRowsBySchedule = []; // key: "origin|dest|dep_datetime" => ['route_key','vehicle','dep','arr','classes'=>[]]
$vehicleNames = [];

foreach ($files as $file) {
    $basename = basename($file);
    echo "Parsing: $basename ...\n";

    $handle = fopen($file, 'r');
    // Remove BOM
    $bom = fread($handle, 3);
    if ($bom !== "\xEF\xBB\xBF") rewind($handle);

    $headers = null;
    $count = 0;
    while (($row = fgetcsv($handle)) !== false) {
        if ($headers === null) {
            $headers = array_map(fn($h) => strtolower(trim(str_replace([' ', '_', '-'], '', $h))), $row);
            continue;
        }
        if (count($row) < count($headers)) {
            $row = array_pad($row, count($headers), '');
        }
        $data = array_combine(array_slice($headers, 0, count($row)), array_slice($row, 0, count($headers)));

        $orig = trim($data['origin'] ?? '');
        $dest = trim($data['destination'] ?? '');
        $depDate = trim($data['departuredate'] ?? '');
        $depTime = trim($data['departuretime'] ?? '');
        $arrTime = trim($data['arrivaltime'] ?? '');
        $vehicle = trim($data['vehicle'] ?? '');
        $accommodation = trim($data['accommodation'] ?? '');
        $price = (float) ($data['price'] ?? 0);
        $hasBed = ((int) ($data['hasbed'] ?? 0)) === 1;
        $tickets = (int) ($data['ticketsavailable'] ?? 50);

        if (empty($orig) || empty($dest) || empty($depDate) || empty($depTime)) continue;

        // Parse departure datetime
        try {
            $depDt = Carbon::createFromFormat('d/m/Y H:i', "{$depDate} {$depTime}");
        } catch (\Exception $e) {
            continue;
        }

        // Calculate arrival datetime (handle overnight: if arr < dep, it's next day)
        try {
            [$arrH, $arrM] = explode(':', $arrTime . ':00');
            $arrDt = $depDt->copy()->setTime((int)$arrH, (int)$arrM, 0);
            if ($arrDt->lte($depDt)) {
                $arrDt->addDay();
            }
        } catch (\Exception $e) {
            $arrDt = $depDt->copy()->addHours(4);
        }

        $normOrig = $resolver->resolve($orig, 'ferry');
        $normDest = $resolver->resolve($dest, 'ferry');
        $routeKey = "{$normOrig}|{$normDest}";
        $schedKey = "{$routeKey}|{$depDt->format('Y-m-d H:i')}|{$vehicle}";

        if (!isset($allRowsBySchedule[$schedKey])) {
            $allRowsBySchedule[$schedKey] = [
                'route_key' => $routeKey,
                'origin' => $normOrig,
                'destination' => $normDest,
                'vehicle' => $vehicle,
                'dep' => $depDt->toDateTimeString(),
                'arr' => $arrDt->toDateTimeString(),
                'classes' => [],
            ];
        }

        if (!empty($accommodation)) {
            $allRowsBySchedule[$schedKey]['classes'][] = [
                'name' => $accommodation,
                'price' => $price,
                'has_bed' => $hasBed,
                'tickets' => $tickets,
            ];
        }

        $vehicleNames[$vehicle] = true;
        $count++;
    }
    fclose($handle);
    echo "  Parsed $count data rows.\n";
}

echo "\nTotal unique schedules: " . count($allRowsBySchedule) . "\n";
echo "Total unique vehicles: " . count($vehicleNames) . "\n\n";

// 3. Ensure vehicles exist
foreach (array_keys($vehicleNames) as $vName) {
    Vehicle::firstOrCreate(
        ['vehicle_id' => 'STARLITE_' . strtoupper(str_replace(' ', '_', $vName))],
        [
            'type' => 'ferry',
            'name' => 'MV ' . $vName,
            'operator' => 'Starlite',
            'operator_id' => $operator->id,
            'capacity' => 500,
            'is_active' => true,
        ]
    );
}
echo "Vehicles ensured.\n";

// 4. Ensure transport classes exist
$accommodationNames = [];
foreach ($allRowsBySchedule as $sched) {
    foreach ($sched['classes'] as $cls) {
        $accommodationNames[$cls['name']] = $cls;
    }
}

$tcCache = [];
foreach ($accommodationNames as $name => $cls) {
    $tc = TransportClass::firstOrCreate(
        ['name' => $name, 'operator' => 'Starlite'],
        [
            'operator_id' => $operator->id,
            'type' => 'ferry',
            'price' => $cls['price'],
            'capacity' => $cls['tickets'],
            'has_bed' => $cls['has_bed'],
            'is_active' => true,
        ]
    );
    $tcCache[$name] = $tc;
}
echo "Transport classes ensured: " . count($tcCache) . "\n";

// 5. Ensure routes exist
$routeKeys = [];
foreach ($allRowsBySchedule as $sched) {
    $routeKeys[$sched['route_key']] = [
        'origin' => $sched['origin'],
        'destination' => $sched['destination'],
    ];
}

$routeCache = [];
$existingRoutes = FerryRoute::where('operator', 'Starlite')->get();
foreach ($existingRoutes as $r) {
    $routeCache["{$r->origin}|{$r->destination}"] = $r;
}

foreach ($routeKeys as $rKey => $rData) {
    if (!isset($routeCache[$rKey])) {
        $route = FerryRoute::create([
            'origin' => $rData['origin'],
            'destination' => $rData['destination'],
            'operator' => 'Starlite',
            'operator_id' => $operator->id,
            'mode' => 'ferry',
            'is_active' => true,
        ]);
        $routeCache[$rKey] = $route;
    }
}
echo "Routes ensured: " . count($routeCache) . "\n";

// 6. Bulk insert schedules
// Filter out already existing
$existingScheduleMap = [];
$existingSchedules = DB::table('schedules')
    ->join('ferry_routes', 'schedules.ferry_route_id', '=', 'ferry_routes.id')
    ->where('ferry_routes.operator', 'Starlite')
    ->select('schedules.id', 'schedules.ferry_route_id', 'schedules.departure_time')
    ->get();
foreach ($existingSchedules as $es) {
    $existingScheduleMap[$es->ferry_route_id . '|' . substr($es->departure_time, 0, 16)] = $es->id;
}

$maxIdBefore = DB::table('schedules')->max('id') ?? 0;

$schedRows = [];
$schedRatesMap = []; // schedKey => classes array

foreach ($allRowsBySchedule as $schedKey => $sched) {
    $route = $routeCache[$sched['route_key']] ?? null;
    if (!$route) continue;

    $lookupKey = $route->id . '|' . substr($sched['dep'], 0, 16);
    if (isset($existingScheduleMap[$lookupKey])) continue;

    $schedRows[] = [
        'ferry_route_id' => $route->id,
        'vehicle_name' => $sched['vehicle'],
        'departure_time' => $sched['dep'],
        'arrival_time' => $sched['arr'],
        'price' => 0,
        'is_active' => 1,
        'created_at' => $now,
        'updated_at' => $now,
        '_key' => $schedKey,
        '_route_id' => $route->id,
        '_classes' => $sched['classes'],
    ];
}

echo "New schedules to insert: " . count($schedRows) . "\n";

DB::beginTransaction();
try {
    // Insert schedules in chunks
    $insertRows = array_map(function($r) {
        $copy = $r;
        unset($copy['_key'], $copy['_route_id'], $copy['_classes']);
        return $copy;
    }, $schedRows);

    foreach (array_chunk($insertRows, 500) as $chunk) {
        DB::table('schedules')->insert($chunk);
    }

    // Retrieve inserted IDs
    $inserted = DB::table('schedules')
        ->where('schedules.id', '>', $maxIdBefore)
        ->join('ferry_routes', 'schedules.ferry_route_id', '=', 'ferry_routes.id')
        ->where('ferry_routes.operator', 'Starlite')
        ->select('schedules.id', 'schedules.ferry_route_id', 'schedules.departure_time')
        ->get();

    $insertedMap = [];
    foreach ($inserted as $ins) {
        $insertedMap[$ins->ferry_route_id . '|' . substr($ins->departure_time, 0, 16)] = $ins->id;
    }

    // Build pivot rows
    $pivotRows = [];
    $accRows = [];

    foreach ($schedRows as $sr) {
        $lookupKey = $sr['_route_id'] . '|' . substr($sr['departure_time'], 0, 16);
        $schedId = $insertedMap[$lookupKey] ?? null;
        if (!$schedId) continue;

        foreach ($sr['_classes'] as $cls) {
            $tc = $tcCache[$cls['name']] ?? null;
            if ($tc) {
                $pivotRows[] = [
                    'schedule_id' => $schedId,
                    'transport_class_id' => $tc->id,
                    'additional_price' => $cls['price'],
                    'tickets_available' => $cls['tickets'],
                    'rate_type' => 'regular',
                    'is_promo' => false,
                    'rate_code' => null,
                    'has_bed' => $cls['has_bed'],
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            $accRows[] = [
                'schedule_id' => $schedId,
                'name' => $cls['name'],
                'rate_code' => null,
                'price' => $cls['price'],
                'tickets_available' => $cls['tickets'],
                'has_bed' => $cls['has_bed'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
    }

    foreach (array_chunk($pivotRows, 500) as $chunk) {
        DB::table('schedule_transport_class')->insert($chunk);
    }
    foreach (array_chunk($accRows, 500) as $chunk) {
        DB::table('schedule_accommodations')->insert($chunk);
    }

    DB::commit();

    \App\Models\Schedule::bust();

    echo "\n=== SUCCESS ===\n";
    echo "Schedules inserted: " . count($schedRows) . "\n";
    echo "Transport class pivots: " . count($pivotRows) . "\n";
    echo "Accommodation records: " . count($accRows) . "\n\n";

    // Final counts
    $finalRoutes = FerryRoute::where('operator', 'Starlite')->count();
    $finalScheds = DB::table('schedules')
        ->join('ferry_routes', 'schedules.ferry_route_id', '=', 'ferry_routes.id')
        ->where('ferry_routes.operator', 'Starlite')
        ->count();
    echo "Starlite Routes in DB: $finalRoutes\n";
    echo "Starlite Schedules in DB: $finalScheds\n";

} catch (\Throwable $e) {
    DB::rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

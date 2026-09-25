<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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

echo "=========================================================================\n";
echo "              COMPREHENSIVE 2GO SCHEDULE AUDIT: RAILWAY DB               \n";
echo "=========================================================================\n\n";

$now = Carbon::now();
echo "Audit Timestamp: " . $now->toDateTimeString() . " (Current date in system: {$now->toDateString()})\n\n";

// 1. Overall Operator Details
$op = $db->table('operators')->where('name', '2GO')->first();
if (!$op) {
    die("Error: Operator 2GO not found in Railway DB.\n");
}
echo "Operator: {$op->name} | ID: {$op->id} | Mode: {$op->mode} | Status: " . ($op->is_active ? 'Active' : 'Inactive') . "\n";

// 2. Schedule Totals
$totalSchedules = $db->table('schedules')
    ->join('ferry_routes', 'schedules.ferry_route_id', '=', 'ferry_routes.id')
    ->where('ferry_routes.operator', '2GO')
    ->count();

$activeSchedules = $db->table('schedules')
    ->join('ferry_routes', 'schedules.ferry_route_id', '=', 'ferry_routes.id')
    ->where('ferry_routes.operator', '2GO')
    ->where('schedules.is_active', 1)
    ->count();

$futureSchedules = $db->table('schedules')
    ->join('ferry_routes', 'schedules.ferry_route_id', '=', 'ferry_routes.id')
    ->where('ferry_routes.operator', '2GO')
    ->where('schedules.is_active', 1)
    ->where('schedules.departure_time', '>=', $now)
    ->count();

echo "Total 2GO Schedules in DB:       {$totalSchedules}\n";
echo "Active 2GO Schedules:            {$activeSchedules}\n";
echo "Upcoming (Future) Active Scheds: {$futureSchedules}\n\n";

// 3. Breakdown by Route
echo "-------------------------------------------------------------------------\n";
echo "ROUTE BREAKDOWN FOR 2GO\n";
echo "-------------------------------------------------------------------------\n";
echo sprintf("%-6s | %-16s -> %-20s | %-6s | %-10s -> %-10s\n", "Route", "Origin", "Destination", "Scheds", "From", "To");
echo str_repeat("-", 75) . "\n";

$routeStats = $db->table('schedules')
    ->join('ferry_routes', 'schedules.ferry_route_id', '=', 'ferry_routes.id')
    ->where('ferry_routes.operator', '2GO')
    ->where('schedules.is_active', 1)
    ->selectRaw('
        ferry_routes.id as route_id,
        ferry_routes.origin,
        ferry_routes.destination,
        count(*) as total_schedules,
        min(schedules.departure_time) as earliest_dep,
        max(schedules.departure_time) as latest_dep
    ')
    ->groupBy('ferry_routes.id', 'ferry_routes.origin', 'ferry_routes.destination')
    ->orderByDesc('total_schedules')
    ->get();

foreach ($routeStats as $r) {
    $minD = $r->earliest_dep ? Carbon::parse($r->earliest_dep)->format('d/m/Y') : 'N/A';
    $maxD = $r->latest_dep ? Carbon::parse($r->latest_dep)->format('d/m/Y') : 'N/A';
    echo sprintf(
        "%-6d | %-16s -> %-20s | %-6d | %-10s -> %-10s\n",
        $r->route_id,
        substr($r->origin, 0, 16),
        substr($r->destination, 0, 20),
        $r->total_schedules,
        $minD,
        $maxD
    );
}

// Routes with 0 schedules
$emptyRoutes = $db->table('ferry_routes')
    ->where('operator', '2GO')
    ->where('is_active', 1)
    ->whereNotIn('id', $routeStats->pluck('route_id'))
    ->get();

if ($emptyRoutes->isNotEmpty()) {
    echo "\nActive 2GO Routes with 0 schedules (" . $emptyRoutes->count() . " routes):\n";
    foreach ($emptyRoutes as $er) {
        echo "  - Route ID: {$er->id} | {$er->origin} -> {$er->destination}\n";
    }
}

// 4. Fleet / Vessel Breakdown
echo "\n-------------------------------------------------------------------------\n";
echo "VESSEL / FLEET BREAKDOWN FOR 2GO\n";
echo "-------------------------------------------------------------------------\n";

$vesselStats = $db->table('schedules')
    ->join('ferry_routes', 'schedules.ferry_route_id', '=', 'ferry_routes.id')
    ->where('ferry_routes.operator', '2GO')
    ->where('schedules.is_active', 1)
    ->selectRaw('
        schedules.vehicle_name,
        count(*) as total_schedules,
        min(schedules.departure_time) as earliest_dep,
        max(schedules.departure_time) as latest_dep
    ')
    ->groupBy('schedules.vehicle_name')
    ->orderByDesc('total_schedules')
    ->get();

foreach ($vesselStats as $v) {
    $minD = $v->earliest_dep ? Carbon::parse($v->earliest_dep)->format('d/m/Y') : 'N/A';
    $maxD = $v->latest_dep ? Carbon::parse($v->latest_dep)->format('d/m/Y') : 'N/A';
    echo sprintf(
        "%-30s | %-6d departures | %-10s to %-10s\n",
        $v->vehicle_name ?: '(Empty/Unnamed)',
        $v->total_schedules,
        $minD,
        $maxD
    );
}

// 5. Data Quality & Anomaly Checks
echo "\n-------------------------------------------------------------------------\n";
echo "INTEGRITY & DATA QUALITY CHECKS\n";
echo "-------------------------------------------------------------------------\n";

// Check 5.1: Negative travel durations (Arrival < Departure)
$negativeDurations = $db->table('schedules')
    ->join('ferry_routes', 'schedules.ferry_route_id', '=', 'ferry_routes.id')
    ->where('ferry_routes.operator', '2GO')
    ->whereRaw('schedules.arrival_time < schedules.departure_time')
    ->select('schedules.id', 'ferry_routes.origin', 'ferry_routes.destination', 'schedules.departure_time', 'schedules.arrival_time')
    ->get();

echo "[Check 1] Arrival before Departure (Negative Duration): " . ($negativeDurations->isEmpty() ? "PASSED (0 anomalies)" : "FAILED (" . $negativeDurations->count() . " found)") . "\n";
foreach ($negativeDurations as $nd) {
    echo "  - Sched ID {$nd->id}: {$nd->origin} -> {$nd->destination} | Dep: {$nd->departure_time} | Arr: {$nd->arrival_time}\n";
}

// Check 5.2: Schedules without accommodations
$orphanedAccommodations = $db->table('schedules')
    ->join('ferry_routes', 'schedules.ferry_route_id', '=', 'ferry_routes.id')
    ->where('ferry_routes.operator', '2GO')
    ->where('schedules.is_active', 1)
    ->whereNotExists(function ($q) {
        $q->select(DB::raw(1))
            ->from('schedule_accommodations')
            ->whereColumn('schedule_accommodations.schedule_id', 'schedules.id');
    })
    ->count();

echo "[Check 2] Schedules missing Accommodations: " . ($orphanedAccommodations === 0 ? "PASSED (All have accommodations)" : "NOTICE ({$orphanedAccommodations} without accommodations)") . "\n";
if ($orphanedAccommodations > 0) {
    $orphanedList = $db->table('schedules')
        ->join('ferry_routes', 'schedules.ferry_route_id', '=', 'ferry_routes.id')
        ->where('ferry_routes.operator', '2GO')
        ->where('schedules.is_active', 1)
        ->whereNotExists(function ($q) {
            $q->select(DB::raw(1))->from('schedule_accommodations')->whereColumn('schedule_accommodations.schedule_id', 'schedules.id');
        })
        ->select('schedules.id', 'schedules.ferry_route_id', 'schedules.departure_time', 'ferry_routes.origin', 'ferry_routes.destination')
        ->get();
    foreach ($orphanedList as $ol) {
        echo "  - Sched ID {$ol->id} | {$ol->origin} -> {$ol->destination} | Dep: {$ol->departure_time}\n";
    }
}

// Check 5.3: Duplicate departures (Same route, vessel, and departure minute)
$duplicates = $db->table('schedules')
    ->join('ferry_routes', 'schedules.ferry_route_id', '=', 'ferry_routes.id')
    ->where('ferry_routes.operator', '2GO')
    ->where('schedules.is_active', 1)
    ->select('schedules.ferry_route_id', 'schedules.vehicle_name', 'schedules.departure_time', DB::raw('count(*) as cnt'))
    ->groupBy('schedules.ferry_route_id', 'schedules.vehicle_name', 'schedules.departure_time')
    ->having('cnt', '>', 1)
    ->get();

echo "[Check 3] Duplicate Departures (Exact same route, vessel, & time): " . ($duplicates->isEmpty() ? "PASSED (0 duplicates)" : "NOTICE (" . $duplicates->count() . " duplicates found)") . "\n";
foreach ($duplicates->take(5) as $dup) {
    echo "  - Route ID {$dup->ferry_route_id} | Vessel: {$dup->vehicle_name} | Time: {$dup->departure_time} (Count: {$dup->cnt})\n";
}

// 6. Accommodations & Pricing Summary
echo "\n-------------------------------------------------------------------------\n";
echo "ACCOMMODATION CLASSES & PRICING OVERVIEW FOR 2GO\n";
echo "-------------------------------------------------------------------------\n";

$accStats = $db->table('schedule_accommodations')
    ->join('schedules', 'schedule_accommodations.schedule_id', '=', 'schedules.id')
    ->join('ferry_routes', 'schedules.ferry_route_id', '=', 'ferry_routes.id')
    ->where('ferry_routes.operator', '2GO')
    ->selectRaw('
        schedule_accommodations.name,
        count(*) as occurrences,
        min(schedule_accommodations.price) as min_price,
        max(schedule_accommodations.price) as max_price,
        avg(schedule_accommodations.price) as avg_price
    ')
    ->groupBy('schedule_accommodations.name')
    ->orderByDesc('occurrences')
    ->get();

foreach ($accStats as $acc) {
    echo sprintf(
        "%-25s | Occurrences: %-6d | Min: ₱%-8.2f | Max: ₱%-8.2f | Avg: ₱%.2f\n",
        $acc->name,
        $acc->occurrences,
        $acc->min_price,
        $acc->max_price,
        $acc->avg_price
    );
}

echo "\n=========================================================================\n";
echo "AUDIT COMPLETE\n";
echo "=========================================================================\n";

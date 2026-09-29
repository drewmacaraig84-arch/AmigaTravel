<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\Schedule;

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

echo "========================================================================\n";
echo "       SEARCH VERIFICATION: BATANGAS -> CATICLAN (MULTI-VESSEL)         \n";
echo "========================================================================\n\n";

$testDates = ['2026-09-30', '2026-10-01', '2026-10-05'];

foreach ($testDates as $date) {
    echo "=== Date: {$date} ===\n";
    $scheds = Schedule::where('ferry_route_id', 105)
        ->whereDate('departure_time', $date)
        ->with(['accommodations', 'transportClasses', 'ferryRoute.operatorRecord'])
        ->orderBy('departure_time')
        ->orderBy('vehicle_name')
        ->get();

    echo "Total schedules found: {$scheds->count()}\n";
    $vesselsSeen = [];
    foreach ($scheds as $s) {
        $vesselsSeen[$s->vehicle_name] = ($vesselsSeen[$s->vehicle_name] ?? 0) + 1;
        $accCount = $s->accommodations->count();
        $tcCount = $s->transportClasses->count();
        $arr = $s->toBookingArray();
        echo sprintf("  [%s] %-32s | Price: ₱%7.2f | Accs: %d | Pivots: %d | Service: %s\n", 
            substr($s->departure_time, 11, 5), 
            $arr['vehicle_name'], 
            $arr['price'], 
            $accCount, 
            $tcCount,
            $arr['service']
        );
    }

    echo "\nVessels Breakdown for {$date}:\n";
    foreach ($vesselsSeen as $v => $c) {
        echo "  - '{$v}': {$c} departures\n";
    }
    echo "\n";
}

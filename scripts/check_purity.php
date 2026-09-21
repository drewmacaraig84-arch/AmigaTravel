<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

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

echo "=== CHECKING LEFTOVER LCT & PLACEHOLDERS TO PURIFY ===" . PHP_EOL;

// 1. Sprint 1 & Pacific on Roxas Mindoro <-> Caticlan (LCT vessels)
$lctRoxasCat = $db->table('schedules')
    ->join('ferry_routes', 'schedules.ferry_route_id', '=', 'ferry_routes.id')
    ->whereIn('ferry_routes.id', [125, 126])
    ->whereIn('schedules.vehicle_name', ['MV Starlite Sprint 1', 'MV Starlite Pacific'])
    ->where('schedules.is_active', 1)
    ->count();
echo "1. Sprint 1 & Pacific LCT on Roxas Mindoro <-> Caticlan: {$lctRoxasCat} active departures" . PHP_EOL;

// 2. Old 18:00 placeholder on Batangas <-> Caticlan (Route 105, 106)
$caticlanOld = $db->table('schedules')
    ->whereIn('ferry_route_id', [105, 106])
    ->whereRaw('TIME(departure_time) = ?', ['18:00:00'])
    ->where('is_active', 1)
    ->count();
echo "2. Old 18:00 placeholder on Batangas <-> Caticlan: {$caticlanOld} active departures" . PHP_EOL;

// 3. Old 16:00 placeholder on Batangas <-> Roxas Capiz (Route 107, 108)
$roxasCapizOld = $db->table('schedules')
    ->whereIn('ferry_route_id', [107, 108])
    ->whereRaw('TIME(departure_time) = ?', ['16:00:00'])
    ->where('is_active', 1)
    ->count();
echo "3. Old 16:00 placeholder on Batangas <-> Roxas Capiz: {$roxasCapizOld} active departures" . PHP_EOL;

// 4. Old 08:00, 14:00, 20:00 placeholders on Batangas <-> Calapan (Route 103, 104)
$calapanOld = $db->table('schedules')
    ->whereIn('ferry_route_id', [103, 104])
    ->whereIn(DB::raw('TIME(departure_time)'), ['08:00:00', '14:00:00', '20:00:00'])
    ->whereIn('vehicle_name', ['MV Starlite Eagle', 'MV Starlite Pioneer', 'MV Starlite Saturn'])
    ->where('is_active', 1)
    ->count();
echo "4. Old even-hour placeholders on Batangas <-> Calapan: {$calapanOld} active departures" . PHP_EOL;

$totalToDeactivate = $lctRoxasCat + $caticlanOld + $roxasCapizOld + $calapanOld;
echo PHP_EOL . "Total leftover departures to deactivate (set is_active = 0, no delete): {$totalToDeactivate}" . PHP_EOL;
echo "Active schedules before: " . $db->table('schedules')->where('is_active', 1)->count() . PHP_EOL;
echo "Active schedules after: " . ($db->table('schedules')->where('is_active', 1)->count() - $totalToDeactivate) . PHP_EOL;

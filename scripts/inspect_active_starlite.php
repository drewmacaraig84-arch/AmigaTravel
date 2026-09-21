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

// Check what vehicles are in schedules
$vehicles = $db->table('schedules')
    ->join('ferry_routes', 'schedules.ferry_route_id', '=', 'ferry_routes.id')
    ->where('ferry_routes.operator', 'Starlite')
    ->where('schedules.is_active', 1)
    ->selectRaw('schedules.vehicle_name, count(*) as cnt')
    ->groupBy('schedules.vehicle_name')
    ->get();

echo "Vehicles in active Starlite schedules:" . PHP_EOL;
foreach ($vehicles as $v) {
    echo "  {$v->vehicle_name}: {$v->cnt}" . PHP_EOL;
}

// Check Roxas Mindoro - Caticlan vehicles
$roxCat = $db->table('schedules')
    ->join('ferry_routes', 'schedules.ferry_route_id', '=', 'ferry_routes.id')
    ->where('ferry_routes.origin', 'Roxas Mindoro')
    ->where('ferry_routes.destination', 'Caticlan')
    ->where('ferry_routes.operator', 'Starlite')
    ->where('schedules.is_active', 1)
    ->selectRaw('schedules.vehicle_name, TIME(schedules.departure_time) as dep_time, count(*) as cnt')
    ->groupBy('schedules.vehicle_name', DB::raw('TIME(schedules.departure_time)'))
    ->get();

echo PHP_EOL . "Roxas Mindoro -> Caticlan departure times:" . PHP_EOL;
foreach ($roxCat as $r) {
    echo "  {$r->dep_time} ({$r->vehicle_name}): {$r->cnt}" . PHP_EOL;
}

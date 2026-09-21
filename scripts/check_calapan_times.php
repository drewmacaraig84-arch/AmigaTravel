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

$calapanTimes = $db->table('schedules')
    ->where('ferry_route_id', 103)
    ->where('is_active', 1)
    ->whereDate('departure_time', '2026-10-15')
    ->select('vehicle_name', DB::raw('TIME(departure_time) as dep_time'))
    ->orderBy('dep_time')
    ->get();

echo "Batangas -> Calapan departures on 2026-10-15:" . PHP_EOL;
foreach ($calapanTimes as $t) {
    echo "  {$t->dep_time} - {$t->vehicle_name}" . PHP_EOL;
}

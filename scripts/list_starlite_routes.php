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

$routes = $db->table('ferry_routes')
    ->where('operator', 'Starlite')
    ->select('id', 'origin', 'destination', 'is_active')
    ->get();

echo "All Starlite ferry_routes on Railway:" . PHP_EOL;
foreach ($routes as $r) {
    $schedCount = $db->table('schedules')->where('ferry_route_id', $r->id)->where('is_active', 1)->count();
    echo "  [ID: {$r->id}] {$r->origin} -> {$r->destination} (Active Schedules: {$schedCount})" . PHP_EOL;
}

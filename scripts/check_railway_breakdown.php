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

echo "=== RAILWAY PRODUCTION BREAKDOWN ===" . PHP_EOL;
$byOp = $db->table('schedules')
    ->join('ferry_routes', 'schedules.ferry_route_id', '=', 'ferry_routes.id')
    ->where('schedules.is_active', 1)
    ->selectRaw('ferry_routes.operator, count(*) as cnt')
    ->groupBy('ferry_routes.operator')
    ->get();

foreach ($byOp as $row) {
    echo "  {$row->operator}: {$row->cnt}" . PHP_EOL;
}

$prodTotal = $db->table('schedules')->where('is_active', 1)->count();
echo "Total active schedules in DB: {$prodTotal}" . PHP_EOL;

echo PHP_EOL . "Starlite routes on Railway:" . PHP_EOL;
$starliteRoutes = $db->table('schedules')
    ->join('ferry_routes', 'schedules.ferry_route_id', '=', 'ferry_routes.id')
    ->where('ferry_routes.operator', 'Starlite')
    ->where('schedules.is_active', 1)
    ->selectRaw('ferry_routes.origin, ferry_routes.destination, count(*) as cnt, min(schedules.departure_time) as min_dep, max(schedules.departure_time) as max_dep')
    ->groupBy('ferry_routes.origin', 'ferry_routes.destination')
    ->get();

foreach ($starliteRoutes as $r) {
    echo "  {$r->origin} -> {$r->destination}: {$r->cnt} ({$r->min_dep} to {$r->max_dep})" . PHP_EOL;
}

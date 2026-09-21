<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

// Configure Railway Production Database
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

foreach (['railway', 'mysql'] as $conn) {
    $db = DB::connection($conn);
    $connName = ($conn === 'railway') ? 'RAILWAY PRODUCTION' : 'LOCAL';
    echo "=== PURIFYING ON {$connName} DATABASE ===" . PHP_EOL;

    // 1. Deactivate Sprint 1 & Pacific on Roxas Mindoro <-> Caticlan (LCT vessels)
    $d1 = $db->table('schedules')
        ->join('ferry_routes', 'schedules.ferry_route_id', '=', 'ferry_routes.id')
        ->where(function($q) {
            $q->where(function($sub) {
                $sub->where('ferry_routes.origin', 'Roxas Mindoro')->where('ferry_routes.destination', 'Caticlan');
            })->orWhere(function($sub) {
                $sub->where('ferry_routes.origin', 'Caticlan')->where('ferry_routes.destination', 'Roxas Mindoro');
            });
        })
        ->whereIn('schedules.vehicle_name', ['MV Starlite Sprint 1', 'MV Starlite Pacific'])
        ->where('schedules.is_active', 1)
        ->update(['schedules.is_active' => 0]);

    echo "  1. Deactivated {$d1} LCT departures (Sprint 1 & Pacific) on Roxas Mindoro <-> Caticlan" . PHP_EOL;

    // 2. Deactivate Old 18:00 placeholder on Batangas <-> Caticlan
    $d2 = $db->table('schedules')
        ->join('ferry_routes', 'schedules.ferry_route_id', '=', 'ferry_routes.id')
        ->where(function($q) {
            $q->where(function($sub) {
                $sub->where('ferry_routes.origin', 'Batangas')->where('ferry_routes.destination', 'Caticlan');
            })->orWhere(function($sub) {
                $sub->where('ferry_routes.origin', 'Caticlan')->where('ferry_routes.destination', 'Batangas');
            });
        })
        ->where('ferry_routes.operator', 'Starlite')
        ->whereRaw('TIME(schedules.departure_time) = ?', ['18:00:00'])
        ->where('schedules.is_active', 1)
        ->update(['schedules.is_active' => 0]);

    echo "  2. Deactivated {$d2} old 18:00 placeholder departures on Batangas <-> Caticlan" . PHP_EOL;

    // 3. Deactivate Old 16:00 placeholder on Batangas <-> Roxas Capiz
    $d3 = $db->table('schedules')
        ->join('ferry_routes', 'schedules.ferry_route_id', '=', 'ferry_routes.id')
        ->where(function($q) {
            $q->where(function($sub) {
                $sub->where('ferry_routes.origin', 'Batangas')->where('ferry_routes.destination', 'Roxas City, Capiz');
            })->orWhere(function($sub) {
                $sub->where('ferry_routes.origin', 'Roxas City, Capiz')->where('ferry_routes.destination', 'Batangas');
            });
        })
        ->where('ferry_routes.operator', 'Starlite')
        ->whereRaw('TIME(schedules.departure_time) = ?', ['16:00:00'])
        ->where('schedules.is_active', 1)
        ->update(['schedules.is_active' => 0]);

    echo "  3. Deactivated {$d3} old 16:00 placeholder departures on Batangas <-> Roxas Capiz" . PHP_EOL;

    $totalActive = $db->table('schedules')->where('is_active', 1)->count();
    echo "  Remaining ACTIVE schedules on {$connName}: {$totalActive}" . PHP_EOL . PHP_EOL;
}

echo "=== PURITY COMPLETED WITHOUT DELETING ANY DATA ===" . PHP_EOL;

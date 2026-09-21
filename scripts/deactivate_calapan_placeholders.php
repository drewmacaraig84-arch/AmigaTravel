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

foreach (['railway', 'mysql'] as $conn) {
    $db = DB::connection($conn);
    $connName = ($conn === 'railway') ? 'RAILWAY PRODUCTION' : 'LOCAL';

    $affected = $db->affectingStatement("
        UPDATE schedules s
        JOIN ferry_routes fr ON s.ferry_route_id = fr.id
        SET s.is_active = 0
        WHERE ((fr.origin = 'Batangas' AND fr.destination = 'Calapan') OR (fr.origin = 'Calapan' AND fr.destination = 'Batangas'))
          AND fr.operator = 'Starlite'
          AND TIME(s.departure_time) IN ('08:00:00', '14:00:00', '20:00:00')
          AND s.vehicle_name IN ('MV Starlite Eagle', 'MV Starlite Pioneer', 'MV Starlite Saturn', 'Starlite Eagle', 'Starlite Pioneer', 'Starlite Saturn')
          AND s.is_active = 1
    ");

    echo "Deactivated {$affected} old placeholder departures on Batangas <-> Calapan on {$connName}" . PHP_EOL;

    $cnt = $db->table('schedules')
        ->join('ferry_routes', 'schedules.ferry_route_id', '=', 'ferry_routes.id')
        ->where('ferry_routes.origin', 'Batangas')
        ->where('ferry_routes.destination', 'Calapan')
        ->where('ferry_routes.operator', 'Starlite')
        ->where('schedules.is_active', 1)
        ->whereDate('schedules.departure_time', '2026-10-15')
        ->count();

    echo "Batangas -> Calapan on 2026-10-15 on {$connName}: {$cnt} departures (Expected: 15)" . PHP_EOL;
}

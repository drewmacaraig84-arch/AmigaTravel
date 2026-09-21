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

// Check the placeholder departures that came from the first script
$placeholders = [
    // Batangas <-> Calapan placeholders: 08:00, 14:00, 20:00 (vessels: Eagle, Pioneer, Saturn)
    ['Batangas', 'Calapan', ['08:00:00', '14:00:00', '20:00:00']],
    ['Calapan', 'Batangas', ['08:00:00', '14:00:00', '20:00:00']],
    // Batangas <-> Caticlan placeholder: 18:00 (vessel: Archer)
    ['Batangas', 'Caticlan', ['18:00:00']],
    ['Caticlan', 'Batangas', ['18:00:00']],
    // Roxas Mindoro <-> Caticlan placeholders: Sprint 1 (08:00) and Pacific (14:00)
    ['Roxas Mindoro', 'Caticlan', ['08:00:00', '14:00:00']],
    ['Caticlan', 'Roxas Mindoro', ['08:00:00', '14:00:00']],
    // Batangas <-> Roxas Capiz placeholder: 16:00
    ['Batangas', 'Roxas City, Capiz', ['16:00:00']],
    ['Roxas City, Capiz', 'Batangas', ['16:00:00']],
];

$totalPlaceholders = 0;
foreach ($placeholders as [$orig, $dest, $times]) {
    $cnt = $db->table('schedules')
        ->join('ferry_routes', 'schedules.ferry_route_id', '=', 'ferry_routes.id')
        ->where('ferry_routes.origin', $orig)
        ->where('ferry_routes.destination', $dest)
        ->where('ferry_routes.operator', 'Starlite')
        ->where('schedules.is_active', 1)
        ->whereIn(DB::raw('TIME(schedules.departure_time)'), $times)
        ->where(function($q) {
            // Also ensure we don't deactivate Fastcraft 14:30 or true departures
            $q->where('schedules.vehicle_name', 'like', '%Eagle%')
              ->orWhere('schedules.vehicle_name', 'like', '%Pioneer%')
              ->orWhere('schedules.vehicle_name', 'like', '%Saturn%')
              ->orWhere('schedules.vehicle_name', 'like', '%Sprint 1%')
              ->orWhere('schedules.vehicle_name', 'like', '%Pacific%')
              ->orWhere('schedules.vehicle_name', 'like', '%Archer%')
              ->orWhere('schedules.vehicle_name', 'like', '%Annapolis%');
        })
        ->count();

    echo "{$orig} -> {$dest} placeholders: {$cnt}" . PHP_EOL;
    $totalPlaceholders += $cnt;
}

echo "Total placeholder/LCT departures to deactivate: {$totalPlaceholders}" . PHP_EOL;

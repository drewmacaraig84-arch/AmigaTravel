<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\FerryRoute;
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
    'engine'    => null,
]);
Config::set('database.connections.mysql', Config::get('database.connections.railway'));
Config::set('database.default', 'railway');
DB::setDefaultConnection('railway');

$routes = FerryRoute::where('operator', 'Starlite')->where('is_active', true)->get();

echo "Active Starlite Routes and their current upcoming schedule vessels:\n\n";

foreach ($routes as $r) {
    $upcoming = Schedule::where('ferry_route_id', $r->id)
        ->where('departure_time', '>=', now())
        ->select('vehicle_name', DB::raw('count(*) as c'))
        ->groupBy('vehicle_name')
        ->get();
        
    $vesselSummary = [];
    foreach ($upcoming as $u) {
        $vesselSummary[] = "'{$u->vehicle_name}' ({$u->c})";
    }
    
    echo "Route {$r->id}: {$r->origin} -> {$r->destination}\n";
    echo "  Upcoming Vessels: " . (empty($vesselSummary) ? "NONE" : implode(', ', $vesselSummary)) . "\n";
}

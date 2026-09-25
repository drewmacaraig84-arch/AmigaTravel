<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\FerryRoute;
use App\Models\Schedule;
use App\Models\Operator;
use App\Models\Vehicle;
use App\Models\TransportClass;
use App\Models\ScheduleAccommodation;
use App\Services\ScheduleCsvImportService;
use App\Services\LocationCodeResolver;

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

// Also set mysql connection to railway so no local connection is ever attempted!
Config::set('database.connections.mysql', Config::get('database.connections.railway'));
Config::set('database.default', 'railway');
DB::setDefaultConnection('railway');

echo "Testing single query on Railway...\n";
$start = microtime(true);
$op = Operator::firstOrCreate(['name' => '2GO'], ['mode' => 'ferry', 'is_active' => true]);
echo "Operator resolved in " . round(microtime(true) - $start, 3) . "s (ID: {$op->id})\n";

$start = microtime(true);
$veh = Vehicle::where('operator', '2GO')->where('name', 'MV 2GO Masikap')->first();
echo "Vehicle query in " . round(microtime(true) - $start, 3) . "s (Found: " . ($veh ? $veh->id : 'no') . ")\n";

$start = microtime(true);
$route = FerryRoute::where('origin', 'Manila')->where('destination', 'Cebu')->where('operator', '2GO')->first();
echo "FerryRoute query in " . round(microtime(true) - $start, 3) . "s (Found: " . ($route ? $route->id : 'no') . ")\n";

$start = microtime(true);
$tc = TransportClass::where('operator', '2GO')->where('name', 'Tourist Class')->first();
echo "TransportClass query in " . round(microtime(true) - $start, 3) . "s (Found: " . ($tc ? $tc->id : 'no') . ")\n";

<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\FerryRoute;
use App\Models\Schedule;
use Illuminate\Support\Facades\DB;

$routes = FerryRoute::where('operator', 'Starlite')->count();
$scheds = DB::table('schedules')
    ->join('ferry_routes', 'schedules.ferry_route_id', '=', 'ferry_routes.id')
    ->where('ferry_routes.operator', 'Starlite')
    ->count();
$accs = DB::table('schedule_accommodations')
    ->join('schedules', 'schedule_accommodations.schedule_id', '=', 'schedules.id')
    ->join('ferry_routes', 'schedules.ferry_route_id', '=', 'ferry_routes.id')
    ->where('ferry_routes.operator', 'Starlite')
    ->count();

echo "Starlite Routes: $routes\n";
echo "Starlite Schedules: $scheds\n";
echo "Starlite Accommodations: $accs\n";

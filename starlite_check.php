<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\FerryRoute;
use App\Models\Schedule;
use Illuminate\Support\Facades\DB;

// Check what's already there
$routes = FerryRoute::where('operator', 'Starlite')->count();
$scheds = DB::table('schedules')
    ->join('ferry_routes', 'schedules.ferry_route_id', '=', 'ferry_routes.id')
    ->where('ferry_routes.operator', 'Starlite')
    ->count();
echo "Starlite Routes: $routes, Schedules: $scheds\n";

// Clean up any partial import
if ($routes > 0 || $scheds > 0) {
    echo "Cleaning partial import...\n";
    $routeIds = FerryRoute::where('operator', 'Starlite')->pluck('id');
    if ($routeIds->count() > 0) {
        $schedIds = DB::table('schedules')->whereIn('ferry_route_id', $routeIds)->pluck('id');
        if ($schedIds->count() > 0) {
            DB::table('schedule_accommodations')->whereIn('schedule_id', $schedIds)->delete();
            DB::table('schedule_transport_class')->whereIn('schedule_id', $schedIds)->delete();
            DB::table('schedules')->whereIn('ferry_route_id', $routeIds)->delete();
        }
        FerryRoute::whereIn('id', $routeIds)->delete();
    }
    echo "Cleaned up.\n";
}

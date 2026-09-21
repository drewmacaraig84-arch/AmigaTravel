<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== LOCAL Database ===" . PHP_EOL;
$localTotal = DB::table('schedules')->where('is_active', 1)->count();
echo "Total active schedules: {$localTotal}" . PHP_EOL . PHP_EOL;

$byOperator = DB::table('schedules')
    ->join('ferry_routes', 'schedules.ferry_route_id', '=', 'ferry_routes.id')
    ->where('schedules.is_active', 1)
    ->selectRaw('ferry_routes.operator, count(*) as cnt')
    ->groupBy('ferry_routes.operator')
    ->get();

echo "By operator:" . PHP_EOL;
foreach ($byOperator as $r) {
    echo "  {$r->operator}: {$r->cnt}" . PHP_EOL;
}

echo PHP_EOL . "Starlite by route:" . PHP_EOL;
$starliteRoutes = DB::table('schedules')
    ->join('ferry_routes', 'schedules.ferry_route_id', '=', 'ferry_routes.id')
    ->where('ferry_routes.operator', 'Starlite')
    ->where('schedules.is_active', 1)
    ->selectRaw('ferry_routes.origin, ferry_routes.destination, count(*) as cnt, MIN(schedules.departure_time) as min_dep, MAX(schedules.departure_time) as max_dep')
    ->groupBy('ferry_routes.origin', 'ferry_routes.destination')
    ->get();

foreach ($starliteRoutes as $r) {
    echo "  {$r->origin} -> {$r->destination}: {$r->cnt} ({$r->min_dep} to {$r->max_dep})" . PHP_EOL;
}

echo PHP_EOL . "2GO by route:" . PHP_EOL;
$twoGoRoutes = DB::table('schedules')
    ->join('ferry_routes', 'schedules.ferry_route_id', '=', 'ferry_routes.id')
    ->where('ferry_routes.operator', '2GO')
    ->where('schedules.is_active', 1)
    ->selectRaw('ferry_routes.origin, ferry_routes.destination, count(*) as cnt, MIN(schedules.departure_time) as min_dep, MAX(schedules.departure_time) as max_dep')
    ->groupBy('ferry_routes.origin', 'ferry_routes.destination')
    ->get();

foreach ($twoGoRoutes as $r) {
    echo "  {$r->origin} -> {$r->destination}: {$r->cnt} ({$r->min_dep} to {$r->max_dep})" . PHP_EOL;
}

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

// Check across all operators
$suspicious = $db->table('ferry_routes')
    ->where('origin', 'like', '%:%')
    ->orWhere('origin', 'like', '%@%')
    ->orWhere('origin', 'Operator')
    ->orWhere('origin', 'Departure Time')
    ->orWhere('destination', 'like', '%:%')
    ->orWhere('destination', 'like', '%@%')
    ->orWhere('destination', 'Arrival Time')
    ->orWhere('destination', 'Vehicle Tail No')
    ->get();

echo "Total suspicious routes across whole Railway DB: " . $suspicious->count() . "\n";
$ids = $suspicious->pluck('id')->toArray();

if (!empty($ids)) {
    $schedCount = $db->table('schedules')->whereIn('ferry_route_id', $ids)->count();
    echo "Schedules linked to these routes: " . $schedCount . "\n";

    // Check if bookings table has ferry_route_id or schedule_id
    if ($schedCount > 0) {
        $schedIds = $db->table('schedules')->whereIn('ferry_route_id', $ids)->pluck('id');
        $bookingsCount = $db->table('bookings')->whereIn('schedule_id', $schedIds)->count();
        echo "Bookings linked to these schedules: " . $bookingsCount . "\n";
    } else {
        echo "Bookings linked: 0 (No schedules exist for these routes)\n";
    }

    echo "Suspect Route IDs: " . min($ids) . " to " . max($ids) . " (Count: " . count($ids) . ")\n";
}

$localSuspicious = DB::connection('mysql')->table('ferry_routes')
    ->where('origin', 'like', '%:%')
    ->orWhere('origin', 'like', '%@%')
    ->orWhere('origin', 'Operator')
    ->orWhere('origin', 'Departure Time')
    ->orWhere('destination', 'like', '%:%')
    ->orWhere('destination', 'like', '%@%')
    ->orWhere('destination', 'Arrival Time')
    ->orWhere('destination', 'Vehicle Tail No')
    ->count();
echo "Local DB suspicious routes: " . $localSuspicious . "\n";

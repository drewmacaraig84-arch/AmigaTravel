<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

$isDryRun = !in_array('--force', $argv);

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

echo "=== PURGE 2GO NON-TARGET ROUTES (" . ($isDryRun ? "DRY-RUN" : "LIVE EXECUTION") . ") ===\n\n";

// Permitted corridor pairs (both outbound and inbound)
$allowedPairs = [
    'manila -> cebu',
    'cebu -> manila',
    'manila -> bacolod',
    'bacolod -> manila',
    'manila -> butuan (nasipit)',
    'butuan (nasipit) -> manila',
    'manila -> butuan via nasipit',
    'butuan via nasipit -> manila',
];

$all2goRoutes = $db->table('ferry_routes')->where('operator', '2GO')->get();

$routesToKeep = [];
$routesToDelete = [];

foreach ($all2goRoutes as $r) {
    $pair = strtolower(trim($r->origin)) . ' -> ' . strtolower(trim($r->destination));
    if (in_array($pair, $allowedPairs, true)) {
        $routesToKeep[] = $r;
    } else {
        $routesToDelete[] = $r;
    }
}

echo "Total 2GO Routes Evaluated: " . $all2goRoutes->count() . "\n";
echo "  Routes to KEEP (Manila <-> Cebu, Bacolod, Butuan): " . count($routesToKeep) . "\n";
echo "  Routes to DELETE: " . count($routesToDelete) . "\n\n";

echo "=== ROUTES TO KEEP ===\n";
foreach ($routesToKeep as $rk) {
    $sc = $db->table('schedules')->where('ferry_route_id', $rk->id)->count();
    echo "  [KEEP] ID: {$rk->id} | {$rk->origin} -> {$rk->destination} | {$sc} schedules\n";
}

$deleteRouteIds = array_column($routesToDelete, 'id');
$schedIdsToDelete = $db->table('schedules')->whereIn('ferry_route_id', $deleteRouteIds)->pluck('id')->toArray();

echo "\nSchedules attached to deleted routes: " . count($schedIdsToDelete) . "\n";

// Check child records for schedules
$pivotCount = 0;
$accCount = 0;
$bookingIdsToDelete = [];

if (!empty($schedIdsToDelete)) {
    $pivotCount = $db->table('schedule_transport_class')->whereIn('schedule_id', $schedIdsToDelete)->count();
    $accCount = $db->table('schedule_accommodations')->whereIn('schedule_id', $schedIdsToDelete)->count();
    $bookingIdsToDelete = $db->table('bookings')->whereIn('schedule_id', $schedIdsToDelete)->pluck('id')->toArray();
}

echo "Schedule Transport Class records to remove: {$pivotCount}\n";
echo "Schedule Accommodations records to remove:  {$accCount}\n";
echo "Test Bookings to remove:                    " . count($bookingIdsToDelete) . "\n";

$passengersCount = 0;
$transactionsCount = 0;

if (!empty($bookingIdsToDelete)) {
    if ($db->getSchemaBuilder()->hasTable('passengers')) {
        $passengersCount = $db->table('passengers')->whereIn('booking_id', $bookingIdsToDelete)->count();
    }
    if ($db->getSchemaBuilder()->hasTable('transactions')) {
        $transactionsCount = $db->table('transactions')->whereIn('booking_id', $bookingIdsToDelete)->count();
    }
}

echo "Passenger records to remove:                {$passengersCount}\n";
echo "Transaction records to remove:              {$transactionsCount}\n";

if ($isDryRun) {
    echo "\n[DRY RUN COMPLETE] To execute live deletion on Railway production, pass --force.\n";
    exit(0);
}

// LIVE EXECUTION IN TRANSACTION
echo "\n--- Executing Live Deletion in Database Transaction ---\n";

$db->beginTransaction();

try {
    // 1. Delete transactions & passengers for test bookings
    if (!empty($bookingIdsToDelete)) {
        if ($db->getSchemaBuilder()->hasTable('passengers')) {
            $delP = $db->table('passengers')->whereIn('booking_id', $bookingIdsToDelete)->delete();
            echo "  Deleted {$delP} passenger records.\n";
        }
        if ($db->getSchemaBuilder()->hasTable('transactions')) {
            $delT = $db->table('transactions')->whereIn('booking_id', $bookingIdsToDelete)->delete();
            echo "  Deleted {$delT} transaction records.\n";
        }
        $delB = $db->table('bookings')->whereIn('id', $bookingIdsToDelete)->delete();
        echo "  Deleted {$delB} test booking records.\n";
    }

    // 2. Delete schedule relations in chunks
    if (!empty($schedIdsToDelete)) {
        foreach (array_chunk($schedIdsToDelete, 500) as $chunk) {
            $db->table('schedule_transport_class')->whereIn('schedule_id', $chunk)->delete();
            $db->table('schedule_accommodations')->whereIn('schedule_id', $chunk)->delete();
            $db->table('schedules')->whereIn('id', $chunk)->delete();
        }
        echo "  Deleted " . count($schedIdsToDelete) . " schedules and all associated accommodations.\n";
    }

    // 3. Delete the ferry routes
    $delR = $db->table('ferry_routes')->whereIn('id', $deleteRouteIds)->delete();
    echo "  Deleted {$delR} non-target 2GO ferry routes.\n";

    $db->commit();
    echo "✓ Database transaction committed successfully!\n";
} catch (Throwable $e) {
    $db->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    die("Transaction rolled back. No changes made.\n");
}

// Clear production cache table
if ($db->getSchemaBuilder()->hasTable('cache')) {
    $db->table('cache')->truncate();
    echo "✓ Cleared Railway production cache table.\n";
}

// Final Summary
echo "\n=== FINAL REMAINING 2GO ROUTES ON RAILWAY ===\n";
$finalRoutes = $db->table('ferry_routes')->where('operator', '2GO')->get();
foreach ($finalRoutes as $fr) {
    $cnt = $db->table('schedules')->where('ferry_route_id', $fr->id)->count();
    echo "  Route ID: {$fr->id} | {$fr->origin} -> {$fr->destination} | Active Scheds: {$cnt}\n";
}

echo "\n=== PURGE COMPLETED SUCCESSFULLY ===\n";

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

$isDryRun = !in_array('--force', $argv);

$db = DB::connection('railway');

echo "=== RAILWAY SUSPECT ROUTES REMOVAL (" . ($isDryRun ? "DRY-RUN / READ-ONLY" : "LIVE EXECUTION") . ") ===\n\n";

// Target specifically the 66 suspect malformed route records
$suspectRoutes = $db->table('ferry_routes')
    ->where('operator', '2GO')
    ->whereBetween('id', [157, 222])
    ->get();

echo "Targeted records found: " . $suspectRoutes->count() . "\n";
$targetIds = $suspectRoutes->pluck('id')->toArray();

// Single batch query for attached schedules
$blockedIds = $db->table('schedules')
    ->whereIn('ferry_route_id', $targetIds)
    ->pluck('ferry_route_id')
    ->unique()
    ->toArray();

$safeToDelete = array_values(array_diff($targetIds, $blockedIds));

echo "Safe to delete (0 attached schedules): " . count($safeToDelete) . "\n";
echo "Blocked (has attached schedules): " . count($blockedIds) . "\n";

if (!empty($blockedIds)) {
    echo "ABORTING: Some routes have active schedules attached: " . implode(', ', $blockedIds) . "\n";
    exit(1);
}

if ($isDryRun) {
    echo "\n[DRY RUN COMPLETE] No records were modified or deleted. Pass --force to execute deletion.\n";
    exit(0);
}

// Live Deletion
$deleted = $db->table('ferry_routes')
    ->whereIn('id', $safeToDelete)
    ->delete();

echo "\n✓ Successfully deleted {$deleted} malformed ferry routes from Railway production database.\n";

if ($db->getSchemaBuilder()->hasTable('cache')) {
    $db->table('cache')->truncate();
    echo "✓ Cleared Railway production cache table.\n";
}

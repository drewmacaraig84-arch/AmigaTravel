<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Services\ScheduleCsvImportService;
use App\Services\LocationCodeResolver;

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

Config::set('database.default', 'railway');
DB::setDefaultConnection('railway');

$db = DB::connection('railway');

echo "=== RAILWAY 2GO SCHEDULE IMPORT (" . ($isDryRun ? "DRY-RUN / VALIDATION" : "LIVE EXECUTION") . ") ===\n\n";

$info = $db->selectOne('SELECT DATABASE() as db');
echo "Connected to Railway DB: {$info->db}\n";

$csvPath = base_path('2go_schedules/2GO_All_3_Routes_Combined.csv');
if (!file_exists($csvPath)) {
    die("Error: File not found: {$csvPath}\n");
}

$rows = array_map('str_getcsv', file($csvPath));
$header = array_shift($rows);
echo "Total rows to import from CSV: " . count($rows) . "\n";

// Show existing counts before import
echo "\nCurrent 2GO Schedules on Railway:\n";
$currentRoutes = $db->table('ferry_routes')
    ->where('operator', '2GO')
    ->whereIn('destination', ['Cebu', 'Bacolod', 'Butuan (nasipit)'])
    ->get();

foreach ($currentRoutes as $cr) {
    $cnt = $db->table('schedules')->where('ferry_route_id', $cr->id)->count();
    echo "  Route ID: {$cr->id} | {$cr->origin} -> {$cr->destination} | Current Scheds: {$cnt}\n";
}

if ($isDryRun) {
    echo "\n[DRY RUN COMPLETE] To execute live import into Railway production, pass --force.\n";
    exit(0);
}

// Execute Live Import using ScheduleCsvImportService
echo "\n--- Executing Live Import via ScheduleCsvImportService ---\n";
$importer = new ScheduleCsvImportService(new LocationCodeResolver());
$result = $importer->import($csvPath, '2GO');

echo "Import Results:\n";
echo "  Imported (New): " . $result['imported'] . "\n";
echo "  Updated:        " . $result['updated'] . "\n";
echo "  Errors:         " . count($result['errors']) . "\n";

if (!empty($result['errors'])) {
    echo "Errors encountered:\n";
    foreach (array_slice($result['errors'], 0, 10) as $err) {
        echo "  - $err\n";
    }
}

// Show updated counts
echo "\nUpdated 2GO Schedules on Railway:\n";
$updatedRoutes = $db->table('ferry_routes')
    ->where('operator', '2GO')
    ->whereIn('destination', ['Cebu', 'Bacolod', 'Butuan (nasipit)'])
    ->get();

foreach ($updatedRoutes as $ur) {
    $cnt = $db->table('schedules')->where('ferry_route_id', $ur->id)->count();
    echo "  Route ID: {$ur->id} | {$ur->origin} -> {$ur->destination} | New Total Scheds: {$cnt}\n";
}

// Clear production cache table
if ($db->getSchemaBuilder()->hasTable('cache')) {
    $db->table('cache')->truncate();
    echo "\n✓ Cleared Railway production cache table.\n";
}

echo "\n=== IMPORT COMPLETED SUCCESSFULLY ===\n";

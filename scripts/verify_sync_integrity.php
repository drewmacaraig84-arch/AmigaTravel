<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

// Configure Railway Production Database
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

echo "=== VERIFYING INTEGRITY ON RAILWAY PRODUCTION DATABASE ===" . PHP_EOL . PHP_EOL;

// 1. Verify Exclusions
echo "--- 1. VERIFYING EXCLUSIONS ---" . PHP_EOL;
$roxasOdiongan = $db->table('schedules')
    ->join('ferry_routes', 'schedules.ferry_route_id', '=', 'ferry_routes.id')
    ->where(function($q) {
        $q->where(function($sub) {
            $sub->where('ferry_routes.origin', 'like', '%Roxas Mindoro%')
                ->where('ferry_routes.destination', 'like', '%Odiongan%');
        })->orWhere(function($sub) {
            $sub->where('ferry_routes.origin', 'like', '%Odiongan%')
                ->where('ferry_routes.destination', 'like', '%Roxas Mindoro%');
        });
    })
    ->where('ferry_routes.operator', 'Starlite')
    ->count();

echo "  Roxas Mindoro <-> Odiongan active schedules: {$roxasOdiongan} " . ($roxasOdiongan === 0 ? "✅ (CORRECT - EXCLUDED)" : "❌ (ERROR)") . PHP_EOL;

$lctCount = $db->table('schedules')
    ->join('ferry_routes', 'schedules.ferry_route_id', '=', 'ferry_routes.id')
    ->where('ferry_routes.operator', 'Starlite')
    ->where('schedules.is_active', 1)
    ->where(function($q) {
        $q->where('schedules.service_name', 'like', '%LCT%')
          ->orWhere('schedules.vehicle_name', 'like', '%LCT%')
          ->orWhere('schedules.vehicle_name', 'like', '%Sprint 1%')
          ->orWhere(function($sub) {
              $sub->where('ferry_routes.origin', 'Batangas')
                  ->where('ferry_routes.destination', 'like', '%Roxas%')
                  ->whereRaw('TIME(schedules.departure_time) = ?', ['04:00:00']);
          })
          ->orWhere(function($sub) {
              $sub->where('ferry_routes.origin', 'like', '%Roxas%')
                  ->where('ferry_routes.destination', 'Batangas')
                  ->whereRaw('TIME(schedules.departure_time) = ?', ['01:00:00']);
          });
    })
    ->count();

echo "  LCT active schedules: {$lctCount} " . ($lctCount === 0 ? "✅ (CORRECT - EXCLUDED)" : "❌ (ERROR)") . PHP_EOL . PHP_EOL;

// 2. Verify Sample Routes and Rates
echo "--- 2. VERIFYING RATES & ACCOMMODATIONS FOR KEY ROUTES ---" . PHP_EOL;
$sampleRoutes = [
    ['Batangas', 'Calapan'],
    ['Batangas', 'Caticlan'],
    ['Roxas Mindoro', 'Caticlan'],
    ['Batangas', 'Roxas City, Capiz'],
    ['Batangas', 'Romblon'],
    ['Batangas', 'Sibuyan (Magdiwang)'],
    ['Batangas', 'Cajidiocan'],
    ['Batangas', 'Odiongan'],
    ['Odiongan', 'Caticlan'],
    ['Cebu', 'Surigao'],
    ['Cebu', 'Dapitan'],
    ['Roxas Mindoro', 'Buruanga'],
];

foreach ($sampleRoutes as [$orig, $dest]) {
    $sched = $db->table('schedules')
        ->join('ferry_routes', 'schedules.ferry_route_id', '=', 'ferry_routes.id')
        ->where('ferry_routes.origin', $orig)
        ->where('ferry_routes.destination', $dest)
        ->where('ferry_routes.operator', 'Starlite')
        ->where('schedules.is_active', 1)
        ->select('schedules.id', 'schedules.service_name', 'schedules.departure_time', 'schedules.price')
        ->first();

    if (!$sched) {
        echo "❌ {$orig} -> {$dest}: NO SCHEDULE FOUND!" . PHP_EOL;
        continue;
    }

    $accs = $db->table('schedule_accommodations')
        ->where('schedule_id', $sched->id)
        ->select('name', 'price')
        ->orderBy('sort_order')
        ->get();

    $accList = $accs->map(fn($a) => "{$a->name}: ₱{$a->price}")->implode(', ');
    echo "✅ {$orig} -> {$dest} (Base: ₱{$sched->price}): {$accList}" . PHP_EOL;
}

echo PHP_EOL . "--- 3. VERIFYING DAILY FREQUENCIES (SAMPLE DATE: 2026-10-15) ---" . PHP_EOL;
$sampleDate = '2026-10-15';
$dailyCheck = [
    ['Batangas', 'Calapan', 15], // 12 ROPAX + 3 Fastcraft
    ['Batangas', 'Caticlan', 4],
    ['Roxas Mindoro', 'Caticlan', 6],
    ['Roxas Mindoro', 'Buruanga', 4],
    ['Batangas', 'Roxas City, Capiz', 1],
    ['Batangas', 'Romblon', 1],
    ['Batangas', 'Sibuyan (Magdiwang)', 1], // Thu is active (Tue, Thu, Sat, Sun)
    ['Batangas', 'Odiongan', 1], // Thu is active (Tue, Thu, Sat)
];

foreach ($dailyCheck as [$orig, $dest, $expected]) {
    $count = $db->table('schedules')
        ->join('ferry_routes', 'schedules.ferry_route_id', '=', 'ferry_routes.id')
        ->where('ferry_routes.origin', $orig)
        ->where('ferry_routes.destination', $dest)
        ->where('ferry_routes.operator', 'Starlite')
        ->where('schedules.is_active', 1)
        ->whereDate('schedules.departure_time', $sampleDate)
        ->count();

    $status = ($count === $expected) ? "✅ MATCH" : "⚠️ MISMATCH (Got {$count}, expected {$expected})";
    echo "  {$orig} -> {$dest} on {$sampleDate}: {$count} departures | {$status}" . PHP_EOL;
}

echo PHP_EOL . "=== AUDIT COMPLETE ===" . PHP_EOL;

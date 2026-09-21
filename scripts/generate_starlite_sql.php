<?php
/**
 * Generates a raw SQL file for direct execution via mysql CLI.
 * No remote connection needed — just produces the .sql file.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Carbon\Carbon;
use Illuminate\Support\Str;

$startDate = Carbon::today(); // 2026-09-21
$endDate = Carbon::parse('2026-12-31');
$now = Carbon::now()->format('Y-m-d H:i:s');

$routesData = [
    ['origin' => 'Batangas', 'destination' => 'Calapan', 'schedules' => [
        ['service_name' => 'Starlite Eagle', 'vehicle_name' => 'MV Starlite Eagle', 'plate_no' => 'STE-101', 'dep_time' => '08:00:00', 'duration' => 120, 'price' => 680.00],
        ['service_name' => 'Starlite Pioneer', 'vehicle_name' => 'MV Starlite Pioneer', 'plate_no' => 'STP-102', 'dep_time' => '14:00:00', 'duration' => 120, 'price' => 680.00],
        ['service_name' => 'Starlite Saturn', 'vehicle_name' => 'MV Starlite Saturn', 'plate_no' => 'STS-103', 'dep_time' => '20:00:00', 'duration' => 120, 'price' => 680.00],
    ], 'accommodations' => [
        ['name' => 'Reclining Seat', 'desc' => 'Air-conditioned reclining seat accommodation.', 'price' => 680.00, 'has_bed' => 0, 'sort' => 1],
        ['name' => 'Economy Bed Bunk', 'desc' => 'Air-conditioned bunk bed accommodation.', 'price' => 680.00, 'has_bed' => 1, 'sort' => 2],
        ['name' => 'Tourist Bed Bunk', 'desc' => 'Comfortable tourist class bed accommodation.', 'price' => 680.00, 'has_bed' => 1, 'sort' => 3],
    ]],
    ['origin' => 'Calapan', 'destination' => 'Batangas', 'schedules' => [
        ['service_name' => 'Starlite Eagle', 'vehicle_name' => 'MV Starlite Eagle', 'plate_no' => 'STE-101', 'dep_time' => '08:00:00', 'duration' => 120, 'price' => 680.00],
        ['service_name' => 'Starlite Pioneer', 'vehicle_name' => 'MV Starlite Pioneer', 'plate_no' => 'STP-102', 'dep_time' => '14:00:00', 'duration' => 120, 'price' => 680.00],
        ['service_name' => 'Starlite Saturn', 'vehicle_name' => 'MV Starlite Saturn', 'plate_no' => 'STS-103', 'dep_time' => '20:00:00', 'duration' => 120, 'price' => 680.00],
    ], 'accommodations' => [
        ['name' => 'Reclining Seat', 'desc' => 'Air-conditioned reclining seat accommodation.', 'price' => 680.00, 'has_bed' => 0, 'sort' => 1],
        ['name' => 'Economy Bed Bunk', 'desc' => 'Air-conditioned bunk bed accommodation.', 'price' => 680.00, 'has_bed' => 1, 'sort' => 2],
        ['name' => 'Tourist Bed Bunk', 'desc' => 'Comfortable tourist class bed accommodation.', 'price' => 680.00, 'has_bed' => 1, 'sort' => 3],
    ]],
    ['origin' => 'Batangas', 'destination' => 'Caticlan', 'schedules' => [
        ['service_name' => 'MV Starlite Archer', 'vehicle_name' => 'MV Starlite Archer', 'plate_no' => 'STA-201', 'dep_time' => '18:00:00', 'duration' => 540, 'price' => 2170.00],
    ], 'accommodations' => [
        ['name' => 'Reclining Seat', 'desc' => 'Comfortable reclining seat.', 'price' => 2170.00, 'has_bed' => 0, 'sort' => 1],
        ['name' => 'Economy Bed Bunk', 'desc' => 'Air-conditioned lower/upper bunk.', 'price' => 2270.00, 'has_bed' => 1, 'sort' => 2],
        ['name' => 'Tourist Bed Bunk', 'desc' => 'Spacious tourist class bed.', 'price' => 2790.00, 'has_bed' => 1, 'sort' => 3],
        ['name' => 'Cabin', 'desc' => 'Shared 4-berth cabin with privacy.', 'price' => 3720.00, 'has_bed' => 1, 'sort' => 4],
        ['name' => 'VIP Room (2-3 pax)', 'desc' => 'Exclusive VIP room with en-suite bath.', 'price' => 8300.00, 'has_bed' => 1, 'sort' => 5],
    ]],
    ['origin' => 'Caticlan', 'destination' => 'Batangas', 'schedules' => [
        ['service_name' => 'MV Starlite Archer', 'vehicle_name' => 'MV Starlite Archer', 'plate_no' => 'STA-201', 'dep_time' => '18:00:00', 'duration' => 540, 'price' => 2170.00],
    ], 'accommodations' => [
        ['name' => 'Reclining Seat', 'desc' => 'Comfortable reclining seat.', 'price' => 2170.00, 'has_bed' => 0, 'sort' => 1],
        ['name' => 'Economy Bed Bunk', 'desc' => 'Air-conditioned lower/upper bunk.', 'price' => 2270.00, 'has_bed' => 1, 'sort' => 2],
        ['name' => 'Tourist Bed Bunk', 'desc' => 'Spacious tourist class bed.', 'price' => 2790.00, 'has_bed' => 1, 'sort' => 3],
        ['name' => 'Cabin', 'desc' => 'Shared 4-berth cabin with privacy.', 'price' => 3720.00, 'has_bed' => 1, 'sort' => 4],
        ['name' => 'VIP Room (2-3 pax)', 'desc' => 'Exclusive VIP room with en-suite bath.', 'price' => 8300.00, 'has_bed' => 1, 'sort' => 5],
    ]],
    ['origin' => 'Batangas', 'destination' => 'Roxas City, Capiz', 'schedules' => [
        ['service_name' => 'MV Starlite Annapolis', 'vehicle_name' => 'MV Starlite Annapolis', 'plate_no' => 'STA-301', 'dep_time' => '16:00:00', 'duration' => 780, 'price' => 2580.00],
    ], 'accommodations' => [
        ['name' => 'Reclining Seat', 'desc' => 'Comfortable reclining seat.', 'price' => 2580.00, 'has_bed' => 0, 'sort' => 1],
        ['name' => 'Economy Bed Bunk', 'desc' => 'Air-conditioned bunk bed.', 'price' => 2580.00, 'has_bed' => 1, 'sort' => 2],
        ['name' => 'Tourist Bed Bunk', 'desc' => 'Spacious tourist class bed.', 'price' => 3200.00, 'has_bed' => 1, 'sort' => 3],
        ['name' => 'Cabin', 'desc' => 'Shared cabin.', 'price' => 3820.00, 'has_bed' => 1, 'sort' => 4],
        ['name' => 'VIP Room (2-3 pax)', 'desc' => 'Exclusive private VIP room.', 'price' => 11500.00, 'has_bed' => 1, 'sort' => 5],
    ]],
    ['origin' => 'Roxas City, Capiz', 'destination' => 'Batangas', 'schedules' => [
        ['service_name' => 'MV Starlite Annapolis', 'vehicle_name' => 'MV Starlite Annapolis', 'plate_no' => 'STA-301', 'dep_time' => '16:00:00', 'duration' => 780, 'price' => 2580.00],
    ], 'accommodations' => [
        ['name' => 'Reclining Seat', 'desc' => 'Comfortable reclining seat.', 'price' => 2580.00, 'has_bed' => 0, 'sort' => 1],
        ['name' => 'Economy Bed Bunk', 'desc' => 'Air-conditioned bunk bed.', 'price' => 2580.00, 'has_bed' => 1, 'sort' => 2],
        ['name' => 'Tourist Bed Bunk', 'desc' => 'Spacious tourist class bed.', 'price' => 3200.00, 'has_bed' => 1, 'sort' => 3],
        ['name' => 'Cabin', 'desc' => 'Shared cabin.', 'price' => 3820.00, 'has_bed' => 1, 'sort' => 4],
        ['name' => 'VIP Room (2-3 pax)', 'desc' => 'Exclusive private VIP room.', 'price' => 11500.00, 'has_bed' => 1, 'sort' => 5],
    ]],
    ['origin' => 'Cebu', 'destination' => 'Surigao', 'schedules' => [
        ['service_name' => 'MV Starlite Stella Maris', 'vehicle_name' => 'MV Starlite Stella Maris', 'plate_no' => 'SSM-401', 'dep_time' => '20:00:00', 'duration' => 480, 'price' => 1550.00],
    ], 'accommodations' => [
        ['name' => 'Reclining Seat', 'desc' => 'Comfortable reclining seat.', 'price' => 1550.00, 'has_bed' => 0, 'sort' => 1],
        ['name' => 'Economy Bed Bunk', 'desc' => 'Air-conditioned bunk bed.', 'price' => 1650.00, 'has_bed' => 1, 'sort' => 2],
        ['name' => 'Tourist Bed Bunk', 'desc' => 'Spacious tourist class bed.', 'price' => 1960.00, 'has_bed' => 1, 'sort' => 3],
        ['name' => 'Cabin', 'desc' => 'Shared 4-berth cabin.', 'price' => 2380.00, 'has_bed' => 1, 'sort' => 4],
        ['name' => 'VIP Room (2-3 pax)', 'desc' => 'VIP room with bath.', 'price' => 7700.00, 'has_bed' => 1, 'sort' => 5],
    ]],
    ['origin' => 'Surigao', 'destination' => 'Cebu', 'schedules' => [
        ['service_name' => 'MV Starlite Stella Maris', 'vehicle_name' => 'MV Starlite Stella Maris', 'plate_no' => 'SSM-401', 'dep_time' => '20:00:00', 'duration' => 480, 'price' => 1550.00],
    ], 'accommodations' => [
        ['name' => 'Reclining Seat', 'desc' => 'Comfortable reclining seat.', 'price' => 1550.00, 'has_bed' => 0, 'sort' => 1],
        ['name' => 'Economy Bed Bunk', 'desc' => 'Air-conditioned bunk bed.', 'price' => 1650.00, 'has_bed' => 1, 'sort' => 2],
        ['name' => 'Tourist Bed Bunk', 'desc' => 'Spacious tourist class bed.', 'price' => 1960.00, 'has_bed' => 1, 'sort' => 3],
        ['name' => 'Cabin', 'desc' => 'Shared 4-berth cabin.', 'price' => 2380.00, 'has_bed' => 1, 'sort' => 4],
        ['name' => 'VIP Room (2-3 pax)', 'desc' => 'VIP room with bath.', 'price' => 7700.00, 'has_bed' => 1, 'sort' => 5],
    ]],
    ['origin' => 'Cebu', 'destination' => 'Dapitan', 'schedules' => [
        ['service_name' => 'MV Starlite Salve Regina', 'vehicle_name' => 'MV Starlite Salve Regina', 'plate_no' => 'SSR-501', 'dep_time' => '21:00:00', 'duration' => 480, 'price' => 1130.00],
    ], 'accommodations' => [
        ['name' => 'Reclining Seat', 'desc' => 'Comfortable reclining seat.', 'price' => 1130.00, 'has_bed' => 0, 'sort' => 1],
        ['name' => 'Economy Bed Bunk', 'desc' => 'Air-conditioned bunk bed.', 'price' => 1440.00, 'has_bed' => 1, 'sort' => 2],
        ['name' => 'Tourist Bed Bunk', 'desc' => 'Spacious tourist class bed.', 'price' => 1860.00, 'has_bed' => 1, 'sort' => 3],
        ['name' => 'Cabin', 'desc' => 'Shared 4-berth cabin.', 'price' => 2270.00, 'has_bed' => 1, 'sort' => 4],
        ['name' => 'VIP Room (2-3 pax)', 'desc' => 'VIP room with bath.', 'price' => 7700.00, 'has_bed' => 1, 'sort' => 5],
    ]],
    ['origin' => 'Dapitan', 'destination' => 'Cebu', 'schedules' => [
        ['service_name' => 'MV Starlite Salve Regina', 'vehicle_name' => 'MV Starlite Salve Regina', 'plate_no' => 'SSR-501', 'dep_time' => '21:00:00', 'duration' => 480, 'price' => 1130.00],
    ], 'accommodations' => [
        ['name' => 'Reclining Seat', 'desc' => 'Comfortable reclining seat.', 'price' => 1130.00, 'has_bed' => 0, 'sort' => 1],
        ['name' => 'Economy Bed Bunk', 'desc' => 'Air-conditioned bunk bed.', 'price' => 1440.00, 'has_bed' => 1, 'sort' => 2],
        ['name' => 'Tourist Bed Bunk', 'desc' => 'Spacious tourist class bed.', 'price' => 1860.00, 'has_bed' => 1, 'sort' => 3],
        ['name' => 'Cabin', 'desc' => 'Shared 4-berth cabin.', 'price' => 2270.00, 'has_bed' => 1, 'sort' => 4],
        ['name' => 'VIP Room (2-3 pax)', 'desc' => 'VIP room with bath.', 'price' => 7700.00, 'has_bed' => 1, 'sort' => 5],
    ]],
    ['origin' => 'Roxas Mindoro', 'destination' => 'Caticlan', 'schedules' => [
        ['service_name' => 'MV Starlite Sprint 1', 'vehicle_name' => 'MV Starlite Sprint 1', 'plate_no' => 'SSS-601', 'dep_time' => '08:00:00', 'duration' => 240, 'price' => 750.00],
        ['service_name' => 'MV Starlite Pacific', 'vehicle_name' => 'MV Starlite Pacific', 'plate_no' => 'SSP-602', 'dep_time' => '14:00:00', 'duration' => 240, 'price' => 750.00],
    ], 'accommodations' => [
        ['name' => 'Reclining Seat', 'desc' => 'Comfortable reclining seat.', 'price' => 750.00, 'has_bed' => 0, 'sort' => 1],
        ['name' => 'Economy Bed Bunk', 'desc' => 'Air-conditioned bunk bed.', 'price' => 850.00, 'has_bed' => 1, 'sort' => 2],
        ['name' => 'Tourist Bed Bunk', 'desc' => 'Spacious tourist class bed.', 'price' => 1050.00, 'has_bed' => 1, 'sort' => 3],
    ]],
    ['origin' => 'Caticlan', 'destination' => 'Roxas Mindoro', 'schedules' => [
        ['service_name' => 'MV Starlite Sprint 1', 'vehicle_name' => 'MV Starlite Sprint 1', 'plate_no' => 'SSS-601', 'dep_time' => '08:00:00', 'duration' => 240, 'price' => 750.00],
        ['service_name' => 'MV Starlite Pacific', 'vehicle_name' => 'MV Starlite Pacific', 'plate_no' => 'SSP-602', 'dep_time' => '14:00:00', 'duration' => 240, 'price' => 750.00],
    ], 'accommodations' => [
        ['name' => 'Reclining Seat', 'desc' => 'Comfortable reclining seat.', 'price' => 750.00, 'has_bed' => 0, 'sort' => 1],
        ['name' => 'Economy Bed Bunk', 'desc' => 'Air-conditioned bunk bed.', 'price' => 850.00, 'has_bed' => 1, 'sort' => 2],
        ['name' => 'Tourist Bed Bunk', 'desc' => 'Spacious tourist class bed.', 'price' => 1050.00, 'has_bed' => 1, 'sort' => 3],
    ]],
];

$sqlFile = __DIR__ . '/starlite_sync.sql';
$f = fopen($sqlFile, 'w');

fwrite($f, "-- Starlite Schedule Sync to Railway Production\n");
fwrite($f, "-- Generated: {$now}\n");
fwrite($f, "-- WRITE-ONLY: Uses INSERT IGNORE to skip existing records\n\n");
fwrite($f, "SET autocommit = 0;\n");
fwrite($f, "SET foreign_key_checks = 0;\n\n");

// Step 1: Ensure operator
fwrite($f, "-- Ensure Starlite operator exists\n");
fwrite($f, "INSERT IGNORE INTO operators (name, mode, logo_path, is_active, created_at, updated_at)\n");
fwrite($f, "VALUES ('Starlite', 'ferry', 'operators/Starlite_Logo.png', 1, '{$now}', '{$now}');\n");
fwrite($f, "SET @op_id = (SELECT id FROM operators WHERE name = 'Starlite' LIMIT 1);\n\n");

// Step 2: Ensure vehicles
fwrite($f, "-- Ensure vehicles exist\n");
$vehiclesDone = [];
foreach ($routesData as $rd) {
    foreach ($rd['schedules'] as $s) {
        if (isset($vehiclesDone[$s['plate_no']])) continue;
        $vehiclesDone[$s['plate_no']] = true;
        $vn = addslashes($s['vehicle_name']);
        $pn = addslashes($s['plate_no']);
        fwrite($f, "INSERT IGNORE INTO vehicles (vehicle_id, name, type, operator, operator_id, is_active, created_at, updated_at)\n");
        fwrite($f, "VALUES ('{$pn}', '{$vn}', 'ferry', 'Starlite', @op_id, 1, '{$now}', '{$now}');\n");
    }
}
fwrite($f, "\n");

// Step 3: Ensure routes
fwrite($f, "-- Ensure ferry routes exist\n");
$routesDone = [];
foreach ($routesData as $rd) {
    $key = $rd['origin'] . '|' . $rd['destination'];
    if (isset($routesDone[$key])) continue;
    $routesDone[$key] = true;
    $origin = addslashes($rd['origin']);
    $dest = addslashes($rd['destination']);
    $firstPlate = addslashes($rd['schedules'][0]['plate_no']);
    fwrite($f, "INSERT IGNORE INTO ferry_routes (origin, destination, mode, vehicle_id, operator, operator_id, trip_type, is_active, created_at, updated_at)\n");
    fwrite($f, "SELECT '{$origin}', '{$dest}', 'ferry', (SELECT id FROM vehicles WHERE vehicle_id = '{$firstPlate}' LIMIT 1), 'Starlite', @op_id, 'local', 1, '{$now}', '{$now}'\n");
    fwrite($f, "FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM ferry_routes WHERE origin = '{$origin}' AND destination = '{$dest}' AND (operator = 'Starlite' OR operator_id = @op_id));\n");
}
fwrite($f, "\n");

// Step 4: Ensure transport classes
fwrite($f, "-- Ensure transport classes exist\n");
$tcDone = [];
foreach ($routesData as $rd) {
    foreach ($rd['accommodations'] as $acc) {
        $code = Str::slug($acc['name']);
        if (isset($tcDone[$code])) continue;
        $tcDone[$code] = true;
        $name = addslashes($acc['name']);
        $desc = addslashes($acc['desc']);
        fwrite($f, "INSERT IGNORE INTO transport_classes (operator, operator_id, code, name, description, price, is_active, sort_order, created_at, updated_at)\n");
        fwrite($f, "VALUES ('Starlite', @op_id, '{$code}', '{$name}', '{$desc}', {$acc['price']}, 1, {$acc['sort']}, '{$now}', '{$now}');\n");
    }
}
fwrite($f, "\n");

// Step 5: Generate schedule INSERTs — bulk INSERT IGNORE
fwrite($f, "-- =============================================\n");
fwrite($f, "-- SCHEDULES: Bulk INSERT IGNORE (skip existing)\n");
fwrite($f, "-- =============================================\n\n");

$totalRows = 0;
$batchValues = [];
$batchAccValues = [];
$batchPivotValues = [];
$batchSize = 300;

// We'll use a temp marker column approach: insert schedules, then use LAST_INSERT_ID range
// Better approach: use INSERT IGNORE and then separately insert accommodations for new rows

// For accommodations/pivot, we need the schedule IDs. We'll use a two-pass approach:
// 1. Insert all schedules with INSERT IGNORE
// 2. Then insert accommodations using a subquery join

foreach ($routesData as $rd) {
    $origin = addslashes($rd['origin']);
    $dest = addslashes($rd['destination']);

    foreach ($rd['schedules'] as $sData) {
        $sn = addslashes($sData['service_name']);
        $vn = addslashes($sData['vehicle_name']);
        $pn = addslashes($sData['plate_no']);
        $seatCols = json_encode(['A', 'B', 'C', 'D', 'E', 'F']);

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $depTime = $date->format('Y-m-d') . ' ' . $sData['dep_time'];
            $arrTime = Carbon::parse($depTime)->addMinutes($sData['duration'])->format('Y-m-d H:i:s');

            $batchValues[] = "((SELECT id FROM ferry_routes WHERE origin = '{$origin}' AND destination = '{$dest}' AND (operator = 'Starlite' OR operator_id = @op_id) LIMIT 1), "
                . "'{$sn}', '{$vn}', '{$pn}', '{$depTime}', '{$arrTime}', {$sData['duration']}, {$sData['price']}, "
                . "'Available', 15, '{$seatCols}', 1, '{$now}', '{$now}')";

            $totalRows++;

            if (count($batchValues) >= $batchSize) {
                fwrite($f, "INSERT IGNORE INTO schedules (ferry_route_id, service_name, vehicle_name, plate_no, departure_time, arrival_time, duration_minutes, price, availability_label, seat_rows, seat_columns, is_active, created_at, updated_at)\nVALUES\n");
                fwrite($f, implode(",\n", $batchValues) . ";\n\n");
                $batchValues = [];
            }
        }
    }
}

// Flush remaining schedules
if (!empty($batchValues)) {
    fwrite($f, "INSERT IGNORE INTO schedules (ferry_route_id, service_name, vehicle_name, plate_no, departure_time, arrival_time, duration_minutes, price, availability_label, seat_rows, seat_columns, is_active, created_at, updated_at)\nVALUES\n");
    fwrite($f, implode(",\n", $batchValues) . ";\n\n");
}

fwrite($f, "COMMIT;\n\n");

// Step 6: Insert accommodations for schedules that don't have them yet
fwrite($f, "-- =============================================\n");
fwrite($f, "-- ACCOMMODATIONS: Insert for schedules missing them\n");
fwrite($f, "-- =============================================\n\n");
fwrite($f, "SET autocommit = 0;\n\n");

foreach ($routesData as $rd) {
    $origin = addslashes($rd['origin']);
    $dest = addslashes($rd['destination']);

    foreach ($rd['accommodations'] as $acc) {
        $accName = addslashes($acc['name']);
        $accDesc = addslashes($acc['desc']);
        $tcCode = Str::slug($acc['name']);

        // Insert schedule_accommodations for Starlite schedules that don't have this accommodation yet
        fwrite($f, "INSERT INTO schedule_accommodations (schedule_id, name, description, price, tickets_available, has_bed, is_active, sort_order, created_at, updated_at)\n");
        fwrite($f, "SELECT s.id, '{$accName}', '{$accDesc}', {$acc['price']}, 50, {$acc['has_bed']}, 1, {$acc['sort']}, '{$now}', '{$now}'\n");
        fwrite($f, "FROM schedules s\n");
        fwrite($f, "JOIN ferry_routes fr ON s.ferry_route_id = fr.id\n");
        fwrite($f, "WHERE fr.origin = '{$origin}' AND fr.destination = '{$dest}'\n");
        fwrite($f, "  AND (fr.operator = 'Starlite' OR fr.operator_id = @op_id)\n");
        fwrite($f, "  AND s.is_active = 1\n");
        fwrite($f, "  AND NOT EXISTS (SELECT 1 FROM schedule_accommodations sa WHERE sa.schedule_id = s.id AND sa.name = '{$accName}');\n\n");

        // Insert schedule_transport_class pivot
        fwrite($f, "INSERT INTO schedule_transport_class (schedule_id, transport_class_id, additional_price, tickets_available, description, has_bed, is_active, created_at, updated_at)\n");
        fwrite($f, "SELECT s.id, (SELECT id FROM transport_classes WHERE operator = 'Starlite' AND code = '{$tcCode}' LIMIT 1), {$acc['price']}, 50, '{$accDesc}', {$acc['has_bed']}, 1, '{$now}', '{$now}'\n");
        fwrite($f, "FROM schedules s\n");
        fwrite($f, "JOIN ferry_routes fr ON s.ferry_route_id = fr.id\n");
        fwrite($f, "WHERE fr.origin = '{$origin}' AND fr.destination = '{$dest}'\n");
        fwrite($f, "  AND (fr.operator = 'Starlite' OR fr.operator_id = @op_id)\n");
        fwrite($f, "  AND s.is_active = 1\n");
        fwrite($f, "  AND NOT EXISTS (SELECT 1 FROM schedule_transport_class stc WHERE stc.schedule_id = s.id AND stc.transport_class_id = (SELECT id FROM transport_classes WHERE operator = 'Starlite' AND code = '{$tcCode}' LIMIT 1));\n\n");
    }
}

fwrite($f, "COMMIT;\n");
fwrite($f, "SET foreign_key_checks = 1;\n\n");

// Final summary query
fwrite($f, "-- Summary\n");
fwrite($f, "SELECT 'TOTAL ACTIVE' as label, COUNT(*) as cnt FROM schedules WHERE is_active = 1\n");
fwrite($f, "UNION ALL\n");
fwrite($f, "SELECT CONCAT(fr.origin, ' -> ', fr.destination), COUNT(*)\n");
fwrite($f, "FROM schedules s JOIN ferry_routes fr ON s.ferry_route_id = fr.id\n");
fwrite($f, "WHERE fr.operator = 'Starlite' AND s.is_active = 1\n");
fwrite($f, "GROUP BY fr.origin, fr.destination;\n");

fclose($f);

echo "=== SQL file generated ===" . PHP_EOL;
echo "File: {$sqlFile}" . PHP_EOL;
echo "Total schedule rows to insert: {$totalRows}" . PHP_EOL;
echo "File size: " . round(filesize($sqlFile) / 1024, 1) . " KB" . PHP_EOL;
echo PHP_EOL;
echo "Run this to execute:" . PHP_EOL;
echo "mysql -h kodama.proxy.rlwy.net -u root -pCvPVaydCTsLSQigiGlbOaEYYhcqiYVsk --port 34553 --protocol=TCP railway < scripts/starlite_sync.sql" . PHP_EOL;

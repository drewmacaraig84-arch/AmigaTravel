<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

$file = 'C:/Users/macar/Downloads/VESSEL ROUTE.xlsx';
$spreadsheet = IOFactory::load($file);
$sheet = $spreadsheet->getActiveSheet();
$highestRow = $sheet->getHighestRow();

$startDate = Carbon::parse('2026-09-21');
$endDate = Carbon::parse('2026-12-31');

echo "=== CHECKING ALL ROWS WHEN ONLY (ROXAS-ODIONGAN + LCT) ARE EXCLUDED ===" . PHP_EOL;

for ($row = 4; $row <= $highestRow; $row++) {
    $vessel = trim((string)$sheet->getCell('B' . $row)->getValue());
    $routeCol = trim((string)$sheet->getCell('F' . $row)->getValue());
    $daysCol = trim((string)$sheet->getCell('G' . $row)->getValue());
    $timeCol = trim((string)$sheet->getCell('H' . $row)->getValue());

    if (empty($routeCol) || empty($timeCol)) continue;

    $isLct = stripos($routeCol, '(LCT)') !== false;
    $isRoxasOdiongan = (stripos($routeCol, 'ROXAS') !== false && stripos($routeCol, 'ODIONGAN') !== false && stripos($routeCol, 'BATANGAS') === false);

    $status = 'KEEP';
    if ($isLct) $status = 'EXCLUDE (LCT)';
    if ($isRoxasOdiongan) $status = 'EXCLUDE (Roxas-Odiongan)';

    echo sprintf("Row %-2d | %-25s | Route: %-35s | Days: %-20s | Time: %s\n",
        $row, $status, $routeCol, $daysCol, $timeCol
    );
}

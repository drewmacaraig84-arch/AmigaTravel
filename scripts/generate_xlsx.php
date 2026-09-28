<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$csvPath = __DIR__ . '/../2go_schedules/2GO_Manila_CagayanDeOro.csv';
$xlsxPath = __DIR__ . '/../2go_schedules/2GO_Manila_CagayanDeOro.xlsx';

if (!file_exists($csvPath)) {
    die("CSV not found\n");
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$handle = fopen($csvPath, 'r');
$rowNum = 1;
while (($data = fgetcsv($handle)) !== false) {
    $colNum = 1;
    foreach ($data as $cell) {
        $sheet->setCellValueExplicitByColumnAndRow($colNum, $rowNum, $cell, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $colNum++;
    }
    $rowNum++;
}
fclose($handle);

$writer = new Xlsx($spreadsheet);
$writer->save($xlsxPath);

echo "Successfully created XLSX: {$xlsxPath} with " . ($rowNum - 1) . " rows.\n";

<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$file = 'C:/Users/macar/Downloads/VESSEL ROUTE.xlsx';
$spreadsheet = IOFactory::load($file);
$sheet = $spreadsheet->getActiveSheet();
$highestRow = $sheet->getHighestRow();

echo "=== SCANNING FOR 'LCT' IN VESSEL ROUTE.xlsx ===" . PHP_EOL;

for ($row = 1; $row <= $highestRow; $row++) {
    $line = '';
    for ($col = 'A'; $col <= 'J'; $col++) {
        $val = trim((string)$sheet->getCell($col . $row)->getValue());
        if ($val !== '') {
            $line .= " [Col {$col}: {$val}]";
        }
    }
    if (stripos($line, 'LCT') !== false) {
        echo "Row {$row}: {$line}" . PHP_EOL;
    }
}

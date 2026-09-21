<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$file = 'C:/Users/macar/Downloads/VESSEL ROUTE.xlsx';
$spreadsheet = IOFactory::load($file);
$sheet = $spreadsheet->getActiveSheet();

echo "=== ROXAS MINDORO <-> ODIONGAN IN SPREADSHEET ===" . PHP_EOL;

foreach ([14, 15] as $row) {
    echo "--- Row {$row} ---" . PHP_EOL;
    for ($col = 'A'; $col <= 'J'; $col++) {
        $val = trim((string)$sheet->getCell($col . $row)->getValue());
        $color = $sheet->getCell($col . $row)->getStyle()->getFont()->getColor()->getARGB();
        if ($val !== '') {
            echo "  Col {$col}: '{$val}' (Font color: {$color})" . PHP_EOL;
        }
    }
}

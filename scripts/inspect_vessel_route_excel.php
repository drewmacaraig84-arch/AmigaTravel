<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$file = 'C:/Users/macar/Downloads/VESSEL ROUTE.xlsx';

$spreadsheet = IOFactory::load($file);
$sheet = $spreadsheet->getActiveSheet();

$highestRow = $sheet->getHighestRow();

echo "=== CHECKING EXACT COLORS IN 'VESSEL ROUTE.xlsx' ===" . PHP_EOL . PHP_EOL;

for ($row = 1; $row <= $highestRow; $row++) {
    $routeCol = trim((string)$sheet->getCell('F' . $row)->getValue());
    $daysCol = trim((string)$sheet->getCell('G' . $row)->getValue());
    $timeCol = trim((string)$sheet->getCell('H' . $row)->getValue());
    $vesselCol = trim((string)$sheet->getCell('B' . $row)->getValue());

    if (empty($routeCol) && empty($timeCol)) {
        continue;
    }

    $isRed = false;
    $redCols = [];

    foreach (['B', 'C', 'F', 'G', 'H', 'I', 'J'] as $col) {
        $cell = $sheet->getCell($col . $row);
        $fontColor = $cell->getStyle()->getFont()->getColor()->getARGB();
        $fillColor = $cell->getStyle()->getFill()->getStartColor()->getARGB();

        // Pure red is FFFF0000, dark red is FFC00000 or similar
        // Note: FF000000 is BLACK!
        if ($fontColor && $fontColor !== 'FF000000') {
            // Check if high red and low green/blue
            if (strlen($fontColor) === 8) {
                $r = hexdec(substr($fontColor, 2, 2));
                $g = hexdec(substr($fontColor, 4, 2));
                $b = hexdec(substr($fontColor, 6, 2));
                if ($r > 150 && $g < 80 && $b < 80) {
                    $isRed = true;
                    $redCols[] = "Font {$col}: {$fontColor}";
                }
            }
        }

        if ($fillColor && !in_array($fillColor, ['FFFFFFFF', '00000000', 'FF000000'])) {
            if (strlen($fillColor) === 8) {
                $r = hexdec(substr($fillColor, 2, 2));
                $g = hexdec(substr($fillColor, 4, 2));
                $b = hexdec(substr($fillColor, 6, 2));
                if ($r > 180 && $g < 100 && $b < 100) {
                    $isRed = true;
                    $redCols[] = "Fill {$col}: {$fillColor}";
                }
            }
        }
    }

    $status = $isRed ? "❌ RED (EXCLUDED)" : "✅ INCLUDED (NOT RED)";
    $detail = $isRed ? " (" . implode(', ', $redCols) . ")" : "";
    
    echo sprintf("Row %-2d | %-20s | Route: %-35s | Days: %-22s | Time: %s%s\n", 
        $row, 
        $status, 
        $routeCol, 
        $daysCol, 
        $timeCol,
        $detail
    );
}

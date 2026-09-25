<?php
require __DIR__ . '/../vendor/autoload.php';
use Carbon\Carbon;

$rawText = file_get_contents('C:/Users/macar/.gemini/antigravity-ide/brain/247c9bea-5446-4ad6-b85e-db993a81bf7a/scratch/raw_three_schedules.txt');

// Step 1: Normalize newlines where rows were glued together with "REGferry"
// Fix "Rate Codeferry," -> "Rate Code\nferry,"
$normalized = preg_replace('/Rate Codeferry,/i', "Rate Code\nferry,", $rawText);
// Fix "REGferry," -> "REG\nferry,"
$normalized = preg_replace('/REGferry,/i', "REG\nferry,", $normalized);

// Split lines
$lines = explode("\n", trim($normalized));

$routesData = [
    'cebu' => [],
    'bacolod' => [],
    'butuan' => [],
];

$header = null;

foreach ($lines as $line) {
    $line = trim($line);
    if (empty($line)) continue;

    if (str_starts_with($line, 'Mode,Operator')) {
        $header = str_getcsv($line);
        continue;
    }

    $cols = str_getcsv($line);
    if (count($cols) < 10) continue;

    $row = array_combine(array_slice($header, 0, count($cols)), $cols);

    // Identify which route this is
    $dest = strtolower($row['Destination'] ?? '');
    if (str_contains($dest, 'cebu')) {
        $routesData['cebu'][] = $row;
    } elseif (str_contains($dest, 'bacolod')) {
        $routesData['bacolod'][] = $row;
    } elseif (str_contains($dest, 'butuan') || str_contains($dest, 'nasipit')) {
        $routesData['butuan'][] = $row;
    }
}

echo "Parsed row counts:\n";
echo "  Cebu: " . count($routesData['cebu']) . "\n";
echo "  Bacolod: " . count($routesData['bacolod']) . "\n";
echo "  Butuan: " . count($routesData['butuan']) . "\n";

function formatRow(array $row): array
{
    // 1. Clean Departure Date
    $rawDepDate = trim($row['Departure Date']);
    $dParts = explode('/', $rawDepDate);
    $m = str_pad($dParts[0], 2, '0', STR_PAD_LEFT);
    $d = str_pad($dParts[1], 2, '0', STR_PAD_LEFT);
    $y = $dParts[2];
    $cleanDepDate = "$d/$m/$y";

    // 2. Clean Departure Time
    $cleanDepTime = preg_replace('/^ETD\s*:\s*/i', '', trim($row['Departure Time']));
    $cleanDepTime = date('h:i A', strtotime($cleanDepTime));

    // 3. Clean Arrival Time & Extract Arrival Date
    $rawEta = trim($row['Arrival Time']);
    $rawEta = str_ireplace('@1O:', '@10:', $rawEta);
    $cleanEta = preg_replace('/^ETA\s*:\s*/i', '', $rawEta);

    $arrDate = null;
    $arrTime = null;

    if (preg_match('/([A-Za-z]+)\.?\s*([0-9]{1,2})\s*@?\s*([0-9]{1,2}(?::[0-9]{2})?\s*(?:AM|PM)?)/i', $cleanEta, $mMatches)) {
        $monthName = $mMatches[1];
        $day = str_pad($mMatches[2], 2, '0', STR_PAD_LEFT);
        $timePart = trim($mMatches[3]);
        $monthNum = str_pad(date('n', strtotime("$monthName 1 2000")), 2, '0', STR_PAD_LEFT);
        $year = $y;
        if (intval($monthNum) < intval($m)) {
            $year = strval(intval($year) + 1);
        }
        $arrDate = "$day/$monthNum/$year";
        $arrTime = date('h:i A', strtotime($timePart));
    } else {
        $arrDate = $cleanDepDate;
        $arrTime = date('h:i A', strtotime($cleanEta));
    }

    // 4. Failsafe: Ensure Arrival DateTime is strictly after Departure DateTime
    $depDt = Carbon::createFromFormat('d/m/Y h:i A', "$cleanDepDate $cleanDepTime");
    $arrDt = Carbon::createFromFormat('d/m/Y h:i A', "$arrDate $arrTime");
    if ($arrDt->lessThan($depDt)) {
        while ($arrDt->lessThan($depDt)) {
            $arrDt->addDay();
        }
        $arrDate = $arrDt->format('d/m/Y');
    }

    // 5. Clean Rates
    $rawRate = preg_replace('/[^\d.]/', '', str_replace(',', '', $row['Rate']));
    $rawAddPrice = preg_replace('/[^\d.]/', '', str_replace(',', '', $row['Additional Price']));

    $rateVal = floatval($rawRate);
    $addVal = floatval($rawAddPrice);

    // If Rate is 0 but Additional Price has the fare, promote Additional Price to Rate
    if ($rateVal <= 0 && $addVal > 0) {
        $rateVal = $addVal;
        $addVal = 0.0;
    }

    $destClean = trim($row['Destination']);
    if (str_contains(strtolower($destClean), 'butuan')) {
        $destClean = 'Butuan (nasipit)';
    }

    return [
        'Mode' => 'ferry',
        'Operator' => '2GO',
        'Vehicle Tail No' => trim($row['Vehicle Tail No']),
        'Plate No' => '',
        'Origin' => trim($row['Origin']),
        'Destination' => $destClean,
        'Departure Date' => $cleanDepDate,
        'Departure Time' => $cleanDepTime,
        'Arrival Date' => $arrDate,
        'Arrival Time' => $arrTime,
        'Transport Class' => trim($row['Transport Class']),
        'Rate' => number_format($rateVal, 2, '.', ''),
        'Additional Price' => $addVal > 0 ? number_format($addVal, 2, '.', '') : '0.00',
        'Rate Tier' => 'regular',
        'Tickets Available' => intval($row['Tickets Available'] ?? 50) ?: 50,
        'Has Bed' => strtolower($row['Has Bed'] ?? 'yes') === 'yes' ? 'yes' : 'no',
        'Rate Code' => 'REG',
    ];
}

$outputHeaders = [
    'Mode', 'Operator', 'Vehicle Tail No', 'Plate No', 'Origin', 'Destination',
    'Departure Date', 'Departure Time', 'Arrival Date', 'Arrival Time',
    'Transport Class', 'Rate', 'Additional Price', 'Rate Tier',
    'Tickets Available', 'Has Bed', 'Rate Code'
];

@mkdir('c:/laragon/www/AmigaTravel/2go_schedules', 0777, true);
@mkdir('c:/laragon/www/AmigaTravel/public/downloads/schedules', 0777, true);

$allRowsCombined = [];

foreach ($routesData as $key => $rows) {
    $formattedRows = array_map('formatRow', $rows);
    $allRowsCombined = array_merge($allRowsCombined, $formattedRows);

    $filename = match ($key) {
        'cebu' => '2GO_Manila_Cebu_Formatted.csv',
        'bacolod' => '2GO_Manila_Bacolod_Formatted.csv',
        'butuan' => '2GO_Manila_Butuan_Formatted.csv',
    };

    $path1 = "c:/laragon/www/AmigaTravel/2go_schedules/{$filename}";
    $path2 = "c:/laragon/www/AmigaTravel/public/downloads/schedules/{$filename}";

    foreach ([$path1, $path2] as $target) {
        $fp = fopen($target, 'w');
        fputcsv($fp, $outputHeaders);
        foreach ($formattedRows as $r) {
            fputcsv($fp, array_values($r));
        }
        fclose($fp);
    }
    echo "✓ Written {$filename} (" . count($formattedRows) . " rows)\n";
}

// Combined file
$combo1 = "c:/laragon/www/AmigaTravel/2go_schedules/2GO_All_3_Routes_Combined.csv";
$combo2 = "c:/laragon/www/AmigaTravel/public/downloads/schedules/2GO_All_3_Routes_Combined.csv";

foreach ([$combo1, $combo2] as $target) {
    $fp = fopen($target, 'w');
    fputcsv($fp, $outputHeaders);
    foreach ($allRowsCombined as $r) {
        fputcsv($fp, array_values($r));
    }
    fclose($fp);
}
echo "✓ Written 2GO_All_3_Routes_Combined.csv (" . count($allRowsCombined) . " total rows)\n";

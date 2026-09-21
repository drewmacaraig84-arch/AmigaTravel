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
$totalDays = $startDate->diffInDays($endDate) + 1; // 102 days

echo "=== FILTERED OPERATIONAL STARLITE SCHEDULES (NO RED, NO LCT) ===" . PHP_EOL;
echo "Horizon: {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')} ({$totalDays} days)" . PHP_EOL . PHP_EOL;

$finalRules = [];
$totalDepartures = 0;

for ($row = 4; $row <= $highestRow; $row++) {
    $vessel = trim((string)$sheet->getCell('B' . $row)->getValue());
    $routeCol = trim((string)$sheet->getCell('F' . $row)->getValue());
    $daysCol = trim((string)$sheet->getCell('G' . $row)->getValue());
    $timeCol = trim((string)$sheet->getCell('H' . $row)->getValue());
    $durationCol = trim((string)$sheet->getCell('J' . $row)->getValue());

    if (empty($routeCol) || empty($timeCol)) {
        continue;
    }

    // Check if red
    $isRed = false;
    foreach (['F', 'G', 'H', 'I', 'J'] as $col) {
        $fontColor = $sheet->getCell($col . $row)->getStyle()->getFont()->getColor()->getARGB();
        if ($fontColor && strlen($fontColor) === 8) {
            $r = hexdec(substr($fontColor, 2, 2));
            $g = hexdec(substr($fontColor, 4, 2));
            $b = hexdec(substr($fontColor, 6, 2));
            if ($r > 150 && $g < 80 && $b < 80) {
                $isRed = true;
                break;
            }
        }
    }

    if ($isRed) {
        continue; // Skip red
    }

    // Check if whole row is LCT
    if (stripos($routeCol, '(LCT)') !== false) {
        continue; // Skip LCT row
    }

    // Clean origin / destination
    $cleanRoute = preg_replace('/\s*\((ROPAX|LCT|FASTCRAFT)\)\s*/i', '', $routeCol);
    $cleanRoute = preg_replace('/\s*-\s*/', ' - ', $cleanRoute);
    if (str_contains($cleanRoute, ' TO ')) {
        $parts = explode(' TO ', $cleanRoute);
    } elseif (str_contains($cleanRoute, ' to ')) {
        $parts = explode(' to ', $cleanRoute);
    } else {
        $parts = explode(' - ', $cleanRoute);
    }
    
    $origin = ucwords(strtolower(trim($parts[0])));
    $dest = ucwords(strtolower(trim($parts[1])));

    // Canonical port names matching Amiga system
    $normalize = function($name) {
        $name = str_ireplace('Romblom', 'Romblon', $name);
        $name = str_ireplace(['Roxas, Capiz', 'Roxas Capiz'], 'Roxas City, Capiz', $name);
        $name = str_ireplace('Sibuyan (Mag)', 'Sibuyan (Magdiwang)', $name);
        $name = str_ireplace('Buruangga', 'Buruanga', $name);
        return trim($name);
    };

    $origin = $normalize($origin);
    $dest = $normalize($dest);

    // Parse departure times, filtering out LCT
    $depTimes = [];
    $rawTime = strtoupper($timeCol);
    if (str_contains($rawTime, 'EVERY ODD')) {
        $depTimes = ['01:00', '03:00', '05:00', '07:00', '09:00', '11:00', '13:00', '15:00', '17:00', '19:00', '21:00', '23:00'];
    } else {
        $chunks = preg_split('/[\/,;]| AND /i', $rawTime);
        foreach ($chunks as $chunk) {
            $chunk = trim($chunk);
            if (empty($chunk)) continue;
            // Check if this specific chunk is LCT
            if (stripos($chunk, 'LCT') !== false) {
                continue;
            }
            $timeClean = preg_replace('/\([^)]+\)/', '', $chunk);
            $timeClean = trim($timeClean);

            if ($timeClean === '12NN' || $timeClean === '12 NOON') {
                $depTimes[] = '12:00';
            } elseif ($timeClean === '12MN' || $timeClean === '12 MIDNIGHT') {
                $depTimes[] = '00:00';
            } else {
                try {
                    $parsed = Carbon::parse($timeClean);
                    $depTimes[] = $parsed->format('H:i');
                } catch (\Throwable $e) {
                    if (preg_match('/^([0-9]{1,2})(?::([0-9]{2}))?\s*(AM|PM)$/i', $timeClean, $m)) {
                        $h = (int)$m[1];
                        $min = isset($m[2]) && $m[2] !== '' ? (int)$m[2] : 0;
                        if (strtoupper($m[3]) === 'PM' && $h < 12) $h += 12;
                        elseif (strtoupper($m[3]) === 'AM' && $h === 12) $h = 0;
                        $depTimes[] = sprintf('%02d:%02d', $h, $min);
                    }
                }
            }
        }
    }
    $depTimes = array_values(array_unique($depTimes));

    // Parse active days
    $activeDays = 'all';
    $rawDays = strtoupper($daysCol);
    if (!str_contains($rawDays, 'DAILY') && !str_contains($rawDays, 'MON-SUN') && !empty($rawDays)) {
        $dayMap = [
            'SUN' => 0, 'SUNDAY' => 0,
            'MON' => 1, 'MONDAY' => 1,
            'TUE' => 2, 'TUES' => 2, 'TUESDAY' => 2,
            'WED' => 3, 'WEDNESDAY' => 3,
            'THU' => 4, 'THUR' => 4, 'THURS' => 4, 'THURSDAY' => 4,
            'FRI' => 5, 'FRIDAY' => 5,
            'SAT' => 6, 'SATURDAY' => 6,
        ];
        $activeDays = [];
        foreach (preg_split('/[,]| AND /i', $rawDays) as $d) {
            $d = trim($d);
            if (isset($dayMap[$d])) {
                $activeDays[] = $dayMap[$d];
            }
        }
    }

    // Parse duration
    $durClean = strtoupper(trim($durationCol));
    $durationMinutes = 120;
    if (preg_match('/([0-9]+)\s*HOUR/i', $durClean, $m)) {
        $durationMinutes = ((int)$m[1]) * 60;
    }

    // Count in horizon
    $count = 0;
    $period = CarbonPeriod::create($startDate, $endDate);
    foreach ($period as $dt) {
        if ($activeDays === 'all' || in_array($dt->dayOfWeek, $activeDays, true)) {
            $count += count($depTimes);
        }
    }

    $finalRules[] = [
        'row' => $row,
        'origin' => $origin,
        'destination' => $dest,
        'vessel' => $vessel ?: 'Starlite Ferry',
        'days' => $daysCol,
        'active_days' => $activeDays,
        'dep_times' => $depTimes,
        'duration_minutes' => $durationMinutes,
        'total_departures' => $count,
    ];
    $totalDepartures += $count;
}

echo PHP_EOL . "=== FINAL VERIFIED OPERATIONAL RULES (NO RED, NO LCT) ===" . PHP_EOL;
printf("%-4s | %-25s -> %-25s | %-20s | %-32s | %s\n", "Row", "Origin", "Destination", "Days", "Departure Times", "Total");
echo str_repeat('-', 120) . PHP_EOL;
foreach ($finalRules as $r) {
    printf("%-4d | %-25s -> %-25s | %-20s | %-32s | %d\n",
        $r['row'], $r['origin'], $r['destination'], $r['days'], implode(', ', $r['dep_times']), $r['total_departures']
    );
}

echo str_repeat('-', 120) . PHP_EOL;
echo "TOTAL OPERATIONAL DEPARTURES (Sep 21 - Dec 31, 2026): {$totalDepartures}" . PHP_EOL;

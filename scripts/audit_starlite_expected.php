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

echo "=== DETAILED AUDIT: VESSEL ROUTE.xlsx (JULY 2026 TIMETABLE) ===" . PHP_EOL;
echo "Evaluation Period: {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')} ({$totalDays} days)" . PHP_EOL . PHP_EOL;

$includedRoutes = [];
$excludedRoutes = [];

for ($row = 4; $row <= $highestRow; $row++) {
    $vessel = trim((string)$sheet->getCell('B' . $row)->getValue());
    $routeCol = trim((string)$sheet->getCell('F' . $row)->getValue());
    $daysCol = trim((string)$sheet->getCell('G' . $row)->getValue());
    $timeCol = trim((string)$sheet->getCell('H' . $row)->getValue());
    $freqCol = trim((string)$sheet->getCell('I' . $row)->getValue());
    $durationCol = trim((string)$sheet->getCell('J' . $row)->getValue());

    if (empty($routeCol) || empty($timeCol)) {
        continue;
    }

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

    $item = [
        'row' => $row,
        'vessel' => $vessel,
        'route' => $routeCol,
        'days' => $daysCol,
        'time' => $timeCol,
        'freq' => $freqCol,
        'duration' => $durationCol,
    ];

    if ($isRed) {
        $excludedRoutes[] = $item;
    } else {
        $includedRoutes[] = $item;
    }
}

echo "--- ❌ RED ROUTES (EXCLUDED / NOT OPERATIONAL) ---" . PHP_EOL;
foreach ($excludedRoutes as $r) {
    echo "Row {$r['row']}: {$r['route']} | Days: {$r['days']} | Time: {$r['time']} | Freq: {$r['freq']}" . PHP_EOL;
}

echo PHP_EOL . "--- ✅ VALID ROUTES (BLACK / OPERATIONAL) ---" . PHP_EOL;
$totalExpectedDepartures = 0;

foreach ($includedRoutes as &$r) {
    // Calculate expected departures per week / over 102 days
    $depTimes = [];
    $rawTime = strtoupper($r['time']);
    if (str_contains($rawTime, 'EVERY ODD')) {
        $depTimes = ['01:00', '03:00', '05:00', '07:00', '09:00', '11:00', '13:00', '15:00', '17:00', '19:00', '21:00', '23:00'];
    } else {
        $clean = preg_replace('/\([^)]+\)/', '', $rawTime);
        $clean = str_replace([' AND ', ' and ', '/', ';', ':10:30PM'], [',', ',', ',', ',', ',10:30PM'], $clean);
        foreach (explode(',', $clean) as $c) {
            $c = trim($c);
            if (!empty($c)) {
                $depTimes[] = $c;
            }
        }
    }

    $activeDays = 'all';
    $rawDays = strtoupper($r['days']);
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
        foreach (explode(',', str_replace([' AND ', ' and ', '/'], [',', ',', ','], $rawDays)) as $d) {
            $d = trim($d);
            if (isset($dayMap[$d])) {
                $activeDays[] = $dayMap[$d];
            }
        }
    }

    // Count departures from 2026-09-21 to 2026-12-31
    $count = 0;
    $period = CarbonPeriod::create($startDate, $endDate);
    foreach ($period as $dt) {
        if ($activeDays === 'all' || in_array($dt->dayOfWeek, $activeDays, true)) {
            $count += count($depTimes);
        }
    }

    $r['dep_count'] = count($depTimes);
    $r['total_departures'] = $count;
    $totalExpectedDepartures += $count;

    echo sprintf("Row %-2d | Route: %-32s | Days: %-20s | Times/Day: %-2d | Total in Horizon: %d\n",
        $r['row'], $r['route'], $r['days'], count($depTimes), $count
    );
}

echo PHP_EOL . "TOTAL EXPECTED STARLITE DEPARTURES (Sep 21 - Dec 31, 2026): {$totalExpectedDepartures}" . PHP_EOL;

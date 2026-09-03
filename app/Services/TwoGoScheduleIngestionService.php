<?php

namespace App\Services;

use App\Models\FerryRoute;
use App\Models\Operator;
use App\Models\Schedule;
use App\Models\ScheduleAccommodation;
use App\Models\TransportClass;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;
use ZipArchive;

class TwoGoScheduleIngestionService
{
    /**
     * 2GO Fleet / Vessel Registry.
     */
    public const TWOGO_VESSELS = [
        ['code' => 'MLG', 'name' => 'MV 2GO Maligaya', 'capacity' => 1000],
        ['code' => 'MAS', 'name' => 'MV 2GO Masagana', 'capacity' => 1000],
        ['code' => 'MNG', 'name' => 'MV 2GO Masigla', 'capacity' => 1000],
        ['code' => 'SMA', 'name' => 'MV St. Michael The Archangel', 'capacity' => 900],
        ['code' => 'MSK', 'name' => 'MV St. Francis Xavier', 'capacity' => 850],
        ['code' => 'MSN', 'name' => 'MV St. Nicholas of Myra', 'capacity' => 850],
        ['code' => 'SFX', 'name' => 'MV St. Francis Xavier', 'capacity' => 850],
        ['code' => 'SIL/SAH', 'name' => 'MV St. Ignatius of Loyola', 'capacity' => 750],
        ['code' => 'SAH/SIL', 'name' => 'MV St. Augustine of Hippo', 'capacity' => 750],
        ['code' => 'SIL', 'name' => 'MV St. Ignatius of Loyola', 'capacity' => 750],
        ['code' => 'SAH', 'name' => 'MV St. Augustine of Hippo', 'capacity' => 750],
    ];

    public function __construct(
        protected LocationCodeResolver $locationResolver = new LocationCodeResolver(),
    ) {}

    /**
     * Ingest 2GO Travel schedules and rates from master timetable workbook.
     * STRICTLY scoped to 2GO operator.
     *
     * @param string|null $excelFilePath Path to 2GO Excel workbook
     * @param Carbon|null $startDate Horizon start date (e.g. Sept 3, 2026)
     * @param Carbon|null $endDate Horizon end date (e.g. Dec 31, 2026)
     * @return array Summary of sync results
     */
    public function ingest(
        ?string $excelFilePath = null,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): array {
        $excelFilePath = $excelFilePath ?? base_path('2go_schedules/2GO_TIMETABLE.xlsx');
        $startDate = $startDate ?? Carbon::create(2026, 9, 3);
        $endDate = $endDate ?? Carbon::create(2026, 12, 31);

        if (! file_exists($excelFilePath)) {
            return [
                'success' => false,
                'message' => "2GO timetable file not found at: {$excelFilePath}",
                'routes_count' => 0,
                'schedules_count' => 0,
                'vessels_count' => 0,
            ];
        }

        $operator = Operator::firstOrCreate(
            ['name' => '2GO'],
            [
                'mode' => 'ferry',
                'is_active' => true,
            ]
        );

        // 1. Ensure all 2GO vessels exist (checking vehicle_id to respect unique constraint)
        $vesselsCreated = 0;
        foreach (self::TWOGO_VESSELS as $vData) {
            $v = Vehicle::where('vehicle_id', $vData['code'])
                ->orWhere(function ($q) use ($vData) {
                    $q->where('operator', '2GO')
                      ->where('name', $vData['name']);
                })
                ->first();

            if (! $v) {
                Vehicle::create([
                    'type' => 'ferry',
                    'name' => $vData['name'],
                    'vehicle_id' => $vData['code'],
                    'operator' => '2GO',
                    'operator_id' => $operator->id,
                    'capacity' => $vData['capacity'],
                    'is_active' => true,
                ]);
                $vesselsCreated++;
            }
        }

        // 2. Parse Excel file
        $zip = new ZipArchive();
        if ($zip->open($excelFilePath) !== true) {
            return [
                'success' => false,
                'message' => 'Could not read XLSX zip archive.',
                'routes_count' => 0,
                'schedules_count' => 0,
            ];
        }

        $sharedStrings = $this->parseSharedStrings($zip);
        $sheetNames = $this->parseSheetNames($zip);

        // Parse rate matrices from route sheets
        $rateMatrix = $this->extractRateMatrix($zip, $sheetNames, $sharedStrings);

        // Parse recurring timetable legs from all route sheets + Schedule sheet
        $timetableLegs = $this->extractTimetableLegs($zip, $sharedStrings, $sheetNames);

        $zip->close();

        if (empty($timetableLegs)) {
            return [
                'success' => false,
                'message' => 'No schedule timetable legs found in Excel file.',
                'routes_count' => 0,
                'schedules_count' => 0,
            ];
        }

        // 3. Pre-cache all Transport Classes in memory
        $tcCache = $this->preCacheTransportClasses($operator, $rateMatrix);

        // 4. Pre-build dates in range
        $datesInRange = [];
        $curr = $startDate->copy();
        while ($curr->lte($endDate)) {
            $datesInRange[] = [
                'day' => strtoupper($curr->format('D')),
                'ymd' => $curr->format('Y-m-d'),
            ];
            $curr->addDay();
        }

        // 5. Pre-cache all existing 2GO Routes
        $groupedByRoute = [];
        foreach ($timetableLegs as $leg) {
            $orig = $this->locationResolver->resolve($leg['origin'], 'ferry');
            $dest = $this->locationResolver->resolve($leg['destination'], 'ferry');
            $key = "{$orig}|{$dest}";
            $groupedByRoute[$key][] = $leg;
        }

        $routesCache = [];
        $existingRoutes = FerryRoute::where('operator', '2GO')->get();
        foreach ($existingRoutes as $r) {
            $routesCache["{$r->origin}|{$r->destination}"] = $r;
        }

        foreach (array_keys($groupedByRoute) as $routeKey) {
            if (!isset($routesCache[$routeKey])) {
                [$origin, $destination] = explode('|', $routeKey);
                $route = FerryRoute::create([
                    'origin' => $origin,
                    'destination' => $destination,
                    'operator' => '2GO',
                    'operator_id' => $operator->id,
                    'mode' => 'ferry',
                    'is_active' => true,
                ]);
                $routesCache[$routeKey] = $route;
            }
        }

        // 6. Pre-cache existing schedules in DB
        $existingSchedulesMap = [];
        $existingSchedules = DB::table('schedules')
            ->join('ferry_routes', 'schedules.ferry_route_id', '=', 'ferry_routes.id')
            ->where('ferry_routes.operator', '2GO')
            ->select('schedules.id', 'schedules.ferry_route_id', 'schedules.departure_time')
            ->get();

        foreach ($existingSchedules as $s) {
            $existingSchedulesMap[$s->ferry_route_id . '|' . substr($s->departure_time, 0, 16)] = $s->id;
        }

        // 7. Compile all schedules to insert in memory
        $now = now()->toDateTimeString();
        $allSchedules = [];

        foreach ($groupedByRoute as $routeKey => $legs) {
            $route = $routesCache[$routeKey];
            [$origin, $destination] = explode('|', $routeKey);
            $routeRates = $this->resolveRouteRates($origin, $destination, $rateMatrix);

            foreach ($legs as $leg) {
                $recurringDays = $this->parseRecurringDays($leg['dep_day_time']);
                $depTimeStr = $this->parseTimeComponent($leg['dep_day_time']);
                $arrTimeStr = $this->parseTimeComponent($leg['arr_day_time']);
                $vesselCode = trim($leg['vessel']);

                if (empty($recurringDays) || empty($depTimeStr)) continue;

                foreach ($datesInRange as $d) {
                    if (in_array($d['day'], $recurringDays, true) || in_array('DAILY', $recurringDays, true)) {
                        $depDtStr = "{$d['ymd']} {$depTimeStr}:00";
                        $arrDtStr = $this->calculateArrivalDateTime($depDtStr, $leg['dep_day_time'], $leg['arr_day_time']);

                        $allSchedules[] = [
                            'ferry_route_id' => $route->id,
                            'vehicle_name' => !empty($vesselCode) ? $vesselCode : 'MV 2GO Vessel',
                            'departure_time' => $depDtStr,
                            'arrival_time' => $arrDtStr,
                            'price' => 0.0,
                            'is_active' => 1,
                            'created_at' => $now,
                            'updated_at' => $now,
                            '_rates' => $routeRates,
                        ];
                    }
                }
            }
        }

        // Filter out already existing schedules
        $newSchedules = [];
        foreach ($allSchedules as $s) {
            $key = $s['ferry_route_id'] . '|' . substr($s['departure_time'], 0, 16);
            if (!isset($existingSchedulesMap[$key])) {
                $newSchedules[] = $s;
            }
        }

        if (empty($newSchedules)) {
            return [
                'success' => true,
                'message' => "All 2GO schedules from {$startDate->format('M d, Y')} to {$endDate->format('M d, Y')} are already up to date.",
                'routes_count' => count($groupedByRoute),
                'schedules_count' => count($allSchedules),
                'vessels_count' => $vesselsCreated,
                'errors' => [],
            ];
        }

        $schedRows = [];
        foreach ($newSchedules as $ns) {
            $item = $ns;
            unset($item['_rates']);
            $schedRows[] = $item;
        }

        $maxIdBefore = DB::table('schedules')->max('id') ?? 0;

        DB::beginTransaction();
        try {
            // Bulk-insert schedules
            foreach (array_chunk($schedRows, 200) as $chunk) {
                DB::table('schedules')->insert($chunk);
            }

            // Retrieve inserted schedules by Primary Key range
            $inserted = DB::table('schedules')
                ->where('id', '>', $maxIdBefore)
                ->select('id', 'ferry_route_id', 'departure_time')
                ->get();

            $insertedMap = [];
            foreach ($inserted as $row) {
                $insertedMap[$row->ferry_route_id . '|' . substr($row->departure_time, 0, 16)] = $row->id;
            }

            $allPivot = [];
            $allAcc = [];

            foreach ($newSchedules as $ns) {
                $lookupKey = $ns['ferry_route_id'] . '|' . substr($ns['departure_time'], 0, 16);
                $schedId = $insertedMap[$lookupKey] ?? null;
                if (! $schedId) continue;

                foreach ($ns['_rates'] as $acc) {
                    $className = trim($acc['name']);
                    $fare = (float) $acc['price'];
                    $hasBed = (bool) ($acc['has_bed'] ?? false);
                    $tickets = (int) ($acc['tickets'] ?? 50);

                    $tc = $tcCache[$className] ?? null;
                    if ($tc) {
                        $allPivot[] = [
                            'schedule_id' => $schedId,
                            'transport_class_id' => $tc->id,
                            'additional_price' => $fare,
                            'tickets_available' => $tickets,
                            'rate_type' => 'regular',
                            'is_promo' => false,
                            'rate_code' => null,
                            'has_bed' => $hasBed,
                            'is_active' => true,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }

                    $allAcc[] = [
                        'schedule_id' => $schedId,
                        'name' => $className,
                        'rate_code' => null,
                        'price' => $fare,
                        'tickets_available' => $tickets,
                        'has_bed' => $hasBed,
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            foreach (array_chunk($allPivot, 500) as $pChunk) {
                DB::table('schedule_transport_class')->insert($pChunk);
            }
            foreach (array_chunk($allAcc, 500) as $aChunk) {
                DB::table('schedule_accommodations')->insert($aChunk);
            }

            DB::commit();

            // Bust all schedule and route caches so client website/app sees imported schedules immediately
            \App\Models\Schedule::bust();

            return [
                'success' => true,
                'message' => "Successfully ingested 2GO schedules from {$startDate->format('M d, Y')} to {$endDate->format('M d, Y')}.",
                'routes_count' => count($groupedByRoute),
                'schedules_count' => count($newSchedules),
                'vessels_count' => $vesselsCreated,
                'errors' => [],
            ];
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('2GO Ingestion Failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return [
                'success' => false,
                'message' => '2GO Ingestion Error: ' . $e->getMessage(),
                'routes_count' => 0,
                'schedules_count' => 0,
                'errors' => [$e->getMessage()],
            ];
        }
    }

    /**
     * Pre-cache all Transport Classes in memory.
     */
    protected function preCacheTransportClasses(Operator $operator, array $rateMatrix): array
    {
        $cache = [];

        $existing = TransportClass::where('operator', '2GO')->get();
        foreach ($existing as $tc) {
            $cache[$tc->name] = $tc;
        }

        foreach ($rateMatrix as $routeRates) {
            foreach ($routeRates as $className => $cData) {
                if (!isset($cache[$className])) {
                    $tc = TransportClass::firstOrCreate(
                        [
                            'name' => $className,
                            'operator' => '2GO',
                        ],
                        [
                            'operator_id' => $operator->id,
                            'type' => 'ferry',
                            'price' => (float) $cData['price'],
                            'capacity' => (int) ($cData['tickets'] ?? 50),
                            'has_bed' => (bool) ($cData['has_bed'] ?? false),
                            'is_active' => true,
                        ]
                    );
                    $cache[$className] = $tc;
                }
            }
        }

        $defaults = [
            'Stateroom' => 8440.0,
            'Suite Room' => 9364.0,
            'VIP Room (2-3 pax)' => 13517.0,
            'Business Class for 2' => 4112.0,
            'Business Class for 4' => 4112.0,
            'Business Class for 6' => 4043.0,
            'Business Class for 8' => 4112.0,
            'Business Class Solo' => 6013.0,
            'Cabin for 4' => 4564.0,
            'Tourist Bed Bunk' => 3822.0,
            'Megavalue' => 3622.0,
            'Supervalue' => 3422.0,
        ];

        foreach ($defaults as $name => $price) {
            if (!isset($cache[$name])) {
                $tc = TransportClass::firstOrCreate(
                    [
                        'name' => $name,
                        'operator' => '2GO',
                    ],
                    [
                        'operator_id' => $operator->id,
                        'type' => 'ferry',
                        'price' => $price,
                        'capacity' => $this->classDefaultCapacity($name),
                        'has_bed' => $this->classHasBed($name),
                        'is_active' => true,
                    ]
                );
                $cache[$name] = $tc;
            }
        }

        return $cache;
    }

    /**
     * Parse recurring timetable rows from both route sheets and the Schedule sheet.
     */
    protected function extractTimetableLegs(ZipArchive $zip, array $sharedStrings, array $sheetNames = []): array
    {
        $legs = [];

        // 1. Extract from all route-specific sheets first (contains full route timetable with stops like Davao -> Iloilo)
        foreach ($sheetNames as $sheetIndex => $name) {
            if (in_array($name, ['Schedule', 'Frequency', 'Sheet6'], true)) {
                continue;
            }

            $xmlContent = $zip->getFromName("xl/worksheets/sheet" . ($sheetIndex + 1) . ".xml");
            if (! $xmlContent) continue;

            $shXml = simplexml_load_string($xmlContent);
            foreach ($shXml->sheetData->row as $row) {
                $cells = [];
                foreach ($row->c as $c) {
                    $r = (string) $c['r'];
                    $col = preg_replace('/[0-9]/', '', $r);
                    $t = (string) $c['t'];
                    $v = isset($c->v) ? (string) $c->v : '';

                    if ($t === 's' && isset($sharedStrings[(int) $v])) {
                        $cellVal = $sharedStrings[(int) $v];
                    } elseif ($t === 'inlineStr' && isset($c->is->t)) {
                        $cellVal = (string) $c->is->t;
                    } else {
                        $cellVal = $v;
                    }
                    $cells[$col] = trim(str_replace("\xc2\xa0", ' ', $cellVal));
                }

                $orig = $cells['B'] ?? '';
                $dest = $cells['C'] ?? '';
                $dep = $cells['D'] ?? '';
                $arr = $cells['E'] ?? '';
                $ves = $cells['F'] ?? '';

                if (!empty($orig) && !empty($dest) && !empty($dep) 
                    && !in_array($orig, ['ORIGIN', 'RATES (PFA & PFE)']) 
                    && !in_array($dep, ['DEPARTURE', 'DAY & TIME'])) {
                    $normO = $this->locationResolver->resolve($orig, 'ferry');
                    $normD = $this->locationResolver->resolve($dest, 'ferry');
                    $key = "{$normO}|{$normD}|" . preg_replace('/\s+/', ' ', trim($dep));
                    $legs[$key] = [
                        'origin' => $normO,
                        'destination' => $normD,
                        'dep_day_time' => $dep,
                        'arr_day_time' => $arr,
                        'vessel' => $ves,
                    ];
                }
            }
        }

        // 2. Also extract from Schedule sheet (Sheet 1) to ensure no legs are missed
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheetXml) {
            $xml = simplexml_load_string($sheetXml);
            $leftOrigin = ''; $leftDest = '';
            $rightOrigin = ''; $rightDest = '';

            foreach ($xml->sheetData->row as $row) {
                $cells = [];
                foreach ($row->c as $c) {
                    $r = (string) $c['r'];
                    $col = preg_replace('/[0-9]/', '', $r);
                    $t = (string) $c['t'];
                    $v = isset($c->v) ? (string) $c->v : '';

                    if ($t === 's' && isset($sharedStrings[(int) $v])) {
                        $cellVal = $sharedStrings[(int) $v];
                    } elseif ($t === 'inlineStr' && isset($c->is->t)) {
                        $cellVal = (string) $c->is->t;
                    } else {
                        $cellVal = $v;
                    }
                    $cells[$col] = trim(str_replace("\xc2\xa0", ' ', $cellVal));
                }

                // Left block (Columns B, C, D, E, F)
                if (!empty($cells['B']) && !in_array($cells['B'], ['ORIGIN', ''])) $leftOrigin = $cells['B'];
                if (!empty($cells['C']) && !in_array($cells['C'], ['DESTINATION', ''])) $leftDest = $cells['C'];
                if (!empty($cells['D']) && !in_array($cells['D'], ['DEPARTURE', 'DAY & TIME', '']) && !empty($leftOrigin) && !empty($leftDest)) {
                    $normO = $this->locationResolver->resolve($leftOrigin, 'ferry');
                    $normD = $this->locationResolver->resolve($leftDest, 'ferry');
                    $key = "{$normO}|{$normD}|" . preg_replace('/\s+/', ' ', trim($cells['D']));
                    if (!isset($legs[$key])) {
                        $legs[$key] = [
                            'origin' => $normO,
                            'destination' => $normD,
                            'dep_day_time' => $cells['D'],
                            'arr_day_time' => $cells['E'] ?? '',
                            'vessel' => $cells['F'] ?? '',
                        ];
                    }
                }

                // Right block (Columns H, I, J, K, L)
                if (!empty($cells['H']) && !in_array($cells['H'], ['ORIGIN', ''])) $rightOrigin = $cells['H'];
                if (!empty($cells['I']) && !in_array($cells['I'], ['DESTINATION', ''])) $rightDest = $cells['I'];
                if (!empty($cells['J']) && !in_array($cells['J'], ['DEPARTURE', 'DAY & TIME', '']) && !empty($rightOrigin) && !empty($rightDest)) {
                    $normO = $this->locationResolver->resolve($rightOrigin, 'ferry');
                    $normD = $this->locationResolver->resolve($rightDest, 'ferry');
                    $key = "{$normO}|{$normD}|" . preg_replace('/\s+/', ' ', trim($cells['J']));
                    if (!isset($legs[$key])) {
                        $legs[$key] = [
                            'origin' => $normO,
                            'destination' => $normD,
                            'dep_day_time' => $cells['J'],
                            'arr_day_time' => $cells['K'] ?? '',
                            'vessel' => $cells['L'] ?? '',
                        ];
                    }
                }
            }
        }

        return array_values($legs);
    }

    /**
     * Calculate arrival datetime considering the arrival day of week from Excel.
     * E.g. Dep: WED - 11:00 AM, Arr: FRI - 6:00 PM -> +2 days offset.
     * E.g. Dep: SAT - 04:00 AM, Arr: TUE - 9:00 AM -> +3 days offset.
     * E.g. Dep: SAT - 04:00 AM, Arr: MON - 4:00 AM -> +2 days offset.
     */
    protected function calculateArrivalDateTime(string $depDtStr, string $depDayTimeStr, string $arrDayTimeStr): string
    {
        $arrTimeStr = $this->parseTimeComponent($arrDayTimeStr);
        if (empty($arrTimeStr)) {
            return Carbon::parse($depDtStr)->addHours(4)->toDateTimeString();
        }

        $depCarbon = Carbon::parse($depDtStr);
        $dayMap = [
            'MON' => 1, 'TUE' => 2, 'WED' => 3, 'THU' => 4, 'FRI' => 5, 'SAT' => 6, 'SUN' => 7
        ];

        $depDayNum = null;
        foreach ($dayMap as $k => $num) {
            if (stripos($depDayTimeStr, $k) !== false) {
                $depDayNum = $num;
                break;
            }
        }
        if ($depDayNum === null) {
            $depDayNum = (int) $depCarbon->dayOfWeekIso;
        }

        $arrDayNum = null;
        foreach ($dayMap as $k => $num) {
            if (stripos($arrDayTimeStr, $k) !== false) {
                $arrDayNum = $num;
                break;
            }
        }

        $daysOffset = 0;
        if ($arrDayNum !== null) {
            $daysOffset = ($arrDayNum - $depDayNum + 7) % 7;
        }

        $arrCarbon = Carbon::parse($depCarbon->format('Y-m-d') . " {$arrTimeStr}:00")->addDays($daysOffset);

        // If same day offset (0) but arrival time is earlier than departure time (e.g. 11:00 PM to 5:00 AM), add 1 day
        if ($daysOffset === 0 && $arrCarbon->lte($depCarbon)) {
            $arrCarbon->addDay();
        }

        return $arrCarbon->toDateTimeString();
    }

    /**
     * Extract full rate matrix from all route-specific sheets.
     */
    protected function extractRateMatrix(ZipArchive $zip, array $sheetNames, array $sharedStrings): array
    {
        $matrix = [];

        foreach ($sheetNames as $sheetIndex => $name) {
            if (in_array($name, ['Schedule', 'Frequency', 'Sheet6'], true)) {
                continue;
            }

            $xmlFile = "xl/worksheets/sheet" . ($sheetIndex + 1) . ".xml";
            $xmlContent = $zip->getFromName($xmlFile);
            if (! $xmlContent) continue;

            $shXml = simplexml_load_string($xmlContent);
            $grid = [];
            foreach ($shXml->sheetData->row as $row) {
                $rNum = (int) $row['r'];
                foreach ($row->c as $c) {
                    $r = (string) $c['r'];
                    $col = preg_replace('/[0-9]/', '', $r);
                    $t = (string) $c['t'];
                    $v = isset($c->v) ? (string) $c->v : '';

                    if ($t === 's' && isset($sharedStrings[(int) $v])) {
                        $val = $sharedStrings[(int) $v];
                    } elseif ($t === 'inlineStr' && isset($c->is->t)) {
                        $val = (string) $c->is->t;
                    } else {
                        $val = $v;
                    }
                    $grid[$rNum][$col] = trim(str_replace("\xc2\xa0", ' ', $val));
                }
            }

            // Parse routes and column rate mappings
            $currOrigin = ''; $currDest = '';
            $headerCols = [];

            foreach ($grid as $rNum => $row) {
                if (!empty($row['B']) && !in_array($row['B'], ['ORIGIN', 'RATES (PFA & PFE)'])) {
                    $currOrigin = $this->locationResolver->resolve($row['B'], 'ferry');
                }
                if (!empty($row['C']) && !in_array($row['C'], ['DESTINATION', ''])) {
                    $currDest = $this->locationResolver->resolve($row['C'], 'ferry');
                }

                $isHeaderRow = false;
                foreach ($row as $colKey => $val) {
                    if (in_array(strtoupper($val), ['STATE', 'MEGAB', 'TOURB', 'SUPER', 'SUITE', 'CAB4W', 'BCB2', 'BCB4', 'BCB6', 'BCB8', 'BCR2', 'VIP'], true)) {
                        $isHeaderRow = true;
                        break;
                    }
                }

                if ($isHeaderRow) {
                    $headerCols = [];
                    foreach ($row as $colKey => $val) {
                        if (!empty($val) && !in_array($colKey, ['A', 'B', 'C', 'D', 'E', 'F'])) {
                            $headerCols[$colKey] = $this->normalizeClassName($val);
                        }
                    }
                    continue;
                }

                if (!empty($currOrigin) && !empty($currDest) && !empty($headerCols)) {
                    $routeKey = "{$currOrigin}|{$currDest}";
                    foreach ($headerCols as $colKey => $className) {
                        if (!empty($row[$colKey]) && is_numeric(str_replace(',', '', $row[$colKey]))) {
                            $price = floatval(str_replace(',', '', $row[$colKey]));
                            if ($price > 0) {
                                $matrix[$routeKey][$className] = [
                                    'name' => $className,
                                    'price' => $price,
                                    'has_bed' => $this->classHasBed($className),
                                    'tickets' => $this->classDefaultCapacity($className),
                                ];
                            }
                        }
                    }
                }
            }
        }

        return $matrix;
    }

    /**
     * Resolve rates for a specific route, falling back to reverse or default 2GO matrix.
     */
    protected function resolveRouteRates(string $origin, string $destination, array $rateMatrix): array
    {
        $key = "{$origin}|{$destination}";
        $reverseKey = "{$destination}|{$origin}";

        if (!empty($rateMatrix[$key])) {
            return array_values($rateMatrix[$key]);
        }
        if (!empty($rateMatrix[$reverseKey])) {
            return array_values($rateMatrix[$reverseKey]);
        }

        // Standard Default 2GO Accommodations if route not in specific sheet
        return [
            ['name' => 'Stateroom', 'price' => 8440.00, 'has_bed' => true, 'tickets' => 10],
            ['name' => 'Business Class for 4', 'price' => 4112.66, 'has_bed' => true, 'tickets' => 40],
            ['name' => 'Tourist Bed Bunk', 'price' => 3822.68, 'has_bed' => true, 'tickets' => 80],
            ['name' => 'Megavalue', 'price' => 3622.68, 'has_bed' => true, 'tickets' => 100],
            ['name' => 'Supervalue', 'price' => 3422.69, 'has_bed' => false, 'tickets' => 120],
        ];
    }

    /**
     * Normalize abbreviated accommodation code to formal title.
     */
    protected function normalizeClassName(string $code): string
    {
        $c = strtoupper(trim(preg_replace('/\s*\(.*?\)/', '', $code)));

        return match ($c) {
            'STATE', 'STATEROOM' => 'Stateroom',
            'SUITE' => 'Suite Room',
            'VIP', 'VIP SUITE' => 'VIP Room (2-3 pax)',
            'BCR2', 'BCB2' => 'Business Class for 2',
            'BCB4' => 'Business Class for 4',
            'BCB6', 'BC6' => 'Business Class for 6',
            'BCB8' => 'Business Class for 8',
            'BCP1' => 'Business Class Solo',
            'CAB4', 'CAB4W', 'CABIN' => 'Cabin for 4',
            'TOUR', 'TOURB', 'TOURIST' => 'Tourist Bed Bunk',
            'MEGA', 'MEGAB', 'MEGAVALUE' => 'Megavalue',
            'SUPER', 'SUPERVALUE' => 'Supervalue',
            default => ucwords(strtolower($code)),
        };
    }

    protected function classHasBed(string $className): bool
    {
        return ! str_contains(strtolower($className), 'supervalue');
    }

    protected function classDefaultCapacity(string $className): int
    {
        $c = strtolower($className);
        if (str_contains($c, 'stateroom') || str_contains($c, 'suite') || str_contains($c, 'vip') || str_contains($c, 'for 2')) return 10;
        if (str_contains($c, 'for 4') || str_contains($c, 'for 6') || str_contains($c, 'for 8')) return 30;
        if (str_contains($c, 'tourist')) return 80;
        if (str_contains($c, 'megavalue')) return 100;
        return 120;
    }

    /**
     * Parse days of week from strings like "TUE - 12:30 PM", "DAILY - 9:00 PM", "SUN/MON/WED/FRI 9:00 PM".
     */
    protected function parseRecurringDays(string $str): array
    {
        $clean = strtoupper(trim($str));
        $days = [];

        if (str_contains($clean, 'DAILY')) {
            return ['DAILY'];
        }

        $allWeekdays = ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'];
        foreach ($allWeekdays as $d) {
            if (str_contains($clean, $d)) {
                $days[] = $d;
            }
        }

        return array_unique($days);
    }

    /**
     * Extract 24-hour time component (HH:MM) from timetable strings.
     */
    protected function parseTimeComponent(string $str): ?string
    {
        $clean = trim($str);

        if (preg_match('/(\d{1,2}):(\d{2})\s*(AM|PM)?/i', $clean, $matches)) {
            $hours = (int) $matches[1];
            $minutes = (int) $matches[2];
            $meridiem = strtoupper($matches[3] ?? '');

            if ($meridiem === 'PM' && $hours < 12) {
                $hours += 12;
            } elseif ($meridiem === 'AM' && $hours === 12) {
                $hours = 0;
            }

            return sprintf('%02d:%02d', $hours, $minutes);
        }

        return null;
    }

    protected function parseSharedStrings(ZipArchive $zip): array
    {
        $sharedStrings = [];
        $ssXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($ssXml) {
            $sXml = simplexml_load_string($ssXml);
            foreach ($sXml->si as $val) {
                if (isset($val->t)) {
                    $sharedStrings[] = (string) $val->t;
                } elseif (isset($val->r)) {
                    $text = '';
                    foreach ($val->r as $r) {
                        $text .= (string) $r->t;
                    }
                    $sharedStrings[] = $text;
                } else {
                    $sharedStrings[] = '';
                }
            }
        }
        return $sharedStrings;
    }

    protected function parseSheetNames(ZipArchive $zip): array
    {
        $sheetNames = [];
        $wbXml = simplexml_load_string($zip->getFromName('xl/workbook.xml'));
        foreach ($wbXml->sheets->sheet as $s) {
            $sheetNames[] = (string) $s['name'];
        }
        return $sheetNames;
    }
}

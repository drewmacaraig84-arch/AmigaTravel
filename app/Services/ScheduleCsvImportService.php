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

class ScheduleCsvImportService
{
    public function __construct(
        protected LocationCodeResolver $locationResolver = new LocationCodeResolver(),
        protected ?StarliteScheduleIngestionService $starliteService = null,
        protected ?TwoGoScheduleIngestionService $twoGoService = null,
    ) {
        $this->starliteService = $starliteService ?? new StarliteScheduleIngestionService($this->locationResolver);
        $this->twoGoService = $twoGoService ?? new TwoGoScheduleIngestionService($this->locationResolver);
    }

    /**
     * Import schedules from a CSV or XLSX file.
     *
     * @param string $filePath
     * @param string|null $forcedOperator Optional operator constraint
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return array Summary of import results
     */
    public function import(
        string $filePath,
        ?string $forcedOperator = null,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): array {
        $importedCount = 0;
        $skippedCount = 0;
        $errors = [];

        if (! file_exists($filePath) || ! is_readable($filePath)) {
            return [
                'imported' => 0,
                'skipped' => 0,
                'errors' => ['File not found or is not readable.'],
            ];
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        // Detect 2GO Timetable format
        if ($extension === 'xlsx') {
            try {
                $rawRows = $this->parseXlsxRows($filePath);
                $isTwoGoTimetable = false;
                foreach (array_slice($rawRows, 0, 8) as $r) {
                    $rowStr = strtoupper(implode(' ', (array) $r));
                    if (str_contains($rowStr, '2GO') || (str_contains($rowStr, 'ORIGIN') && str_contains($rowStr, 'DESTINATION') && str_contains($rowStr, 'VESSEL') && str_contains($rowStr, 'DAY & TIME'))) {
                        $isTwoGoTimetable = true;
                        break;
                    }
                }

                if ($isTwoGoTimetable || strtolower($forcedOperator ?? '') === '2go') {
                    $result = $this->twoGoService->ingest($filePath, $startDate, $endDate);
                    return [
                        'imported' => $result['schedules_count'] ?? 0,
                        'skipped' => 0,
                        'errors' => $result['success'] ? [] : [$result['message']],
                        'twogo_result' => $result,
                    ];
                }
            } catch (Throwable $e) {
                // Fall back to standard parser if error
            }
        }

        // Detect Starlite Timetable format
        if ($extension === 'xlsx') {
            try {
                $rawRows = $this->parseXlsxRows($filePath);
                $isStarliteTimetable = false;
                foreach (array_slice($rawRows, 0, 5) as $r) {
                    $rowStr = strtoupper(implode(' ', (array) $r));
                    if (str_contains($rowStr, 'STARLITE FERRIES') || (str_contains($rowStr, 'ROUTE') && str_contains($rowStr, 'DAYS') && str_contains($rowStr, 'DEPARTURE TIME'))) {
                        $isStarliteTimetable = true;
                        break;
                    }
                }

                if ($isStarliteTimetable || strtolower($forcedOperator ?? '') === 'starlite') {
                    $result = $this->starliteService->ingest($filePath, $startDate, $endDate);
                    return [
                        'imported' => $result['schedules_count'] ?? 0,
                        'skipped' => 0,
                        'errors' => $result['success'] ? [] : [$result['message']],
                        'starlite_result' => $result,
                    ];
                }
            } catch (Throwable $e) {
                // Fall back to standard parser if error
            }
        }

        try {
            if ($extension === 'xlsx') {
                $allRows = $this->parseXlsxRows($filePath);
            } else {
                $allRows = $this->parseCsvRows($filePath);
            }
        } catch (Throwable $e) {
            return [
                'imported' => 0,
                'skipped' => 0,
                'errors' => ['Failed to read spreadsheet file: ' . $e->getMessage()],
            ];
        }

        if (empty($allRows)) {
            return [
                'imported' => 0,
                'skipped' => 0,
                'errors' => ['File is empty or contains no data rows.'],
            ];
        }

        $rawHeader = array_shift($allRows);

        // Normalize header keys
        $headers = array_map(function ($h) {
            $normalized = strtolower(trim((string) $h));
            $normalized = str_replace(['.', '_', '-', ' '], '', $normalized);
            return $normalized;
        }, $rawHeader);

        $rowNumber = 1;

        foreach ($allRows as $row) {
            $rowNumber++;

            // Skip empty rows
            if (empty(array_filter($row, fn ($val) => trim((string) $val) !== ''))) {
                continue;
            }

            if (count($row) < count($headers)) {
                $row = array_pad($row, count($headers), '');
            }

            $rowData = array_combine(array_slice($headers, 0, count($row)), array_slice($row, 0, count($headers)));

            try {
                $result = DB::transaction(function () use ($rowData, $forcedOperator) {
                    return $this->processRow($rowData, $forcedOperator);
                });

                if ($result === 'imported') {
                    $importedCount++;
                } elseif ($result === 'skipped') {
                    $skippedCount++;
                }
            } catch (Throwable $e) {
                Log::error("Schedule Import Error on row {$rowNumber}", [
                    'error' => $e->getMessage(),
                    'row' => $rowData,
                ]);
                $errors[] = "Row {$rowNumber}: " . $e->getMessage();
            }
        }

        return [
            'imported' => $importedCount,
            'skipped' => $skippedCount,
            'errors' => $errors,
        ];
    }

    /**
     * Parse rows from CSV file.
     */
    protected function parseCsvRows(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        if (! $handle) {
            throw new \RuntimeException('Could not open CSV file.');
        }

        // Remove UTF-8 BOM if present
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = $row;
        }

        fclose($handle);

        return $rows;
    }

    /**
     * Parse rows from an .xlsx file using ZipArchive & SimpleXML natively.
     */
    protected function parseXlsxRows(string $filePath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new \RuntimeException('Unable to open XLSX file ZIP archive.');
        }

        $sharedStrings = [];
        if (($index = $zip->locateName('xl/sharedStrings.xml')) !== false) {
            $xmlStr = $zip->getFromIndex($index);
            $xml = @simplexml_load_string($xmlStr);
            if ($xml && isset($xml->si)) {
                foreach ($xml->si as $val) {
                    if (isset($val->t)) {
                        $sharedStrings[] = (string) $val->t;
                    } elseif (isset($val->r)) {
                        $text = '';
                        foreach ($val->r as $run) {
                            $text .= (string) $run->t;
                        }
                        $sharedStrings[] = $text;
                    } else {
                        $sharedStrings[] = '';
                    }
                }
            }
        }

        $sheetIndex = $zip->locateName('xl/worksheets/sheet1.xml');
        if ($sheetIndex === false) {
            $zip->close();
            throw new \RuntimeException('No worksheet XML found in XLSX file.');
        }

        $xmlStr = $zip->getFromIndex($sheetIndex);
        $xml = @simplexml_load_string($xmlStr);
        $zip->close();

        if (! $xml || ! isset($xml->sheetData)) {
            throw new \RuntimeException('Invalid worksheet XML structure.');
        }

        $allRows = [];
        foreach ($xml->sheetData->row as $rowNode) {
            $rowCells = [];
            foreach ($rowNode->c as $cellNode) {
                $ref = (string) $cellNode['r'];
                $colLetters = preg_replace('/[0-9]/', '', $ref);
                $colIndex = $this->columnLetterToIndex($colLetters);

                $val = (string) $cellNode->v;
                $type = (string) $cellNode['t'];

                if ($type === 's' && isset($sharedStrings[(int) $val])) {
                    $cellValue = $sharedStrings[(int) $val];
                } else {
                    $cellValue = $val;
                }

                $rowCells[$colIndex] = $cellValue;
            }

            if (! empty($rowCells)) {
                ksort($rowCells);
                $maxIndex = max(array_keys($rowCells));
                $denseRow = [];
                for ($i = 0; $i <= $maxIndex; $i++) {
                    $denseRow[] = $rowCells[$i] ?? '';
                }
                $allRows[] = $denseRow;
            }
        }

        return $allRows;
    }

    protected function columnLetterToIndex(string $letters): int
    {
        $letters = strtoupper($letters);
        $index = 0;
        for ($i = 0; $i < strlen($letters); $i++) {
            $index = $index * 26 + (ord($letters[$i]) - ord('A') + 1);
        }
        return $index - 1;
    }

    /**
     * Process a single CSV/XLSX row.
     *
     * @param array $row
     * @param string|null $forcedOperator
     * @return string 'imported' or 'skipped'
     */
    protected function processRow(array $row, ?string $forcedOperator = null): string
    {
        // Header mappings with extensive multi-operator alias matching
        $modeRaw = $this->getValue($row, ['mode', 'transport_mode', 'transportmode', 'type']);
        $operatorRaw = $this->getValue($row, ['operator', 'operator_name', 'airline', 'carrier', 'shipping_line', 'company']);
        $vehicleTailNo = $this->getValue($row, [
            'vehicletailno', 'vehicle', 'tailno', 'vehicleno', 'vehicle_name', 'vessel', 'vessel_name', 'vesselname',
            'flight', 'flightno', 'flight_no', 'flight_number', 'flightnumber', 'ship', 'craft', 'plane', 'aircraft'
        ]);
        $plateNo = $this->getValue($row, ['plateno', 'plate', 'plate_no', 'registration']);
        $origin = $this->getValue($row, [
            'origin', 'from', 'departure_port', 'departureport', 'departure_airport', 'departureairport',
            'dep_port', 'dep_airport', 'source', 'orig'
        ]);
        $destination = $this->getValue($row, [
            'destination', 'dest', 'to', 'arrival_port', 'arrivalport', 'arrival_airport', 'arrivalairport',
            'arr_port', 'arr_airport'
        ]);
        $depDateStr = $this->getValue($row, [
            'departuredate', 'depdate', 'departure_date', 'date', 'flight_date', 'flightdate',
            'sail_date', 'saildate', 'voyage_date', 'travel_date', 'traveldate'
        ]);
        $depTimeStr = $this->getValue($row, [
            'departuretime', 'deptime', 'departure_time', 'time', 'etd', 'departure', 'dep_time', 'flight_time'
        ]);
        $arrTimeStr = $this->getValue($row, [
            'arrivaltime', 'arrtime', 'arrival_time', 'eta', 'arrival', 'arr_time'
        ]);
        $returnDateStr = $this->getValue($row, ['returndate', 'retdate', 'return_date']);
        $transportClassStr = $this->getValue($row, [
            'transportclass', 'transport_class', 'class', 'accommodation', 'accommodation_class',
            'seat_class', 'seatclass', 'cabin', 'cabin_type', 'cabinclass', 'tier', 'service_class'
        ]);
        $rateRaw = $this->getValue($row, [
            'rate', 'price', 'fare', 'basefare', 'base_fare', 'ticket_price', 'ticketprice', 'cost', 'amount'
        ]);
        $additionalPriceRaw = $this->getValue($row, [
            'additionalprice', 'additional_price', 'classprice', 'class_price', 'extraprice', 'addonprice'
        ]);
        $rateTierRaw = $this->getValue($row, [
            'ratetier', 'rate_tier', 'ratetype', 'rate_type', 'tier', 'farepolicy', 'fare_policy', 'policy'
        ]);
        $ticketsAvailableRaw = $this->getValue($row, [
            'ticketsavailable', 'tickets_available', 'tickets', 'seats', 'capacity', 'inventory', 'qty', 'allotment'
        ]);
        $hasBedRaw = $this->getValue($row, [
            'hasbed', 'has_bed', 'bed', 'includesbed', 'includes_bed', 'berth', 'bunk'
        ]);
        $rateCodeStr = $this->getValue($row, ['ratecode', 'rate_code', 'code', 'fare_code']);

        if (blank($origin) || blank($destination) || blank($depDateStr) || blank($depTimeStr)) {
            throw new \InvalidArgumentException('Missing required fields (Origin, Destination, Departure Date, or Departure Time).');
        }

        // Determine Mode first (needed for code resolution)
        $mode = str_contains(strtolower((string) $modeRaw), 'air') ? 'airline' : 'ferry';

        // Resolve location codes (e.g. MNL => Manila, BTG => Batangas)
        $origin      = $this->locationResolver->resolve($origin, $mode);
        $destination = $this->locationResolver->resolve($destination, $mode);

        // Normalize Operator
        $operator = filled($forcedOperator) ? trim($forcedOperator) : $this->normalizeOperatorName($operatorRaw, $mode);
        
        // Auto-create or resolve Operator
        $operatorModel = Operator::firstOrCreate(
            ['name' => $operator],
            [
                'mode' => $mode,
                'is_active' => true,
            ]
        );

        $vehicleTailNo = filled($vehicleTailNo) ? trim($vehicleTailNo) : ($mode === 'airline' ? "{$operator} Aircraft" : "{$operator} Vessel");
        $transportClassStr = filled($transportClassStr) ? trim($transportClassStr) : ($mode === 'airline' ? 'Economy' : 'Standard');
        
        $rate = floatval(preg_replace('/[^0-9.]/', '', $rateRaw ?? '0'));
        $additionalPrice = filled($additionalPriceRaw) ? floatval(preg_replace('/[^0-9.]/', '', $additionalPriceRaw)) : 0.0;

        // Rate Tier / Policy Normalization
        $rateType = 'regular';
        if (filled($rateTierRaw)) {
            $cleanTier = strtolower(trim((string) $rateTierRaw));
            if (str_contains($cleanTier, 'super')) {
                $rateType = 'super_promotional';
            } elseif (str_contains($cleanTier, 'promo')) {
                $rateType = 'promotional';
            } else {
                $rateType = 'regular';
            }
        }
        $isPromo = in_array($rateType, ['promotional', 'super_promotional'], true);

        // Tickets available inventory
        $ticketsAvailable = 50;
        if (filled($ticketsAvailableRaw)) {
            $parsedTickets = intval(preg_replace('/[^0-9]/', '', (string) $ticketsAvailableRaw));
            if ($parsedTickets > 0) {
                $ticketsAvailable = $parsedTickets;
            }
        }

        // Has Bed / Berth
        $hasBed = false;
        if (filled($hasBedRaw)) {
            $cleanBed = strtolower(trim((string) $hasBedRaw));
            $hasBed = in_array($cleanBed, ['1', 'true', 'yes', 'y'], true);
        }

        $rateCode = filled($rateCodeStr) ? trim($rateCodeStr) : null;

        // 1. Resolve or Create Vehicle (Operator-isolated)
        $vehicle = Vehicle::where('type', $mode)
            ->where('operator', $operator)
            ->where(function ($q) use ($vehicleTailNo) {
                $q->where('vehicle_id', $vehicleTailNo)
                  ->orWhere('name', $vehicleTailNo);
            })
            ->first();

        if (! $vehicle) {
            $vehicle = Vehicle::create([
                'type' => $mode,
                'name' => $vehicleTailNo,
                'vehicle_id' => $vehicleTailNo,
                'operator' => $operator,
                'operator_id' => $operatorModel->id,
                'is_active' => true,
            ]);
        }

        // 2. Resolve or Create FerryRoute (Operator-isolated)
        $route = FerryRoute::where('origin', trim($origin))
            ->where('destination', trim($destination))
            ->where('mode', $mode)
            ->where('operator', $operator)
            ->first();

        if (! $route) {
            $route = FerryRoute::create([
                'origin' => trim($origin),
                'destination' => trim($destination),
                'mode' => $mode,
                'operator' => $operator,
                'operator_id' => $operatorModel->id,
                'vehicle_id' => $vehicle->id,
                'is_active' => true,
            ]);
        }

        // 3. Parse Departure & Arrival Datetimes
        $depTimeStrClean = trim($depTimeStr);
        $depDateStrClean = trim($depDateStr);
        $departureDateTime = $this->parseImportedDateTime($depDateStrClean, $depTimeStrClean);

        if (filled($arrTimeStr)) {
            $arrivalDateTime = $this->parseImportedDateTime($depDateStrClean, trim($arrTimeStr));
            if ($arrivalDateTime->lessThan($departureDateTime)) {
                $arrivalDateTime->addDay();
            }
        } else {
            $arrivalDateTime = (clone $departureDateTime)->addHours(2);
        }

        // 4. Resolve or Create Schedule
        $scheduleCreated = false;
        $schedule = Schedule::where('ferry_route_id', $route->id)
            ->whereBetween('departure_time', [
                (clone $departureDateTime)->subMinute(),
                (clone $departureDateTime)->addMinute(),
            ])
            ->first();

        // 5. Resolve or Attach Transport Class / Accommodation
        $status = 'imported';
        // For ferry accommodations, itemPrice defaults to rate if no explicit additionalPrice is specified.
        // When additionalPrice is not provided for ferries, each class carries its full rate, and schedule base price is 0.
        // For airline transport classes, additional_price is strictly the class add-on (0 if blank/zero).
        $accommodationPrice = $additionalPrice > 0 ? $additionalPrice : $rate;
        $transportClassPrice = $additionalPrice > 0 ? $additionalPrice : ($mode === 'ferry' ? $rate : 0.0);

        if (! $schedule) {
            $scheduleBasePrice = ($mode === 'ferry' && $additionalPrice <= 0) ? 0.0 : $rate;
            $schedule = Schedule::create([
                'ferry_route_id' => $route->id,
                'vehicle_name' => $vehicleTailNo,
                'plate_no' => $plateNo,
                'departure_time' => $departureDateTime,
                'arrival_time' => $arrivalDateTime,
                'price' => $scheduleBasePrice,
                'is_active' => true,
            ]);
            $scheduleCreated = true;
        }

        // Ensure TransportClass exists in catalog with smart canonical resolution
        $transportClass = $this->resolveTransportClass(
            $transportClassStr,
            $operator,
            $operatorModel,
            $mode,
            $transportClassPrice
        );

        // Attach to schedule_transport_class pivot
        $alreadyAttachedTc = $schedule->transportClasses()
            ->where('transport_classes.id', $transportClass->id)
            ->exists();

        if (! $alreadyAttachedTc) {
            $schedule->transportClasses()->attach($transportClass->id, [
                'additional_price' => $transportClassPrice,
                'tickets_available' => $ticketsAvailable,
                'rate_type' => $rateType,
                'is_promo' => $isPromo,
                'rate_code' => $rateCode,
                'has_bed' => $hasBed,
                'is_active' => true,
            ]);
        }

        $accommodationCreated = false;
        // For Ferry mode, also maintain schedule_accommodations compatibility
        if ($mode === 'ferry') {
            $accommodationExists = $schedule->scheduleAccommodations()
                ->where('name', $transportClass->name)
                ->where('rate_code', $rateCode)
                ->exists();

            if (! $accommodationExists) {
                ScheduleAccommodation::create([
                    'schedule_id' => $schedule->id,
                    'name' => $transportClass->name,
                    'rate_code' => $rateCode,
                    'price' => $accommodationPrice,
                    'tickets_available' => $ticketsAvailable,
                    'has_bed' => $hasBed,
                    'is_active' => true,
                ]);
                $accommodationCreated = true;
            }
        }

        if ($alreadyAttachedTc && ! $scheduleCreated && ! $accommodationCreated) {
            $status = 'skipped';
        }

        // 6. Handle optional Return Date if present
        if (filled($returnDateStr)) {
            $this->processReturnSchedule(
                $route,
                $vehicleTailNo,
                $plateNo,
                $operator,
                $mode,
                trim($returnDateStr),
                $depTimeStrClean,
                $arrTimeStr,
                $transportClassStr,
                $rate,
                $accommodationPrice,
                $transportClassPrice,
                $rateType,
                $isPromo,
                $ticketsAvailable,
                $hasBed,
                $rateCode
            );
        }

        return $status;
    }

    /**
     * Helper to process reverse/return schedule if return date is specified.
     */
    protected function processReturnSchedule(
        FerryRoute $forwardRoute,
        string $vehicleTailNo,
        ?string $plateNo,
        string $operator,
        string $mode,
        string $returnDateStr,
        string $depTimeStr,
        ?string $arrTimeStr,
        string $transportClassStr,
        float $rate,
        float $accommodationPrice,
        float $transportClassPrice,
        string $rateType,
        bool $isPromo,
        int $ticketsAvailable,
        bool $hasBed,
        ?string $rateCode = null
    ): void {
        $returnRoute = FerryRoute::where('origin', $forwardRoute->destination)
            ->where('destination', $forwardRoute->origin)
            ->where('mode', $mode)
            ->where('operator', $operator)
            ->first();

        if (! $returnRoute) {
            $returnRoute = FerryRoute::create([
                'origin' => $forwardRoute->destination,
                'destination' => $forwardRoute->origin,
                'mode' => $mode,
                'operator' => $operator,
                'operator_id' => $forwardRoute->operator_id,
                'vehicle_id' => $forwardRoute->vehicle_id,
                'is_active' => true,
            ]);
        }

        $departureDateTime = $this->parseImportedDateTime($returnDateStr, $depTimeStr);

        if (filled($arrTimeStr)) {
            $arrivalDateTime = $this->parseImportedDateTime($returnDateStr, trim($arrTimeStr));
            if ($arrivalDateTime->lessThan($departureDateTime)) {
                $arrivalDateTime->addDay();
            }
        } else {
            $arrivalDateTime = (clone $departureDateTime)->addHours(2);
        }

        $schedule = Schedule::where('ferry_route_id', $returnRoute->id)
            ->whereBetween('departure_time', [
                (clone $departureDateTime)->subMinute(),
                (clone $departureDateTime)->addMinute(),
            ])
            ->first();

        if (! $schedule) {
            $returnScheduleBasePrice = ($mode === 'ferry' && $additionalPrice <= 0) ? 0.0 : $rate;
            $schedule = Schedule::create([
                'ferry_route_id' => $returnRoute->id,
                'vehicle_name' => $vehicleTailNo,
                'plate_no' => $plateNo,
                'departure_time' => $departureDateTime,
                'arrival_time' => $arrivalDateTime,
                'price' => $returnScheduleBasePrice,
                'is_active' => true,
            ]);
        }

        $transportClass = $this->resolveTransportClass(
            $transportClassStr,
            $operator,
            $forwardRoute->operatorRecord,
            $mode,
            $transportClassPrice
        );

        if (! $schedule->transportClasses()->where('transport_classes.id', $transportClass->id)->exists()) {
            $schedule->transportClasses()->attach($transportClass->id, [
                'additional_price' => $transportClassPrice,
                'tickets_available' => $ticketsAvailable,
                'rate_type' => $rateType,
                'is_promo' => $isPromo,
                'rate_code' => $rateCode,
                'has_bed' => $hasBed,
                'is_active' => true,
            ]);
        }

        if ($mode === 'ferry') {
            if (! $schedule->scheduleAccommodations()->where('name', $transportClass->name)->where('rate_code', $rateCode)->exists()) {
                ScheduleAccommodation::create([
                    'schedule_id' => $schedule->id,
                    'name' => $transportClass->name,
                    'rate_code' => $rateCode,
                    'price' => $accommodationPrice,
                    'tickets_available' => $ticketsAvailable,
                    'has_bed' => $hasBed,
                    'is_active' => true,
                ]);
            }
        }
    }

    /**
     * Helper to normalize operator names to canonical values (e.g. AirAsia -> AirAsia).
     */
    protected function normalizeOperatorName(?string $operator, string $mode): string
    {
        if (blank($operator)) {
            return $mode === 'airline' ? 'AirAsia' : '2GO';
        }

        $clean = trim($operator);
        $lower = strtolower($clean);

        if ($mode === 'airline') {
            if (str_contains($lower, 'airasia')) {
                return 'AirAsia';
            }
            if (str_contains($lower, 'cebu') || str_contains($lower, 'ceb')) {
                return 'Cebu Pacific';
            }
            if (str_contains($lower, 'philippine') || str_contains($lower, 'pal')) {
                return 'Philippine Airlines';
            }
        }

        return normalize_operator_name($clean) ?? $clean;
    }

    /**
     * Parse imported schedule datetimes with explicit support for DD/MM/YYYY files.
     */
    protected function parseImportedDateTime(string $date, string $time): Carbon
    {
        $dateTime = trim($date) . ' ' . trim($time);

        foreach (['d/m/Y H:i:s', 'd/m/Y H:i', 'Y-m-d H:i:s', 'Y-m-d H:i'] as $format) {
            try {
                $parsed = Carbon::createFromFormat($format, $dateTime);

                if ($parsed !== false) {
                    return $parsed;
                }
            } catch (Throwable) {
                // Try the next supported format.
            }
        }

        try {
            return Carbon::parse($dateTime);
        } catch (Throwable $e) {
            throw new \InvalidArgumentException(
                "Could not parse '{$dateTime}'. Expected DD/MM/YYYY with time like HH:MM or HH:MM:SS.",
                previous: $e,
            );
        }
    }

    /**
     * Helper to retrieve value from normalized CSV/XLSX row array by candidate keys.
     */
    protected function getValue(array $row, array $candidateKeys): ?string
    {
        foreach ($candidateKeys as $key) {
            if (isset($row[$key]) && (string) $row[$key] !== '') {
                return trim((string) $row[$key]);
            }
        }

        return null;
    }

    /**
     * Resolve or find the canonical TransportClass for an operator, avoiding duplicate creation.
     */
    public function resolveTransportClass(
        string $rawClassName,
        string $operator,
        ?Operator $operatorModel,
        string $mode,
        float $defaultPrice = 0.0
    ): TransportClass {
        $clean = trim($rawClassName);

        // 1. Strip parenthetical fare or route notes like (Romblon Fare), (Culasi Fare), (Fare), etc.
        // Keep capacity notes like (2-3 pax) or (5 pax)
        $normalized = preg_replace('/\s*\((?![0-9]+(?:\s*-\s*[0-9]+)?\s*pax)[^)]*(?:fare|rate|route|vv|via)[^)]*\)/i', '', $clean);
        $normalized = trim($normalized);

        // 2. Canonical mapping for common variations
        $canonicalName = $this->canonicalizeClassName($normalized, $operator, $mode);

        // 3. Try to find existing TransportClass for this operator
        $transportClass = TransportClass::query()
            ->where(function ($q) use ($operator, $operatorModel) {
                if ($operatorModel) {
                    $q->where('operator_id', $operatorModel->id)
                      ->orWhere('operator', $operator);
                } else {
                    $q->where('operator', $operator)
                      ->orWhereNull('operator');
                }
            })
            ->where(function ($q) use ($canonicalName, $clean) {
                $q->where('name', $canonicalName)
                  ->orWhere('name', $clean)
                  ->orWhere('code', str($canonicalName)->slug()->value())
                  ->orWhere('code', str($clean)->slug()->value());
            })
            ->first();

        // 4. Special handling for Starlite Ferries: strictly map to official tariff accommodation classes
        if (! $transportClass && strtolower($operator) === 'starlite') {
            $starliteCanonicalMap = [
                'reclining' => 'Reclining Seat',
                'economy'   => 'Economy Bed Bunk',
                'tourist'   => 'Tourist Bed Bunk',
                'cabin'     => 'Cabin',
                'vip'       => str_contains(strtolower($canonicalName), '5') ? 'VIP Room 5pax' : 'VIP Room 2-3pax',
            ];

            foreach ($starliteCanonicalMap as $keyword => $targetName) {
                if (str_contains(strtolower($canonicalName), $keyword) || str_contains(strtolower($clean), $keyword)) {
                    $transportClass = TransportClass::where('operator', 'Starlite')
                        ->where(function ($q) use ($targetName) {
                            $q->where('name', $targetName)
                              ->orWhere('name', 'like', '%' . $targetName . '%');
                        })
                        ->first();
                    if ($transportClass) {
                        break;
                    }
                }
            }
        }

        // 5. Fallback: match by partial name across operator's existing classes before creating
        if (! $transportClass) {
            $transportClass = TransportClass::query()
                ->where(function ($q) use ($operator, $operatorModel) {
                    if ($operatorModel) {
                        $q->where('operator_id', $operatorModel->id)->orWhere('operator', $operator);
                    } else {
                        $q->where('operator', $operator)->orWhereNull('operator');
                    }
                })
                ->where('mode', $mode)
                ->where('name', 'like', '%' . $canonicalName . '%')
                ->first();
        }

        // 6. Only create if truly a brand new, unrecognized class
        if (! $transportClass) {
            $transportClass = TransportClass::create([
                'name' => $canonicalName,
                'code' => str($canonicalName)->slug()->value(),
                'operator' => $operator,
                'operator_id' => $operatorModel?->id,
                'mode' => $mode,
                'price' => $defaultPrice,
                'is_active' => true,
            ]);
        }

        return $transportClass;
    }

    /**
     * Canonicalize accommodation/seat class names into standard master data names.
     */
    protected function canonicalizeClassName(string $name, string $operator, string $mode): string
    {
        $lower = strtolower(trim($name));

        if (strtolower($operator) === 'starlite' || $mode === 'ferry') {
            if (str_starts_with($lower, 'reclining') || str_contains($lower, 'recliner')) {
                return 'Reclining Seat';
            }
            if (str_contains($lower, 'tourist')) {
                return 'Tourist Bed Bunk';
            }
            if (str_contains($lower, 'economy')) {
                return 'Economy Bed Bunk';
            }
            if (str_starts_with($lower, 'cabin')) {
                return 'Cabin';
            }
            if (str_contains($lower, 'vip')) {
                if (str_contains($lower, '5')) {
                    return 'VIP Room 5pax';
                }
                return 'VIP Room 2-3pax';
            }
        }

        if ($mode === 'airline') {
            if (str_contains($lower, 'business')) {
                return 'Business Class';
            }
            if (str_contains($lower, 'premium eco')) {
                return 'Premium Economy';
            }
            if (str_contains($lower, 'economy')) {
                return 'Economy Class';
            }
            if ($lower === 'standard plus') {
                return 'Standard Plus';
            }
            if ($lower === 'standard' || $lower === 'standard seat') {
                return 'Standard';
            }
            if (str_contains($lower, 'hot seat')) {
                return 'Hot Seat';
            }
        }

        return trim($name);
    }
}

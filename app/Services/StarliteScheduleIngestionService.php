<?php

namespace App\Services;

use App\Models\FerryRoute;
use App\Models\Operator;
use App\Models\Schedule;
use App\Models\ScheduleAccommodation;
use App\Models\Vehicle;
use App\Models\VehicleRate;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;
use ZipArchive;

class StarliteScheduleIngestionService
{
    /**
     * Starlite official vessels registry.
     */
    public const STARLITE_VESSELS = [
        ['name' => 'ANNAPOLIS', 'type' => 'ferry', 'subtype' => 'ROPAX', 'capacity' => 750],
        ['name' => 'SAGA', 'type' => 'ferry', 'subtype' => 'ROPAX', 'capacity' => 750],
        ['name' => 'JUPITER', 'type' => 'ferry', 'subtype' => 'ROPAX', 'capacity' => 750],
        ['name' => 'PACIFIC', 'type' => 'ferry', 'subtype' => 'LCT', 'capacity' => 500],
        ['name' => 'SPRINT 1', 'type' => 'ferry', 'subtype' => 'LCT', 'capacity' => 500],
        ['name' => 'ARCHER', 'type' => 'ferry', 'subtype' => 'FASTCRAFT', 'capacity' => 300],
        ['name' => 'PIONEER', 'type' => 'ferry', 'subtype' => 'ROPAX', 'capacity' => 750],
        ['name' => 'RELIANCE', 'type' => 'ferry', 'subtype' => 'ROPAX', 'capacity' => 750],
        ['name' => 'RESILIENCE', 'type' => 'ferry', 'subtype' => 'ROPAX', 'capacity' => 750],
        ['name' => 'EAGLE', 'type' => 'ferry', 'subtype' => 'ROPAX', 'capacity' => 600],
        ['name' => 'SALVE REGINA', 'type' => 'ferry', 'subtype' => 'ROPAX', 'capacity' => 750],
        ['name' => 'STELLA MARIS', 'type' => 'ferry', 'subtype' => 'ROPAX', 'capacity' => 800],
        ['name' => 'SATURN', 'type' => 'ferry', 'subtype' => 'ROPAX', 'capacity' => 750],
        ['name' => 'VENUS', 'type' => 'ferry', 'subtype' => 'ROPAX', 'capacity' => 750],
        ['name' => 'TRANSASIA 20', 'type' => 'ferry', 'subtype' => 'ROPAX', 'capacity' => 700],
        ['name' => 'GRATITUDE', 'type' => 'ferry', 'subtype' => 'ROPAX', 'capacity' => 750],
        ['name' => 'POSEIDON 43', 'type' => 'ferry', 'subtype' => 'ROPAX', 'capacity' => 650],
        ['name' => 'POSEIDON 53', 'type' => 'ferry', 'subtype' => 'ROPAX', 'capacity' => 650],
        ['name' => 'POSEIDON 37', 'type' => 'ferry', 'subtype' => 'ROPAX', 'capacity' => 650],
        ['name' => 'PROMETHEUS 54', 'type' => 'ferry', 'subtype' => 'ROPAX', 'capacity' => 650],
        ['name' => 'PROMETHEUS 55', 'type' => 'ferry', 'subtype' => 'ROPAX', 'capacity' => 650],
        ['name' => 'PROMETHEUS 56', 'type' => 'ferry', 'subtype' => 'ROPAX', 'capacity' => 650],
        ['name' => 'PROMETHEUS 57', 'type' => 'ferry', 'subtype' => 'ROPAX', 'capacity' => 650],
    ];

    /**
     * Starlite Passenger Rates Matrix from UPDATED RATES AS OF JUNE 22, 2026.
     * Maps canonical [Origin, Destination] (bidirectional) => Accommodation classes & base regular fares.
     */
    public const STARLITE_FARE_MATRIX = [
        'Batangas|Calapan' => [
            'base_price' => 680,
            'accommodations' => [
                ['name' => 'Reclining Seat', 'price' => 680, 'has_bed' => false, 'tickets' => 100],
                ['name' => 'Economy Bed Bunk', 'price' => 680, 'has_bed' => true, 'tickets' => 120],
                ['name' => 'Tourist Bed Bunk', 'price' => 680, 'has_bed' => true, 'tickets' => 80],
            ],
            'vehicle_rates' => [
                'Motorcycle' => 1440,
                'Below 3 meters' => 2160,
                '3 to 3.9 meters (Small Car)' => 3100,
                '4 to 4.9 meters (Regular Car / SUV)' => 3840,
            ],
        ],
        'Batangas|Caticlan' => [
            'base_price' => 2170,
            'accommodations' => [
                ['name' => 'Reclining Seat', 'price' => 2170, 'has_bed' => false, 'tickets' => 80],
                ['name' => 'Economy Bed Bunk', 'price' => 2270, 'has_bed' => true, 'tickets' => 150],
                ['name' => 'Tourist Bed Bunk', 'price' => 2790, 'has_bed' => true, 'tickets' => 100],
                ['name' => 'Cabin', 'price' => 3720, 'has_bed' => true, 'tickets' => 40],
                ['name' => 'VIP Room (2-3 pax)', 'price' => 8300, 'has_bed' => true, 'tickets' => 4],
                ['name' => 'VIP Room (5 pax)', 'price' => 14400, 'has_bed' => true, 'tickets' => 2],
            ],
            'vehicle_rates' => [
                'Motorcycle' => 7020,
                'Below 3 meters' => 7200,
                '3 to 3.9 meters (Small Car)' => 15030,
                '4 to 4.9 meters (Regular Car / SUV)' => 16650,
            ],
        ],
        'Batangas|Roxas Capiz' => [
            'base_price' => 2580,
            'accommodations' => [
                ['name' => 'Reclining Seat', 'price' => 2580, 'has_bed' => false, 'tickets' => 80],
                ['name' => 'Economy Bed Bunk', 'price' => 2580, 'has_bed' => true, 'tickets' => 150],
                ['name' => 'Tourist Bed Bunk', 'price' => 3200, 'has_bed' => true, 'tickets' => 100],
                ['name' => 'Cabin', 'price' => 3820, 'has_bed' => true, 'tickets' => 40],
                ['name' => 'VIP Room (2-3 pax)', 'price' => 11500, 'has_bed' => true, 'tickets' => 4],
            ],
            'vehicle_rates' => [
                'Motorcycle' => 8010,
                'Below 3 meters' => 8010,
                '3 to 3.9 meters (Small Car)' => 15300,
                '4 to 4.9 meters (Regular Car / SUV)' => 17100,
            ],
        ],
        'Cebu|Surigao' => [
            'base_price' => 1550,
            'accommodations' => [
                ['name' => 'Reclining Seat', 'price' => 1550, 'has_bed' => false, 'tickets' => 80],
                ['name' => 'Economy Bed Bunk', 'price' => 1650, 'has_bed' => true, 'tickets' => 120],
                ['name' => 'Tourist Bed Bunk', 'price' => 1960, 'has_bed' => true, 'tickets' => 80],
                ['name' => 'Cabin', 'price' => 2380, 'has_bed' => true, 'tickets' => 30],
                ['name' => 'VIP Room (2-3 pax)', 'price' => 7700, 'has_bed' => true, 'tickets' => 4],
            ],
            'vehicle_rates' => [
                'Motorcycle' => 3310,
                'Below 3 meters' => 5380,
                '3 to 3.9 meters (Small Car)' => 10140,
                '4 to 4.9 meters (Regular Car / SUV)' => 12310,
            ],
        ],
        'Cebu|Nasipit' => [
            'base_price' => 1520,
            'accommodations' => [
                ['name' => 'Reclining Seat', 'price' => 1520, 'has_bed' => false, 'tickets' => 80],
                ['name' => 'Economy Bed Bunk', 'price' => 1700, 'has_bed' => true, 'tickets' => 120],
                ['name' => 'Tourist Bed Bunk', 'price' => 2070, 'has_bed' => true, 'tickets' => 80],
                ['name' => 'Cabin', 'price' => 2580, 'has_bed' => true, 'tickets' => 30],
                ['name' => 'VIP Room (2-3 pax)', 'price' => 8200, 'has_bed' => true, 'tickets' => 4],
            ],
            'vehicle_rates' => [
                'Motorcycle' => 4030,
                'Below 3 meters' => 6720,
                '3 to 3.9 meters (Small Car)' => 12210,
                '4 to 4.9 meters (Regular Car / SUV)' => 14900,
            ],
        ],
        'Cebu|Dapitan' => [
            'base_price' => 1130,
            'accommodations' => [
                ['name' => 'Reclining Seat', 'price' => 1130, 'has_bed' => false, 'tickets' => 80],
                ['name' => 'Economy Bed Bunk', 'price' => 1440, 'has_bed' => true, 'tickets' => 120],
                ['name' => 'Tourist Bed Bunk', 'price' => 1860, 'has_bed' => true, 'tickets' => 80],
                ['name' => 'Cabin', 'price' => 2270, 'has_bed' => true, 'tickets' => 30],
                ['name' => 'VIP Room (2-3 pax)', 'price' => 7700, 'has_bed' => true, 'tickets' => 4],
            ],
            'vehicle_rates' => [
                'Motorcycle' => 1650,
                'Below 3 meters' => 5170,
                '3 to 3.9 meters (Small Car)' => 8380,
                '4 to 4.9 meters (Regular Car / SUV)' => 9000,
            ],
        ],
        'Batangas|Romblon' => [
            'base_price' => 1240,
            'accommodations' => [
                ['name' => 'Reclining Seat', 'price' => 1240, 'has_bed' => false, 'tickets' => 80],
                ['name' => 'Economy Bed Bunk', 'price' => 1240, 'has_bed' => true, 'tickets' => 120],
                ['name' => 'Tourist Bed Bunk', 'price' => 1860, 'has_bed' => true, 'tickets' => 80],
                ['name' => 'Cabin', 'price' => 2790, 'has_bed' => true, 'tickets' => 30],
                ['name' => 'VIP Room (2-3 pax)', 'price' => 8300, 'has_bed' => true, 'tickets' => 4],
            ],
            'vehicle_rates' => [
                'Motorcycle' => 4860,
                'Below 3 meters' => 8070,
                '3 to 3.9 meters (Small Car)' => 14590,
                '4 to 4.9 meters (Regular Car / SUV)' => 14590,
            ],
        ],
        'Batangas|Sibuyan (Magdiwang)' => [
            'base_price' => 1240,
            'accommodations' => [
                ['name' => 'Reclining Seat', 'price' => 1240, 'has_bed' => false, 'tickets' => 80],
                ['name' => 'Economy Bed Bunk', 'price' => 1240, 'has_bed' => true, 'tickets' => 120],
                ['name' => 'Tourist Bed Bunk', 'price' => 1860, 'has_bed' => true, 'tickets' => 80],
                ['name' => 'Cabin', 'price' => 3200, 'has_bed' => true, 'tickets' => 30],
                ['name' => 'VIP Room (2-3 pax)', 'price' => 9600, 'has_bed' => true, 'tickets' => 4],
            ],
            'vehicle_rates' => [
                'Motorcycle' => 5900,
                'Below 3 meters' => 9730,
                '3 to 3.9 meters (Small Car)' => 15620,
                '4 to 4.9 meters (Regular Car / SUV)' => 15620,
            ],
        ],
        'Romblon|Sibuyan (Magdiwang)' => [
            'base_price' => 445,
            'accommodations' => [
                ['name' => 'Reclining Seat', 'price' => 445, 'has_bed' => false, 'tickets' => 60],
                ['name' => 'Economy Bed Bunk', 'price' => 445, 'has_bed' => true, 'tickets' => 80],
                ['name' => 'Tourist Bed Bunk', 'price' => 445, 'has_bed' => true, 'tickets' => 60],
                ['name' => 'Cabin', 'price' => 620, 'has_bed' => true, 'tickets' => 20],
            ],
            'vehicle_rates' => [
                'Motorcycle' => 720,
                'Below 3 meters' => 1340,
                '3 to 3.9 meters (Small Car)' => 3930,
                '4 to 4.9 meters (Regular Car / SUV)' => 3930,
            ],
        ],
        'Sibuyan (Magdiwang)|Roxas Capiz' => [
            'base_price' => 1035,
            'accommodations' => [
                ['name' => 'Reclining Seat', 'price' => 1035, 'has_bed' => false, 'tickets' => 60],
                ['name' => 'Economy Bed Bunk', 'price' => 1035, 'has_bed' => true, 'tickets' => 80],
                ['name' => 'Tourist Bed Bunk', 'price' => 1135, 'has_bed' => true, 'tickets' => 60],
                ['name' => 'Cabin', 'price' => 1550, 'has_bed' => true, 'tickets' => 20],
                ['name' => 'VIP Room (2-3 pax)', 'price' => 4400, 'has_bed' => true, 'tickets' => 4],
            ],
            'vehicle_rates' => [
                'Motorcycle' => 3310,
                'Below 3 meters' => 5480,
                '3 to 3.9 meters (Small Car)' => 10140,
                '4 to 4.9 meters (Regular Car / SUV)' => 10140,
            ],
        ],
        'Romblon|Roxas Capiz' => [
            'base_price' => 1550,
            'accommodations' => [
                ['name' => 'Reclining Seat', 'price' => 1550, 'has_bed' => false, 'tickets' => 60],
                ['name' => 'Economy Bed Bunk', 'price' => 1550, 'has_bed' => true, 'tickets' => 80],
                ['name' => 'Tourist Bed Bunk', 'price' => 1750, 'has_bed' => true, 'tickets' => 60],
                ['name' => 'Cabin', 'price' => 2170, 'has_bed' => true, 'tickets' => 20],
                ['name' => 'VIP Room (2-3 pax)', 'price' => 6500, 'has_bed' => true, 'tickets' => 4],
            ],
            'vehicle_rates' => [
                'Motorcycle' => 3310,
                'Below 3 meters' => 5790,
                '3 to 3.9 meters (Small Car)' => 10450,
                '4 to 4.9 meters (Regular Car / SUV)' => 10450,
            ],
        ],
        'Odiongan|Caticlan' => [
            'base_price' => 863,
            'accommodations' => [
                ['name' => 'Reclining Seat', 'price' => 863, 'has_bed' => false, 'tickets' => 60],
                ['name' => 'Economy Bed Bunk', 'price' => 863, 'has_bed' => true, 'tickets' => 80],
                ['name' => 'Tourist Bed Bunk', 'price' => 1035, 'has_bed' => true, 'tickets' => 60],
                ['name' => 'Cabin', 'price' => 1725, 'has_bed' => true, 'tickets' => 20],
                ['name' => 'VIP Room (2-3 pax)', 'price' => 4945, 'has_bed' => true, 'tickets' => 4],
            ],
        ],
        'Batangas|Cajidiocan' => [
            'base_price' => 1550,
            'accommodations' => [
                ['name' => 'Reclining Seat', 'price' => 1550, 'has_bed' => false, 'tickets' => 60],
                ['name' => 'Economy Bed Bunk', 'price' => 1550, 'has_bed' => true, 'tickets' => 80],
                ['name' => 'Tourist Bed Bunk', 'price' => 2170, 'has_bed' => true, 'tickets' => 60],
                ['name' => 'Cabin', 'price' => 3410, 'has_bed' => true, 'tickets' => 20],
                ['name' => 'VIP Room (2-3 pax)', 'price' => 9900, 'has_bed' => true, 'tickets' => 4],
            ],
            'vehicle_rates' => [
                'Motorcycle' => 5900,
                'Below 3 meters' => 9730,
                '3 to 3.9 meters (Small Car)' => 15620,
                '4 to 4.9 meters (Regular Car / SUV)' => 15620,
            ],
        ],
        'Roxas Mindoro|Caticlan' => [
            'base_price' => 1340,
            'accommodations' => [
                ['name' => 'Reclining Seat', 'price' => 1340, 'has_bed' => false, 'tickets' => 60],
                ['name' => 'Economy Bed Bunk', 'price' => 1340, 'has_bed' => true, 'tickets' => 80],
                ['name' => 'Tourist Bed Bunk', 'price' => 1550, 'has_bed' => true, 'tickets' => 60],
                ['name' => 'Cabin', 'price' => 1750, 'has_bed' => true, 'tickets' => 20],
                ['name' => 'VIP Room (2-3 pax)', 'price' => 4400, 'has_bed' => true, 'tickets' => 4],
            ],
            'vehicle_rates' => [
                'Motorcycle' => 2880,
                'Below 3 meters' => 4320,
                '3 to 3.9 meters (Small Car)' => 5760,
                '4 to 4.9 meters (Regular Car / SUV)' => 7200,
            ],
        ],
        'Batangas|Odiongan' => [
            'base_price' => 1380,
            'accommodations' => [
                ['name' => 'Reclining Seat', 'price' => 1380, 'has_bed' => false, 'tickets' => 60],
                ['name' => 'Economy Bed Bunk', 'price' => 1380, 'has_bed' => true, 'tickets' => 80],
                ['name' => 'Tourist Bed Bunk', 'price' => 2070, 'has_bed' => true, 'tickets' => 60],
                ['name' => 'Cabin', 'price' => 3105, 'has_bed' => true, 'tickets' => 20],
                ['name' => 'VIP Room (2-3 pax)', 'price' => 9315, 'has_bed' => true, 'tickets' => 4],
            ],
        ],
        'Roxas Mindoro|Odiongan' => [
            'base_price' => 800,
            'accommodations' => [
                ['name' => 'Reclining Seat', 'price' => 800, 'has_bed' => false, 'tickets' => 60],
                ['name' => 'Economy Bed Bunk', 'price' => 800, 'has_bed' => true, 'tickets' => 80],
                ['name' => 'Tourist Bed Bunk', 'price' => 1000, 'has_bed' => true, 'tickets' => 60],
            ],
        ],
        'Romblon|Cajidiocan' => [
            'base_price' => 445,
            'accommodations' => [
                ['name' => 'Reclining Seat', 'price' => 445, 'has_bed' => false, 'tickets' => 60],
                ['name' => 'Economy Bed Bunk', 'price' => 445, 'has_bed' => true, 'tickets' => 80],
                ['name' => 'Tourist Bed Bunk', 'price' => 600, 'has_bed' => true, 'tickets' => 60],
            ],
        ],
        'Cajidiocan|Roxas Capiz' => [
            'base_price' => 1035,
            'accommodations' => [
                ['name' => 'Reclining Seat', 'price' => 1035, 'has_bed' => false, 'tickets' => 60],
                ['name' => 'Economy Bed Bunk', 'price' => 1035, 'has_bed' => true, 'tickets' => 80],
                ['name' => 'Tourist Bed Bunk', 'price' => 1135, 'has_bed' => true, 'tickets' => 60],
                ['name' => 'Cabin', 'price' => 1550, 'has_bed' => true, 'tickets' => 20],
            ],
        ],
        'Roxas Mindoro|Buruanga' => [
            'base_price' => 1200,
            'accommodations' => [
                ['name' => 'Reclining Seat', 'price' => 1200, 'has_bed' => false, 'tickets' => 60],
                ['name' => 'Economy Bed Bunk', 'price' => 1200, 'has_bed' => true, 'tickets' => 80],
                ['name' => 'Tourist Bed Bunk', 'price' => 1400, 'has_bed' => true, 'tickets' => 60],
            ],
        ],
    ];

    public function __construct(
        protected LocationCodeResolver $locationResolver = new LocationCodeResolver(),
    ) {}

    /**
     * Ingest Starlite Ferries schedules and rates.
     * STRICTLY scoped to Starlite operator to prevent touching other operators.
     *
     * @param string|null $excelFilePath Custom path to VESSEL ROUTE.xlsx (defaults to starlite_schedules dir)
     * @param Carbon|null $startDate Horizon start date (defaults to today)
     * @param Carbon|null $endDate Horizon end date (defaults to +60 days)
     * @return array Summary of sync results
     */
    public function ingest(
        ?string $excelFilePath = null,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): array {
        $excelFilePath = $excelFilePath ?? base_path('starlite_schedules/VESSEL ROUTE.xlsx');
        $startDate = $startDate ?? Carbon::today();
        $endDate = $endDate ?? Carbon::today()->addDays(60);

        if (! file_exists($excelFilePath)) {
            return [
                'success' => false,
                'message' => "Starlite timetable file not found at: {$excelFilePath}",
                'routes_count' => 0,
                'schedules_count' => 0,
                'vessels_count' => 0,
            ];
        }

        // 1. Ensure Starlite Operator Exists
        $operator = Operator::firstOrCreate(
            ['name' => 'Starlite'],
            [
                'mode' => 'ferry',
                'logo_path' => 'operators/Starlite_Logo.png',
                'is_active' => true,
            ]
        );

        // 2. Sync Starlite Vessels (Only for Starlite)
        $syncedVessels = $this->syncVessels($operator);

        // 3. Sync Rolling Cargo / Vehicle Rates
        $this->syncVehicleRates();

        // 4. Parse Timetable Rows from Excel
        $rows = $this->parseXlsxRows($excelFilePath);
        $timetableRules = $this->extractTimetableRules($rows);

        // 5. Expand Timetable Rules into Route & Schedule Records (Strictly for Starlite)
        $routesCreated = 0;
        $schedulesCreated = 0;
        $accommodationsAttached = 0;

        DB::transaction(function () use (
            $operator, $timetableRules, $startDate, $endDate, $syncedVessels,
            &$routesCreated, &$schedulesCreated, &$accommodationsAttached
        ) {
            foreach ($timetableRules as $rule) {
                $origin = $rule['origin'];
                $destination = $rule['destination'];
                $vesselName = $rule['vessel_name'];
                $durationMinutes = $rule['duration_minutes'];
                $departureTimes = $rule['departure_times'];
                $activeDays = $rule['active_days']; // array of 0..6 (Sunday=0) or 'all'

                // Resolve Vehicle
                $vehicle = $syncedVessels[$vesselName] ?? null;
                if (! $vehicle) {
                    $vehicle = Vehicle::firstOrCreate(
                        ['name' => $vesselName, 'operator' => 'Starlite'],
                        [
                            'type' => 'ferry',
                            'vehicle_id' => $vesselName,
                            'operator_id' => $operator->id,
                            'is_active' => true,
                        ]
                    );
                    $syncedVessels[$vesselName] = $vehicle;
                }

                // Resolve or Create FerryRoute STRICTLY for Starlite
                $route = FerryRoute::where('origin', $origin)
                    ->where('destination', $destination)
                    ->where('mode', 'ferry')
                    ->where('operator', 'Starlite')
                    ->first();

                if (! $route) {
                    $route = FerryRoute::create([
                        'origin' => $origin,
                        'destination' => $destination,
                        'mode' => 'ferry',
                        'operator' => 'Starlite',
                        'operator_id' => $operator->id,
                        'vehicle_id' => $vehicle->id,
                        'is_active' => true,
                    ]);
                    $routesCreated++;
                }

                // Lookup Rate Matrix for this Route Pair
                $rateConfig = $this->lookupFareMatrix($origin, $destination);
                $basePrice = $rateConfig['base_price'] ?? 680.0;
                $accommodations = $rateConfig['accommodations'] ?? [
                    ['name' => 'Economy Bed Bunk', 'price' => $basePrice, 'has_bed' => true, 'tickets' => 100],
                    ['name' => 'Tourist Bed Bunk', 'price' => $basePrice * 1.2, 'has_bed' => true, 'tickets' => 80],
                ];

                // Expand over Date Horizon
                $period = CarbonPeriod::create($startDate, $endDate);
                foreach ($period as $date) {
                    $dayOfWeek = $date->dayOfWeek; // 0 (Sun) - 6 (Sat)
                    if ($activeDays !== 'all' && ! in_array($dayOfWeek, $activeDays, true)) {
                        continue;
                    }

                    foreach ($departureTimes as $timeStr) {
                        $parts = explode(':', $timeStr);
                        $depDateTime = (clone $date)->setTime((int) $parts[0], (int) $parts[1], 0);
                        $arrDateTime = (clone $depDateTime)->addMinutes($durationMinutes);

                        // Check if schedule already exists within 2 minutes for this route
                        $schedule = Schedule::where('ferry_route_id', $route->id)
                            ->whereBetween('departure_time', [
                                (clone $depDateTime)->subMinutes(2),
                                (clone $depDateTime)->addMinutes(2),
                            ])
                            ->first();

                        $isNewSchedule = false;
                        if (! $schedule) {
                            $schedule = Schedule::create([
                                'ferry_route_id' => $route->id,
                                'service_name' => "Starlite {$vesselName}",
                                'vehicle_name' => $vesselName,
                                'departure_time' => $depDateTime,
                                'arrival_time' => $arrDateTime,
                                'duration_minutes' => $durationMinutes,
                                'price' => $basePrice,
                                'is_active' => true,
                            ]);
                            $schedulesCreated++;
                            $isNewSchedule = true;
                        }

                        // Attach/Sync Accommodations
                        foreach ($accommodations as $index => $acc) {
                            $existingAcc = $schedule->scheduleAccommodations()
                                ->where('name', $acc['name'])
                                ->first();

                            if (! $existingAcc) {
                                ScheduleAccommodation::create([
                                    'schedule_id' => $schedule->id,
                                    'name' => $acc['name'],
                                    'price' => $acc['price'],
                                    'tickets_available' => $acc['tickets'] ?? 50,
                                    'has_bed' => $acc['has_bed'] ?? false,
                                    'sort_order' => $index + 1,
                                    'is_active' => true,
                                ]);
                                $accommodationsAttached++;
                            } elseif ($existingAcc->price != $acc['price']) {
                                $existingAcc->update(['price' => $acc['price']]);
                            }
                        }
                    }
                }
            }
        });

        // Bust Schedule cache
        Schedule::bust();

        return [
            'success' => true,
            'message' => 'Starlite schedules and rates successfully synchronized.',
            'routes_count' => $routesCreated,
            'schedules_count' => $schedulesCreated,
            'accommodations_count' => $accommodationsAttached,
            'vessels_count' => count($syncedVessels),
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
        ];
    }

    /**
     * Sync Starlite Vessels into database.
     */
    public function syncVessels(Operator $operator): array
    {
        $vessels = [];
        foreach (self::STARLITE_VESSELS as $item) {
            $vehicle = Vehicle::where('name', $item['name'])
                ->where('operator', 'Starlite')
                ->first();

            if (! $vehicle) {
                $vehicle = Vehicle::create([
                    'type' => 'ferry',
                    'name' => $item['name'],
                    'vehicle_id' => $item['name'],
                    'operator' => 'Starlite',
                    'operator_id' => $operator->id,
                    'capacity' => $item['capacity'],
                    'description' => "Starlite {$item['subtype']} Vessel",
                    'is_active' => true,
                ]);
            } else {
                $vehicle->update([
                    'operator_id' => $operator->id,
                    'capacity' => $item['capacity'],
                    'is_active' => true,
                ]);
            }

            $vessels[$item['name']] = $vehicle;
        }

        return $vessels;
    }

    /**
     * Sync Rolling Cargo / Vehicle Rates for Starlite Ferries.
     */
    public function syncVehicleRates(): void
    {
        $defaultRates = [
            ['name' => 'Motorcycle', 'price' => 1440.00, 'sort_order' => 1],
            ['name' => 'Below 3 meters', 'price' => 2160.00, 'sort_order' => 2],
            ['name' => '3 to 3.9 meters (Small Car)', 'price' => 3100.00, 'sort_order' => 3],
            ['name' => '4 to 4.9 meters (Regular Car / SUV)', 'price' => 3840.00, 'sort_order' => 4],
        ];

        foreach ($defaultRates as $rateData) {
            VehicleRate::updateOrCreate(
                ['name' => $rateData['name']],
                [
                    'price' => $rateData['price'],
                    'sort_order' => $rateData['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }

    /**
     * Lookup fare matrix matching origin and destination.
     */
    public function lookupFareMatrix(string $origin, string $destination): array
    {
        $key1 = "{$origin}|{$destination}";
        $key2 = "{$destination}|{$origin}";

        if (isset(self::STARLITE_FARE_MATRIX[$key1])) {
            return self::STARLITE_FARE_MATRIX[$key1];
        }

        if (isset(self::STARLITE_FARE_MATRIX[$key2])) {
            return self::STARLITE_FARE_MATRIX[$key2];
        }

        // Fuzzy match if exact pair not found
        foreach (self::STARLITE_FARE_MATRIX as $pair => $config) {
            [$o, $d] = explode('|', $pair);
            if (
                (str_contains(strtolower($origin), strtolower($o)) && str_contains(strtolower($destination), strtolower($d))) ||
                (str_contains(strtolower($origin), strtolower($d)) && str_contains(strtolower($destination), strtolower($o)))
            ) {
                return $config;
            }
        }

        return [
            'base_price' => 680,
            'accommodations' => [
                ['name' => 'Economy Bed Bunk', 'price' => 680, 'has_bed' => true, 'tickets' => 100],
                ['name' => 'Tourist Bed Bunk', 'price' => 850, 'has_bed' => true, 'tickets' => 80],
            ],
        ];
    }

    /**
     * Extract structured timetable rules from parsed Excel rows.
     */
    public function extractTimetableRules(array $rows): array
    {
        $rules = [];
        $vesselMap = [];

        // 1. Build Vessel Route Map from Left Columns (B & C)
        foreach ($rows as $row) {
            $vessel = trim($row['B'] ?? '');
            $shorthandRoute = trim($row['C'] ?? '');
            if (filled($vessel) && filled($shorthandRoute) && strtoupper($vessel) !== 'VESSEL') {
                $vesselMap[$shorthandRoute][] = $vessel;
            }
        }

        // 2. Parse Timetable Rows from Right Columns (F, G, H, I, J)
        foreach ($rows as $row) {
            $routeRaw = trim($row['F'] ?? '');
            $daysRaw = trim($row['G'] ?? '');
            $depTimeRaw = trim($row['H'] ?? '');
            $freqRaw = trim($row['I'] ?? '');
            $travelTimeRaw = trim($row['J'] ?? '');

            if (blank($routeRaw) || blank($depTimeRaw) || strtoupper($routeRaw) === 'ROUTE' || str_contains($routeRaw, 'Schedule of Trips')) {
                continue;
            }

            // Extract Origin, Destination, and Vessel Type
            $parsedRoute = $this->parseRouteCell($routeRaw);
            if (! $parsedRoute) {
                continue;
            }

            $origin = $parsedRoute['origin'];
            $destination = $parsedRoute['destination'];
            $vesselType = $parsedRoute['type']; // ROPAX, LCT, FASTCRAFT

            // Assign Vessel Name based on Route/Type
            $vesselName = $this->resolveVesselNameForRoute($origin, $destination, $vesselType, $vesselMap);

            // Parse Departure Times
            $depTimes = $this->parseDepartureTimes($depTimeRaw);
            if (empty($depTimes)) {
                continue;
            }

            // Parse Travel Duration in Minutes
            $durationMinutes = $this->parseDurationMinutes($travelTimeRaw);

            // Parse Days of Week
            $activeDays = $this->parseDaysOfWeek($daysRaw);

            $rules[] = [
                'origin' => $origin,
                'destination' => $destination,
                'vessel_name' => $vesselName,
                'vessel_type' => $vesselType,
                'departure_times' => $depTimes,
                'duration_minutes' => $durationMinutes,
                'active_days' => $activeDays,
                'raw_route' => $routeRaw,
                'raw_days' => $daysRaw,
            ];
        }

        return $rules;
    }

    /**
     * Parse route string into Origin, Destination, and Vessel Type.
     * e.g. "BATANGAS-CALAPAN (ROPAX)", "CALAPAN-BATANGAS (FASTCRAFT)", "ODIONGAN TO ROXAS MINDORO"
     */
    public function parseRouteCell(string $raw): ?array
    {
        $type = 'ROPAX';
        if (preg_match('/\((ROPAX|LCT|FASTCRAFT)\)/i', $raw, $m)) {
            $type = strtoupper($m[1]);
        }

        // Clean route string
        $clean = preg_replace('/\s*\((ROPAX|LCT|FASTCRAFT)\)\s*/i', '', $raw);
        $clean = str_replace(["\n", "\r"], ' ', $clean);
        $clean = trim($clean);

        $delimiter = '-';
        if (str_contains($clean, ' TO ')) {
            $delimiter = ' TO ';
        } elseif (str_contains($clean, ' to ')) {
            $delimiter = ' to ';
        } elseif (str_contains($clean, '-')) {
            $delimiter = '-';
        }

        $parts = explode($delimiter, $clean);
        if (count($parts) < 2) {
            return null;
        }

        $rawOrigin = trim($parts[0]);
        $rawDest = trim($parts[1]);

        $origin = $this->locationResolver->resolve($rawOrigin, 'ferry');
        $destination = $this->locationResolver->resolve($rawDest, 'ferry');

        if (blank($origin) || blank($destination)) {
            return null;
        }

        return [
            'origin' => $origin,
            'destination' => $destination,
            'type' => $type,
        ];
    }

    /**
     * Resolve appropriate vessel name for the route and vessel type.
     */
    protected function resolveVesselNameForRoute(string $origin, string $destination, string $type, array $vesselMap): string
    {
        if ($type === 'FASTCRAFT') {
            return 'ARCHER';
        }

        if ($type === 'LCT') {
            return ($origin === 'Calapan' || $destination === 'Calapan') ? 'SPRINT 1' : 'PACIFIC';
        }

        // Special routes mapping
        if (str_contains($origin, 'Cebu') || str_contains($destination, 'Cebu')) {
            if (str_contains($origin, 'Dapitan') || str_contains($destination, 'Dapitan')) {
                return 'SATURN';
            }
            return 'SATURN';
        }

        if (str_contains($origin, 'Caticlan') || str_contains($destination, 'Caticlan')) {
            if (str_contains($origin, 'Batangas') || str_contains($destination, 'Batangas')) {
                return 'PIONEER';
            }
            if (str_contains($origin, 'Roxas') || str_contains($destination, 'Roxas')) {
                return 'RESILIENCE';
            }
            return 'RELIANCE';
        }

        if (str_contains($origin, 'Roxas Capiz') || str_contains($destination, 'Roxas Capiz')) {
            return 'STELLA MARIS';
        }

        if (str_contains($origin, 'Romblon') || str_contains($destination, 'Romblon')) {
            return 'VENUS';
        }

        if (str_contains($origin, 'Sibuyan') || str_contains($destination, 'Sibuyan')) {
            return 'GRATITUDE';
        }

        if (str_contains($origin, 'Cajidiocan') || str_contains($destination, 'Cajidiocan')) {
            return 'PROMETHEUS 54';
        }

        if (str_contains($origin, 'Odiongan') || str_contains($destination, 'Odiongan')) {
            return 'EAGLE';
        }

        if ($origin === 'Batangas' && $destination === 'Calapan') {
            return 'ANNAPOLIS';
        }

        if ($origin === 'Calapan' && $destination === 'Batangas') {
            return 'JUPITER';
        }

        return 'SAGA';
    }

    /**
     * Parse departure times from timetable string into 24-hour HH:MM array.
     */
    public function parseDepartureTimes(string $raw): array
    {
        $clean = strtoupper(trim($raw));
        $times = [];

        // 1. Check "EVERY ODD NUMBERS" (1, 3, 5, 7, 9, 11 AM/PM)
        if (str_contains($clean, 'EVERY ODD')) {
            return [
                '01:00', '03:00', '05:00', '07:00', '09:00', '11:00',
                '13:00', '15:00', '17:00', '19:00', '21:00', '23:00',
            ];
        }

        // Clean up notations like (LCT), (ROPAX), AND, /
        $clean = preg_replace('/\([^)]+\)/', '', $clean);
        $clean = str_replace([' AND ', ' and ', '/', ';', ':10:30PM'], [',', ',', ',', ',', ',10:30PM'], $clean);
        
        $chunks = explode(',', $clean);
        foreach ($chunks as $chunk) {
            $chunk = trim($chunk);
            if (empty($chunk)) {
                continue;
            }

            // Match times like "12:30AM", "7:30 AM", "1:00PM", "2AM", "6PM", "12NN", "9PM"
            if ($chunk === '12NN' || $chunk === '12 NOON' || $chunk === '12:00NN') {
                $times[] = '12:00';
                continue;
            }
            if ($chunk === '12MN' || $chunk === '12 MIDNIGHT') {
                $times[] = '00:00';
                continue;
            }

            try {
                $parsed = Carbon::parse($chunk);
                $times[] = $parsed->format('H:i');
            } catch (Throwable) {
                // If standard parse fails, try regex for "2AM" or "11PM"
                if (preg_match('/^([0-9]{1,2})(?::([0-9]{2}))?\s*(AM|PM)$/i', $chunk, $m)) {
                    $hour = (int) $m[1];
                    $min = isset($m[2]) && $m[2] !== '' ? (int) $m[2] : 0;
                    $meridiem = strtoupper($m[3]);
                    if ($meridiem === 'PM' && $hour < 12) {
                        $hour += 12;
                    } elseif ($meridiem === 'AM' && $hour === 12) {
                        $hour = 0;
                    }
                    $times[] = sprintf('%02d:%02d', $hour, $min);
                }
            }
        }

        return array_values(array_unique($times));
    }

    /**
     * Parse duration string like "3 HOURS", "1 & 30MINS", "10 HOURS", "18 HOURS" into minutes.
     */
    public function parseDurationMinutes(string $raw): int
    {
        $clean = strtoupper(trim($raw));
        $minutes = 0;

        if (preg_match('/([0-9]+)\s*(?:&|AND)?\s*([0-9]+)?\s*MIN/i', $clean, $m)) {
            $hours = (int) $m[1];
            $mins = isset($m[2]) ? (int) $m[2] : 0;
            return ($hours * 60) + $mins;
        }

        if (preg_match('/([0-9]+)\s*HOUR/i', $clean, $m)) {
            return ((int) $m[1]) * 60;
        }

        if (preg_match('/([0-9]+)\s*MIN/i', $clean, $m)) {
            return (int) $m[1];
        }

        return 120; // Default 2 hours if unspecified
    }

    /**
     * Parse Days of week string into array of Carbon day-of-week integers (0=Sun, 1=Mon, ..., 6=Sat) or 'all'.
     */
    public function parseDaysOfWeek(string $raw): array|string
    {
        $clean = strtoupper(trim($raw));

        if (empty($clean) || str_contains($clean, 'DAILY') || str_contains($clean, 'MON-SUN') || str_contains($clean, 'MON - SUN')) {
            return 'all';
        }

        $dayMap = [
            'SUN' => Carbon::SUNDAY,
            'SUNDAY' => Carbon::SUNDAY,
            'MON' => Carbon::MONDAY,
            'MONDAY' => Carbon::MONDAY,
            'TUE' => Carbon::TUESDAY,
            'TUES' => Carbon::TUESDAY,
            'TUESDAY' => Carbon::TUESDAY,
            'WED' => Carbon::WEDNESDAY,
            'WEDNESDAY' => Carbon::WEDNESDAY,
            'THU' => Carbon::THURSDAY,
            'THUR' => Carbon::THURSDAY,
            'THURS' => Carbon::THURSDAY,
            'THURSDAY' => Carbon::THURSDAY,
            'FRI' => Carbon::FRIDAY,
            'FRIDAY' => Carbon::FRIDAY,
            'SAT' => Carbon::SATURDAY,
            'SATURDAY' => Carbon::SATURDAY,
        ];

        $clean = str_replace([' AND ', ' and ', '/'], [',', ',', ','], $clean);
        $chunks = explode(',', $clean);
        $days = [];

        foreach ($chunks as $chunk) {
            $token = trim($chunk);
            if (isset($dayMap[$token])) {
                $days[] = $dayMap[$token];
            }
        }

        return empty($days) ? 'all' : array_values(array_unique($days));
    }

    /**
     * Parse rows from an .xlsx file using ZipArchive & SimpleXML.
     */
    protected function parseXlsxRows(string $filePath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new \RuntimeException('Unable to open XLSX file.');
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

        $rows = [];
        foreach ($xml->sheetData->row as $rowNode) {
            $rowCells = [];
            foreach ($rowNode->c as $cellNode) {
                $ref = (string) $cellNode['r'];
                $colLetters = preg_replace('/[0-9]/', '', $ref);

                $val = (string) $cellNode->v;
                $type = (string) $cellNode['t'];

                if ($type === 's' && isset($sharedStrings[(int) $val])) {
                    $cellValue = $sharedStrings[(int) $val];
                } else {
                    $cellValue = $val;
                }

                $rowCells[$colLetters] = $cellValue;
            }

            if (! empty($rowCells)) {
                $rows[] = $rowCells;
            }
        }

        return $rows;
    }
}

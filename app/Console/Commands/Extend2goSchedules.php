<?php

namespace App\Console\Commands;

use App\Models\FerryRoute;
use App\Models\Operator;
use App\Models\Schedule;
use App\Models\TransportClass;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Extend2goSchedules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedules:extend-2go {--until=2026-12-31 : Target end date in YYYY-MM-DD format} {--from= : Start date in YYYY-MM-DD format (defaults to today)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and extend 2GO ferry schedules and accommodations until a specified date';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $untilStr = $this->option('until') ?: '2026-12-31';
        $fromStr = $this->option('from');

        $startDate = $fromStr ? Carbon::parse($fromStr) : Carbon::today();
        $endDate = Carbon::parse($untilStr);

        if ($endDate->lessThan($startDate)) {
            $this->error("End date ({$endDate->format('Y-m-d')}) cannot be before start date ({$startDate->format('Y-m-d')}).");
            return self::FAILURE;
        }

        $this->info("Extending 2GO schedules from {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}...");

        $routesData = [
            // Batangas <-> Caticlan
            [
                'origin' => 'Batangas',
                'destination' => 'Caticlan',
                'mode' => 'ferry',
                'operator' => '2GO',
                'trip_type' => 'local',
                'schedules' => [
                    ['service_name' => 'MV 2GO Maligaya', 'vehicle_name' => 'MV 2GO Maligaya', 'plate_no' => '2GO-201', 'dep_time' => '09:00:00', 'duration' => 540, 'price' => 1200.00],
                    ['service_name' => 'MV 2GO Masagana', 'vehicle_name' => 'MV 2GO Masagana', 'plate_no' => '2GO-202', 'dep_time' => '21:00:00', 'duration' => 540, 'price' => 1200.00],
                ],
                'accommodations' => [
                    ['name' => 'Super Value Class', 'description' => 'Budget-friendly open-air bunk beds.', 'price' => 1200.00, 'has_bed' => true, 'sort_order' => 1],
                    ['name' => 'Tourist Class', 'description' => 'Air-conditioned shared cabin bunk beds.', 'price' => 1500.00, 'has_bed' => true, 'sort_order' => 2],
                    ['name' => 'Cabin Class', 'description' => 'Shared 4-berth or 6-berth cabin with privacy.', 'price' => 2200.00, 'has_bed' => true, 'sort_order' => 3],
                    ['name' => 'State Room', 'description' => 'Private luxury suite with private bathroom and TV.', 'price' => 3500.00, 'has_bed' => true, 'sort_order' => 4],
                ],
            ],
            [
                'origin' => 'Caticlan',
                'destination' => 'Batangas',
                'mode' => 'ferry',
                'operator' => '2GO',
                'trip_type' => 'local',
                'schedules' => [
                    ['service_name' => 'MV 2GO Maligaya', 'vehicle_name' => 'MV 2GO Maligaya', 'plate_no' => '2GO-201', 'dep_time' => '09:00:00', 'duration' => 540, 'price' => 1200.00],
                    ['service_name' => 'MV 2GO Masagana', 'vehicle_name' => 'MV 2GO Masagana', 'plate_no' => '2GO-202', 'dep_time' => '21:00:00', 'duration' => 540, 'price' => 1200.00],
                ],
                'accommodations' => [
                    ['name' => 'Super Value Class', 'description' => 'Budget-friendly open-air bunk beds.', 'price' => 1200.00, 'has_bed' => true, 'sort_order' => 1],
                    ['name' => 'Tourist Class', 'description' => 'Air-conditioned shared cabin bunk beds.', 'price' => 1500.00, 'has_bed' => true, 'sort_order' => 2],
                    ['name' => 'Cabin Class', 'description' => 'Shared 4-berth or 6-berth cabin with privacy.', 'price' => 2200.00, 'has_bed' => true, 'sort_order' => 3],
                    ['name' => 'State Room', 'description' => 'Private luxury suite with private bathroom and TV.', 'price' => 3500.00, 'has_bed' => true, 'sort_order' => 4],
                ],
            ],

            // Manila <-> Cebu
            [
                'origin' => 'Manila',
                'destination' => 'Cebu',
                'mode' => 'ferry',
                'operator' => '2GO',
                'trip_type' => 'local',
                'schedules' => [
                    ['service_name' => 'MV St. Michael the Archangel', 'vehicle_name' => 'MV St. Michael the Archangel', 'plate_no' => 'SMA-301', 'dep_time' => '10:00:00', 'duration' => 1320, 'price' => 1800.00],
                    ['service_name' => 'MV St. Francis Xavier', 'vehicle_name' => 'MV St. Francis Xavier', 'plate_no' => 'SFX-302', 'dep_time' => '18:00:00', 'duration' => 1320, 'price' => 1800.00],
                ],
                'accommodations' => [
                    ['name' => 'Super Value Class', 'description' => 'Budget-friendly open-air bunk beds.', 'price' => 1800.00, 'has_bed' => true, 'sort_order' => 1],
                    ['name' => 'Tourist Class', 'description' => 'Air-conditioned shared cabin bunk beds.', 'price' => 2300.00, 'has_bed' => true, 'sort_order' => 2],
                    ['name' => 'Cabin Class', 'description' => 'Shared 4-berth cabin with comfort amenities.', 'price' => 3500.00, 'has_bed' => true, 'sort_order' => 3],
                    ['name' => 'State Room', 'description' => 'Private state room with en-suite bath and lounge.', 'price' => 5000.00, 'has_bed' => true, 'sort_order' => 4],
                ],
            ],
            [
                'origin' => 'Cebu',
                'destination' => 'Manila',
                'mode' => 'ferry',
                'operator' => '2GO',
                'trip_type' => 'local',
                'schedules' => [
                    ['service_name' => 'MV St. Michael the Archangel', 'vehicle_name' => 'MV St. Michael the Archangel', 'plate_no' => 'SMA-301', 'dep_time' => '10:00:00', 'duration' => 1320, 'price' => 1800.00],
                    ['service_name' => 'MV St. Francis Xavier', 'vehicle_name' => 'MV St. Francis Xavier', 'plate_no' => 'SFX-302', 'dep_time' => '18:00:00', 'duration' => 1320, 'price' => 1800.00],
                ],
                'accommodations' => [
                    ['name' => 'Super Value Class', 'description' => 'Budget-friendly open-air bunk beds.', 'price' => 1800.00, 'has_bed' => true, 'sort_order' => 1],
                    ['name' => 'Tourist Class', 'description' => 'Air-conditioned shared cabin bunk beds.', 'price' => 2300.00, 'has_bed' => true, 'sort_order' => 2],
                    ['name' => 'Cabin Class', 'description' => 'Shared 4-berth cabin with comfort amenities.', 'price' => 3500.00, 'has_bed' => true, 'sort_order' => 3],
                    ['name' => 'State Room', 'description' => 'Private state room with en-suite bath and lounge.', 'price' => 5000.00, 'has_bed' => true, 'sort_order' => 4],
                ],
            ],
        ];

        $op = Operator::where('name', 'like', '%2GO%')->first();
        $opId = $op?->id;
        $opName = $op ? $op->name : '2GO';

        $createdSchedules = 0;
        $allAccRecords = [];
        $allPivotRecords = [];
        $now = Carbon::now();

        DB::transaction(function () use ($routesData, $startDate, $endDate, $opId, $opName, &$createdSchedules, &$allAccRecords, &$allPivotRecords, $now) {
            foreach ($routesData as $rData) {
                $transportClasses = [];
                foreach ($rData['accommodations'] as $accData) {
                    $code = str($accData['name'])->slug()->value();
                    $tc = TransportClass::firstOrCreate(
                        [
                            'operator' => $opName,
                            'code' => $code,
                        ],
                        [
                            'operator_id' => $opId,
                            'name' => $accData['name'],
                            'description' => $accData['description'] ?? null,
                            'price' => $accData['price'] ?? 0,
                            'is_active' => true,
                            'sort_order' => $accData['sort_order'] ?? 1,
                        ]
                    );
                    $transportClasses[$accData['name']] = $tc;
                }

                foreach ($rData['schedules'] as $sData) {
                    $vehicleName = $sData['vehicle_name'];
                    $plateNo = $sData['plate_no'];

                    $vehicle = Vehicle::where('vehicle_id', $plateNo)->first();
                    if (!$vehicle) {
                        $vehicle = Vehicle::where('name', $vehicleName)->first();
                    }
                    if (!$vehicle) {
                        $vehicle = Vehicle::create([
                            'vehicle_id' => $plateNo,
                            'name' => $vehicleName,
                            'type' => 'ferry',
                            'operator' => $opName,
                            'operator_id' => $opId,
                            'is_active' => true,
                        ]);
                    }

                    $route = FerryRoute::firstOrCreate(
                        [
                            'origin' => $rData['origin'],
                            'destination' => $rData['destination'],
                            'mode' => 'ferry',
                            'vehicle_id' => $vehicle->id,
                        ],
                        [
                            'operator' => $opName,
                            'operator_id' => $opId,
                            'trip_type' => $rData['trip_type'],
                            'is_active' => true,
                        ]
                    );

                    for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                        $depTime = Carbon::parse($date->format('Y-m-d') . ' ' . $sData['dep_time']);
                        $arrTime = $depTime->copy()->addMinutes($sData['duration']);

                        $existing = Schedule::where('ferry_route_id', $route->id)
                            ->where('departure_time', $depTime)
                            ->first();

                        if (!$existing) {
                            $schedule = Schedule::create([
                                'ferry_route_id' => $route->id,
                                'service_name' => $sData['service_name'],
                                'vehicle_name' => $sData['vehicle_name'],
                                'plate_no' => $sData['plate_no'],
                                'departure_time' => $depTime,
                                'arrival_time' => $arrTime,
                                'duration_minutes' => $sData['duration'],
                                'price' => $sData['price'],
                                'availability_label' => 'Available',
                                'seat_rows' => 15,
                                'seat_columns' => ['A', 'B', 'C', 'D', 'E', 'F'],
                                'is_active' => true,
                            ]);
                            $createdSchedules++;

                            foreach ($rData['accommodations'] as $accData) {
                                $allAccRecords[] = [
                                    'schedule_id' => $schedule->id,
                                    'name' => $accData['name'],
                                    'description' => $accData['description'] ?? null,
                                    'price' => $accData['price'] ?? 0,
                                    'tickets_available' => 50,
                                    'has_bed' => $accData['has_bed'] ?? false,
                                    'is_active' => true,
                                    'sort_order' => $accData['sort_order'] ?? 1,
                                    'created_at' => $now,
                                    'updated_at' => $now,
                                ];

                                if (isset($transportClasses[$accData['name']])) {
                                    $tc = $transportClasses[$accData['name']];
                                    $allPivotRecords[] = [
                                        'schedule_id' => $schedule->id,
                                        'transport_class_id' => $tc->id,
                                        'additional_price' => $accData['price'] ?? 0,
                                        'tickets_available' => 50,
                                        'description' => $accData['description'] ?? null,
                                        'has_bed' => $accData['has_bed'] ?? false,
                                        'is_active' => true,
                                        'created_at' => $now,
                                        'updated_at' => $now,
                                    ];
                                }
                            }
                        }
                    }
                }
            }

            if (!empty($allAccRecords)) {
                foreach (array_chunk($allAccRecords, 500) as $chunk) {
                    DB::table('schedule_accommodations')->insert($chunk);
                }
            }

            if (!empty($allPivotRecords)) {
                foreach (array_chunk($allPivotRecords, 500) as $chunk) {
                    DB::table('schedule_transport_class')->insert($chunk);
                }
            }
        });

        Schedule::bust();

        $this->info("Successfully generated {$createdSchedules} new 2GO schedules through {$endDate->format('Y-m-d')}. Cache busted.");

        return self::SUCCESS;
    }
}

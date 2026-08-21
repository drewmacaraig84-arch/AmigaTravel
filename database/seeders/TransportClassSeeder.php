<?php

namespace Database\Seeders;

use App\Models\Schedule;
use App\Models\TransportClass;
use App\Models\ScheduleAccommodation;
use App\Models\FerryRoute;
use Illuminate\Database\Seeder;

class TransportClassSeeder extends Seeder
{
    public function run(): void
    {
        $operatorConfigs = config('airline_seating.operators', []);
        $classIdsByCode = [];
        
        $operatorMap = \App\Models\Operator::pluck('id', 'name')->mapWithKeys(function ($id, $name) {
            return [strtolower($name) => $id];
        })->toArray();
        $operatorMap['philippines airasia'] = $operatorMap[strtolower('AirAsia')] ?? null;
        $operatorMap['airasia'] = $operatorMap[strtolower('AirAsia')] ?? null;
        $operatorMap['pal'] = $operatorMap[strtolower('Philippine Airlines')] ?? null;

        foreach ($operatorConfigs as $operator => $operatorConfig) {
            $opId = $operatorMap[strtolower($operator)] ?? null;
            foreach ($operatorConfig['classes'] ?? [] as $code => $classConfig) {
                $class = TransportClass::updateOrCreate(
                    ['operator' => $operator, 'code' => $code],
                    [
                        'name' => $classConfig['name'],
                        'description' => $classConfig['description'],
                        'price' => $classConfig['price'],
                        'sort_order' => $classConfig['sort_order'],
                        'is_active' => true,
                        'operator_id' => $opId,
                    ],
                );
                $classIdsByCode[$operator][$code] = $class->id;
            }
        }

        $airlineSchedules = Schedule::query()
            ->with('ferryRoute')
            ->whereHas('ferryRoute', fn ($q) => $q->where('mode', 'airline'))
            ->get();

        foreach ($airlineSchedules as $schedule) {
            $resolvedOperator = $schedule->resolveOperatorConfigKey($schedule->ferryRoute->operator);
            $operatorConfig = $operatorConfigs[$resolvedOperator] ?? null;
            if (! $operatorConfig) {
                continue;
            }

            $resolvedType = $schedule->resolveAircraftConfigKey($schedule->service_name);
            $aircraftConfig = $operatorConfig['aircraft'][$resolvedType] ?? null;
            if (! $aircraftConfig) {
                continue;
            }

            $attachedClassIds = collect($aircraftConfig['class_order'] ?? [])
                ->map(fn (string $code) => $classIdsByCode[$resolvedOperator][$code] ?? null)
                ->filter()
                ->values()
                ->all();

            $schedule->transportClasses()->sync($attachedClassIds);
        }

        // Ensure default ferry transport classes exist for each ferry operator
        $defaultFerryClasses = [
            'standard' => ['name' => 'Standard', 'description' => 'Standard seating or shared bunk.', 'price' => 0, 'sort_order' => 1],
            'deluxe' => ['name' => 'Deluxe', 'description' => 'More comfortable seating or private berth.', 'price' => 0, 'sort_order' => 2],
            'cabin' => ['name' => 'Cabin', 'description' => 'Private cabin accommodation.', 'price' => 0, 'sort_order' => 3],
            'vip' => ['name' => 'VIP', 'description' => 'Premium private suite.', 'price' => 0, 'sort_order' => 4],
        ];

        $ferryOperators = FerryRoute::query()
            ->where('mode', 'ferry')
            ->pluck('operator')
            ->filter()
            ->unique()
            ->values()
            ->all();

        foreach ($ferryOperators as $operator) {
            $opId = $operatorMap[strtolower($operator)] ?? null;
            foreach ($defaultFerryClasses as $code => $cfg) {
                TransportClass::updateOrCreate(
                    ['operator' => $operator, 'code' => $code],
                    [
                        'name' => $cfg['name'],
                        'description' => $cfg['description'],
                        'price' => $cfg['price'],
                        'sort_order' => $cfg['sort_order'],
                        'is_active' => true,
                        'operator_id' => $opId,
                    ]
                );
            }
        }

        // Also ensure ferry transport classes are created for any unlinked ferry schedules
        $ferrySchedules = Schedule::query()
            ->with(['ferryRoute', 'scheduleAccommodations'])
            ->whereHas('ferryRoute', fn ($q) => $q->where('mode', 'ferry'))
            ->doesntHave('transportClasses')
            ->get();

        foreach ($ferrySchedules as $schedule) {
            $operator = $schedule->ferryRoute->operator ?? null;
            if (! $operator) {
                continue;
            }
            
            $opId = $operatorMap[strtolower($operator)] ?? null;

            $pivotData = [];
            foreach ($schedule->scheduleAccommodations as $acc) {
                $code = str($acc->name)->slug()->value();
                $tc = TransportClass::firstOrCreate(
                    ['operator' => $operator, 'code' => $code],
                    [
                        'name' => $acc->name,
                        'description' => $acc->description ?? null,
                        'price' => $acc->price ?? 0,
                        'sort_order' => $acc->sort_order ?? 1,
                        'is_active' => true,
                        'operator_id' => $opId,
                    ]
                );

                $pivotData[$tc->id] = [
                    'additional_price' => $acc->price ?? 0,
                    'tickets_available' => $acc->tickets_available ?? 0,
                    'description' => $acc->description ?? null,
                    'has_bed' => $acc->has_bed ?? false,
                    'is_active' => $acc->is_active ?? true,
                ];
            }

            if (! empty($pivotData)) {
                $schedule->transportClasses()->syncWithoutDetaching($pivotData);
            }
        }
    }
}
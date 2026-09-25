<?php

namespace Database\Seeders;

use App\Models\VehicleModel;
use App\Models\VehicleRate;
use App\Models\VehicleRouteRate;
use Illuminate\Database\Seeder;

class VehicleRouteRateSeeder extends Seeder
{
    /**
     * Starlite routes that support vehicle rolling-cargo.
     * Extracted from StarliteScheduleIngestionService::STARLITE_FARE_MATRIX.
     * Routes with vehicle_supported: false or empty vehicle_rates are excluded.
     */
    private const STARLITE_VEHICLE_ROUTES = [
        ['origin' => 'Batangas',             'destination' => 'Calapan'],
        ['origin' => 'Batangas',             'destination' => 'Caticlan'],
        ['origin' => 'Batangas',             'destination' => 'Roxas Capiz'],
        ['origin' => 'Batangas',             'destination' => 'Roxas City, Capiz'],
        ['origin' => 'Batangas',             'destination' => 'Sibuyan (Magdiwang)'],
        ['origin' => 'Batangas',             'destination' => 'Sibuyan (Magdiwang), Romblon'],
        ['origin' => 'Batangas',             'destination' => 'Cajidiocan'],
        ['origin' => 'Batangas',             'destination' => 'Cajidiocan, Romblon'],
        ['origin' => 'Batangas',             'destination' => 'Romblon'],
        ['origin' => 'Batangas',             'destination' => 'Romblon, Romblon'],
        ['origin' => 'Cebu',                 'destination' => 'Surigao'],
        ['origin' => 'Cebu',                 'destination' => 'Nasipit'],
        ['origin' => 'Cebu',                 'destination' => 'Dapitan'],
        ['origin' => 'Romblon',              'destination' => 'Sibuyan (Magdiwang)'],
        ['origin' => 'Romblon',              'destination' => 'Cajidiocan'],
        ['origin' => 'Romblon',              'destination' => 'Roxas Capiz'],
        ['origin' => 'Sibuyan (Magdiwang)', 'destination' => 'Roxas Capiz'],
        ['origin' => 'Cajidiocan',           'destination' => 'Roxas Capiz'],
        ['origin' => 'Roxas Mindoro',        'destination' => 'Caticlan'],
    ];

    public function run(): void
    {
        // Clean up any legacy brand-level entries so route prices live on models
        VehicleRouteRate::whereNotNull('vehicle_brand_id')->delete();

        $now = now();

        // 1. Seed Categories (VehicleRate)
        $vehicleRates = VehicleRate::all();
        $rateInserts = [];

        foreach ($vehicleRates as $rate) {
            foreach (self::STARLITE_VEHICLE_ROUTES as $route) {
                $routeKey = $route['origin'] . '|' . $route['destination'];

                $rateInserts[] = [
                    'vehicle_rate_id'  => $rate->id,
                    'vehicle_brand_id' => null,
                    'vehicle_model_id' => null,
                    'route_key'        => $routeKey,
                    'origin'           => $route['origin'],
                    'destination'      => $route['destination'],
                    'price'            => $rate->price,
                    'is_active'        => true,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ];
            }
        }

        foreach (array_chunk($rateInserts, 200) as $chunk) {
            VehicleRouteRate::upsert(
                $chunk,
                ['vehicle_rate_id', 'route_key'],
                ['origin', 'destination', 'is_active']
            );
        }

        // 2. Seed Models (VehicleModel) - brand > model > route prices
        $models = VehicleModel::all();
        $modelInserts = [];

        foreach ($models as $model) {
            $modelPrice = (float) ($model->price ?: 1000);

            foreach (self::STARLITE_VEHICLE_ROUTES as $route) {
                $routeKey = $route['origin'] . '|' . $route['destination'];

                $modelInserts[] = [
                    'vehicle_rate_id'  => null,
                    'vehicle_brand_id' => null,
                    'vehicle_model_id' => $model->id,
                    'route_key'        => $routeKey,
                    'origin'           => $route['origin'],
                    'destination'      => $route['destination'],
                    'price'            => $modelPrice,
                    'is_active'        => true,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ];
            }
        }

        foreach (array_chunk($modelInserts, 200) as $chunk) {
            VehicleRouteRate::upsert(
                $chunk,
                ['vehicle_model_id', 'route_key'],
                ['origin', 'destination', 'is_active']
            );
        }

        $categoryCount = count($rateInserts);
        $modelCount = count($modelInserts);
        $this->command->info(
            "VehicleRouteRateSeeder: {$categoryCount} Category route rates & {$modelCount} Model route rates seeded successfully."
        );
    }
}

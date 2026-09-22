<?php

namespace Database\Seeders;

use App\Models\VehicleRate;
use Illuminate\Database\Seeder;

class VehicleRateSeeder extends Seeder
{
    /**
     * Seed vehicle rates according to standard Philippine RoRo tariff.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Motorcycle (100cc to 200cc)', 'price' => 400.00, 'sort_order' => 1],
            ['name' => 'Motorcycle (250cc & above / Big Bike)', 'price' => 500.00, 'sort_order' => 2],
            ['name' => 'Tricycle', 'price' => 450.00, 'sort_order' => 3],
            ['name' => 'Multicab', 'price' => 650.00, 'sort_order' => 4],
            ['name' => 'AUV (Asian Utility Vehicle)', 'price' => 700.00, 'sort_order' => 5],
            ['name' => 'Hatchback', 'price' => 900.00, 'sort_order' => 6],
            ['name' => 'Owner / Light Cars', 'price' => 1100.00, 'sort_order' => 7],
            ['name' => 'Owner Jeep', 'price' => 1200.00, 'sort_order' => 8],
            ['name' => 'Pick-up', 'price' => 1400.00, 'sort_order' => 9],
            ['name' => 'SUV (Sport Utility Vehicle)', 'price' => 1500.00, 'sort_order' => 10],
            ['name' => 'Van', 'price' => 1600.00, 'sort_order' => 11],
        ];

        // Migrate legacy uppercase names to formatted Title Case names
        $legacyNameMap = [
            'MOTORCYCLE (100cc to 200cc)' => 'Motorcycle (100cc to 200cc)',
            'MOTORCYCLE (250cc & above)' => 'Motorcycle (250cc & above / Big Bike)',
            'TRICYCLE' => 'Tricycle',
            'MULTICAB' => 'Multicab',
            'AUV' => 'AUV (Asian Utility Vehicle)',
            'HATCHBACK' => 'Hatchback',
            'OWNER / LIGHT CARS' => 'Owner / Light Cars',
            'OWNER JEEP' => 'Owner Jeep',
            'PICK - UP' => 'Pick-up',
            'SUV' => 'SUV (Sport Utility Vehicle)',
            'VAN' => 'Van',
        ];

        foreach ($legacyNameMap as $oldName => $newName) {
            $existing = VehicleRate::where('name', $oldName)->first();
            if ($existing) {
                $existing->update(['name' => $newName]);
            }
        }

        foreach ($types as $type) {
            VehicleRate::updateOrCreate(
                ['name' => $type['name']],
                [
                    'price' => $type['price'],
                    'sort_order' => $type['sort_order'],
                    'is_active' => true,
                ],
            );
        }
    }
}

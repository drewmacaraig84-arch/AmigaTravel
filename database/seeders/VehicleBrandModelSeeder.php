<?php

namespace Database\Seeders;

use App\Models\VehicleBrand;
use App\Models\VehicleModel;
use Illuminate\Database\Seeder;

class VehicleBrandModelSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            [
                'name' => 'Toyota',
                'sort_order' => 1,
                'models' => [
                    ['name' => 'Vios', 'price' => 1100],
                    ['name' => 'Corolla Altis', 'price' => 1100],
                    ['name' => 'Wigo', 'price' => 900],
                    ['name' => 'Yaris Hatchback', 'price' => 900],
                    ['name' => 'Innova', 'price' => 700],
                    ['name' => 'Avanza', 'price' => 700],
                    ['name' => 'Revo', 'price' => 700],
                    ['name' => 'Tamaraw FX', 'price' => 700],
                    ['name' => 'Fortuner', 'price' => 1500],
                    ['name' => 'Land Cruiser Prado / LC300', 'price' => 1500],
                    ['name' => 'RAV4', 'price' => 1500],
                    ['name' => 'Hilux', 'price' => 1400],
                    ['name' => 'Tundra', 'price' => 1400],
                    ['name' => 'HiAce (Commuter / GL Grandia)', 'price' => 1600],
                ],
            ],
            [
                'name' => 'Mitsubishi',
                'sort_order' => 2,
                'models' => [
                    ['name' => 'Mirage G4 (Sedan)', 'price' => 1100],
                    ['name' => 'Mirage Hatchback', 'price' => 900],
                    ['name' => 'Adventure', 'price' => 700],
                    ['name' => 'Xpander', 'price' => 700],
                    ['name' => 'L300', 'price' => 700],
                    ['name' => 'Montero Sport', 'price' => 1500],
                    ['name' => 'Pajero', 'price' => 1500],
                    ['name' => 'Triton / Strada', 'price' => 1400],
                    ['name' => 'Minicab', 'price' => 650],
                ],
            ],
            [
                'name' => 'Honda',
                'sort_order' => 3,
                'models' => [
                    ['name' => 'City (Sedan)', 'price' => 1100],
                    ['name' => 'Civic', 'price' => 1100],
                    ['name' => 'Brio', 'price' => 900],
                    ['name' => 'City Hatchback', 'price' => 900],
                    ['name' => 'CR-V', 'price' => 1500],
                    ['name' => 'Click 125/160', 'price' => 400],
                    ['name' => 'Beat', 'price' => 400],
                    ['name' => 'ADV160', 'price' => 400],
                    ['name' => 'PCX 160', 'price' => 400],
                    ['name' => 'TMX 125 / Supremo', 'price' => 400],
                    ['name' => 'Rebel 500', 'price' => 500],
                    ['name' => 'CB500X / NX500', 'price' => 500],
                    ['name' => 'CB650R', 'price' => 500],
                    ['name' => 'Transalp 750', 'price' => 500],
                    ['name' => 'Acty', 'price' => 650],
                ],
            ],
            [
                'name' => 'Nissan',
                'sort_order' => 4,
                'models' => [
                    ['name' => 'Almera', 'price' => 1100],
                    ['name' => 'Sentra', 'price' => 1100],
                    ['name' => 'Navara', 'price' => 1400],
                    ['name' => 'Terra', 'price' => 1500],
                    ['name' => 'Patrol', 'price' => 1500],
                    ['name' => 'NV350 Urvan', 'price' => 1600],
                ],
            ],
            [
                'name' => 'Isuzu',
                'sort_order' => 5,
                'models' => [
                    ['name' => 'Crosswind', 'price' => 700],
                    ['name' => 'Hi-Lander / Panther', 'price' => 700],
                    ['name' => 'D-Max', 'price' => 1400],
                    ['name' => 'mu-X', 'price' => 1500],
                ],
            ],
            [
                'name' => 'Ford',
                'sort_order' => 6,
                'models' => [
                    ['name' => 'Ranger', 'price' => 1400],
                    ['name' => 'Ranger Raptor', 'price' => 1400],
                    ['name' => 'F-150', 'price' => 1400],
                    ['name' => 'Everest', 'price' => 1500],
                    ['name' => 'Explorer', 'price' => 1500],
                    ['name' => 'Territory', 'price' => 1500],
                ],
            ],
            [
                'name' => 'Suzuki',
                'sort_order' => 7,
                'models' => [
                    ['name' => 'Swift', 'price' => 900],
                    ['name' => 'S-Presso', 'price' => 900],
                    ['name' => 'Celerio', 'price' => 900],
                    ['name' => 'Ertiga', 'price' => 700],
                    ['name' => 'Carry / Every', 'price' => 650],
                    ['name' => 'Super Carry', 'price' => 650],
                    ['name' => 'Raider R150', 'price' => 400],
                    ['name' => 'Burgman Street 125', 'price' => 400],
                    ['name' => 'Smash 115', 'price' => 400],
                    ['name' => 'Jimny', 'price' => 1500],
                ],
            ],
            [
                'name' => 'Hyundai',
                'sort_order' => 8,
                'models' => [
                    ['name' => 'Accent', 'price' => 1100],
                    ['name' => 'Elantra', 'price' => 1100],
                    ['name' => 'Tucson', 'price' => 1500],
                    ['name' => 'Santa Fe', 'price' => 1500],
                    ['name' => 'Staria', 'price' => 1600],
                    ['name' => 'Grand Starex', 'price' => 1600],
                ],
            ],
            [
                'name' => 'Kia',
                'sort_order' => 9,
                'models' => [
                    ['name' => 'Soluto', 'price' => 1100],
                    ['name' => 'Sorento', 'price' => 1500],
                    ['name' => 'Sportage', 'price' => 1500],
                    ['name' => 'Carnival', 'price' => 1600],
                ],
            ],
            [
                'name' => 'Mazda',
                'sort_order' => 10,
                'models' => [
                    ['name' => 'Mazda 2 Hatchback', 'price' => 900],
                    ['name' => 'Mazda 3', 'price' => 1100],
                    ['name' => 'BT-50', 'price' => 1400],
                    ['name' => 'CX-5 / CX-8 / CX-9', 'price' => 1500],
                ],
            ],
            [
                'name' => 'MG (Morris Garages)',
                'sort_order' => 11,
                'models' => [
                    ['name' => 'MG 5', 'price' => 1100],
                    ['name' => 'MG GT', 'price' => 1100],
                    ['name' => 'MG ZS', 'price' => 1500],
                    ['name' => 'MG HS', 'price' => 1500],
                ],
            ],
            [
                'name' => 'Yamaha',
                'sort_order' => 12,
                'models' => [
                    ['name' => 'NMAX 155', 'price' => 400],
                    ['name' => 'Aerox 155', 'price' => 400],
                    ['name' => 'Mio (Sporty / i125 / Gear)', 'price' => 400],
                    ['name' => 'Sniper 155', 'price' => 400],
                    ['name' => 'YTX 125', 'price' => 400],
                    ['name' => 'MT-07', 'price' => 500],
                    ['name' => 'MT-09', 'price' => 500],
                    ['name' => 'YZF-R3', 'price' => 500],
                    ['name' => 'TMAX 560', 'price' => 500],
                ],
            ],
            [
                'name' => 'Kawasaki',
                'sort_order' => 13,
                'models' => [
                    ['name' => 'Barako II (175cc)', 'price' => 400],
                    ['name' => 'Rouser NS200', 'price' => 400],
                    ['name' => 'Ninja 400/500', 'price' => 500],
                    ['name' => 'Z400', 'price' => 500],
                    ['name' => 'Versys 650', 'price' => 500],
                    ['name' => 'Vulcan S', 'price' => 500],
                ],
            ],
            [
                'name' => 'CFMOTO',
                'sort_order' => 14,
                'models' => [
                    ['name' => '450NK', 'price' => 500],
                    ['name' => '450SR', 'price' => 500],
                    ['name' => '450MT', 'price' => 500],
                    ['name' => '300NK / 300SR', 'price' => 500],
                ],
            ],
            [
                'name' => 'Royal Enfield',
                'sort_order' => 15,
                'models' => [
                    ['name' => 'Classic 350', 'price' => 500],
                    ['name' => 'Hunter 350', 'price' => 500],
                    ['name' => 'Himalayan 450', 'price' => 500],
                ],
            ],
            [
                'name' => 'Bajaj (Three-Wheelers)',
                'sort_order' => 16,
                'models' => [
                    ['name' => 'RE (Tricycle)', 'price' => 450],
                    ['name' => 'Maxima Cargo / Z', 'price' => 450],
                ],
            ],
            [
                'name' => 'TVS (Three-Wheelers)',
                'sort_order' => 17,
                'models' => [
                    ['name' => 'King Deluxe / Duramax', 'price' => 450],
                    ['name' => 'Dazz / XL100', 'price' => 400],
                ],
            ],
            [
                'name' => 'Piaggio',
                'sort_order' => 18,
                'models' => [
                    ['name' => 'Apé (Tricycle)', 'price' => 450],
                ],
            ],
            [
                'name' => 'Daihatsu',
                'sort_order' => 19,
                'models' => [
                    ['name' => 'Hijet (Dropside / Cargo Van)', 'price' => 650],
                ],
            ],
            [
                'name' => 'Foton / Changan',
                'sort_order' => 20,
                'models' => [
                    ['name' => 'Gratour Mini Truck', 'price' => 650],
                    ['name' => 'View Transvan', 'price' => 1600],
                    ['name' => 'View Traveller', 'price' => 1600],
                    ['name' => 'Toplander', 'price' => 1500],
                ],
            ],
            [
                'name' => 'Maxus',
                'sort_order' => 21,
                'models' => [
                    ['name' => 'V80', 'price' => 1600],
                    ['name' => 'G10', 'price' => 1600],
                    ['name' => 'T60', 'price' => 1400],
                    ['name' => 'D60', 'price' => 1500],
                ],
            ],
            [
                'name' => 'Owner Jeep (Sarao / Armak / Custom)',
                'sort_order' => 22,
                'models' => [
                    ['name' => 'Owner Type Jeep (Stainless Steel)', 'price' => 1200],
                    ['name' => 'Owner Type Jeep (Galvanized Steel)', 'price' => 1200],
                    ['name' => 'Sarao Owner Jeep', 'price' => 1200],
                    ['name' => 'MD Juan / Armak Jeep', 'price' => 1200],
                ],
            ],
            [
                'name' => 'Other / Unlisted',
                'sort_order' => 23,
                'models' => [
                    ['name' => 'Other Model (Light Car / Sedan)', 'price' => 1100],
                    ['name' => 'Other Model (SUV / MPV)', 'price' => 1500],
                    ['name' => 'Other Model (Van)', 'price' => 1600],
                    ['name' => 'Other Model (Motorcycle)', 'price' => 400],
                    ['name' => 'Other Model (Pick-up)', 'price' => 1400],
                ],
            ],
        ];

        // Clean up legacy erroneous entries
        VehicleModel::where('name', 'Innove')->delete();
        VehicleModel::where('name', 'City')->delete(); // superseded by 'City (Sedan)'
        VehicleModel::where('name', 'Strada')->delete(); // superseded by 'Triton / Strada'
        VehicleModel::where('name', 'Starex')->delete(); // superseded by 'Grand Starex'

        foreach ($brands as $brandData) {
            $brand = VehicleBrand::updateOrCreate(
                ['name' => $brandData['name']],
                [
                    'is_active' => true,
                    'sort_order' => $brandData['sort_order'],
                ],
            );

            foreach ($brandData['models'] as $index => $modelData) {
                VehicleModel::updateOrCreate(
                    [
                        'vehicle_brand_id' => $brand->id,
                        'name' => $modelData['name'],
                    ],
                    [
                        'price' => $modelData['price'],
                        'is_active' => true,
                        'sort_order' => $index + 1,
                    ],
                );
            }
        }
    }
}

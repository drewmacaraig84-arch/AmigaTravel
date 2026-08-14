<?php

namespace Database\Seeders;

use App\Models\AirlineBaggageRule;
use Illuminate\Database\Seeder;

class AirlineBaggageRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rules = [
            // ==========================================
            // LOCAL / DOMESTIC BAGGAGE RATES
            // ==========================================
            // Cebu Pacific (Local)
            [
                'operator' => 'ceb_pac',
                'operator_name' => 'Cebu Pacific',
                'code' => 'Cebu Pacific',
                'logo' => 'CebuPecific-Logo.png',
                'trip_type' => 'local',
                'weight' => '20 kg',
                'weight_kg' => 20,
                'price' => 672,
                'sort_order' => 10,
            ],
            [
                'operator' => 'ceb_pac',
                'operator_name' => 'Cebu Pacific',
                'code' => 'Cebu Pacific',
                'logo' => 'CebuPecific-Logo.png',
                'trip_type' => 'local',
                'weight' => '24 kg',
                'weight_kg' => 24,
                'price' => 1120,
                'sort_order' => 20,
            ],
            [
                'operator' => 'ceb_pac',
                'operator_name' => 'Cebu Pacific',
                'code' => 'Cebu Pacific',
                'logo' => 'CebuPecific-Logo.png',
                'trip_type' => 'local',
                'weight' => '28 kg',
                'weight_kg' => 28,
                'price' => 1568,
                'sort_order' => 30,
            ],
            [
                'operator' => 'ceb_pac',
                'operator_name' => 'Cebu Pacific',
                'code' => 'Cebu Pacific',
                'logo' => 'CebuPecific-Logo.png',
                'trip_type' => 'local',
                'weight' => '32 kg',
                'weight_kg' => 32,
                'price' => 2016,
                'sort_order' => 40,
            ],

            // Philippine Airlines (Local)
            [
                'operator' => 'pal',
                'operator_name' => 'Philippine Airline',
                'code' => 'PAL',
                'logo' => 'Pal-Logo.jfif',
                'trip_type' => 'local',
                'weight' => '15 kg',
                'weight_kg' => 15,
                'price' => 825,
                'sort_order' => 10,
            ],
            [
                'operator' => 'pal',
                'operator_name' => 'Philippine Airline',
                'code' => 'PAL',
                'logo' => 'Pal-Logo.jfif',
                'trip_type' => 'local',
                'weight' => '20 kg',
                'weight_kg' => 20,
                'price' => 1100,
                'sort_order' => 20,
            ],
            [
                'operator' => 'pal',
                'operator_name' => 'Philippine Airline',
                'code' => 'PAL',
                'logo' => 'Pal-Logo.jfif',
                'trip_type' => 'local',
                'weight' => '25 kg',
                'weight_kg' => 25,
                'price' => 1375,
                'sort_order' => 30,
            ],
            [
                'operator' => 'pal',
                'operator_name' => 'Philippine Airline',
                'code' => 'PAL',
                'logo' => 'Pal-Logo.jfif',
                'trip_type' => 'local',
                'weight' => '30 kg',
                'weight_kg' => 30,
                'price' => 1650,
                'sort_order' => 40,
            ],
            [
                'operator' => 'pal',
                'operator_name' => 'Philippine Airline',
                'code' => 'PAL',
                'logo' => 'Pal-Logo.jfif',
                'trip_type' => 'local',
                'weight' => '40 kg',
                'weight_kg' => 40,
                'price' => 2200,
                'sort_order' => 50,
            ],
            [
                'operator' => 'pal',
                'operator_name' => 'Philippine Airline',
                'code' => 'PAL',
                'logo' => 'Pal-Logo.jfif',
                'trip_type' => 'local',
                'weight' => '50 kg',
                'weight_kg' => 50,
                'price' => 2750,
                'sort_order' => 60,
            ],

            // AirAsia (Local)
            [
                'operator' => 'airasia',
                'operator_name' => 'AirAsia',
                'code' => 'AirAsia',
                'logo' => 'AirAsia-Logo.png',
                'trip_type' => 'local',
                'weight' => '15 kg',
                'weight_kg' => 15,
                'price' => 594,
                'sort_order' => 10,
            ],
            [
                'operator' => 'airasia',
                'operator_name' => 'AirAsia',
                'code' => 'AirAsia',
                'logo' => 'AirAsia-Logo.png',
                'trip_type' => 'local',
                'weight' => '20 kg',
                'weight_kg' => 20,
                'price' => 762,
                'sort_order' => 20,
            ],
            [
                'operator' => 'airasia',
                'operator_name' => 'AirAsia',
                'code' => 'AirAsia',
                'logo' => 'AirAsia-Logo.png',
                'trip_type' => 'local',
                'weight' => '25 kg',
                'weight_kg' => 25,
                'price' => 1019,
                'sort_order' => 30,
            ],
            [
                'operator' => 'airasia',
                'operator_name' => 'AirAsia',
                'code' => 'AirAsia',
                'logo' => 'AirAsia-Logo.png',
                'trip_type' => 'local',
                'weight' => '30 kg',
                'weight_kg' => 30,
                'price' => 1333,
                'sort_order' => 40,
            ],
            [
                'operator' => 'airasia',
                'operator_name' => 'AirAsia',
                'code' => 'AirAsia',
                'logo' => 'AirAsia-Logo.png',
                'trip_type' => 'local',
                'weight' => '40 kg',
                'weight_kg' => 40,
                'price' => 1512,
                'sort_order' => 50,
            ],
            [
                'operator' => 'airasia',
                'operator_name' => 'AirAsia',
                'code' => 'AirAsia',
                'logo' => 'AirAsia-Logo.png',
                'trip_type' => 'local',
                'weight' => '50 kg',
                'weight_kg' => 50,
                'price' => 2016,
                'sort_order' => 60,
            ],
            [
                'operator' => 'airasia',
                'operator_name' => 'AirAsia',
                'code' => 'AirAsia',
                'logo' => 'AirAsia-Logo.png',
                'trip_type' => 'local',
                'weight' => '60 kg',
                'weight_kg' => 60,
                'price' => 2262,
                'sort_order' => 70,
            ],

            // ==========================================
            // INTERNATIONAL BAGGAGE RATES
            // ==========================================
            // Philippine Airlines (International)
            [
                'operator' => 'pal',
                'operator_name' => 'Philippine Airline',
                'code' => 'PAL',
                'logo' => 'Pal-Logo.jfif',
                'trip_type' => 'international',
                'weight' => '20 kg',
                'weight_kg' => 20,
                'price' => 3700,
                'sort_order' => 10,
            ],
            [
                'operator' => 'pal',
                'operator_name' => 'Philippine Airline',
                'code' => 'PAL',
                'logo' => 'Pal-Logo.jfif',
                'trip_type' => 'international',
                'weight' => '25 kg',
                'weight_kg' => 25,
                'price' => 4625,
                'sort_order' => 20,
            ],
            [
                'operator' => 'pal',
                'operator_name' => 'Philippine Airline',
                'code' => 'PAL',
                'logo' => 'Pal-Logo.jfif',
                'trip_type' => 'international',
                'weight' => '30 kg',
                'weight_kg' => 30,
                'price' => 5550,
                'sort_order' => 30,
            ],
            [
                'operator' => 'pal',
                'operator_name' => 'Philippine Airline',
                'code' => 'PAL',
                'logo' => 'Pal-Logo.jfif',
                'trip_type' => 'international',
                'weight' => '40 kg',
                'weight_kg' => 40,
                'price' => 7400,
                'sort_order' => 40,
            ],
            [
                'operator' => 'pal',
                'operator_name' => 'Philippine Airline',
                'code' => 'PAL',
                'logo' => 'Pal-Logo.jfif',
                'trip_type' => 'international',
                'weight' => '50 kg',
                'weight_kg' => 50,
                'price' => 9250,
                'sort_order' => 50,
            ],

            // Cebu Pacific (International)
            [
                'operator' => 'ceb_pac',
                'operator_name' => 'Cebu Pacific',
                'code' => 'Cebu Pacific',
                'logo' => 'CebuPecific-Logo.png',
                'trip_type' => 'international',
                'weight' => '20 kg',
                'weight_kg' => 20,
                'price' => 1505,
                'sort_order' => 10,
            ],
            [
                'operator' => 'ceb_pac',
                'operator_name' => 'Cebu Pacific',
                'code' => 'Cebu Pacific',
                'logo' => 'CebuPecific-Logo.png',
                'trip_type' => 'international',
                'weight' => '24 kg',
                'weight_kg' => 24,
                'price' => 2105,
                'sort_order' => 20,
            ],
            [
                'operator' => 'ceb_pac',
                'operator_name' => 'Cebu Pacific',
                'code' => 'Cebu Pacific',
                'logo' => 'CebuPecific-Logo.png',
                'trip_type' => 'international',
                'weight' => '28 kg',
                'weight_kg' => 28,
                'price' => 2705,
                'sort_order' => 30,
            ],
            [
                'operator' => 'ceb_pac',
                'operator_name' => 'Cebu Pacific',
                'code' => 'Cebu Pacific',
                'logo' => 'CebuPecific-Logo.png',
                'trip_type' => 'international',
                'weight' => '32 kg',
                'weight_kg' => 32,
                'price' => 3305,
                'sort_order' => 40,
            ],

            // AirAsia (International)
            [
                'operator' => 'airasia',
                'operator_name' => 'AirAsia',
                'code' => 'AirAsia',
                'logo' => 'AirAsia-Logo.png',
                'trip_type' => 'international',
                'weight' => '20 kg',
                'weight_kg' => 20,
                'price' => 1470,
                'sort_order' => 10,
            ],
            [
                'operator' => 'airasia',
                'operator_name' => 'AirAsia',
                'code' => 'AirAsia',
                'logo' => 'AirAsia-Logo.png',
                'trip_type' => 'international',
                'weight' => '25 kg',
                'weight_kg' => 25,
                'price' => 2220,
                'sort_order' => 20,
            ],
            [
                'operator' => 'airasia',
                'operator_name' => 'AirAsia',
                'code' => 'AirAsia',
                'logo' => 'AirAsia-Logo.png',
                'trip_type' => 'international',
                'weight' => '30 kg',
                'weight_kg' => 30,
                'price' => 2650,
                'sort_order' => 30,
            ],
            [
                'operator' => 'airasia',
                'operator_name' => 'AirAsia',
                'code' => 'AirAsia',
                'logo' => 'AirAsia-Logo.png',
                'trip_type' => 'international',
                'weight' => '40 kg',
                'weight_kg' => 40,
                'price' => 2930,
                'sort_order' => 40,
            ],
            [
                'operator' => 'airasia',
                'operator_name' => 'AirAsia',
                'code' => 'AirAsia',
                'logo' => 'AirAsia-Logo.png',
                'trip_type' => 'international',
                'weight' => '50 kg',
                'weight_kg' => 50,
                'price' => 3730,
                'sort_order' => 50,
            ],
            [
                'operator' => 'airasia',
                'operator_name' => 'AirAsia',
                'code' => 'AirAsia',
                'logo' => 'AirAsia-Logo.png',
                'trip_type' => 'international',
                'weight' => '60 kg',
                'weight_kg' => 60,
                'price' => 4400,
                'sort_order' => 60,
            ],
        ];

        $operatorMap = \App\Models\Operator::pluck('id', 'name')->mapWithKeys(function ($id, $name) {
            return [strtolower($name) => $id];
        })->toArray();
        $operatorMap['philippines airasia'] = $operatorMap[strtolower('Philippines AirAsia')] ?? null;
        $operatorMap['airasia'] = $operatorMap['philippines airasia'] ?? null;
        $operatorMap['pal'] = $operatorMap[strtolower('Philippine Airlines')] ?? null;
        $operatorMap['ceb_pac'] = $operatorMap[strtolower('Cebu Pacific')] ?? null;

        foreach ($rules as $ruleData) {
            $operatorCode = $ruleData['operator'];
            $opId = $operatorMap[$operatorCode] ?? null;

            AirlineBaggageRule::updateOrCreate(
                [
                    'operator' => $ruleData['operator'],
                    'trip_type' => $ruleData['trip_type'],
                    'weight' => $ruleData['weight'],
                ],
                array_merge($ruleData, [
                    'is_active' => true,
                    'operator_id' => $opId,
                ])
            );
        }
    }
}

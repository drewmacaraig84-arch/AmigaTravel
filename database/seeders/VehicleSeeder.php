<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Vehicle;

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ferries = [
            // 2GO
            ['name' => 'MV 2GO Maligaya', 'vehicle_id' => '9263150', 'operator' => '2GO', 'type' => 'ferry', 'is_active' => true],
            ['name' => 'MV 2GO Masagana', 'vehicle_id' => '9263162', 'operator' => '2GO', 'type' => 'ferry', 'is_active' => true],
            ['name' => 'MV 2GO Masinag', 'vehicle_id' => '9258416', 'operator' => '2GO', 'type' => 'ferry', 'is_active' => true],
            ['name' => 'MV 2GO Masigla', 'vehicle_id' => '9193214', 'operator' => '2GO', 'type' => 'ferry', 'is_active' => true],
            ['name' => 'MV 2GO Masikap', 'vehicle_id' => '9258404', 'operator' => '2GO', 'type' => 'ferry', 'is_active' => true],
            ['name' => 'MV St. Michael the Archangel', 'vehicle_id' => '9007328', 'operator' => '2GO', 'type' => 'ferry', 'is_active' => true],
            ['name' => 'MV St. Francis Xavier', 'vehicle_id' => '9007316', 'operator' => '2GO', 'type' => 'ferry', 'is_active' => true],
            ['name' => 'MV St. Ignatius of Loyola', 'vehicle_id' => '9279068', 'operator' => '2GO', 'type' => 'ferry', 'is_active' => true],

            // Starlite
            ['name' => 'MV Starlite Pioneer', 'vehicle_id' => '9766504', 'operator' => 'Starlite', 'type' => 'ferry', 'is_active' => true],
            ['name' => 'MV Starlite Reliance', 'vehicle_id' => '9766516', 'operator' => 'Starlite', 'type' => 'ferry', 'is_active' => true],
            ['name' => 'MV Starlite Eagle', 'vehicle_id' => '9772474', 'operator' => 'Starlite', 'type' => 'ferry', 'is_active' => true],
            ['name' => 'MV Starlite Saturn', 'vehicle_id' => '9766528', 'operator' => 'Starlite', 'type' => 'ferry', 'is_active' => true],
            ['name' => 'MV Starlite Archer', 'vehicle_id' => '9772486', 'operator' => 'Starlite', 'type' => 'ferry', 'is_active' => true],
            ['name' => 'MV Starlite Annapolis', 'vehicle_id' => '9851608', 'operator' => 'Starlite', 'type' => 'ferry', 'is_active' => true],
            ['name' => 'MV Starlite Venus', 'vehicle_id' => '9858371', 'operator' => 'Starlite', 'type' => 'ferry', 'is_active' => true],
            ['name' => 'MV Starlite Stella Maris', 'vehicle_id' => '9852298', 'operator' => 'Starlite', 'type' => 'ferry', 'is_active' => true],
            ['name' => 'MV Starlite Resilience', 'vehicle_id' => '1073561', 'operator' => 'Starlite', 'type' => 'ferry', 'is_active' => true],
        ];

        $airlines = [
            // Philippine Airlines
            ['name' => 'De Havilland Dash 8-Q400', 'vehicle_id' => 'RP-C5901', 'operator' => 'Philippine Airlines', 'type' => 'airline', 'is_active' => true],
            ['name' => 'De Havilland Dash 8-Q400', 'vehicle_id' => 'RP-C5902', 'operator' => 'Philippine Airlines', 'type' => 'airline', 'is_active' => true],
            ['name' => 'De Havilland Dash 8-Q400', 'vehicle_id' => 'RP-C5903', 'operator' => 'Philippine Airlines', 'type' => 'airline', 'is_active' => true],
            ['name' => 'De Havilland Dash 8-Q400', 'vehicle_id' => 'RP-C5905', 'operator' => 'Philippine Airlines', 'type' => 'airline', 'is_active' => true],
            ['name' => 'De Havilland Dash 8-Q400', 'vehicle_id' => 'RP-C5906', 'operator' => 'Philippine Airlines', 'type' => 'airline', 'is_active' => true],
            ['name' => 'De Havilland Dash 8-Q400', 'vehicle_id' => 'RP-C5907', 'operator' => 'Philippine Airlines', 'type' => 'airline', 'is_active' => true],
            ['name' => 'De Havilland Dash 8-Q400', 'vehicle_id' => 'RP-C5910', 'operator' => 'Philippine Airlines', 'type' => 'airline', 'is_active' => true],
            ['name' => 'De Havilland Dash 8-Q400', 'vehicle_id' => 'RP-C5911', 'operator' => 'Philippine Airlines', 'type' => 'airline', 'is_active' => true],
            ['name' => 'De Havilland Dash 8-Q400', 'vehicle_id' => 'RP-C5912', 'operator' => 'Philippine Airlines', 'type' => 'airline', 'is_active' => true],
            ['name' => 'De Havilland Dash 8-Q400', 'vehicle_id' => 'RP-C5915', 'operator' => 'Philippine Airlines', 'type' => 'airline', 'is_active' => true],
            ['name' => 'Airbus A320-200', 'vehicle_id' => 'RP-C8393', 'operator' => 'Philippine Airlines', 'type' => 'airline', 'is_active' => true],
            ['name' => 'Airbus A320-200', 'vehicle_id' => 'RP-C8395', 'operator' => 'Philippine Airlines', 'type' => 'airline', 'is_active' => true],
            ['name' => 'Airbus A320-200', 'vehicle_id' => 'RP-C8397', 'operator' => 'Philippine Airlines', 'type' => 'airline', 'is_active' => true],
            ['name' => 'Airbus A320-200', 'vehicle_id' => 'RP-C8398', 'operator' => 'Philippine Airlines', 'type' => 'airline', 'is_active' => true],
            ['name' => 'Airbus A320-200', 'vehicle_id' => 'RP-C8604', 'operator' => 'Philippine Airlines', 'type' => 'airline', 'is_active' => true],
            ['name' => 'Airbus A320-200', 'vehicle_id' => 'RP-C8606', 'operator' => 'Philippine Airlines', 'type' => 'airline', 'is_active' => true],
            ['name' => 'Airbus A320-200', 'vehicle_id' => 'RP-C8609', 'operator' => 'Philippine Airlines', 'type' => 'airline', 'is_active' => true],
            ['name' => 'Airbus A320-200', 'vehicle_id' => 'RP-C8610', 'operator' => 'Philippine Airlines', 'type' => 'airline', 'is_active' => true],
            ['name' => 'Airbus A320-200', 'vehicle_id' => 'RP-C8611', 'operator' => 'Philippine Airlines', 'type' => 'airline', 'is_active' => true],
            ['name' => 'Airbus A320-200', 'vehicle_id' => 'RP-C8612', 'operator' => 'Philippine Airlines', 'type' => 'airline', 'is_active' => true],
            ['name' => 'Airbus A321-200', 'vehicle_id' => 'RP-C9901', 'operator' => 'Philippine Airlines', 'type' => 'airline', 'is_active' => true],
            ['name' => 'Airbus A321-200', 'vehicle_id' => 'RP-C9902', 'operator' => 'Philippine Airlines', 'type' => 'airline', 'is_active' => true],
            ['name' => 'Airbus A321-200', 'vehicle_id' => 'RP-C9903', 'operator' => 'Philippine Airlines', 'type' => 'airline', 'is_active' => true],
            ['name' => 'Airbus A321-200', 'vehicle_id' => 'RP-C9905', 'operator' => 'Philippine Airlines', 'type' => 'airline', 'is_active' => true],
            ['name' => 'Airbus A321-200', 'vehicle_id' => 'RP-C9906', 'operator' => 'Philippine Airlines', 'type' => 'airline', 'is_active' => true],
            ['name' => 'Airbus A321neo', 'vehicle_id' => 'RP-C9930', 'operator' => 'Philippine Airlines', 'type' => 'airline', 'is_active' => true],
            ['name' => 'Airbus A321neo', 'vehicle_id' => 'RP-C9932', 'operator' => 'Philippine Airlines', 'type' => 'airline', 'is_active' => true],
            ['name' => 'Airbus A321neo', 'vehicle_id' => 'RP-C9933', 'operator' => 'Philippine Airlines', 'type' => 'airline', 'is_active' => true],
            ['name' => 'Airbus A321neo', 'vehicle_id' => 'RP-C9934', 'operator' => 'Philippine Airlines', 'type' => 'airline', 'is_active' => true],
            ['name' => 'Airbus A321neo', 'vehicle_id' => 'RP-C9935', 'operator' => 'Philippine Airlines', 'type' => 'airline', 'is_active' => true],

            // Cebu Pacific
            ['name' => 'ATR 72-600', 'vehicle_id' => 'RP-C7201', 'operator' => 'Cebu Pacific', 'type' => 'airline', 'is_active' => true],
            ['name' => 'ATR 72-600', 'vehicle_id' => 'RP-C7202', 'operator' => 'Cebu Pacific', 'type' => 'airline', 'is_active' => true],
            ['name' => 'ATR 72-600', 'vehicle_id' => 'RP-C7203', 'operator' => 'Cebu Pacific', 'type' => 'airline', 'is_active' => true],
            ['name' => 'ATR 72-600', 'vehicle_id' => 'RP-C7280', 'operator' => 'Cebu Pacific', 'type' => 'airline', 'is_active' => true],
            ['name' => 'ATR 72-600', 'vehicle_id' => 'RP-C7281', 'operator' => 'Cebu Pacific', 'type' => 'airline', 'is_active' => true],
            ['name' => 'ATR 72-600', 'vehicle_id' => 'RP-C7282', 'operator' => 'Cebu Pacific', 'type' => 'airline', 'is_active' => true],
            ['name' => 'ATR 72-600', 'vehicle_id' => 'RP-C7283', 'operator' => 'Cebu Pacific', 'type' => 'airline', 'is_active' => true],
            ['name' => 'ATR 72-600', 'vehicle_id' => 'RP-C7284', 'operator' => 'Cebu Pacific', 'type' => 'airline', 'is_active' => true],
            ['name' => 'ATR 72-600', 'vehicle_id' => 'RP-C7285', 'operator' => 'Cebu Pacific', 'type' => 'airline', 'is_active' => true],
            ['name' => 'Airbus A320-200', 'vehicle_id' => 'RP-C4101', 'operator' => 'Cebu Pacific', 'type' => 'airline', 'is_active' => true],
            ['name' => 'Airbus A320-200', 'vehicle_id' => 'RP-C4102', 'operator' => 'Cebu Pacific', 'type' => 'airline', 'is_active' => true],
            ['name' => 'Airbus A320-200', 'vehicle_id' => 'RP-C4103', 'operator' => 'Cebu Pacific', 'type' => 'airline', 'is_active' => true],
            ['name' => 'Airbus A320neo', 'vehicle_id' => 'RP-C3239', 'operator' => 'Cebu Pacific', 'type' => 'airline', 'is_active' => true],
            ['name' => 'Airbus A320neo', 'vehicle_id' => 'RP-C3281', 'operator' => 'Cebu Pacific', 'type' => 'airline', 'is_active' => true],
            ['name' => 'Airbus A320neo', 'vehicle_id' => 'RP-C3287', 'operator' => 'Cebu Pacific', 'type' => 'airline', 'is_active' => true],
            ['name' => 'Airbus A320neo', 'vehicle_id' => 'RP-C4109', 'operator' => 'Cebu Pacific', 'type' => 'airline', 'is_active' => true],
            ['name' => 'Airbus A320neo', 'vehicle_id' => 'RP-C4110', 'operator' => 'Cebu Pacific', 'type' => 'airline', 'is_active' => true],
            ['name' => 'Airbus A321ceo', 'vehicle_id' => 'RP-C4111', 'operator' => 'Cebu Pacific', 'type' => 'airline', 'is_active' => true],
            ['name' => 'Airbus A321ceo', 'vehicle_id' => 'RP-C4112', 'operator' => 'Cebu Pacific', 'type' => 'airline', 'is_active' => true],
            ['name' => 'Airbus A321ceo', 'vehicle_id' => 'RP-C4113', 'operator' => 'Cebu Pacific', 'type' => 'airline', 'is_active' => true],
            ['name' => 'Airbus A321neo', 'vehicle_id' => 'RP-C4118', 'operator' => 'Cebu Pacific', 'type' => 'airline', 'is_active' => true],
            ['name' => 'Airbus A321neo', 'vehicle_id' => 'RP-C4119', 'operator' => 'Cebu Pacific', 'type' => 'airline', 'is_active' => true],
            ['name' => 'Airbus A321neo', 'vehicle_id' => 'RP-C4120', 'operator' => 'Cebu Pacific', 'type' => 'airline', 'is_active' => true],
            ['name' => 'Airbus A330neo', 'vehicle_id' => 'RP-C3900', 'operator' => 'Cebu Pacific', 'type' => 'airline', 'is_active' => true],
            ['name' => 'Airbus A330neo', 'vehicle_id' => 'RP-C3901', 'operator' => 'Cebu Pacific', 'type' => 'airline', 'is_active' => true],
            ['name' => 'Airbus A330neo', 'vehicle_id' => 'RP-C3902', 'operator' => 'Cebu Pacific', 'type' => 'airline', 'is_active' => true],

            // AirAsia
            ['name' => 'Airbus A320-200', 'vehicle_id' => 'RP-C8945', 'operator' => 'AirAsia', 'type' => 'airline', 'is_active' => true],
            ['name' => 'Airbus A320-200', 'vehicle_id' => 'RP-C8947', 'operator' => 'AirAsia', 'type' => 'airline', 'is_active' => true],
            ['name' => 'Airbus A320-200', 'vehicle_id' => 'RP-C8950', 'operator' => 'Philippine AirAsia', 'type' => 'airline', 'is_active' => true],
            ['name' => 'Airbus A320-200', 'vehicle_id' => 'RP-C8963', 'operator' => 'Philippine AirAsia', 'type' => 'airline', 'is_active' => true],
            ['name' => 'Airbus A320-200', 'vehicle_id' => 'RP-C8966', 'operator' => 'Philippine AirAsia', 'type' => 'airline', 'is_active' => true],
            ['name' => 'Airbus A320-200', 'vehicle_id' => 'RP-C8972', 'operator' => 'Philippine AirAsia', 'type' => 'airline', 'is_active' => true],
            ['name' => 'Airbus A320-200', 'vehicle_id' => 'RP-C8974', 'operator' => 'Philippine AirAsia', 'type' => 'airline', 'is_active' => true],
            ['name' => 'Airbus A320-200', 'vehicle_id' => 'RP-C8975', 'operator' => 'Philippine AirAsia', 'type' => 'airline', 'is_active' => true],
        ];

        $allVehicles = [];
        $now = now();
        foreach (array_merge($ferries, $airlines) as $v) {
            $allVehicles[] = array_merge($v, [
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        Vehicle::upsert(
            $allVehicles,
            ['vehicle_id'],
            ['name', 'operator', 'type', 'is_active', 'updated_at']
        );
    }
}

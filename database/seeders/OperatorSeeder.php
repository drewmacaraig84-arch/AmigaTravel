<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use App\Models\Operator;

class OperatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $operators = [
            [
                'name' => '2GO',
                'mode' => 'ferry',
                'logo' => '2GO-Logo.png',
            ],
            [
                'name' => 'Starlite',
                'mode' => 'ferry',
                'logo' => 'Starlite_Logo.png',
            ],
            [
                'name' => 'Philippine Airlines',
                'mode' => 'airline',
                'logo' => 'Pal-Logo.jfif',
            ],
            [
                'name' => 'Cebu Pacific',
                'mode' => 'airline',
                'logo' => 'CebuPecific-Logo.png',
            ],
            [
                'name' => 'AirAsia',
                'mode' => 'airline',
                'logo' => 'AirAsia-Logo.png',
            ],
        ];

        $storagePath = storage_path('app/public/operators');
        if (!File::exists($storagePath)) {
            File::makeDirectory($storagePath, 0755, true);
        }

        $operatorMap = [];

        foreach ($operators as $op) {
            $logoPath = null;
            if ($op['logo']) {
                $source = public_path('images/' . $op['logo']);
                if (File::exists($source)) {
                    $destination = $storagePath . '/' . $op['logo'];
                    File::copy($source, $destination);
                    $logoPath = 'operators/' . $op['logo'];
                }
            }

            $operator = Operator::updateOrCreate(
                ['name' => $op['name']],
                [
                    'mode' => $op['mode'],
                    'logo_path' => $logoPath,
                    'is_active' => true,
                ]
            );

            $operatorMap[strtolower($op['name'])] = $operator->id;
        }

        // Expanded alias mapping for all variations
        $operatorMap['2go travel'] = $operatorMap['2go'];
        $operatorMap['starlite ferries'] = $operatorMap['starlite'];
        $operatorMap['starlite ferries inc'] = $operatorMap['starlite'];
        $operatorMap['starlite ferries inc.'] = $operatorMap['starlite'];
        $operatorMap['pal'] = $operatorMap[strtolower('Philippine Airlines')];
        $operatorMap['philippine airline'] = $operatorMap[strtolower('Philippine Airlines')];
        $operatorMap['philippines airlines(pal)'] = $operatorMap[strtolower('Philippine Airlines')];
        $operatorMap['philippines airlines (pal)'] = $operatorMap[strtolower('Philippine Airlines')];
        $operatorMap['philippines airlines'] = $operatorMap[strtolower('Philippine Airlines')];
        $operatorMap['philippine airasia'] = $operatorMap[strtolower('AirAsia')];
        $operatorMap['philippines airasia'] = $operatorMap[strtolower('AirAsia')];
        $operatorMap['air asia'] = $operatorMap[strtolower('AirAsia')];
        $operatorMap['ceb_pac'] = $operatorMap[strtolower('Cebu Pacific')];
        $operatorMap['cebu pacific air'] = $operatorMap[strtolower('Cebu Pacific')];
        $operatorMap['cebpac'] = $operatorMap[strtolower('Cebu Pacific')];
        $operatorMap['cebgo'] = $operatorMap[strtolower('Cebu Pacific')];

        // Migrate existing tables
        $tablesToMigrate = [
            'ferry_routes' => 'operator',
            'accommodations' => 'operator',
            'transport_classes' => 'operator',
            'vehicles' => 'operator',
            'airline_baggage_rules' => 'operator',
        ];

        foreach ($tablesToMigrate as $table => $column) {
            $records = DB::table($table)->whereNotNull($column)->whereNull('operator_id')->get();
            foreach ($records as $record) {
                $opName = strtolower(trim($record->$column));
                if (isset($operatorMap[$opName])) {
                    DB::table($table)->where('id', $record->id)->update([
                        'operator_id' => $operatorMap[$opName]
                    ]);
                } else {
                    // Do nothing - do not create stray operators on the fly
                }
            }
        }
    }
}

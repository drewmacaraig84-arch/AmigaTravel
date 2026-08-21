<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AirlineBaggageRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'operator',
        'operator_id',
        'operator_name',
        'code',
        'logo',
        'trip_type',
        'weight',
        'weight_kg',
        'price',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'float',
        'weight_kg' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get formatted rates for booking form grouped by operator.
     */
    public static function getRatesForBooking(string $tripType = 'local'): array
    {
        $rules = self::where('is_active', true)
            ->where('trip_type', $tripType)
            ->orderBy('sort_order')
            ->orderBy('weight_kg')
            ->get();

        if ($rules->isEmpty()) {
            return self::getFallbackRates($tripType);
        }

        $formatted = [];
        foreach ($rules as $rule) {
            if (! isset($formatted[$rule->operator])) {
                $formatted[$rule->operator] = [
                    'name' => $rule->operator_name,
                    'code' => $rule->code,
                    'logo' => $rule->logo ?: '',
                    'options' => [],
                ];
            }

            $formatted[$rule->operator]['options'][] = [
                'weight' => $rule->weight,
                'price' => floatval($rule->price),
            ];
        }

        return $formatted;
    }

    /**
     * Fallback rates if table is empty.
     */
    public static function getFallbackRates(string $tripType = 'local'): array
    {
        if ($tripType === 'international') {
            return [
                'pal' => [
                    'name' => 'Philippine Airlines',
                    'code' => 'PAL',
                    'logo' => 'Pal-Logo.jfif',
                    'options' => [
                        ['weight' => '20 kg', 'price' => 3700],
                        ['weight' => '25 kg', 'price' => 4625],
                        ['weight' => '30 kg', 'price' => 5550],
                        ['weight' => '40 kg', 'price' => 7400],
                        ['weight' => '50 kg', 'price' => 9250],
                    ],
                ],
                'ceb_pac' => [
                    'name' => 'Cebu Pacific',
                    'code' => 'Cebu Pacific',
                    'logo' => 'CebuPecific-Logo.png',
                    'options' => [
                        ['weight' => '20 kg', 'price' => 1505],
                        ['weight' => '24 kg', 'price' => 2105],
                        ['weight' => '28 kg', 'price' => 2705],
                        ['weight' => '32 kg', 'price' => 3305],
                    ],
                ],
                'airasia' => [
                    'name' => 'AirAsia',
                    'code' => 'AirAsia',
                    'logo' => 'AirAsia-Logo.png',
                    'options' => [
                        ['weight' => '20 kg', 'price' => 1470],
                        ['weight' => '25 kg', 'price' => 2220],
                        ['weight' => '30 kg', 'price' => 2650],
                        ['weight' => '40 kg', 'price' => 2930],
                        ['weight' => '50 kg', 'price' => 3730],
                        ['weight' => '60 kg', 'price' => 4400],
                    ],
                ],
            ];
        }

        // Default to Local / Domestic
        return [
            'ceb_pac' => [
                'name' => 'Cebu Pacific',
                'code' => 'Cebu Pacific',
                'logo' => 'CebuPecific-Logo.png',
                'options' => [
                    ['weight' => '20 kg', 'price' => 672],
                    ['weight' => '24 kg', 'price' => 1120],
                    ['weight' => '28 kg', 'price' => 1568],
                    ['weight' => '32 kg', 'price' => 2016],
                ],
            ],
            'pal' => [
                'name' => 'Philippine Airlines',
                'code' => 'PAL',
                'logo' => 'Pal-Logo.jfif',
                'options' => [
                    ['weight' => '15 kg', 'price' => 825],
                    ['weight' => '20 kg', 'price' => 1100],
                    ['weight' => '25 kg', 'price' => 1375],
                    ['weight' => '30 kg', 'price' => 1650],
                    ['weight' => '40 kg', 'price' => 2200],
                    ['weight' => '50 kg', 'price' => 2750],
                ],
            ],
            'airasia' => [
                'name' => 'Philippines AirAsia',
                'code' => 'Philippines AirAsia',
                'logo' => 'AirAsia-Logo.png',
                'options' => [
                    ['weight' => '15 kg', 'price' => 594],
                    ['weight' => '20 kg', 'price' => 762],
                    ['weight' => '25 kg', 'price' => 1019],
                    ['weight' => '30 kg', 'price' => 1333],
                    ['weight' => '40 kg', 'price' => 1512],
                    ['weight' => '50 kg', 'price' => 2016],
                    ['weight' => '60 kg', 'price' => 2262],
                ],
            ],
        ];
    }

    public function operatorRecord()
    {
        return $this->belongsTo(Operator::class, 'operator_id');
    }
}

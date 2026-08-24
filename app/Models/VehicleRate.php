<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class VehicleRate extends Model
{
    protected $fillable = [
        'name',
        'price',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        $bust = fn() => static::bust();
        static::saved($bust);
        static::deleted($bust);
    }

    public static function bust(): void
    {
        try {
            Cache::forget('api:vehicle_rates');
            Cache::forget('api:vehicle_rates_v3');
        } catch (\Throwable) {
            // Ignore cache driver errors
        }
    }
}

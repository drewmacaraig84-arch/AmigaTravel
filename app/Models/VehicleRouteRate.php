<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleRouteRate extends Model
{
    protected $fillable = [
        'vehicle_rate_id',
        'vehicle_brand_id',
        'vehicle_model_id',
        'route_key',
        'origin',
        'destination',
        'price',
        'is_active',
    ];

    protected $casts = [
        'price'     => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // ─────────────────────────────────────────────────────────────
    // Relations
    // ─────────────────────────────────────────────────────────────

    public function vehicleRate(): BelongsTo
    {
        return $this->belongsTo(VehicleRate::class);
    }

    public function vehicleBrand(): BelongsTo
    {
        return $this->belongsTo(VehicleBrand::class);
    }

    public function vehicleModel(): BelongsTo
    {
        return $this->belongsTo(VehicleModel::class);
    }

    // ─────────────────────────────────────────────────────────────
    // Cache busting
    // ─────────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        $bust = fn () => static::bust();
        static::saved($bust);
        static::deleted($bust);
    }

    public static function bust(): void
    {
        try {
            \Illuminate\Support\Facades\Cache::forget('api:vehicle_rates');
            \Illuminate\Support\Facades\Cache::forget('api:vehicle_rates_v3');
            \Illuminate\Support\Facades\Cache::forget('catalog:vehicle_brands_v3');
            \Illuminate\Support\Facades\Cache::forget('web:vehicleRates');
            \Illuminate\Support\Facades\Cache::forget('web:vehicleBrands');
            \Illuminate\Support\Facades\Cache::forget('web:routeVehicleRates');
            for ($b = 1; $b <= 50; $b++) {
                \Illuminate\Support\Facades\Cache::forget("catalog:vehicle_models_v3:{$b}");
            }
        } catch (\Throwable) {
            // Ignore cache driver errors
        }
    }
}

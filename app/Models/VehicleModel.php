<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleModel extends Model
{
    protected $fillable = [
        'vehicle_brand_id',
        'name',
        'price',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(VehicleBrand::class, 'vehicle_brand_id');
    }

    protected static function booted(): void
    {
        $bust = function ($model) {
            static::bust($model->vehicle_brand_id);
        };

        static::saved($bust);
        static::deleted($bust);
    }

    public static function bust(?int $brandId = null): void
    {
        try {
            if ($brandId) {
                \Illuminate\Support\Facades\Cache::forget('catalog:vehicle_models_v3:' . (int) $brandId);
            }
        } catch (\Throwable) {
            // Ignore cache driver errors
        }
    }
}

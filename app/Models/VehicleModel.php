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
            if ($model->vehicle_brand_id) {
                try {
                    \Illuminate\Support\Facades\Cache::forget('catalog:vehicle_models_v3:' . (int) $model->vehicle_brand_id);
                } catch (\Throwable) {
                    // Ignore cache driver errors
                }
            }
        };

        static::saved($bust);
        static::deleted($bust);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Discount extends Model
{
    protected $fillable = [
        'name',
        'percentage',
    ];

    public function passengers(): HasMany
    {
        return $this->hasMany(Passenger::class);
    }

    protected static function booted(): void
    {
        $bust = fn() => static::bust();
        static::saved($bust);
        static::deleted($bust);
    }

    public static function bust(): void
    {
        try {
            Cache::forget('discounts:all:keyed');
            Cache::forget('api:discounts');
            Cache::forget('catalog:discounts_v3');
        } catch (\Throwable) {
            // Ignore cache driver errors
        }
    }
}

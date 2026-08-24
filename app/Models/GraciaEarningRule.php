<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class GraciaEarningRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'spend_threshold_centavos',
        'points_awarded',
        'is_active',
        'starts_at',
        'ends_at',
        'internal_notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'spend_threshold_centavos' => 'integer',
        'points_awarded' => 'integer',
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
            Cache::forget('gracia:active_rule');
        } catch (\Throwable) {
            // Ignore cache driver errors
        }
    }
}

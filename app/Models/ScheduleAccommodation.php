<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleAccommodation extends Model
{
    protected $fillable = [
        'schedule_id',
        'name',
        'rate_code',
        'description',
        'price',
        'tickets_available',
        'has_bed',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'has_bed' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    protected static function booted(): void
    {
        $bust = fn() => Schedule::bust();
        static::saved($bust);
        static::deleted($bust);
    }
}

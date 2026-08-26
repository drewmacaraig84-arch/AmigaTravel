<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleTransportClass extends Pivot
{
    protected $table = 'schedule_transport_class';
    
    public $incrementing = true;

    protected $fillable = [
        'schedule_id',
        'transport_class_id',
        'description',
        'additional_price',
        'tickets_available',
        'has_bed',
        'is_active',
        'is_promo',
        'rate_type',
        'rate_code',
        'promo_duration_start',
        'promo_duration_end',
    ];

    protected $casts = [
        'additional_price' => 'decimal:2',
        'has_bed' => 'boolean',
        'is_active' => 'boolean',
        'is_promo' => 'boolean',
        'rate_type' => 'string',
        'promo_duration_start' => 'datetime',
        'promo_duration_end' => 'datetime',
    ];

    public function isRegular(): bool
    {
        return ($this->rate_type ?? 'regular') === 'regular';
    }

    public function isPromo(): bool
    {
        return ($this->rate_type ?? 'regular') === 'promotional' || (bool) $this->is_promo;
    }

    public function isSuperPromo(): bool
    {
        return ($this->rate_type ?? 'regular') === 'super_promotional';
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function transportClass(): BelongsTo
    {
        return $this->belongsTo(TransportClass::class);
    }

    public function getEffectivePrice(): float
    {
        if ($this->additional_price !== null) {
            return (float) $this->additional_price;
        }

        return (float) ($this->transportClass?->effective_price ?? $this->transportClass?->price ?? 0.0);
    }

    /**
     * Resolve ScheduleTransportClass by either schedule_transport_class primary ID (pivot_id)
     * or transport_class_id for a given schedule.
     */
    public static function resolveForSchedule(?int $scheduleId, ?int $classOrPivotId): ?self
    {
        if (! $scheduleId || ! $classOrPivotId) {
            return null;
        }

        // Try by pivot primary key ID first
        $stc = static::with('transportClass')->find($classOrPivotId);
        if ($stc && (int) $stc->schedule_id === (int) $scheduleId) {
            return $stc;
        }

        // Fallback: lookup by transport_class_id
        return static::with('transportClass')
            ->where('schedule_id', $scheduleId)
            ->where('transport_class_id', $classOrPivotId)
            ->first();
    }

    protected static function booted(): void
    {
        $bust = fn() => Schedule::bust();
        static::saved($bust);
        static::deleted($bust);
    }
}


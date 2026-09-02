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
        'promo_type',
    ];

    protected $casts = [
        'additional_price' => 'decimal:2',
        'has_bed' => 'boolean',
        'is_active' => 'boolean',
        'is_promo' => 'boolean',
        'rate_type' => 'string',
        'promo_type' => 'string',
        'promo_duration_start' => 'datetime',
        'promo_duration_end' => 'datetime',
    ];

    public function isRegular(): bool
    {
        return $this->getEffectiveRateType() === 'regular';
    }

    public function isPromo(): bool
    {
        $rate = $this->getEffectiveRateType();
        return $rate === 'promotional' || (bool) $this->is_promo;
    }

    public function isSuperPromo(): bool
    {
        return $this->getEffectiveRateType() === 'super_promotional';
    }

    public function isPermanentPromo(): bool
    {
        return ($this->promo_type ?? 'temporary') === 'permanent';
    }

    public function isTemporaryPromo(): bool
    {
        return ($this->promo_type ?? 'temporary') === 'temporary';
    }

    public function isPromoActive(): bool
    {
        $rate = $this->rate_type ?? 'regular';
        if (! in_array($rate, ['promotional', 'super_promotional'], true)) {
            return false;
        }

        $now = now();
        if ($this->promo_duration_start && $now->isBefore($this->promo_duration_start)) {
            return false;
        }
        if ($this->promo_duration_end && $now->isAfter($this->promo_duration_end)) {
            return false;
        }

        return true;
    }

    public function isPromoExpired(): bool
    {
        $rate = $this->rate_type ?? 'regular';
        if (! in_array($rate, ['promotional', 'super_promotional'], true)) {
            return false;
        }

        return $this->promo_duration_end ? now()->isAfter($this->promo_duration_end) : false;
    }

    /**
     * Get the dynamically evaluated rate_type.
     * If a temporary promo has expired, it automatically reverts to 'regular'.
     */
    public function getEffectiveRateType(): string
    {
        $rate = $this->rate_type ?? 'regular';
        if (! in_array($rate, ['promotional', 'super_promotional'], true)) {
            return 'regular';
        }

        $now = now();
        // If before start date: not yet active
        if ($this->promo_duration_start && $now->isBefore($this->promo_duration_start)) {
            return 'regular';
        }

        // If after end date:
        if ($this->promo_duration_end && $now->isAfter($this->promo_duration_end)) {
            if ($this->isTemporaryPromo()) {
                return 'regular';
            }
        }

        return $rate;
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


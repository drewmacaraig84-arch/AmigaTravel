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
        'original_price',
        'tickets_available',
        'promo_tickets_available',
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
        'original_price' => 'decimal:2',
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
        // When a temporary promo has expired, automatically fall back to the pre-promo regular price
        if ($this->isTemporaryPromo() && $this->isPromoExpired()) {
            if ($this->original_price !== null) {
                return (float) $this->original_price;
            }
            $basePrice = (float) ($this->transportClass?->effective_price ?? $this->transportClass?->price ?? 0.0);
            return $basePrice > 0 ? $basePrice : (float) ($this->additional_price ?? 0.0);
        }

        if ($this->additional_price !== null) {
            return (float) $this->additional_price;
        }

        return (float) ($this->transportClass?->effective_price ?? $this->transportClass?->price ?? 0.0);
    }

    /**
     * Revert this promotional class back to regular fare in the database,
     * restoring its pre-promo original_price.
     */
    public function revertToRegular(): bool
    {
        $basePrice = (float) ($this->transportClass?->price ?? 0);
        $restoredPrice = $this->original_price !== null
            ? (float) $this->original_price
            : ($basePrice > 0 ? $basePrice : (float) $this->additional_price);

        $updated = $this->update([
            'rate_type'               => 'regular',
            'is_promo'                => false,
            'additional_price'        => $restoredPrice,
            'original_price'          => null,
            'promo_type'              => null,
            'promo_duration_start'    => null,
            'promo_duration_end'      => null,
            'promo_tickets_available' => null,
        ]);

        if ($updated) {
            Schedule::bust();
        }

        return $updated;
    }

    /**
     * Revert all temporary promos that have expired by end date or exhausted promo tickets.
     * Restores them to their original pre-promo regular price.
     */
    public static function revertExpiredPromos(): int
    {
        $now = now();

        $expired = static::with('transportClass')
            ->where(function ($query) use ($now) {
                // Temporary promos whose duration end has passed
                $query->where(function ($q) use ($now) {
                    $q->whereIn('rate_type', ['promotional', 'super_promotional'])
                      ->where(function ($sub) {
                          $sub->where('promo_type', 'temporary')
                              ->orWhereNull('promo_type');
                      })
                      ->whereNotNull('promo_duration_end')
                      ->where('promo_duration_end', '<=', $now);
                })
                // Promos whose tickets available have reached 0
                ->orWhere(function ($q) {
                    $q->whereIn('rate_type', ['promotional', 'super_promotional'])
                      ->whereNotNull('promo_tickets_available')
                      ->where('promo_tickets_available', '<=', 0);
                });
            })
            ->get();

        $count = 0;
        foreach ($expired as $stc) {
            if ($stc->revertToRegular()) {
                $count++;
            }
        }

        return $count;
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
            if ($stc->isTemporaryPromo() && ($stc->isPromoExpired() || ($stc->promo_tickets_available !== null && $stc->promo_tickets_available <= 0))) {
                $stc->revertToRegular();
                $stc->refresh();
            }
            return $stc;
        }

        // Fallback: lookup by transport_class_id
        $stc = static::with('transportClass')
            ->where('schedule_id', $scheduleId)
            ->where('transport_class_id', $classOrPivotId)
            ->first();

        if ($stc && $stc->isTemporaryPromo() && ($stc->isPromoExpired() || ($stc->promo_tickets_available !== null && $stc->promo_tickets_available <= 0))) {
            $stc->revertToRegular();
            $stc->refresh();
        }

        return $stc;
    }

    protected static function booted(): void
    {
        $bust = fn() => Schedule::bust();
        static::saved($bust);
        static::deleted($bust);
    }
}


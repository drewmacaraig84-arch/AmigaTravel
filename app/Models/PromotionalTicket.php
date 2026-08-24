<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromotionalTicket extends Model
{
    protected $fillable = [
        'schedule_id',
        'promo_price',
        'quantity_available',
        'quantity_sold',
        'landscape_image',
        'portrait_image',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'promo_price' => 'decimal:2',
        'quantity_available' => 'integer',
        'quantity_sold' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function promoPassengers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Passenger::class, 'promotional_ticket_id');
    }

    public function scopeActiveAndAvailable(Builder $query): Builder
    {
        $now = Carbon::now();

        return $query->where('is_active', true)
            ->where('starts_at', '<=', $now)
            ->where('ends_at', '>=', $now)
            ->whereColumn('quantity_sold', '<', 'quantity_available');
    }

    public function getRemainingQuantityAttribute(): int
    {
        return max(0, $this->quantity_available - $this->quantity_sold);
    }

    public function getIsExpiredAttribute(): bool
    {
        return Carbon::now()->greaterThan($this->ends_at);
    }

    public function getIsSoldOutAttribute(): bool
    {
        return $this->remaining_quantity <= 0;
    }

    public function getStatusLabelAttribute(): string
    {
        if (! $this->is_active) {
            return 'Inactive';
        }

        if ($this->is_sold_out) {
            return 'Sold Out';
        }

        if ($this->is_expired) {
            return 'Expired';
        }

        if (Carbon::now()->lessThan($this->starts_at)) {
            return 'Upcoming';
        }

        return 'Active';
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
            \Illuminate\Support\Facades\Cache::forget('api:promotions');
            \Illuminate\Support\Facades\Cache::forget('website_settings:promotions');
            \Illuminate\Support\Facades\Cache::flush();
        } catch (\Throwable) {
            // Ignore cache driver errors
        }
    }
}

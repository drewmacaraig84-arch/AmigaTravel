<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Tour extends Model
{
    protected $fillable = [
        'tour_name', 'promo', 'country', 'destinations', 'duration',
        'duration_days', 'price_per_pax', 'airline', 'origin',
        'destination', 'mode', 'hotel', 'inclusions', 'exclusions',
        'highlights', 'day1', 'day2', 'day3', 'day4', 'day5', 'day6',
        'meals', 'hand_carry', 'check_in_baggage', 'tour_guide',
        'travel_insurance', 'remarks', 'image', 'is_active', 'sort_order', 'is_international',
    ];

    protected $attributes = [
        'destinations' => '',
    ];

    protected $casts = [
        'duration_days' => 'integer',
        'price_per_pax' => 'decimal:2',
        'is_active' => 'boolean',
        'is_international' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted()
    {
        // Guard: `destinations` is NOT NULL in the DB schema.
        // Coerce null → '' so any code path (Filament, CSV import, API) never
        // triggers the integrity constraint violation.
        static::creating(function (self $tour) {
            if (is_null($tour->destinations)) {
                $tour->destinations = '';
            }
        });

        static::saving(function (self $tour) {
            if (is_null($tour->destinations)) {
                $tour->destinations = '';
            }
        });

        static::saved(fn() => static::bust());
        static::deleted(fn() => static::bust());
    }

    public static function bust(): void
    {
        try {
            Cache::forget('api:tours');
            Cache::forget('api:tours:all');
        } catch (\Throwable) {
            // Ignore cache driver errors
        }
    }

    public function scopeOrdered($query)
    {
        $query->orderBy('country', 'asc')
              ->orderBy('sort_order', 'asc')
              ->orderBy('tour_name', 'asc');
    }

    public function dates(): HasMany
    {
        return $this->hasMany(TourDate::class)->orderBy('date');
    }

    public function activeDates(): HasMany
    {
        return $this->dates()->where('is_active', true)->orderBy('date');
    }

    // Helper to get available dates as array of ISO strings
    public function getAvailableDatesAttribute(): array
    {
        return $this->activeDates->pluck('date')->map(fn($d) => $d->format('Y-m-d'))->toArray();
    }

    // Accessor for image to get the full URL
    protected function image(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (!$value) {
                    return null;
                }
                // Check if it's already a full URL
                if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
                    return $value;
                }
                // Otherwise, it's a stored file path
                return storage_asset_path($value);
            },
        );
    }
}

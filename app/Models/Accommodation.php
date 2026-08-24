<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Accommodation extends Model
{
    protected $fillable = [
        'name',
        'destination',
        'operator',
        'operator_id',
        'description',
        'amenities',
        'price',
        'images',
        'is_active',
    ];

    protected $casts = [
        'images' => 'array',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function bookings(): BelongsToMany
    {
        return $this->belongsToMany(Booking::class)
            ->withPivot('price')
            ->withTimestamps();
    }

    /**
     * First image for use as a card cover / thumbnail.
     */
    public function getCoverImageAttribute(): ?string
    {
        return storage_asset_path($this->images[0] ?? null);
    }

    public function operatorRecord()
    {
        return $this->belongsTo(Operator::class, 'operator_id');
    }

    protected static function booted(): void
    {
        $bust = function () {
            static::bust();
        };

        static::saved($bust);
        static::deleted($bust);
    }

    public static function bust(): void
    {
        try {
            \Illuminate\Support\Facades\Cache::forget('api:accommodations:all');
            \Illuminate\Support\Facades\Cache::forget('api:accommodations_v3');
            $destinations = static::query()->distinct()->pluck('destination')->filter();
            foreach ($destinations as $dest) {
                \Illuminate\Support\Facades\Cache::forget('api:accommodations:' . strtolower(trim($dest)));
            }
        } catch (\Throwable) {
            // Ignore during setup/migrations/cache driver errors
        }
    }
}

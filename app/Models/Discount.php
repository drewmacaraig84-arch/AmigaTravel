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

    /**
     * Check if this discount is specifically for Senior Citizens.
     */
    public function isSenior(): bool
    {
        return str_contains(strtolower($this->name ?? ''), 'senior');
    }

    /**
     * Compute the discount amount for a given gross fare.
     * For Senior Citizen: 20% discount first on gross, then 12% VAT removal.
     * For other discounts: standard percentage deduction.
     */
    public function computeDiscountAmount(float $grossAmount): float
    {
        if ($grossAmount <= 0) {
            return 0.0;
        }

        if ($this->isSenior()) {
            // Step 1: 20% discount first
            $discountedRate = $grossAmount * 0.80;
            // Step 2: Remove 12% VAT
            $netSeniorFare = $discountedRate / 1.12;
            // Discount amount is the difference from gross
            return round(max(0.0, $grossAmount - $netSeniorFare), 2);
        }

        return round($grossAmount * ((float) $this->percentage / 100), 2);
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

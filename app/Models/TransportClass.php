<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TransportClass extends Model
{
    protected $fillable = [
        'operator',
        'operator_id',
        'code',
        'name',
        'mode',
        'description',
        'price',
        'is_on_sale',
        'sale_price',
        'sort_order',
        'images',
        'is_active',
    ];

    protected $casts = [
        'images' => 'array',
        'is_active' => 'boolean',
        'is_on_sale' => 'boolean',
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function bookings(): BelongsToMany
    {
        return $this->belongsToMany(Booking::class, 'booking_transport_class')
            ->withPivot('price')
            ->withTimestamps();
    }

    public function schedules(): BelongsToMany
    {
        return $this->belongsToMany(Schedule::class, 'schedule_transport_class')
            ->withPivot('additional_price', 'tickets_available', 'description', 'has_bed', 'is_active', 'is_promo', 'rate_code', 'promo_duration_start', 'promo_duration_end')
            ->withTimestamps();
    }

    public function getEffectivePriceAttribute(): float
    {
        return $this->is_on_sale ? floatval($this->sale_price ?? 0) : floatval($this->price);
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
}

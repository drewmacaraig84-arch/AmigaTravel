<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Passenger extends Model
{
    protected $fillable = [
        'booking_id',
        'type',
        'name',
        'birthdate',
        'discount_id',
        'school_name',
        'id_number',
        'id_image_front',
        'id_image_back',
        'seat_number',
        'seat_row',
        'seat_section',
        'return_seat_number',
        'return_seat_row',
        'return_seat_section',
        'promotional_ticket_id',
        'is_promo',
        'promo_price',
    ];

    protected $with = ['discount'];
    protected $appends = ['id_image_front_url', 'id_image_back_url'];

    protected $casts = [
        'is_promo'    => 'boolean',
        'promo_price' => 'decimal:2',
        'birthdate'   => 'date:Y-m-d',
    ];

    public function getIdImageFrontUrlAttribute(): ?string
    {
        if (! $this->id_image_front) {
            return null;
        }
        if (str_starts_with($this->id_image_front, 'http') || str_starts_with($this->id_image_front, 'data:image')) {
            return $this->id_image_front;
        }
        return storage_asset_path($this->id_image_front);
    }

    public function getIdImageBackUrlAttribute(): ?string
    {
        if (! $this->id_image_back) {
            return null;
        }
        if (str_starts_with($this->id_image_back, 'http') || str_starts_with($this->id_image_back, 'data:image')) {
            return $this->id_image_back;
        }
        return storage_asset_path($this->id_image_back);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    public function promotionalTicket(): BelongsTo
    {
        return $this->belongsTo(PromotionalTicket::class);
    }
}

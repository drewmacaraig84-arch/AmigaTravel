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
        'rate_code',
        'promo_duration_start',
        'promo_duration_end',
    ];

    protected $casts = [
        'additional_price' => 'decimal:2',
        'has_bed' => 'boolean',
        'is_active' => 'boolean',
        'is_promo' => 'boolean',
        'promo_duration_start' => 'datetime',
        'promo_duration_end' => 'datetime',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function transportClass(): BelongsTo
    {
        return $this->belongsTo(TransportClass::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourDate extends Model
{
    protected $fillable = [
        'tour_id', 'date', 'price', 'is_active',
    ];

    protected $casts = [
        'date' => 'date',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    protected static function booted(): void
    {
        $bust = fn() => Tour::bust();
        static::saved($bust);
        static::deleted($bust);
    }
}

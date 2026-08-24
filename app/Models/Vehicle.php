<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    protected $fillable = [
        'type',
        'name',
        'vehicle_id',
        'operator',
        'operator_id',
        'description',
        'capacity',
        'is_active',
    ];

    protected static function booted(): void
    {
        static::saved(function () {
            \App\Models\Schedule::bust();
        });

        static::deleted(function () {
            \App\Models\Schedule::bust();
        });
    }

    protected $casts = [
        'is_active' => 'boolean',
        'capacity' => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where(
            $query->getModel()->qualifyColumn('is_active'),
            true,
        );
    }

    public function ferryRoutes(): HasMany
    {
        return $this->hasMany(FerryRoute::class);
    }

    public function getTypeLabel(): string
    {
        return match ($this->type) {
            'ferry' => 'Ferry',
            'airline' => 'Airline',
            default => ucfirst($this->type),
        };
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->name} ({$this->vehicle_id})";
    }

    public static function ferries()
    {
        return static::where('type', 'ferry')->active();
    }

    public static function airlines()
    {
        return static::where('type', 'airline')->active();
    }

    public function operatorRecord()
    {
        return $this->belongsTo(Operator::class, 'operator_id');
    }
}

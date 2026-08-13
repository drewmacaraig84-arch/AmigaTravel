<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FerryRoute extends Model
{
    protected $fillable = [
        'origin',
        'destination',
        'is_active',
        'mode',
        'trip_type',
        'operator',
        'operator_id',
        'vehicle_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where(
            $query->getModel()->qualifyColumn('is_active'),
            true,
        );
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function getLabelAttribute(): string
    {
        $parts = ["{$this->origin} → {$this->destination}"];

        // Show vehicle name if available
        if ($this->vehicle) {
            $parts[] = $this->vehicle->full_name;
        } elseif (! empty($this->operator)) {
            $parts[] = $this->operator;
        }

        if (! empty($this->mode)) {
            $parts[] = ucfirst($this->mode);
        }

        return implode(' • ', $parts);
    }

    public static function activeOrigins(?string $mode = null, ?string $operator = null): array
    {
        return static::query()
            ->active()
            ->when($mode, function ($query, $mode) {
                $query->where('mode', $mode);
            })
            ->when($operator, function ($query, $operator) {
                $query->where('operator', $operator);
            })
            ->select('origin')
            ->distinct()
            ->orderBy('origin')
            ->pluck('origin')
            ->values()
            ->all();
    }

    public static function activeDestinationsFor(string $origin, ?string $mode = null, ?string $operator = null): array
    {
        return static::query()
            ->active()
            ->where('origin', $origin)
            ->when($mode, function ($query, $mode) {
                $query->where('mode', $mode);
            })
            ->when($operator, function ($query, $operator) {
                $query->where('operator', $operator);
            })
            ->select('destination')
            ->distinct()
            ->orderBy('destination')
            ->pluck('destination')
            ->values()
            ->all();
    }

    public static function activeOperatorsFor(?string $mode = null): array
    {
        return static::query()
            ->active()
            ->when($mode, function ($query, $mode) {
                $query->where('mode', $mode);
            })
            ->whereNotNull('operator')
            ->where('operator', '!=', '')
            ->select('operator')
            ->distinct()
            ->orderBy('operator')
            ->pluck('operator')
            ->values()
            ->all();
    }

    protected static function booted(): void
    {
        static::saved(function () {
            \Illuminate\Support\Facades\Cache::flush();
        });

        static::deleted(function () {
            \Illuminate\Support\Facades\Cache::flush();
        });
    }

    public static function operators(?string $mode = null): array
    {
        return static::query()
            ->active()
            ->when($mode, fn ($query) => $query->where('mode', $mode))
            ->whereNotNull('operator')
            ->where('operator', '!=', '')
            ->select('operator')
            ->distinct()
            ->orderBy('operator')
            ->pluck('operator')
            ->values()
            ->all();
    }

    public static function scheduleOrigins(?string $mode = null, ?string $operator = null): array
    {
        $cacheKey = 'ferry_route:schedule_origins_v4:' . md5(serialize([$mode, $operator]));

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addHours(2), function () use ($mode, $operator) {
            return static::query()
                ->active()
                ->when($mode, fn ($query) => $query->where('mode', $mode))
                ->when($operator, function ($query, $operator) {
                    $query->where(function ($q) use ($operator) {
                        $q->where('operator', $operator)
                          ->orWhereHas('vehicle', fn ($vq) => $vq->where('operator', $operator));
                    });
                })
                ->whereHas('schedules', fn ($q) => $q->active())
                ->select('origin')
                ->distinct()
                ->orderBy('origin')
                ->pluck('origin')
                ->values()
                ->all();
        });
    }

    public static function scheduleDestinationsFor(string $origin, ?string $mode = null, ?string $operator = null, bool $requireReturn = false): array
    {
        $cacheKey = 'ferry_route:schedule_destinations_v4:' . md5(serialize([$origin, $mode, $operator, $requireReturn]));

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addHours(2), function () use ($origin, $mode, $operator, $requireReturn) {
            $query = static::query()
                ->active()
                ->where('origin', $origin)
                ->when($mode, fn ($query) => $query->where('mode', $mode))
                ->when($operator, function ($query, $operator) {
                    $query->where(function ($q) use ($operator) {
                        $q->where('operator', $operator)
                          ->orWhereHas('vehicle', fn ($vq) => $vq->where('operator', $operator));
                    });
                })
                ->whereHas('schedules', fn ($q) => $q->active());

            if ($requireReturn) {
                $query->whereExists(function ($sub) use ($origin, $mode, $operator) {
                    $sub->selectRaw('1')
                        ->from((new static)->getTable() . ' as return_routes')
                        ->join('schedules as return_schedules', 'return_routes.id', '=', 'return_schedules.ferry_route_id')
                        ->whereColumn('return_routes.origin', (new static)->getTable() . '.destination')
                        ->whereColumn('return_routes.destination', (new static)->getTable() . '.origin')
                        ->where('return_routes.is_active', true)
                        ->where('return_schedules.is_active', true)
                        ->when($mode, fn ($q) => $q->where('return_routes.mode', $mode))
                        ->when($operator, function ($q) use ($operator) {
                            $q->where(function ($opq) use ($operator) {
                                $opq->where('return_routes.operator', $operator)
                                    ->orWhereExists(function ($vsub) use ($operator) {
                                        $vsub->selectRaw('1')
                                            ->from('vehicles')
                                            ->whereColumn('vehicles.id', 'return_routes.vehicle_id')
                                            ->where('vehicles.operator', $operator);
                                    });
                            });
                        });
                });
            }

            return $query
                ->select('destination')
                ->distinct()
                ->orderBy('destination')
                ->pluck('destination')
                ->values()
                ->all();
        });
    }

    public static function scheduleOperatorsFor(?string $mode = null): array
    {
        $cacheKey = 'ferry_route:schedule_operators_v4:' . md5(serialize([$mode]));

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addHours(2), function () use ($mode) {
            $routeOperators = static::query()
                ->active()
                ->when($mode, fn ($query) => $query->where('mode', $mode))
                ->whereNotNull('operator')
                ->where('operator', '!=', '')
                ->whereHas('schedules', fn ($q) => $q->active())
                ->pluck('operator')
                ->all();

            $vehicleOperators = static::query()
                ->active()
                ->when($mode, fn ($query) => $query->where('mode', $mode))
                ->whereHas('vehicle', fn ($q) => $q->whereNotNull('operator')->where('operator', '!=', ''))
                ->whereHas('schedules', fn ($q) => $q->active())
                ->get()
                ->map(fn ($r) => $r->vehicle?->operator)
                ->filter()
                ->all();

            return collect(array_merge($routeOperators, $vehicleOperators))
                ->filter()
                ->unique()
                ->sort()
                ->values()
                ->all();
        });
    }

    public static function hasBidirectionalSchedules(string $origin, string $destination, ?string $mode = null, ?string $operator = null): bool
    {
        $cacheKey = 'ferry_route:bidirectional:' . md5(serialize([$origin, $destination, $mode, $operator]));

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addHours(2), function () use ($origin, $destination, $mode, $operator) {
            $hasForward = static::query()
                ->active()
                ->where('origin', $origin)
                ->where('destination', $destination)
                ->when($mode, fn ($q) => $q->where('mode', $mode))
                ->when($operator, fn ($q) => $q->where('operator', $operator))
                ->whereHas('schedules', function ($q) {
                    $q->active();
                })
                ->exists();

            if (! $hasForward) {
                return false;
            }

            return static::query()
                ->active()
                ->where('origin', $destination)
                ->where('destination', $origin)
                ->when($mode, fn ($q) => $q->where('mode', $mode))
                ->when($operator, fn ($q) => $q->where('operator', $operator))
                ->whereHas('schedules', function ($q) {
                    $q->active();
                })
                ->exists();
        });
    }
}

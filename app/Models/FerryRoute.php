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
        'is_international',
        'trip_type',
        'operator',
        'operator_id',
        'vehicle_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_international' => 'boolean',
    ];

    public function isInternational(): bool
    {
        if ($this->is_international) {
            return true;
        }
        if ($this->mode !== 'airline') {
            return false;
        }
        $domesticPorts = ['manila', 'batangas', 'calapan', 'caticlan', 'boracay', 'boracay (caticlan)', 'cebu', 'davao', 'roxas', 'puerto princesa', 'el nido', 'coron', 'bacolod', 'iloilo', 'tagbilaran', 'bohol', 'siargao', 'zamboanga', 'general santos', 'clark', 'laoag', 'legazpi', 'dumaguete', 'tacloban', 'cagayan de oro', 'butuan', 'ozamiz', 'dipolog', 'pagadian', 'surigao', 'tandag', 'camiguin', 'batanes', 'basco', 'busuanga', 'san jose'];
        return !in_array(strtolower(trim($this->origin ?? '')), $domesticPorts, true)
            || !in_array(strtolower(trim($this->destination ?? '')), $domesticPorts, true);
    }

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

    public function operatorRecord(): BelongsTo
    {
        return $this->belongsTo(Operator::class, 'operator_id');
    }

    public function getLabelAttribute(): string
    {
        $parts = ["{$this->origin} → {$this->destination}"];

        // Show vehicle name if available
        if ($this->relationLoaded('vehicle') && $this->vehicle) {
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
        return \App\Models\Operator::query()
            ->where('is_active', true)
            ->when($mode, function ($query, $mode) {
                if ($mode === 'ferry' || $mode === 'airline') {
                    $query->where('mode', $mode);
                }
            })
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }

    public function getOperatorDisplayNameAttribute(): string
    {
        return $this->operatorRecord?->name 
            ?? $this->operator 
            ?? $this->vehicle?->operatorRecord?->name 
            ?? $this->vehicle?->operator 
            ?? '—';
    }

    protected static function booted(): void
    {
        static::saving(function ($route) {
            if ($route->operator_id) {
                $operator = \App\Models\Operator::find($route->operator_id);
                if ($operator) {
                    $route->operator = $operator->name;
                }
            } elseif ($route->operator) {
                $operator = \App\Models\Operator::where('name', $route->operator)->first();
                if (! $operator) {
                    $opName = strtolower(trim($route->operator));
                    if (str_contains($opName, 'starlite')) {
                        $operator = \App\Models\Operator::where('name', 'like', '%Starlite%')->first();
                    } elseif (str_contains($opName, 'pal') || str_contains($opName, 'philippine')) {
                        $operator = \App\Models\Operator::where('name', 'like', '%Philippine%')->first();
                    } elseif (str_contains($opName, 'cebu') || str_contains($opName, 'cebpac')) {
                        $operator = \App\Models\Operator::where('name', 'like', '%Cebu%')->first();
                    } elseif (str_contains($opName, 'airasia')) {
                        $operator = \App\Models\Operator::where('name', 'like', '%AirAsia%')->first();
                    } elseif (str_contains($opName, '2go')) {
                        $operator = \App\Models\Operator::where('name', 'like', '%2GO%')->first();
                    }
                }
                if ($operator) {
                    $route->operator_id = $operator->id;
                    $route->operator = $operator->name;
                }
            }

            if (! $route->operator_id && $route->vehicle_id) {
                $vehicle = \App\Models\Vehicle::find($route->vehicle_id);
                if ($vehicle && $vehicle->operator_id) {
                    $route->operator_id = $vehicle->operator_id;
                    if (empty($route->operator)) {
                        $route->operator = optional($vehicle->operatorRecord)->name ?? $vehicle->operator;
                    }
                }
            }
        });

        static::saved(function () {
            \App\Models\Schedule::bust();
        });

        static::deleted(function () {
            \App\Models\Schedule::bust();
        });
    }

    public static function operators(?string $mode = null): array
    {
        return \App\Models\Operator::query()
            ->where('is_active', true)
            ->when($mode, function ($query, $mode) {
                if ($mode === 'ferry' || $mode === 'airline') {
                    $query->where('mode', $mode);
                }
            })
            ->orderByRaw("CASE 
                WHEN LOWER(name) LIKE '%starlite%' THEN 1 
                WHEN LOWER(name) LIKE '%2go%' THEN 2 
                WHEN LOWER(name) LIKE '%cebu%' THEN 3 
                WHEN LOWER(name) LIKE '%philippine%' OR LOWER(name) LIKE '%pal%' THEN 4 
                WHEN LOWER(name) LIKE '%airasia%' THEN 5 
                ELSE 6 END")
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }

    public function scopeForOperator($query, string $operator)
    {
        return $query->where(function ($q) use ($operator) {
            $q->where('operator', $operator)
              ->orWhere('operator', 'like', '%' . $operator . '%')
              ->orWhereHas('operatorRecord', fn ($oq) => $oq->where('name', $operator))
              ->orWhereHas('vehicle', fn ($vq) => $vq->where('operator', $operator))
              // Fallback for legacy string matching when operator_id is not yet backfilled
              ->when(stripos($operator, 'Philippine Airline') !== false || stripos($operator, 'PAL') !== false, function($sq) {
                  $sq->orWhere('operator', 'like', '%Philippines Airline%')
                     ->orWhere('operator', 'like', '%PAL%');
              })
              ->when(stripos($operator, 'Cebu') !== false, function($sq) {
                  $sq->orWhere('operator', 'like', '%CebuPecific%')
                     ->orWhere('operator', 'like', '%Cebu%');
              })
              ->when(stripos($operator, 'AirAsia') !== false, function($sq) {
                  $sq->orWhere('operator', 'like', '%AirAsia%');
              });
        });
    }


    public static function scheduleOrigins(?string $mode = null, ?string $operator = null): array
    {
        $cacheKey = 'ferry_route:schedule_origins_v4:' . md5(serialize([$mode, $operator]));

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addHours(2), function () use ($mode, $operator) {
            return static::query()
                ->active()
                ->when($mode, fn ($query) => $query->where('mode', $mode))
                ->when($operator, fn ($query) => $query->forOperator($operator))
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
                ->when($operator, fn ($query) => $query->forOperator($operator))
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
                                    ->orWhere('return_routes.operator', 'like', '%' . $operator . '%')
                                    ->orWhereExists(function ($oq) use ($operator) {
                                        $oq->selectRaw('1')
                                            ->from('operators')
                                            ->whereColumn('operators.id', 'return_routes.operator_id')
                                            ->where('operators.name', $operator);
                                    })
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

            return $query->select('destination')
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
            return \App\Models\Operator::query()
                ->where('is_active', true)
                ->when($mode, function ($query, $mode) {
                    if ($mode === 'ferry' || $mode === 'airline') {
                        $query->where('mode', $mode);
                    }
                })
                ->orderByRaw("CASE 
                    WHEN LOWER(name) LIKE '%starlite%' THEN 1 
                    WHEN LOWER(name) LIKE '%2go%' THEN 2 
                    WHEN LOWER(name) LIKE '%cebu%' THEN 3 
                    WHEN LOWER(name) LIKE '%philippine%' OR LOWER(name) LIKE '%pal%' THEN 4 
                    WHEN LOWER(name) LIKE '%airasia%' THEN 5 
                    ELSE 6 END")
                ->orderBy('name')
                ->pluck('name')
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
                ->when($operator, fn ($q) => $q->forOperator($operator))
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
                ->when($operator, fn ($q) => $q->forOperator($operator))
                ->whereHas('schedules', function ($q) {
                    $q->active();
                })
                ->exists();
        });
    }
}

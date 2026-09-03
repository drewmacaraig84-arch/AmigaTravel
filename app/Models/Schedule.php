<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use App\Models\Passenger;

class Schedule extends Model
{
    protected $fillable = [
        'ferry_route_id',
        'service_name',
        'vehicle_name',
        'plate_no',
        'departure_time',
        'arrival_time',
        'duration_minutes',
        'price',
        'availability_label',
        'seat_rows',
        'seat_columns',
        'is_active',
    ];

    /**
     * Always eager-load ferryRoute (with operatorRecord) so that any property
     * access like $schedule->ferryRoute->origin never triggers a lazy-load
     * violation regardless of where in the app the schedule is retrieved.
     */
    protected $with = ['ferryRoute.operatorRecord'];

    protected $casts = [
        'departure_time' => 'datetime',
        'arrival_time' => 'datetime',
        'price' => 'decimal:2',
        'seat_columns' => 'array',
        'is_active' => 'boolean',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function ferryRoute(): BelongsTo
    {
        return $this->belongsTo(FerryRoute::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function transportClasses(): BelongsToMany
    {
        return $this->belongsToMany(TransportClass::class, 'schedule_transport_class')
            ->using(ScheduleTransportClass::class)
            ->withPivot('id', 'additional_price', 'tickets_available', 'description', 'has_bed', 'is_active', 'is_promo', 'rate_type', 'rate_code', 'promo_duration_start', 'promo_duration_end', 'promo_type')
            ->withTimestamps();
    }

    public function scheduleTransportClasses(): HasMany
    {
        return $this->hasMany(ScheduleTransportClass::class);
    }

    public function scheduleAccommodations(): HasMany
    {
        return $this->hasMany(ScheduleAccommodation::class)->orderBy('sort_order');
    }

    public function activeScheduleAccommodations(): HasMany
    {
        return $this->scheduleAccommodations()->where('is_active', true);
    }

    public function promotionalTickets(): HasMany
    {
        return $this->hasMany(PromotionalTicket::class);
    }

    public function activePromotionalTicket(): ?PromotionalTicket
    {
        return $this->promotionalTickets()->activeAndAvailable()->first();
    }

    public function vehicle(): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        return $this->hasOneThrough(Vehicle::class, FerryRoute::class, 'id', 'id', 'ferry_route_id', 'vehicle_id');
    }

    public function getSeatColumnLettersAttribute(): array
    {
        return $this->seat_columns ?? ['A', 'B', 'C', 'D', 'E', 'F'];
    }

    public function getSeatRowCountAttribute(): int
    {
        return $this->seat_rows ?? 30;
    }

    public function getOccupiedSeatsForDate(string $date): array
    {
        /** @var \Illuminate\Database\Eloquent\Builder $passengerQuery */
        $passengerQuery = Passenger::query();

        return $passengerQuery
            ->whereNotNull('seat_number')
            ->whereHas('booking', function (Builder $query) use ($date) {
                $query->where('schedule_id', $this->id)
                    ->where('departure_date', $date)
                    ->where('status', '!=', 'cancelled');
            })
            ->pluck('seat_number')
            ->all();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where(
            $query->getModel()->qualifyColumn('is_active'),
            true
        )->where(function (Builder $q) {
            $q->whereHas('scheduleAccommodations', function (Builder $accQ) {
                $accQ->where(function (Builder $sub) {
                    $sub->where('is_active', true)->orWhereNull('is_active');
                })->where('tickets_available', '>', 0);
            })->orWhereHas('transportClasses', function (Builder $tcQ) {
                $tcQ->where(function (Builder $sub) {
                    $sub->where('schedule_transport_class.is_active', true)->orWhereNull('schedule_transport_class.is_active');
                })->where('schedule_transport_class.tickets_available', '>', 0);
            });
        });
    }

    public function scopeForRouteAndDate(Builder $query, string $origin, string $destination, ?string $date = null, ?string $mode = null, ?string $operator = null): Builder
    {
        $dateStr = $date ?: Carbon::today()->format('Y-m-d');
        $now    = Carbon::now();
        $dayEnd = Carbon::parse($dateStr)->endOfDay();

        // When the selected date is TODAY, use the current second as the lower
        // bound so that any schedule whose departure has already passed — even
        // by a single second — is excluded from results.
        $lowerBound = Carbon::parse($dateStr)->isToday() ? $now : Carbon::parse($dateStr)->startOfDay();

        return $query->active()
            ->whereHas('ferryRoute', function (Builder $routeQuery) use ($origin, $destination, $mode, $operator) {
                $routeQuery->where('origin', $origin)
                    ->where('destination', $destination)
                    ->active();

                if (! empty($mode)) {
                    $routeQuery->where('mode', $mode);
                }
                
                if (! empty($operator)) {
                    $routeQuery->forOperator($operator);
                }
            })
            ->where('departure_time', '>=', $lowerBound)
            ->where('departure_time', '<=', $dayEnd)
            ->orderBy('departure_time');
    }

    public function getFormattedDepartureAttribute(): string
    {
        return Carbon::parse($this->departure_time)->format('F j, Y g:i a');
    }

    public function getFormattedArrivalAttribute(): string
    {
        return Carbon::parse($this->arrival_time)->format('F j, Y g:i a');
    }

    public function getDurationMinutesAttribute(): int
    {
        if (!empty($this->attributes['duration_minutes'])) {
            return (int) $this->attributes['duration_minutes'];
        }

        if (!empty($this->departure_time) && !empty($this->arrival_time)) {
            $departure = Carbon::parse($this->departure_time);
            $arrival = Carbon::parse($this->arrival_time);

            if ($arrival->lessThan($departure)) {
                $arrival->addDay();
            }

            return (int) $departure->diffInMinutes($arrival);
        }

        return 0;
    }

    public function isShortHaul(): bool
    {
        if (strtolower($this->getFerryRouteModel()?->mode ?? '') === 'airline') {
            return false;
        }

        return $this->duration_minutes < 300;
    }

    public function getIsShortHaulAttribute(): bool
    {
        return $this->isShortHaul();
    }

    public function getDurationLabelAttribute(): string
    {
        $totalMinutes = $this->duration_minutes;
        if ($totalMinutes > 0) {
            $hours = intdiv($totalMinutes, 60);
            $minutes = $totalMinutes % 60;

            if ($hours > 0 && $minutes > 0) {
                return "{$hours}h {$minutes}m";
            }

            if ($hours > 0) {
                return "{$hours}h";
            }

            return "{$minutes}m";
        }

        $departure = Carbon::parse($this->departure_time);
        $arrival = Carbon::parse($this->arrival_time);

        if ($arrival->lessThan($departure)) {
            $arrival->addDay();
        }

        $totalMinutes = $departure->diffInMinutes($arrival);
        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;

        return "{$hours}h {$minutes}m";
    }

    public function getFerryRouteModel(): ?FerryRoute
    {
        if ($this->relationLoaded('ferryRoute')) {
            $route = $this->getRelation('ferryRoute');
            // Ensure operatorRecord is also loaded on the cached route
            if ($route && ! $route->relationLoaded('operatorRecord')) {
                $route->load('operatorRecord');
            }
            return $route;
        }
        if ($this->ferry_route_id) {
            $route = FerryRoute::with('operatorRecord')->find($this->ferry_route_id);
            if ($route) {
                $this->setRelation('ferryRoute', $route);
            }
            return $route;
        }
        return null;
    }

    public function getAccommodationLabelAttribute(): string
    {
        if ($this->getFerryRouteModel()?->mode === 'airline') {
            $transportClasses = $this->relationLoaded('transportClasses')
                ? $this->transportClasses
                : $this->transportClasses()->get();

            $names = $transportClasses->pluck('name')->filter()->all();

            return empty($names) ? 'Standard classes' : implode(', ', $names);
        }

        $accommodations = $this->relationLoaded('scheduleAccommodations')
            ? $this->scheduleAccommodations
            : $this->scheduleAccommodations()->where('is_active', true)->get();

        $names = $accommodations->pluck('name')->filter()->all();

        return empty($names) ? 'Standard accommodation' : implode(', ', $names);
    }

    public function getAirlineSeatingProfile(): ?array
    {
        $operator = $this->getFerryRouteModel()?->operator;
        $resolvedAircraftType = $this->resolveAircraftConfigKey($this->service_name);

        if (blank($operator)) {
            return null;
        }

        $resolvedOperator = $this->resolveOperatorConfigKey($operator);
        if (blank($resolvedOperator)) {
            return null;
        }

        if (! blank($resolvedAircraftType)) {
            $profile = config("airline_seating.operators.{$resolvedOperator}.aircraft.{$resolvedAircraftType}");
            if (! blank($profile)) {
                return $profile;
            }
        }

        return $this->getFallbackAirlineSeatingProfile($resolvedOperator);
    }

    protected function getFallbackAirlineSeatingProfile(string $resolvedOperator): ?array
    {
        $operatorAircraft = config("airline_seating.operators.{$resolvedOperator}.aircraft", []);
        if (empty($operatorAircraft)) {
            return null;
        }

        $requiredClassCodes = $this->getTransportClassCodes();

        if (empty($requiredClassCodes)) {
            return reset($operatorAircraft);
        }

        $matchingAircraft = collect($operatorAircraft)
            ->filter(fn (array $aircraftConfig) => $this->aircraftSupportsClassCodes($aircraftConfig, $requiredClassCodes))
            ->sortByDesc(fn (array $aircraftConfig) => count(array_intersect($requiredClassCodes, $aircraftConfig['class_order'] ?? [])))
            ->values();

        return $matchingAircraft->first() ?: reset($operatorAircraft);
    }

    protected function getTransportClassCodes(): array
    {
        $transportClasses = $this->relationLoaded('transportClasses')
            ? $this->transportClasses
            : $this->transportClasses()->get();

        return $transportClasses
            ->map(function (TransportClass $class) {
                return filled($class->code)
                    ? $class->code
                    : $this->inferTransportClassCode($class);
            })
            ->filter()
            ->values()
            ->all();
    }

    protected function aircraftSupportsClassCodes(array $aircraftConfig, array $classCodes): bool
    {
        if (empty($classCodes)) {
            return true;
        }

        $classOrder = $aircraftConfig['class_order'] ?? [];

        return empty(array_diff($classCodes, $classOrder));
    }

    public function resolveOperatorConfigKey(?string $operator): ?string
    {
        if (blank($operator)) {
            return null;
        }

        $normalizedOperator = strtolower(trim($operator));
        $operatorAliases = [
            'pal' => 'Philippine Airlines',
            'philippine airline' => 'Philippine Airlines',
            'philippine airline (pal)' => 'Philippine Airlines',
            'philippine airlines' => 'Philippine Airlines',
            'philippine airlines (pal)' => 'Philippine Airlines',
            'cebu pacific' => 'Cebu Pacific',
            'cebu pacific air' => 'Cebu Pacific',
            'cebpac' => 'Cebu Pacific',
            'cebgo' => 'Cebu Pacific',
            'philippine airasia' => 'AirAsia',
            'philippines airasia' => 'AirAsia',
            'airasia' => 'AirAsia',
            'air asia' => 'AirAsia',
        ];

        return $operatorAliases[$normalizedOperator] ?? null;
    }

    public function resolveAircraftConfigKey(?string $aircraftType): ?string
    {
        if (blank($aircraftType)) {
            return null;
        }

        $normalizedAircraft = strtolower(trim($aircraftType));
    $aircraftAliases = [
        'a320' => 'Airbus A320',
        'airbus a320' => 'Airbus A320',
        'airbus a320-200' => 'Airbus A320',
        'airbus a320neo' => 'Airbus A320',
        'a321' => 'Airbus A321',
        'airbus a321' => 'Airbus A321',
        'airbus a321-200' => 'Airbus A321',
        'airbus a321neo' => 'Airbus A321',
        'airbus a321ceo' => 'Airbus A321',
        'a330' => 'Airbus A330',
        'airbus a330' => 'Airbus A330',
        'a330-300' => 'Airbus A330-300',
        'airbus a330-300' => 'Airbus A330-300',
        'a330neo' => 'Airbus A330neo',
        'airbus a330neo' => 'Airbus A330neo',
        'a350' => 'Airbus A350',
        'airbus a350' => 'Airbus A350',
        'b777' => 'Boeing 777-300ER',
        'b777-300er' => 'Boeing 777-300ER',
        'boeing 777-300er' => 'Boeing 777-300ER',
        'atr72-600' => 'ATR 72-600',
        'atr 72-600' => 'ATR 72-600',
        'atr 72 600' => 'ATR 72-600',
        'de havilland dash 8-q400' => 'De Havilland Dash 8-Q400',
    ];


        return $aircraftAliases[$normalizedAircraft] ?? null;
    }

    protected function inferTransportClassCode(TransportClass $class): string
    {
        if (filled($class->code)) {
            return $class->code;
        }

        return match (strtolower($class->name)) {
            'premium flatbed' => 'premium-flatbed',
            'hot seats' => 'hot-seat',
            'standard plus' => 'standard-plus',
            'premium economy / comfort class' => 'premium-economy',
            'business class' => 'business',
            'economy class' => 'economy',
            'standard' => 'standard',
            'premium' => 'premium',
            default => str($class->name)->slug()->value(),
        };
    }

    protected function buildCabinLayouts(array $aircraftConfig): array
    {
        $route = $this->getFerryRouteModel();
        $resolvedOperator = $this->resolveOperatorConfigKey($route?->operator_name ?? $route?->operator);
        $operatorConfig = config('airline_seating.operators.' . $resolvedOperator . '.classes', []);
        $currentRow = 1;
        $layouts = [];

        foreach ($aircraftConfig['class_order'] ?? [] as $classCode) {
            $classConfig = $operatorConfig[$classCode] ?? null;
            $seatCount = $aircraftConfig['seat_counts'][$classCode] ?? null;

            if (! $classConfig || ! $seatCount) {
                continue;
            }

            $rows = $this->buildSeatRows(
                $currentRow,
                (int) $seatCount,
                $classConfig['columns'] ?? ['A', 'B', 'C', 'D', 'E', 'F'],
            );

            $lastRow = count($rows) > 0 ? $rows[array_key_last($rows)]['label'] : $currentRow - 1;

            $layouts[$classCode] = [
                'code' => $classCode,
                'name' => $classConfig['name'],
                'seat_capacity' => (int) $seatCount,
                'row_start' => $currentRow,
                'row_end' => $lastRow,
                'seat_rows' => $rows,
            ];

            $currentRow = $lastRow + 1;
        }

        return $layouts;
    }

    protected function buildSeatRows(int $startRow, int $seatCount, array $columns): array
    {
        $rows = [];
        $remaining = $seatCount;
        $rowNumber = $startRow;
        $midpoint = (int) ceil(count($columns) / 2);
        $leftColumns = array_slice($columns, 0, $midpoint);
        $rightColumns = array_slice($columns, $midpoint);

        while ($remaining > 0) {
            $rowSeatCount = min(count($columns), $remaining);
            $rowColumns = array_slice($columns, 0, $rowSeatCount);

            $rows[] = [
                'label' => $rowNumber,
                'left' => collect($leftColumns)
                    ->filter(fn (string $column) => in_array($column, $rowColumns, true))
                    ->map(fn (string $column) => [
                        'id' => $rowNumber . $column,
                        'label' => $rowNumber . $column,
                    ])
                    ->values()
                    ->all(),
                'right' => collect($rightColumns)
                    ->filter(fn (string $column) => in_array($column, $rowColumns, true))
                    ->map(fn (string $column) => [
                        'id' => $rowNumber . $column,
                        'label' => $rowNumber . $column,
                    ])
                    ->values()
                    ->all(),
            ];

            $remaining -= $rowSeatCount;
            $rowNumber++;
        }

        return $rows;
    }

    public function toBookingArray(?string $departureDate = null, ?array $occupiedSeats = null): array
    {
        $route = $this->getFerryRouteModel();
        $mode = $route?->mode ?? 'ferry';

        // Explicitly fetch accommodations and transport classes using eager loaded relations if available
        $activeAccommodations = ($this->relationLoaded('scheduleAccommodations')
            ? $this->scheduleAccommodations->filter(fn ($acc) => $acc->is_active === null || (bool) $acc->is_active === true)
            : $this->scheduleAccommodations()->where(function ($q) {
                $q->where('is_active', true)->orWhereNull('is_active');
            })->get())
            ->filter(fn ($acc) => ($acc->tickets_available ?? 50) > 0);
            
        $activeTransportClasses = ($this->relationLoaded('transportClasses')
            ? $this->transportClasses->filter(fn ($tc) => ($tc->pivot?->is_active === null || (bool) $tc->pivot->is_active === true) && ($tc->is_active === null || (bool) $tc->is_active === true))->sortBy('sort_order')
            : $this->transportClasses()->where('transport_classes.is_active', true)->orderBy('sort_order')->get())
            ->filter(fn ($tc) => ($tc->pivot?->tickets_available ?? 50) > 0);

        $promoTicket = $this->activePromotionalTicket();

        $departureCarbon = Carbon::parse($this->departure_time);

        return [
            'id' => $this->id,
            'departure' => $this->formatted_departure,
            'arrival' => $this->formatted_arrival,
            'duration' => $this->duration_label,
            'duration_minutes' => $this->duration_minutes,
            'is_short_haul' => $this->isShortHaul(),
            'price' => floatval($this->price),
            'service' => $this->service_name,
            'vehicle_name' => $this->vehicle_name,
            'availability' => $this->availability_label ?? 'Available',
            'tickets_available' => (int) ($this->tickets_available ?? 0),
            'mode' => $mode,
            'trip_type' => $route?->trip_type ?: 'local',
            'operator' => $route?->operatorRecord?->name ?? $route?->operator,
            'operator_logo' => $route?->operatorRecord?->logo_url,
            // ISO 8601 timestamp for real-time client-side filtering (JS Date comparison)
            'departure_time_iso' => $this->departure_time->toIso8601String(),
            // True when the departure has already passed (race-condition guard for UI)
            'is_past' => $departureCarbon->isPast(),
            // Promotional ticket — null when no active promo exists for this schedule
            'promotional_ticket' => $promoTicket ? [
                'id'                 => $promoTicket->id,
                'promo_price'        => floatval($promoTicket->promo_price),
                'quantity_remaining' => $promoTicket->remaining_quantity,
                'ends_at'            => $promoTicket->ends_at->toISOString(),
            ] : null,
            'accommodations' => $activeAccommodations
                ->map(fn (ScheduleAccommodation $accommodation) => [
                    'id' => $accommodation->id,
                    'name' => $accommodation->name,
                    'rate_code' => $accommodation->rate_code,
                    'description' => $accommodation->description,
                    'price' => floatval($accommodation->price),
                    'has_bed' => (bool) $accommodation->has_bed,
                    'tickets_available' => (int) ($accommodation->tickets_available ?? 50),
                ])
                ->values()
                ->all(),
            'transport_classes' => $activeTransportClasses
                ->map(function (TransportClass $class) {
                    $pivot = $class->pivot;
                    $promoType = $pivot?->promo_type ?? 'temporary';
                    $promoStart = $pivot?->promo_duration_start ? Carbon::parse($pivot->promo_duration_start) : null;
                    $promoEnd = $pivot?->promo_duration_end ? Carbon::parse($pivot->promo_duration_end) : null;
                    $storedRateType = $pivot?->rate_type ?? ($pivot?->is_promo ? 'promotional' : 'regular');
                    $isPromoConfig = in_array($storedRateType, ['promotional', 'super_promotional'], true) || (bool) ($pivot?->is_promo ?? false);

                    $now = now();

                    // Check permanent promo expiry: after end date -> do not display on website booking page!
                    if ($isPromoConfig && $promoEnd && $now->isAfter($promoEnd) && $promoType === 'permanent') {
                        return null;
                    }

                    // Check temporary promo expiry or before promo start
                    $effectiveRateType = $storedRateType;
                    $effectiveIsPromo = $isPromoConfig;
                    $price = $pivot?->additional_price !== null ? $pivot->additional_price : ($class->is_on_sale && $class->sale_price ? $class->sale_price : $class->price);

                    if ($isPromoConfig && $promoEnd && $now->isAfter($promoEnd) && $promoType === 'temporary') {
                        // Temporary promo has expired -> revert to regular fare and restore base price
                        $effectiveRateType = 'regular';
                        $effectiveIsPromo = false;
                        $price = floatval($class->price ?? 0);
                    } elseif ($isPromoConfig && $promoStart && $now->isBefore($promoStart)) {
                        // Not yet active -> behave as regular
                        $effectiveRateType = 'regular';
                        $effectiveIsPromo = false;
                        $price = floatval($class->price ?? 0);
                    }

                    $classCode = $this->inferTransportClassCode($class);

                    return [
                        'id' => $class->id,
                        'pivot_id' => $pivot?->id,
                        'is_promo' => (bool) $effectiveIsPromo,
                        'rate_type' => $effectiveRateType,
                        'rate_code' => $pivot?->rate_code,
                        'promo_type' => $promoType,
                        'promo_duration_start' => $pivot?->promo_duration_start?->toISOString(),
                        'promo_duration_end' => $pivot?->promo_duration_end?->toISOString(),
                        'code' => $classCode,
                        'name' => $class->name,
                        'description' => $pivot?->description ?? $class->description,
                        'price' => floatval($price),
                        'has_bed' => (bool) ($pivot?->has_bed ?? false),
                        'is_on_sale' => (bool) $class->is_on_sale,
                        'sale_price' => $class->sale_price ? floatval($class->sale_price) : null,
                        'cover_image' => $class->cover_image,
                        'tickets_available' => (int) ($pivot?->tickets_available ?? 50),
                    ];
                })
                ->filter()
                ->values()
                ->all(),
        ];
    }

    /**
     * Bust all schedule-related API search caches.
     * Safely deletes only schedule and route keys on Redis without flushing sessions or queues.
     */
    public static function bust(): void
    {
        try {
            $driver = config('cache.default');

            // 1. Redis Store: active when cache store is redis or running with Redis on Railway
            $isRedisDriver = $driver === 'redis' || (! app()->runningUnitTests() && in_array(env('CACHE_STORE'), ['redis', 'octane'], true));
            if ($isRedisDriver) {
                $connections = array_unique([
                    config('cache.stores.redis.connection', 'cache'),
                    'default',
                ]);

                $redisPrefix = (string) config('database.redis.options.prefix', '');
                $cachePrefix = (string) config('cache.prefix', '');

                $patterns = [
                    '*api:schedule:*',
                    '*api:origins:*',
                    '*api:destinations:*',
                    '*api:operators:*',
                    '*api:available_dates:*',
                    '*api:all_schedules:*',
                    '*ferry_route:*',
                    '*schedule_origins*',
                    '*schedule_destinations*',
                    '*schedule_operators*',
                    '*web:activeRoutes*',
                    '*web:schedules:*',
                ];

                foreach ($connections as $connName) {
                    try {
                        $redis = \Illuminate\Support\Facades\Redis::connection($connName);
                        foreach ($patterns as $pattern) {
                            $keys = $redis->keys($pattern);
                            if (! empty($keys)) {
                                $toDelete = [];
                                foreach ($keys as $k) {
                                    $toDelete[] = $k;
                                    if ($redisPrefix !== '' && str_starts_with($k, $redisPrefix)) {
                                        $stripped = substr($k, strlen($redisPrefix));
                                        $toDelete[] = $stripped;
                                        if ($cachePrefix !== '' && str_starts_with($stripped, $cachePrefix)) {
                                            \Illuminate\Support\Facades\Cache::forget(substr($stripped, strlen($cachePrefix)));
                                        }
                                    } elseif ($cachePrefix !== '' && str_starts_with($k, $cachePrefix)) {
                                        \Illuminate\Support\Facades\Cache::forget(substr($k, strlen($cachePrefix)));
                                    }
                                }
                                $toDelete = array_values(array_unique(array_filter($toDelete)));
                                if (! empty($toDelete)) {
                                    $redis->del($toDelete);
                                }
                            }
                        }
                    } catch (\Throwable) {
                    }
                }
            }

            // 2. Database Store: delete from cache table
            if ($driver === 'database') {
                try {
                    $table = config('cache.stores.database.table', 'cache');
                    \Illuminate\Support\Facades\DB::table($table)
                        ->where(function ($q) {
                            $q->where('key', 'like', '%api:schedule:%')
                              ->orWhere('key', 'like', '%api:origins:%')
                              ->orWhere('key', 'like', '%api:destinations:%')
                              ->orWhere('key', 'like', '%api:operators:%')
                              ->orWhere('key', 'like', '%api:available_dates:%')
                              ->orWhere('key', 'like', '%api:all_schedules:%')
                              ->orWhere('key', 'like', '%ferry_route:%')
                              ->orWhere('key', 'like', '%schedule_origins%')
                              ->orWhere('key', 'like', '%schedule_destinations%')
                              ->orWhere('key', 'like', '%schedule_operators%')
                              ->orWhere('key', 'like', '%web:activeRoutes%')
                              ->orWhere('key', 'like', '%web:schedules%');
                        })
                        ->delete();
                } catch (\Throwable) {
                }
            }

            // 3. File Store: flush file cache so local dev updates immediately
            if ($driver === 'file') {
                try {
                    \Illuminate\Support\Facades\Cache::flush();
                } catch (\Throwable) {
                }
            }

            // 4. Direct cache keys via Cache facade
            \Illuminate\Support\Facades\Cache::forget('web:activeRoutes');
            \Illuminate\Support\Facades\Cache::forget('ferry_route:schedule_origins_v4');
            \Illuminate\Support\Facades\Cache::forget('ferry_route:schedule_operators_v4');
            \Illuminate\Support\Facades\Cache::forget('gracia:active_rule');

            // 5. Cache tags if supported
            if (\Illuminate\Support\Facades\Cache::supportsTags()) {
                try {
                    \Illuminate\Support\Facades\Cache::tags(['schedules', 'routes'])->flush();
                } catch (\Throwable) {
                }
            }
        } catch (\Throwable) {
            // Ignore cache driver errors
        }
    }

    protected static function booted(): void
    {
        $bust = fn() => static::bust();
        static::saved($bust);
        static::deleted($bust);
    }
}

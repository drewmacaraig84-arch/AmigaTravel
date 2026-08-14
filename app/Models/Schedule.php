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
            ->withPivot('id', 'additional_price', 'tickets_available', 'description', 'has_bed', 'is_active', 'is_promo', 'rate_code', 'promo_duration_start', 'promo_duration_end')
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

    public function scopeForRouteAndDate(Builder $query, string $origin, string $destination, string $date, ?string $mode = null, ?string $operator = null): Builder
    {
        $now    = Carbon::now();
        $dayEnd = Carbon::parse($date)->endOfDay();

        // When the selected date is TODAY, use the current second as the lower
        // bound so that any schedule whose departure has already passed — even
        // by a single second — is excluded from results.
        $lowerBound = Carbon::parse($date)->isToday() ? $now : Carbon::parse($date)->startOfDay();

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

    public function getDurationLabelAttribute(): string
    {
        if ($this->duration_minutes) {
            $hours = intdiv($this->duration_minutes, 60);
            $minutes = $this->duration_minutes % 60;

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

    public function getAccommodationLabelAttribute(): string
    {
        if ($this->ferryRoute?->mode === 'airline') {
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
    $operator = $this->ferryRoute?->operator;
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
            'pal' => 'Philippine Airline',
            'philippine airline' => 'Philippine Airline',
            'philippine airline (pal)' => 'Philippine Airline',
            'philippine airlines' => 'Philippine Airline',
            'philippine airlines (pal)' => 'Philippine Airline',   // <-- ADD
            'cebu pacific' => 'Cebu Pacific',
            'cebu pacific air' => 'Cebu Pacific',                   // <-- ADD
            'philippine airasia' => 'AirAsia',
            'philippines airasia' => 'AirAsia',          // <-- ADD
            'airasia' => 'AirAsia',
            'cebpac' => 'Cebu Pacific',
            'cebgo' => 'Cebu Pacific',
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
        $resolvedOperator = $this->resolveOperatorConfigKey($this->ferryRoute?->operator_name);
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
        $mode = $this->ferryRoute?->mode ?? 'ferry';

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
            'price' => floatval($this->price),
            'service' => $this->service_name,
            'vehicle_name' => $this->vehicle_name,
            'availability' => $this->availability_label ?? 'Available',
            'tickets_available' => (int) ($this->tickets_available ?? 0),
            'mode' => $mode,
            'trip_type' => $this->ferryRoute?->trip_type ?: 'local',
            'operator' => $this->ferryRoute?->operator,
            'operator_logo' => $this->ferryRoute?->operatorRecord?->logo_url,
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
                    $classCode = $this->inferTransportClassCode($class);
                    $price = $class->pivot?->additional_price !== null ? $class->pivot->additional_price : ($class->is_on_sale && $class->sale_price ? $class->sale_price : $class->price);

                    return [
                        'id' => $class->id,
                        'pivot_id' => $class->pivot?->id,
                        'is_promo' => (bool) ($class->pivot?->is_promo ?? false),
                        'rate_code' => $class->pivot?->rate_code,
                        'promo_duration_start' => $class->pivot?->promo_duration_start?->toISOString(),
                        'promo_duration_end' => $class->pivot?->promo_duration_end?->toISOString(),
                        'code' => $classCode,
                        'name' => $class->name,
                        'description' => $class->pivot?->description ?? $class->description,
                        'price' => floatval($price),
                        'has_bed' => (bool) ($class->pivot?->has_bed ?? false),
                        'is_on_sale' => (bool) $class->is_on_sale,
                        'sale_price' => $class->sale_price ? floatval($class->sale_price) : null,
                        'cover_image' => $class->cover_image,
                        'tickets_available' => (int) ($class->pivot?->tickets_available ?? 50),
                    ];
                })
                ->values()
                ->all(),
        ];
    }
}

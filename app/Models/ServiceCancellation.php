<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceCancellation extends Model
{
    use HasFactory;

    protected $fillable = [
        'cancellation_code',
        'service_type',
        'carrier',
        'ferry_route_id',
        'vehicle_id',
        'scope',
        'schedule_id',
        'affected_date',
        'start_date',
        'end_date',
        'reason_category',
        'internal_notes',
        'customer_message',
        'resume_date',
        'status',
        'created_by_user_id',
    ];

    protected $with = ['ferryRoute', 'vehicle'];

    protected $casts = [
        'affected_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'resume_date' => 'date',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function ferryRoute(): BelongsTo
    {
        return $this->belongsTo(FerryRoute::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function replacementSchedules(): HasMany
    {
        return $this->hasMany(ServiceCancellationReplacementSchedule::class);
    }

    public function affectedBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'service_cancellation_id');
    }

    public static function generateCode(): string
    {
        $year = date('Y');
        $count = static::whereYear('created_at', $year)->count() + 1;

        return sprintf('CNL-%s-%04d', $year, $count);
    }

    /**
     * Get a query builder for matching schedules based on this cancellation's scope.
     */
    public function getAffectedSchedulesQuery(): Builder
    {
        $query = Schedule::query();

        if ($this->scope === 'specific_schedule' && $this->schedule_id) {
            return $query->where('id', $this->schedule_id);
        }

        return $query->whereHas('ferryRoute', function (Builder $routeQuery) {
            if ($this->service_type) {
                $routeQuery->where('mode', $this->service_type);
            }
            if ($this->carrier) {
                $routeQuery->where('operator', $this->carrier);
            }
            if ($this->ferry_route_id) {
                $routeQuery->where('id', $this->ferry_route_id);
            }
            if ($this->vehicle_id) {
                $routeQuery->where('vehicle_id', $this->vehicle_id);
            }
        });
    }

    /**
     * Get a query builder for matching bookings based on this cancellation's scope and date parameters.
     */
    public function getAffectedBookingsQuery(): Builder
    {
        $query = Booking::query()
            ->where('status', '!=', 'cancelled');

        if ($this->scope === 'specific_schedule' && $this->schedule_id) {
            $query->where(function (Builder $q) {
                $q->where('schedule_id', $this->schedule_id);
                if ($this->affected_date) {
                    $q->whereDate('departure_date', $this->affected_date);
                }
            });
        } elseif ($this->scope === 'carrier_date' && $this->affected_date) {
            $query->whereDate('departure_date', $this->affected_date)
                ->whereHas('schedule.ferryRoute', function (Builder $rq) {
                    if ($this->service_type) {
                        $rq->where('mode', $this->service_type);
                    }
                    if ($this->carrier) {
                        $rq->where('operator', $this->carrier);
                    }
                    if ($this->ferry_route_id) {
                        $rq->where('id', $this->ferry_route_id);
                    }
                    if ($this->vehicle_id) {
                        $rq->where('vehicle_id', $this->vehicle_id);
                    }
                });
        } elseif ($this->scope === 'carrier_date_range' && $this->start_date && $this->end_date) {
            $query->whereBetween('departure_date', [$this->start_date->format('Y-m-d'), $this->end_date->format('Y-m-d')])
                ->whereHas('schedule.ferryRoute', function (Builder $rq) {
                    if ($this->service_type) {
                        $rq->where('mode', $this->service_type);
                    }
                    if ($this->carrier) {
                        $rq->where('operator', $this->carrier);
                    }
                    if ($this->ferry_route_id) {
                        $rq->where('id', $this->ferry_route_id);
                    }
                    if ($this->vehicle_id) {
                        $rq->where('vehicle_id', $this->vehicle_id);
                    }
                });
        }

        return $query;
    }
}

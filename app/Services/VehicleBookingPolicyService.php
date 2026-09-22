<?php

namespace App\Services;

use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class VehicleBookingPolicyService
{
    /**
     * Minimum lead time required for vehicle bookings (in calendar days).
     */
    public const CUTOFF_DAYS = 3;

    /**
     * Minimum lead time required for vehicle bookings (in hours).
     */
    public const CUTOFF_HOURS = 72;

    /**
     * Timezone for schedule calculations.
     */
    public const TIMEZONE = 'Asia/Manila';

    /**
     * Check if a given departure time or schedule satisfies the 3-day cutoff policy.
     *
     * Standard rule: Must be at least 3 calendar days ahead AND at least 72 hours
     * from the current time in the Asia/Manila timezone.
     */
    public function isScheduleEligible(Schedule|string|Carbon $scheduleOrDepartureTime, ?Carbon $asOf = null): bool
    {
        $asOf = $asOf ? $asOf->copy()->setTimezone(self::TIMEZONE) : Carbon::now(self::TIMEZONE);

        if ($scheduleOrDepartureTime instanceof Schedule) {
            $departureTime = Carbon::parse($scheduleOrDepartureTime->departure_time, self::TIMEZONE);
        } elseif ($scheduleOrDepartureTime instanceof Carbon) {
            $departureTime = $scheduleOrDepartureTime->copy()->setTimezone(self::TIMEZONE);
        } else {
            $departureTime = Carbon::parse($scheduleOrDepartureTime, self::TIMEZONE);
        }

        $minCalendarDate = $asOf->copy()->addDays(self::CUTOFF_DAYS)->startOfDay();
        $minTimestamp = $asOf->copy()->addHours(self::CUTOFF_HOURS);

        // Must satisfy both: at least 72 hours out and at least 3 calendar days out
        return $departureTime->greaterThanOrEqualTo($minTimestamp)
            && $departureTime->startOfDay()->greaterThanOrEqualTo($minCalendarDate);
    }

    /**
     * Validate whether a schedule is eligible for vehicle booking.
     * Throws a ValidationException if ineligible.
     *
     * @throws ValidationException
     */
    public function validateScheduleLeadTime(Schedule $schedule, bool $hasVehicle, string $field = 'schedule_id'): void
    {
        if (! $hasVehicle) {
            return;
        }

        if (! $this->isScheduleEligible($schedule)) {
            $departureFormatted = Carbon::parse($schedule->departure_time, self::TIMEZONE)->format('M d, Y h:i A');
            throw ValidationException::withMessages([
                $field => [
                    "Vehicle bookings require a minimum of 3 days (72 hours) advance notice prior to departure. The selected departure ({$departureFormatted}) is within the cutoff window."
                ],
            ]);
        }
    }

    /**
     * Validate whether rolling cargo/vehicle service is supported on the schedule's route.
     * Throws a ValidationException if unsupported.
     *
     * @throws ValidationException
     */
    public function validateRouteVehicleSupport(Schedule $schedule, bool $hasVehicle, string $field = 'schedule_id'): void
    {
        if (! $hasVehicle) {
            return;
        }

        $route = $schedule->getFerryRouteModel() ?? $schedule->ferryRoute;
        if (! $route) {
            return;
        }

        $operator = normalize_operator_name($route->operator ?: ($schedule->vehicle?->operator ?? ''));
        if (stripos($operator, 'Starlite') !== false) {
            if (! \App\Services\StarliteScheduleIngestionService::isVehicleSupportedForRoute($route->origin, $route->destination)) {
                throw ValidationException::withMessages([
                    $field => [
                        "Vehicle rolling cargo service is not available on the {$route->origin} to {$route->destination} route (passenger ferry only)."
                    ],
                ]);
            }
        }
    }

    /**
     * Get the earliest valid departure date string ('Y-m-d') for vehicle bookings.
     */
    public function getEarliestVehicleBookingDate(?Carbon $asOf = null): string
    {
        $asOf = $asOf ? $asOf->copy()->setTimezone(self::TIMEZONE) : Carbon::now(self::TIMEZONE);

        return $asOf->copy()->addDays(self::CUTOFF_DAYS)->format('Y-m-d');
    }
}

<?php

namespace App\Rules;

use App\Models\Schedule;
use App\Services\VehicleBookingPolicyService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class VehicleDepartureLeadTimeRule implements ValidationRule
{
    public function __construct(
        protected bool $hasVehicle,
        protected ?VehicleBookingPolicyService $policy = null
    ) {
        $this->policy = $policy ?? app(VehicleBookingPolicyService::class);
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->hasVehicle || empty($value)) {
            return;
        }

        $schedule = Schedule::find($value);
        if (! $schedule) {
            return; // Handled by exists:schedules,id
        }

        if (! $this->policy->isScheduleEligible($schedule)) {
            $fail('Vehicle bookings require a minimum of 3 days (72 hours) advance notice prior to departure.');
        }
    }
}

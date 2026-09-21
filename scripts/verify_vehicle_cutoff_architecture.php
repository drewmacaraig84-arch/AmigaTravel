<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Schedule;
use App\Rules\VehicleDepartureLeadTimeRule;
use App\Services\VehicleBookingPolicyService;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

echo "========================================================\n";
echo "  3-DAY VEHICLE BOOKING CUTOFF ARCHITECTURE AUDIT\n";
echo "========================================================\n\n";

$policy = new VehicleBookingPolicyService();
$asOf = Carbon::now('Asia/Manila');
echo "Current audit baseline time (Asia/Manila): " . $asOf->format('Y-m-d H:i:s') . "\n";
echo "Earliest allowable vehicle booking date: " . $policy->getEarliestVehicleBookingDate($asOf) . "\n\n";

$passCount = 0;
$failCount = 0;

function assertCondition($description, $condition) {
    global $passCount, $failCount;
    if ($condition) {
        echo "  [PASS] {$description}\n";
        $passCount++;
    } else {
        echo "  [FAIL] {$description}\n";
        $failCount++;
    }
}

// ----------------------------------------------------
// 1. VehicleBookingPolicyService Unit Tests
// ----------------------------------------------------
echo "--- 1. Testing VehicleBookingPolicyService ---\n";

// 1 hour away
$oneHour = $asOf->copy()->addHour();
assertCondition("Departure 1h away must be rejected", ! $policy->isScheduleEligible($oneHour, $asOf));

// 24 hours away
$day1 = $asOf->copy()->addDay();
assertCondition("Departure 24h away must be rejected", ! $policy->isScheduleEligible($day1, $asOf));

// 48 hours away
$day2 = $asOf->copy()->addDays(2);
assertCondition("Departure 48h away must be rejected", ! $policy->isScheduleEligible($day2, $asOf));

// 71 hours away
$hours71 = $asOf->copy()->addHours(71);
assertCondition("Departure 71h away must be rejected", ! $policy->isScheduleEligible($hours71, $asOf));

// 72 hours away on day 3
$hours72 = $asOf->copy()->addHours(72);
$is72Valid = $policy->isScheduleEligible($hours72, $asOf);
assertCondition("Departure 72h away and >= 3 days must be accepted", $is72Valid);

// 5 days away
$day5 = $asOf->copy()->addDays(5);
assertCondition("Departure 5 days away must be accepted", $policy->isScheduleEligible($day5, $asOf));

// ----------------------------------------------------
// 2. Exception Throwing on Policy
// ----------------------------------------------------
echo "\n--- 2. Testing Policy Lead Time Assertion ---\n";
$mockNearSchedule = new Schedule(['departure_time' => $asOf->copy()->addHours(12)->toDateTimeString()]);
$mockFarSchedule  = new Schedule(['departure_time' => $asOf->copy()->addDays(5)->toDateTimeString()]);

// has_vehicle = false should NOT throw even if departure is near
try {
    $policy->validateScheduleLeadTime($mockNearSchedule, false);
    assertCondition("validateScheduleLeadTime without vehicle does not throw", true);
} catch (\Throwable $e) {
    assertCondition("validateScheduleLeadTime without vehicle does not throw", false);
}

// has_vehicle = true on near schedule MUST throw ValidationException
try {
    $policy->validateScheduleLeadTime($mockNearSchedule, true);
    assertCondition("validateScheduleLeadTime with vehicle on near schedule throws ValidationException", false);
} catch (ValidationException $e) {
    assertCondition("validateScheduleLeadTime with vehicle on near schedule throws ValidationException", true);
}

// has_vehicle = true on far schedule should NOT throw
try {
    $policy->validateScheduleLeadTime($mockFarSchedule, true);
    assertCondition("validateScheduleLeadTime with vehicle on far schedule does not throw", true);
} catch (\Throwable $e) {
    assertCondition("validateScheduleLeadTime with vehicle on far schedule does not throw", false);
}

// ----------------------------------------------------
// 3. VehicleDepartureLeadTimeRule Validation Rule Test
// ----------------------------------------------------
echo "\n--- 3. Testing VehicleDepartureLeadTimeRule ---\n";

// Find an existing active schedule from database
$nearScheduleDb = Schedule::where('departure_time', '>=', now())
    ->where('departure_time', '<=', now()->addHours(48))
    ->first();

$farScheduleDb = Schedule::where('departure_time', '>=', now()->addDays(5))->first();

if ($nearScheduleDb) {
    $validator = \Illuminate\Support\Facades\Validator::make(
        ['schedule_id' => $nearScheduleDb->id],
        ['schedule_id' => [new VehicleDepartureLeadTimeRule(true)]]
    );
    assertCondition("VehicleDepartureLeadTimeRule blocks schedule ID {$nearScheduleDb->id} departing within 48h", $validator->fails());
} else {
    echo "  [SKIP] No database schedule departing within 48h found for integration test.\n";
}

if ($farScheduleDb) {
    $validator = \Illuminate\Support\Facades\Validator::make(
        ['schedule_id' => $farScheduleDb->id],
        ['schedule_id' => [new VehicleDepartureLeadTimeRule(true)]]
    );
    assertCondition("VehicleDepartureLeadTimeRule accepts schedule ID {$farScheduleDb->id} departing in 5+ days", $validator->passes());
} else {
    echo "  [SKIP] No database schedule departing in 5+ days found for integration test.\n";
}

// ----------------------------------------------------
// 4. Livewire BookingForm Mounting & Rules Test
// ----------------------------------------------------
echo "\n--- 4. Testing Livewire BookingForm Component Logic ---\n";
$component = new \App\Livewire\BookingForm();

// Test updatedHasVehicle logic
$component->departure_date = $asOf->copy()->addDay()->format('Y-m-d'); // Tomorrow
$component->updatedHasVehicle(true);
$expectedEarliest = $policy->getEarliestVehicleBookingDate();
assertCondition(
    "BookingForm auto-adjusts departure date to earliest allowed date ({$expectedEarliest}) when has_vehicle is toggled ON",
    $component->departure_date === $expectedEarliest
);

echo "\n========================================================\n";
echo "SUMMARY: Passed: {$passCount} | Failed: {$failCount}\n";
echo "========================================================\n";

if ($failCount > 0) {
    exit(1);
}

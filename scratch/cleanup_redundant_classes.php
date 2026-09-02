<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\TransportClass;
use App\Models\ScheduleTransportClass;
use App\Models\ScheduleAccommodation;
use Illuminate\Support\Facades\DB;

$remap = [
    47 => 41, // Reclining Seat (Romblon Fare) -> Reclining Seat
    48 => 42, // Economy Bed Bunk (Romblon Fare) -> Economy Bed Bunk
    49 => 43, // Tourist Bed Bunk (Romblon Fare) -> Tourist Bed Bunk
    50 => 44, // Cabin (Romblon Fare) -> Cabin
    51 => 45, // VIP Room 2-3pax (Romblon Fare) -> VIP Room 2-3pax
    52 => 41, // Reclining Seat (Culasi Fare) -> Reclining Seat
    53 => 42, // Economy Bed Bunk (Culasi Fare) -> Economy Bed Bunk
    54 => 43, // Tourist Bed Bunk (Culasi Fare) -> Tourist Bed Bunk
    55 => 44, // Cabin (Culasi Fare) -> Cabin
    56 => 45, // VIP Room 2-3pax (Culasi Fare) -> VIP Room 2-3pax
];

DB::transaction(function () use ($remap) {
    $mergedPivots = 0;
    $deletedPivots = 0;
    $deletedClasses = 0;

    foreach ($remap as $oldId => $targetId) {
        $oldTc = TransportClass::find($oldId);
        $targetTc = TransportClass::find($targetId);

        if (! $oldTc || ! $targetTc) {
            echo "Skipping $oldId -> $targetId (one not found)\n";
            continue;
        }

        // Get all pivots using the old transport class
        $pivots = ScheduleTransportClass::where('transport_class_id', $oldId)->get();

        foreach ($pivots as $pivot) {
            // Check if schedule already has the target transport class
            $existingTarget = ScheduleTransportClass::where('schedule_id', $pivot->schedule_id)
                ->where('transport_class_id', $targetId)
                ->first();

            if ($existingTarget) {
                // Duplicate: delete old pivot row
                $pivot->delete();
                $deletedPivots++;
            } else {
                // Not a duplicate: remap to target class
                $pivot->update(['transport_class_id' => $targetId]);
                $mergedPivots++;
            }
        }

        // Clean up schedule_accommodations matching old class name
        ScheduleAccommodation::where('name', $oldTc->name)
            ->update(['name' => $targetTc->name]);

        // Delete the old TransportClass record
        $oldTc->delete();
        $deletedClasses++;
        echo "Deleted redundant TransportClass: [{$oldId}] {$oldTc->name} -> merged into [{$targetId}] {$targetTc->name}\n";
    }

    echo "\nSummary: Merged $mergedPivots pivots, removed $deletedPivots duplicates, deleted $deletedClasses redundant classes.\n";
});

TransportClass::bust();
echo "Cache cleared successfully.\n";

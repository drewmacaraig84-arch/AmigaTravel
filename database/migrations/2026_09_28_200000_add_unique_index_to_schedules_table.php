<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Safety deduplication check in case any duplicate exists on the executing environment
        $duplicates = DB::select("
            SELECT ferry_route_id, departure_time, vehicle_name, COUNT(*) as c
            FROM schedules
            GROUP BY ferry_route_id, departure_time, vehicle_name
            HAVING c > 1
        ");

        if (!empty($duplicates)) {
            foreach ($duplicates as $dup) {
                // Keep the lowest ID and delete redundant duplicates that have 0 bookings
                $dupIds = DB::table('schedules')
                    ->where('ferry_route_id', $dup->ferry_route_id)
                    ->where('departure_time', $dup->departure_time)
                    ->where('vehicle_name', $dup->vehicle_name)
                    ->orderBy('id', 'asc')
                    ->pluck('id')
                    ->toArray();

                $keepId = array_shift($dupIds);

                foreach ($dupIds as $deleteId) {
                    $hasBookings = DB::table('bookings')->where('schedule_id', $deleteId)->exists();
                    if (!$hasBookings) {
                        DB::table('schedules')->where('id', $deleteId)->delete();
                    }
                }
            }
        }

        // 2. Add composite unique constraint to enforce single schedule per route, departure time, and vehicle
        Schema::table('schedules', function (Blueprint $table) {
            $table->unique(
                ['ferry_route_id', 'departure_time', 'vehicle_name'],
                'uniq_schedules_route_time_vehicle'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropUnique('uniq_schedules_route_time_vehicle');
        });
    }
};

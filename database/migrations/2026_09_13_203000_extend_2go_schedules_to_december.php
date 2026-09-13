<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Artisan::call('schedules:extend-2go', [
            '--until' => '2026-12-31',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destructive reversal of generated schedules to protect potential bookings
    }
};

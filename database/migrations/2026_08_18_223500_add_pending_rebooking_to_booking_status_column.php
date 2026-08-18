<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bookings') && DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `bookings` MODIFY `status` VARCHAR(50) NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('bookings') && DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `bookings` MODIFY `status` ENUM('pending', 'confirmed', 'cancelled', 'operator_cancelled') NOT NULL DEFAULT 'pending'");
        }
    }
};

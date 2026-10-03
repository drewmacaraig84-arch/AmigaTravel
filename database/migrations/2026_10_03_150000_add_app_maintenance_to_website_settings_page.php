<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE website_settings MODIFY COLUMN `page` VARCHAR(50) NOT NULL DEFAULT 'home'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE website_settings MODIFY COLUMN `page` ENUM('header','footer','home','about','gallery','services','tour_package','schedules','contact_us','download','faqs','referrals') DEFAULT 'home'");
        }
    }
};

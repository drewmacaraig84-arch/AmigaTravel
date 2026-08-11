<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('schedule_transport_class', function (Blueprint $table) {
            if (!Schema::hasColumn('schedule_transport_class', 'promo_duration_start')) {
                $table->dateTime('promo_duration_start')->nullable()->after('rate_code');
                $table->dateTime('promo_duration_end')->nullable()->after('promo_duration_start');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedule_transport_class', function (Blueprint $table) {
            if (Schema::hasColumn('schedule_transport_class', 'promo_duration_start')) {
                $table->dropColumn(['promo_duration_start', 'promo_duration_end']);
            }
        });
    }
};

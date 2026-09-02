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
            if (! Schema::hasColumn('schedule_transport_class', 'promo_type')) {
                $table->string('promo_type', 20)->nullable()->default('temporary')->after('promo_duration_end');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedule_transport_class', function (Blueprint $table) {
            if (Schema::hasColumn('schedule_transport_class', 'promo_type')) {
                $table->dropColumn('promo_type');
            }
        });
    }
};

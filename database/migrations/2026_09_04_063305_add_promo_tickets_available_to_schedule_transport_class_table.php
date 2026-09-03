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
            $table->integer('promo_tickets_available')->nullable()->after('promo_duration_end');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedule_transport_class', function (Blueprint $table) {
            $table->dropColumn('promo_tickets_available');
        });
    }
};

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
        if (Schema::hasTable('schedule_transport_class') && !Schema::hasColumn('schedule_transport_class', 'rate_type')) {
            Schema::table('schedule_transport_class', function (Blueprint $table) {
                $table->enum('rate_type', ['regular', 'promotional', 'super_promotional'])->default('regular')->after('tickets_available');
            });

            // Backfill existing promo rows
            DB::table('schedule_transport_class')->where('is_promo', true)->update(['rate_type' => 'promotional']);
        }

        if (Schema::hasTable('passengers') && !Schema::hasColumn('passengers', 'rate_type')) {
            Schema::table('passengers', function (Blueprint $table) {
                $table->enum('rate_type', ['regular', 'promotional', 'super_promotional'])->default('regular')->after('is_promo');
            });

            DB::table('passengers')->where('is_promo', true)->update(['rate_type' => 'promotional']);
        }

        if (Schema::hasTable('booking_transport_class') && !Schema::hasColumn('booking_transport_class', 'rate_type')) {
            Schema::table('booking_transport_class', function (Blueprint $table) {
                $table->enum('rate_type', ['regular', 'promotional', 'super_promotional'])->default('regular')->after('is_promo');
            });

            DB::table('booking_transport_class')->where('is_promo', true)->update(['rate_type' => 'promotional']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('schedule_transport_class') && Schema::hasColumn('schedule_transport_class', 'rate_type')) {
            Schema::table('schedule_transport_class', function (Blueprint $table) {
                $table->dropColumn('rate_type');
            });
        }

        if (Schema::hasTable('passengers') && Schema::hasColumn('passengers', 'rate_type')) {
            Schema::table('passengers', function (Blueprint $table) {
                $table->dropColumn('rate_type');
            });
        }

        if (Schema::hasTable('booking_transport_class') && Schema::hasColumn('booking_transport_class', 'rate_type')) {
            Schema::table('booking_transport_class', function (Blueprint $table) {
                $table->dropColumn('rate_type');
            });
        }
    }
};

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
        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (Blueprint $table) {
                if (! Schema::hasColumn('bookings', 'refund_auth_letter')) {
                    $table->string('refund_auth_letter')->nullable()->after('refund_ticket_file');
                }
            });
        }

        if (Schema::hasTable('passengers')) {
            Schema::table('passengers', function (Blueprint $table) {
                if (! Schema::hasColumn('passengers', 'refund_auth_letter')) {
                    $table->string('refund_auth_letter')->nullable()->after('refund_ticket_file');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (Blueprint $table) {
                if (Schema::hasColumn('bookings', 'refund_auth_letter')) {
                    $table->dropColumn('refund_auth_letter');
                }
            });
        }

        if (Schema::hasTable('passengers')) {
            Schema::table('passengers', function (Blueprint $table) {
                if (Schema::hasColumn('passengers', 'refund_auth_letter')) {
                    $table->dropColumn('refund_auth_letter');
                }
            });
        }
    }
};

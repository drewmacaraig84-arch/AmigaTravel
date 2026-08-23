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
                if (! Schema::hasColumn('bookings', 'refund_id_image')) {
                    $table->string('refund_id_image')->nullable()->after('refund_destination');
                }
                if (! Schema::hasColumn('bookings', 'refund_ticket_file')) {
                    $table->string('refund_ticket_file')->nullable()->after('refund_id_image');
                }
            });
        }

        if (Schema::hasTable('passengers')) {
            Schema::table('passengers', function (Blueprint $table) {
                if (! Schema::hasColumn('passengers', 'refund_id_image')) {
                    $table->string('refund_id_image')->nullable()->after('refund_destination');
                }
                if (! Schema::hasColumn('passengers', 'refund_ticket_file')) {
                    $table->string('refund_ticket_file')->nullable()->after('refund_id_image');
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
                $cols = [];
                if (Schema::hasColumn('bookings', 'refund_id_image')) {
                    $cols[] = 'refund_id_image';
                }
                if (Schema::hasColumn('bookings', 'refund_ticket_file')) {
                    $cols[] = 'refund_ticket_file';
                }
                if (! empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }

        if (Schema::hasTable('passengers')) {
            Schema::table('passengers', function (Blueprint $table) {
                $cols = [];
                if (Schema::hasColumn('passengers', 'refund_id_image')) {
                    $cols[] = 'refund_id_image';
                }
                if (Schema::hasColumn('passengers', 'refund_ticket_file')) {
                    $cols[] = 'refund_ticket_file';
                }
                if (! empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }
    }
};

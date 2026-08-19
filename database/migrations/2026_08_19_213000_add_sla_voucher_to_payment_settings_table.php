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
        Schema::table('payment_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('payment_settings', 'sla_voucher_enabled')) {
                $table->boolean('sla_voucher_enabled')->default(true)->after('rebook_airline_before_departure_surcharge_pct');
            }
            if (! Schema::hasColumn('payment_settings', 'sla_voucher_hours')) {
                $table->unsignedInteger('sla_voucher_hours')->default(2)->after('sla_voucher_enabled');
            }
            if (! Schema::hasColumn('payment_settings', 'sla_voucher_amount')) {
                $table->decimal('sla_voucher_amount', 10, 2)->default(500.00)->after('sla_voucher_hours');
            }
        });

        // Set default values for existing row
        DB::table('payment_settings')->where('id', 1)->update([
            'sla_voucher_enabled' => true,
            'sla_voucher_hours' => 2,
            'sla_voucher_amount' => 500.00,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_settings', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('payment_settings', 'sla_voucher_enabled')) {
                $columns[] = 'sla_voucher_enabled';
            }
            if (Schema::hasColumn('payment_settings', 'sla_voucher_hours')) {
                $columns[] = 'sla_voucher_hours';
            }
            if (Schema::hasColumn('payment_settings', 'sla_voucher_amount')) {
                $columns[] = 'sla_voucher_amount';
            }
            if (! empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};

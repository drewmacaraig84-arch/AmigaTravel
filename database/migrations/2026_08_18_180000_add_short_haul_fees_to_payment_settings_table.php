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
            if (! Schema::hasColumn('payment_settings', 'short_haul_web_admin_fee')) {
                $table->decimal('short_haul_web_admin_fee', 10, 2)->default(30.00)->after('web_admin_fee');
            }
            if (! Schema::hasColumn('payment_settings', 'short_haul_transaction_fee')) {
                $table->decimal('short_haul_transaction_fee', 10, 2)->default(70.00)->after('transaction_fee');
            }
        });

        // Set default values for existing rows if needed
        DB::table('payment_settings')->where('id', 1)->update([
            'short_haul_web_admin_fee' => 30.00,
            'short_haul_transaction_fee' => 70.00,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_settings', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('payment_settings', 'short_haul_web_admin_fee')) {
                $columns[] = 'short_haul_web_admin_fee';
            }
            if (Schema::hasColumn('payment_settings', 'short_haul_transaction_fee')) {
                $columns[] = 'short_haul_transaction_fee';
            }
            if (! empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};

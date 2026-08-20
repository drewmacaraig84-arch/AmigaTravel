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
        Schema::table('passengers', function (Blueprint $table) {
            if (! Schema::hasColumn('passengers', 'extra_baggage_weight')) {
                $table->string('extra_baggage_weight', 50)->nullable()->after('passport_expiry_date');
            }
            if (! Schema::hasColumn('passengers', 'extra_baggage_price')) {
                $table->decimal('extra_baggage_price', 10, 2)->default(0.00)->after('extra_baggage_weight');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('passengers', function (Blueprint $table) {
            if (Schema::hasColumn('passengers', 'extra_baggage_price')) {
                $table->dropColumn('extra_baggage_price');
            }
            if (Schema::hasColumn('passengers', 'extra_baggage_weight')) {
                $table->dropColumn('extra_baggage_weight');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add extra baggage columns to the bookings table.
     * The original migration (2026_08_08_033529) was created empty by mistake.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'has_extra_baggage')) {
                $table->boolean('has_extra_baggage')->default(false)->after('driver_birthday');
            }
            if (!Schema::hasColumn('bookings', 'extra_baggage_price')) {
                $table->decimal('extra_baggage_price', 10, 2)->nullable()->after('has_extra_baggage');
            }
            if (!Schema::hasColumn('bookings', 'extra_baggage_weight')) {
                $table->string('extra_baggage_weight')->nullable()->after('extra_baggage_price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['has_extra_baggage', 'extra_baggage_price', 'extra_baggage_weight']);
        });
    }
};

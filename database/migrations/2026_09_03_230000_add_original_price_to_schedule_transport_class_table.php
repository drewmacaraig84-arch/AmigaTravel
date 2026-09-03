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
            if (! Schema::hasColumn('schedule_transport_class', 'original_price')) {
                $table->decimal('original_price', 10, 2)->nullable()->after('additional_price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedule_transport_class', function (Blueprint $table) {
            if (Schema::hasColumn('schedule_transport_class', 'original_price')) {
                $table->dropColumn('original_price');
            }
        });
    }
};

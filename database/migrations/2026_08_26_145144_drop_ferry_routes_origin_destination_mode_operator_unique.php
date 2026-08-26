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
        Schema::table('ferry_routes', function (Blueprint $table) {
            try {
                $table->dropUnique('ferry_routes_origin_destination_mode_operator_unique');
            } catch (\Throwable $e) {
                // Ignore if it doesn't exist
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ferry_routes', function (Blueprint $table) {
            try {
                $table->unique(['origin', 'destination', 'mode', 'operator']);
            } catch (\Throwable $e) {
                // Ignore
            }
        });
    }
};

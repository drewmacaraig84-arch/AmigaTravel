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
            $table->foreignId('operator_id')->nullable()->constrained('operators')->nullOnDelete();
        });

        Schema::table('accommodations', function (Blueprint $table) {
            $table->foreignId('operator_id')->nullable()->constrained('operators')->nullOnDelete();
        });

        Schema::table('transport_classes', function (Blueprint $table) {
            $table->foreignId('operator_id')->nullable()->constrained('operators')->nullOnDelete();
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->foreignId('operator_id')->nullable()->constrained('operators')->nullOnDelete();
        });

        Schema::table('airline_baggage_rules', function (Blueprint $table) {
            $table->foreignId('operator_id')->nullable()->constrained('operators')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ferry_routes', function (Blueprint $table) {
            $table->dropForeign(['operator_id']);
            $table->dropColumn('operator_id');
        });

        Schema::table('accommodations', function (Blueprint $table) {
            $table->dropForeign(['operator_id']);
            $table->dropColumn('operator_id');
        });

        Schema::table('transport_classes', function (Blueprint $table) {
            $table->dropForeign(['operator_id']);
            $table->dropColumn('operator_id');
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropForeign(['operator_id']);
            $table->dropColumn('operator_id');
        });

        Schema::table('airline_baggage_rules', function (Blueprint $table) {
            $table->dropForeign(['operator_id']);
            $table->dropColumn('operator_id');
        });
    }
};

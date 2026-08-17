<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('passengers', function (Blueprint $table) {
            DB::statement("ALTER TABLE passengers MODIFY COLUMN type ENUM('adult', 'child', 'infant', 'senior', 'pwd', 'student', 'minor') NOT NULL");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('passengers', function (Blueprint $table) {
            DB::statement("ALTER TABLE passengers MODIFY COLUMN type ENUM('adult', 'child', 'infant') NOT NULL");
        });
    }
};

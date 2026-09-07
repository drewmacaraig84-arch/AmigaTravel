<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('deletion_scheduled_at')->nullable()->after('remember_token');
            $table->string('deletion_reason', 255)->nullable()->after('deletion_scheduled_at');
            $table->text('deletion_feedback')->nullable()->after('deletion_reason');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['deletion_scheduled_at', 'deletion_reason', 'deletion_feedback']);
        });
    }
};

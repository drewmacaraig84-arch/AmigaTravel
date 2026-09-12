<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'deleted_at')) {
                $table->softDeletes();
            }
            if (! Schema::hasColumn('bookings', 'deleted_by_user_id')) {
                $table->foreignId('deleted_by_user_id')
                    ->nullable()
                    ->after('deleted_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }
            if (! Schema::hasColumn('bookings', 'deletion_reason')) {
                $table->string('deletion_reason', 255)->nullable()->after('deleted_by_user_id');
            }
        });

        Schema::table('transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('transactions', 'deleted_at')) {
                $table->softDeletes();
            }
            if (! Schema::hasColumn('transactions', 'deleted_by_user_id')) {
                $table->foreignId('deleted_by_user_id')
                    ->nullable()
                    ->after('deleted_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });

        Schema::table('passengers', function (Blueprint $table) {
            if (! Schema::hasColumn('passengers', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'deleted_by_user_id')) {
                $table->dropConstrainedForeignId('deleted_by_user_id');
            }
            if (Schema::hasColumn('bookings', 'deletion_reason')) {
                $table->dropColumn('deletion_reason');
            }
            if (Schema::hasColumn('bookings', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });

        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'deleted_by_user_id')) {
                $table->dropConstrainedForeignId('deleted_by_user_id');
            }
            if (Schema::hasColumn('transactions', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });

        Schema::table('passengers', function (Blueprint $table) {
            if (Schema::hasColumn('passengers', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};

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
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('review_claimed_by_user_id')
                ->nullable()
                ->after('status')
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('review_claimed_at')
                ->nullable()
                ->after('review_claimed_by_user_id');

            $table->string('review_type', 30)
                ->nullable()
                ->after('review_claimed_at');

            $table->index(['review_claimed_by_user_id', 'review_claimed_at'], 'bookings_review_claim_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_review_claim_idx');
            $table->dropForeign(['review_claimed_by_user_id']);
            $table->dropColumn([
                'review_claimed_by_user_id',
                'review_claimed_at',
                'review_type',
            ]);
        });
    }
};

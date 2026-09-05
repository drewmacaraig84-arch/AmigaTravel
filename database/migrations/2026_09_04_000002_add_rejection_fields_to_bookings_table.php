<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Booking rejection fields
            if (! Schema::hasColumn('bookings', 'rejection_reason')) {
                $table->string('rejection_reason')->nullable()->after('review_type');
            }
            if (! Schema::hasColumn('bookings', 'rejection_notes')) {
                $table->text('rejection_notes')->nullable()->after('rejection_reason');
            }
            if (! Schema::hasColumn('bookings', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('rejection_notes');
            }
            if (! Schema::hasColumn('bookings', 'rejected_by_user_id')) {
                $table->foreignId('rejected_by_user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete()
                    ->after('rejected_at');
            }

            // Rebooking rejection fields
            if (! Schema::hasColumn('bookings', 'rebooking_rejection_reason')) {
                $table->string('rebooking_rejection_reason')->nullable()->after('rejected_by_user_id');
            }
            if (! Schema::hasColumn('bookings', 'rebooking_rejection_notes')) {
                $table->text('rebooking_rejection_notes')->nullable()->after('rebooking_rejection_reason');
            }
            if (! Schema::hasColumn('bookings', 'rebooking_rejected_at')) {
                $table->timestamp('rebooking_rejected_at')->nullable()->after('rebooking_rejection_notes');
            }
            if (! Schema::hasColumn('bookings', 'rebooking_rejected_by_user_id')) {
                $table->foreignId('rebooking_rejected_by_user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete()
                    ->after('rebooking_rejected_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $cols = [
                'rejection_reason',
                'rejection_notes',
                'rejected_at',
                'rejected_by_user_id',
                'rebooking_rejection_reason',
                'rebooking_rejection_notes',
                'rebooking_rejected_at',
                'rebooking_rejected_by_user_id',
            ];

            foreach ($cols as $col) {
                if (Schema::hasColumn('bookings', $col)) {
                    if (in_array($col, ['rejected_by_user_id', 'rebooking_rejected_by_user_id'], true)) {
                        $table->dropForeign([$col]);
                    }
                    $table->dropColumn($col);
                }
            }
        });
    }
};

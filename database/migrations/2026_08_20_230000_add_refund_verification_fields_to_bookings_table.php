<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'refund_status')) {
                $table->string('refund_status', 30)->nullable()->after('refund_destination');
            }
            if (! Schema::hasColumn('bookings', 'refund_proof')) {
                $table->string('refund_proof')->nullable()->after('refund_status');
            }
            if (! Schema::hasColumn('bookings', 'refund_reference')) {
                $table->string('refund_reference', 100)->nullable()->after('refund_proof');
            }
            if (! Schema::hasColumn('bookings', 'refund_processed_at')) {
                $table->timestamp('refund_processed_at')->nullable()->after('refund_reference');
            }
            if (! Schema::hasColumn('bookings', 'refund_processed_by_user_id')) {
                $table->foreignId('refund_processed_by_user_id')->nullable()->constrained('users')->nullOnDelete()->after('refund_processed_at');
            }
            if (! Schema::hasColumn('bookings', 'refund_notes')) {
                $table->text('refund_notes')->nullable()->after('refund_processed_by_user_id');
            }
        });

        // For existing cancelled bookings with refund_amount > 0, set refund_status to 'pending' if null
        DB::table('bookings')
            ->whereIn('status', ['cancelled', 'operator_cancelled'])
            ->where('refund_amount', '>', 0)
            ->whereNull('refund_status')
            ->update(['refund_status' => 'pending']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $columns = [];
            foreach (['refund_status', 'refund_proof', 'refund_reference', 'refund_processed_at', 'refund_processed_by_user_id', 'refund_notes'] as $col) {
                if (Schema::hasColumn('bookings', $col)) {
                    $columns[] = $col;
                }
            }
            if (! empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};

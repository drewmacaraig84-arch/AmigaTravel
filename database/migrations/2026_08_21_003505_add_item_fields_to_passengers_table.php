<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('passengers', function (Blueprint $table) {
            // Item Identifier
            $table->unsignedInteger('item_number')->default(1)->after('booking_id');
            $table->string('ticket_number', 80)->nullable()->after('item_number');

            // Item Status Lifecycle
            // Values: pending | confirmed | cancelled | operator_cancelled | operator_rebooking
            //         refund_pending | refunded | rebooking_pending | rebooked
            $table->string('status', 30)->default('pending')->after('ticket_number');

            // Individual Financials
            $table->decimal('fare_amount', 10, 2)->default(0)->after('status');
            $table->decimal('accommodation_amount', 10, 2)->default(0)->after('fare_amount');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('accommodation_amount');
            $table->decimal('voucher_discount_share', 10, 2)->default(0)->after('discount_amount');
            $table->decimal('points_discount_share', 10, 2)->default(0)->after('voucher_discount_share');
            $table->decimal('web_admin_fee_share', 10, 2)->default(0)->after('points_discount_share');
            $table->decimal('transaction_fee_share', 10, 2)->default(0)->after('web_admin_fee_share');
            $table->decimal('item_total', 10, 2)->default(0)->after('transaction_fee_share');

            // Individual Cancellation & Refund
            $table->decimal('cancellation_fee', 10, 2)->default(0)->after('item_total');
            $table->decimal('refund_amount', 10, 2)->default(0)->after('cancellation_fee');
            $table->string('refund_status', 30)->nullable()->after('refund_amount'); // pending | completed
            $table->string('refund_destination', 255)->nullable()->after('refund_status');
            $table->string('refund_reference', 100)->nullable()->after('refund_destination');
            $table->string('refund_proof', 255)->nullable()->after('refund_reference');
            $table->timestamp('refund_processed_at')->nullable()->after('refund_proof');
            $table->foreignId('refund_processed_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->after('refund_processed_at');

            // Individual Rebooking
            $table->boolean('is_rebooked')->default(false)->after('refund_processed_by_user_id');
            $table->string('rebooking_status', 30)->nullable()->after('is_rebooked'); // pending | verified
            $table->date('rebooking_departure_date')->nullable()->after('rebooking_status');
            $table->date('rebooking_return_date')->nullable()->after('rebooking_departure_date');
            $table->foreignId('preferred_replacement_schedule_id')
                ->nullable()
                ->constrained('schedules')
                ->nullOnDelete()
                ->after('rebooking_return_date');
            $table->text('disruption_notes')->nullable()->after('preferred_replacement_schedule_id');

            // Verified By (item-level)
            $table->foreignId('verified_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->after('disruption_notes');
            $table->timestamp('verified_at')->nullable()->after('verified_by_user_id');

            // E-Ticket PDF Path
            $table->string('ticket_pdf_path', 255)->nullable()->after('verified_at');
        });

        // Backfill existing passengers: assign item_number per booking (1, 2, 3...) and sync status
        $bookingIds = DB::table('passengers')->distinct()->pluck('booking_id');

        foreach ($bookingIds as $bookingId) {
            $passengers = DB::table('passengers')
                ->where('booking_id', $bookingId)
                ->orderBy('id')
                ->get(['id']);

            $booking = DB::table('bookings')->where('id', $bookingId)->first();
            $bookingStatus = $booking?->status ?? 'pending';

            // Map booking-level status → passenger item status
            $passengerStatus = match ($bookingStatus) {
                'confirmed'         => 'confirmed',
                'cancelled'         => 'cancelled',
                'operator_cancelled'=> 'operator_cancelled',
                'operator_rebooking'=> 'operator_rebooking',
                'pending_rebooking' => 'rebooking_pending',
                default             => 'pending',
            };

            foreach ($passengers as $index => $passenger) {
                $itemNumber = $index + 1;
                $txNumber = $booking?->transaction_number ?? 'UNKNOWN';
                DB::table('passengers')
                    ->where('id', $passenger->id)
                    ->update([
                        'item_number'   => $itemNumber,
                        'ticket_number' => $txNumber . '-' . $itemNumber,
                        'status'        => $passengerStatus,
                    ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('passengers', function (Blueprint $table) {
            $table->dropForeign(['refund_processed_by_user_id']);
            $table->dropForeign(['preferred_replacement_schedule_id']);
            $table->dropForeign(['verified_by_user_id']);

            $table->dropColumn([
                'item_number',
                'ticket_number',
                'status',
                'fare_amount',
                'accommodation_amount',
                'discount_amount',
                'voucher_discount_share',
                'points_discount_share',
                'web_admin_fee_share',
                'transaction_fee_share',
                'item_total',
                'cancellation_fee',
                'refund_amount',
                'refund_status',
                'refund_destination',
                'refund_reference',
                'refund_proof',
                'refund_processed_at',
                'refund_processed_by_user_id',
                'is_rebooked',
                'rebooking_status',
                'rebooking_departure_date',
                'rebooking_return_date',
                'preferred_replacement_schedule_id',
                'disruption_notes',
                'verified_by_user_id',
                'verified_at',
                'ticket_pdf_path',
            ]);
        });
    }
};

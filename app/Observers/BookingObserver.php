<?php

namespace App\Observers;

use App\Actions\Bookings\CreateBookingAction;
use App\Models\Booking;
use App\Models\ScheduleAccommodation;
use App\Models\ScheduleTransportClass;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Log;

class BookingObserver
{
    /**
     * Called AFTER a Booking is updated.
     *
     * When a booking transitions into a cancelled state we restore the
     * tickets_available counters that were decremented at booking-creation time
     * so the seats/accommodations become available again for other passengers.
     */
    public function updated(Booking $booking): void
    {
        // Only react when the status column actually changed to a cancelled state
        if ($booking->wasChanged('status')) {
            $newStatus = $booking->status;
            $oldStatus = $booking->getOriginal('status');

            $cancelledStatuses = [Booking::STATUS_CANCELLED, Booking::STATUS_OPERATOR_CANCELLED, Booking::STATUS_REJECTED];

            $becomingCancelled = in_array($newStatus, $cancelledStatuses, true);
            $wasAlreadyCancelled = in_array($oldStatus, $cancelledStatuses, true);

            // --- RESTORE tickets and reverse/refund Gracia points when a booking is cancelled or rejected ---
            if ($becomingCancelled && ! $wasAlreadyCancelled) {
                $this->restoreTickets($booking);
                app(\App\Services\GraciaPointsService::class)->reversePointsForBooking($booking);
                app(\App\Services\GraciaPointsService::class)->refundRedeemedPoints($booking);
            }

            // --- DEDUCT tickets if a booking is somehow un-cancelled back to active ---
            if (! in_array($newStatus, $cancelledStatuses, true) && $wasAlreadyCancelled) {
                $this->deductTickets($booking);
            }

            // --- SEND NOTIFICATIONS ---
            $user = User::where('email', $booking->client_email)->first();
            if ($user) {
                if ($newStatus === Booking::STATUS_CONFIRMED && $oldStatus !== Booking::STATUS_CONFIRMED) {
                    if ($booking->rebooking_status === 'rejected') {
                        // Reverted from pending_rebooking to confirmed due to rebooking rejection — do not award points or notify confirmed
                    } elseif ($booking->is_rebooked || in_array($booking->rebooking_status, ['verified', 'approved'], true) || $oldStatus === Booking::STATUS_PENDING_REBOOKING) {
                        UserNotification::notify($user->id, "Rebooking Confirmed", "Your rebooking for {$booking->transaction_number} is confirmed! You can now view and download your updated tickets.", 'rebooking', 'check_circle', ['transaction_number' => $booking->transaction_number]);
                        app(\App\Services\GraciaPointsService::class)->awardPointsForBooking($booking);
                    } else {
                        UserNotification::notify($user->id, "Booking Confirmed", "Your booking {$booking->transaction_number} is confirmed! You can now view and download your tickets.", 'booking', 'check_circle', ['transaction_number' => $booking->transaction_number]);
                        app(\App\Services\GraciaPointsService::class)->awardPointsForBooking($booking);
                    }
                } elseif ($newStatus === Booking::STATUS_CANCELLED && $oldStatus !== Booking::STATUS_CANCELLED) {
                    UserNotification::notify($user->id, "Booking Cancelled", "Your booking {$booking->transaction_number} was automatically cancelled.", 'booking', 'cancel', ['transaction_number' => $booking->transaction_number]);
                } elseif ($newStatus === Booking::STATUS_OPERATOR_CANCELLED && $oldStatus !== Booking::STATUS_OPERATOR_CANCELLED) {
                    UserNotification::notify($user->id, "Booking Cancelled", "Your booking {$booking->transaction_number} has been cancelled by the operator.", 'booking', 'error', ['transaction_number' => $booking->transaction_number]);
                }
            }
        }

        if ($booking->wasChanged('rebooking_status')) {
            $newRebookingStatus = $booking->rebooking_status;
            $user = User::where('email', $booking->client_email)->first();
            // Note: Booking::rejectRebooking() sends its own detailed notification with reason.
            // Only notify here for other decline statuses if needed.
            if ($user && in_array($newRebookingStatus, ['declined'], true)) {
                UserNotification::notify(
                    $user->id,
                    "Rebooking Declined",
                    "Your rebooking request for {$booking->transaction_number} has been declined.",
                    'rebooking',
                    'cancel'
                );
            }
        }
    }

    // -------------------------------------------------------------------------

    private function restoreTickets(Booking $booking): void
    {
        try {
            $paxCount = max(1, $booking->relationLoaded('passengers') ? $booking->passengers->count() : $booking->passengers()->count());

            // Restore outbound schedule accommodation
            if ($booking->schedule_accommodation_id) {
                ScheduleAccommodation::where('id', $booking->schedule_accommodation_id)
                    ->increment('tickets_available', $paxCount);
            }

            // Restore return schedule accommodation
            if ($booking->return_schedule_accommodation_id) {
                ScheduleAccommodation::where('id', $booking->return_schedule_accommodation_id)
                    ->increment('tickets_available', $paxCount);
            }

            // Restore outbound transport class seat
            if ($booking->schedule_id) {
                // We need the transport class linked to this booking
                $booking->loadMissing('transportClasses');
                $allTcs = $booking->transportClasses;
                $depTcs = $allTcs->filter(fn ($tc) => ! (bool) $tc->pivot->is_return);
                // Legacy fallback: if no is_return flag, use first entry
                if ($depTcs->isEmpty() && $allTcs->isNotEmpty()) {
                    $depTcs = collect([$allTcs->first()]);
                }
                $depTc = $depTcs->first();
                if ($depTc) {
                    ScheduleTransportClass::where('schedule_id', $booking->schedule_id)
                        ->where('transport_class_id', $depTc->id)
                        ->increment('tickets_available', $paxCount);
                }
            }

            // Restore return transport class seat (return bookings have the
            // return transport class stored in the same pivot with the return schedule)
            if ($booking->return_schedule_id) {
                $booking->loadMissing('transportClasses');
                $allTcs = $booking->transportClasses;
                $retTcs = $allTcs->filter(fn ($tc) => (bool) $tc->pivot->is_return);
                // Legacy fallback: if no is_return flag, use second entry
                if ($retTcs->isEmpty() && $allTcs->count() > 1) {
                    $retTcs = collect([$allTcs->skip(1)->first()]);
                }
                $returnTc = $retTcs->first();
                if ($returnTc) {
                    ScheduleTransportClass::where('schedule_id', $booking->return_schedule_id)
                        ->where('transport_class_id', $returnTc->id)
                        ->increment('tickets_available', $paxCount);
                }
            }

            Log::info('Tickets restored after booking cancellation.', [
                'booking_id'                       => $booking->id,
                'transaction_number'               => $booking->transaction_number,
                'passenger_count'                  => $paxCount,
                'schedule_accommodation_id'        => $booking->schedule_accommodation_id,
                'return_schedule_accommodation_id' => $booking->return_schedule_accommodation_id,
                'schedule_id'                      => $booking->schedule_id,
                'return_schedule_id'               => $booking->return_schedule_id,
            ]);

            // Bust schedule cache so pages reflect restored seats immediately
            $depSched = $booking->relationLoaded('schedule') ? $booking->schedule : ($booking->schedule_id ? \App\Models\Schedule::find($booking->schedule_id) : null);
            $retSched = $booking->relationLoaded('returnSchedule') ? $booking->returnSchedule : ($booking->return_schedule_id ? \App\Models\Schedule::find($booking->return_schedule_id) : null);

            CreateBookingAction::bustScheduleCache(
                $depSched,
                $retSched
            );
        } catch (\Throwable $e) {
            Log::error('Failed to restore tickets after booking cancellation.', [
                'booking_id' => $booking->id ?? null,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    private function deductTickets(Booking $booking): void
    {
        try {
            $paxCount = max(1, $booking->relationLoaded('passengers') ? $booking->passengers->count() : $booking->passengers()->count());

            if ($booking->schedule_accommodation_id) {
                ScheduleAccommodation::where('id', $booking->schedule_accommodation_id)
                    ->where('tickets_available', '>=', $paxCount)
                    ->decrement('tickets_available', $paxCount);
            }

            if ($booking->return_schedule_accommodation_id) {
                ScheduleAccommodation::where('id', $booking->return_schedule_accommodation_id)
                    ->where('tickets_available', '>=', $paxCount)
                    ->decrement('tickets_available', $paxCount);
            }

            if ($booking->schedule_id) {
                $booking->loadMissing('transportClasses');
                $allTcs = $booking->transportClasses;
                $depTcs = $allTcs->filter(fn ($tc) => ! (bool) $tc->pivot->is_return);
                // Legacy fallback: if no is_return flag, use first entry
                if ($depTcs->isEmpty() && $allTcs->isNotEmpty()) {
                    $depTcs = collect([$allTcs->first()]);
                }
                $depTc = $depTcs->first();
                if ($depTc) {
                    ScheduleTransportClass::where('schedule_id', $booking->schedule_id)
                        ->where('transport_class_id', $depTc->id)
                        ->where('tickets_available', '>=', $paxCount)
                        ->decrement('tickets_available', $paxCount);
                }
            }

            if ($booking->return_schedule_id) {
                $booking->loadMissing('transportClasses');
                $allTcs = $booking->transportClasses;
                $retTcs = $allTcs->filter(fn ($tc) => (bool) $tc->pivot->is_return);
                // Legacy fallback: if no is_return flag, use second entry
                if ($retTcs->isEmpty() && $allTcs->count() > 1) {
                    $retTcs = collect([$allTcs->skip(1)->first()]);
                }
                $returnTc = $retTcs->first();
                if ($returnTc) {
                    ScheduleTransportClass::where('schedule_id', $booking->return_schedule_id)
                        ->where('transport_class_id', $returnTc->id)
                        ->where('tickets_available', '>=', $paxCount)
                        ->decrement('tickets_available', $paxCount);
                }
            }

            Log::info('Tickets re-deducted after booking un-cancellation.', [
                'booking_id'         => $booking->id,
                'transaction_number' => $booking->transaction_number,
            ]);

            // Bust schedule cache so pages reflect re-deducted seats immediately
            $depSched = $booking->relationLoaded('schedule') ? $booking->schedule : ($booking->schedule_id ? \App\Models\Schedule::find($booking->schedule_id) : null);
            $retSched = $booking->relationLoaded('returnSchedule') ? $booking->returnSchedule : ($booking->return_schedule_id ? \App\Models\Schedule::find($booking->return_schedule_id) : null);

            CreateBookingAction::bustScheduleCache(
                $depSched,
                $retSched
            );
        } catch (\Throwable $e) {
            Log::error('Failed to re-deduct tickets after booking un-cancellation.', [
                'booking_id' => $booking->id ?? null,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle soft-deletion of a booking.
     * Records the user who initiated the deletion and cascades soft-delete to passengers and transactions.
     */
    public function deleting(Booking $booking): void
    {
        if (auth()->check() && empty($booking->deleted_by_user_id)) {
            $booking->deleted_by_user_id = auth()->id();
            $booking->saveQuietly();
        }

        // Cascade soft deletion to related records
        $booking->passengers()->delete();
        $booking->transaction()->delete();

        Log::info('Booking soft-deleted with audit trail.', [
            'booking_id' => $booking->id,
            'transaction_number' => $booking->transaction_number,
            'deleted_by_user_id' => $booking->deleted_by_user_id,
        ]);
    }

    /**
     * Handle restoration of a soft-deleted booking.
     */
    public function restoring(Booking $booking): void
    {
        $booking->deleted_by_user_id = null;
        $booking->deletion_reason = null;
        $booking->saveQuietly();

        // Cascade restoration to related records
        $booking->passengers()->restore();
        $booking->transaction()->restore();

        Log::info('Booking restored by Super Admin.', [
            'booking_id' => $booking->id,
            'transaction_number' => $booking->transaction_number,
            'restored_by_user_id' => auth()->id(),
        ]);
    }
}

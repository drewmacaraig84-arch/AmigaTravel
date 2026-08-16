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

            $cancelledStatuses = [Booking::STATUS_CANCELLED, Booking::STATUS_OPERATOR_CANCELLED];

            $becomingCancelled = in_array($newStatus, $cancelledStatuses, true);
            $wasAlreadyCancelled = in_array($oldStatus, $cancelledStatuses, true);

            // --- RESTORE tickets when a booking is cancelled ---
            if ($becomingCancelled && ! $wasAlreadyCancelled) {
                $this->restoreTickets($booking);
            }

            // --- DEDUCT tickets if a booking is somehow un-cancelled back to active ---
            if (! in_array($newStatus, $cancelledStatuses, true) && $wasAlreadyCancelled) {
                $this->deductTickets($booking);
            }

            // --- SEND NOTIFICATIONS ---
            $user = User::where('email', $booking->client_email)->first();
            if ($user) {
                if ($newStatus === Booking::STATUS_CONFIRMED && $oldStatus !== Booking::STATUS_CONFIRMED) {
                    UserNotification::notify($user->id, "Booking Confirmed", "Your booking {$booking->transaction_number} is confirmed! You can now view and download your tickets.", 'booking', 'check_circle', ['transaction_number' => $booking->transaction_number]);
                    
                    // Award Gracia Points on confirmation
                    app(\App\Services\GraciaPointsService::class)->awardPointsForBooking($booking);

                    try {
                        $booking->loadMissing('transaction');
                        $transaction = $booking->transaction;
                        if ($transaction) {
                            $ticketUrl   = $transaction->confirmation_url;
                            $receiptPath = $transaction->confirmation_pdf;
                            $receiptDisk = $receiptPath ? 'public' : null;

                            if (! empty($ticketUrl) || ! empty($receiptPath)) {
                                \Illuminate\Support\Facades\Mail::to($booking->client_email)
                                    ->send(new \App\Mail\BookingConfirmation(
                                        booking: $booking,
                                        ticketUrl: $ticketUrl,
                                        receiptPath: $receiptPath,
                                        receiptDisk: $receiptDisk,
                                    ));
                            }
                        }
                    } catch (\Throwable $e) {
                        Log::error(
                            'BookingObserver: Failed to dispatch confirmation email',
                            ['booking_id' => $booking->id, 'error' => $e->getMessage()]
                        );
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
            if ($user && in_array($newRebookingStatus, ['approved', 'rejected'])) {
                UserNotification::notify(
                    $user->id,
                    "Rebooking " . ucfirst($newRebookingStatus),
                    "Your rebooking request for {$booking->transaction_number} has been {$newRebookingStatus}.",
                    'rebooking',
                    $newRebookingStatus === 'approved' ? 'check_circle' : 'cancel'
                );
            }
        }
    }

    // -------------------------------------------------------------------------

    private function restoreTickets(Booking $booking): void
    {
        try {
            // Restore outbound schedule accommodation
            if ($booking->schedule_accommodation_id) {
                ScheduleAccommodation::where('id', $booking->schedule_accommodation_id)
                    ->increment('tickets_available');
            }

            // Restore return schedule accommodation
            if ($booking->return_schedule_accommodation_id) {
                ScheduleAccommodation::where('id', $booking->return_schedule_accommodation_id)
                    ->increment('tickets_available');
            }

            // Restore outbound transport class seat
            if ($booking->schedule_id) {
                // We need the transport class linked to this booking
                $booking->loadMissing('transportClasses');
                foreach ($booking->transportClasses as $tc) {
                    ScheduleTransportClass::where('schedule_id', $booking->schedule_id)
                        ->where('transport_class_id', $tc->id)
                        ->increment('tickets_available');
                    break; // Only one outbound transport class per booking
                }
            }

            // Restore return transport class seat (return bookings have the
            // return transport class stored in the same pivot with the return schedule)
            if ($booking->return_schedule_id) {
                // Return transport class is the second entry in the pivot (if present)
                $booking->loadMissing('transportClasses');
                $classes = $booking->transportClasses;
                if ($classes->count() > 1) {
                    $returnTc = $classes->skip(1)->first();
                    if ($returnTc) {
                        ScheduleTransportClass::where('schedule_id', $booking->return_schedule_id)
                            ->where('transport_class_id', $returnTc->id)
                            ->increment('tickets_available');
                    }
                }
            }

            Log::info('Tickets restored after booking cancellation.', [
                'booking_id'                       => $booking->id,
                'transaction_number'               => $booking->transaction_number,
                'schedule_accommodation_id'        => $booking->schedule_accommodation_id,
                'return_schedule_accommodation_id' => $booking->return_schedule_accommodation_id,
                'schedule_id'                      => $booking->schedule_id,
                'return_schedule_id'               => $booking->return_schedule_id,
            ]);

            // Bust schedule cache so pages reflect restored seats immediately
            CreateBookingAction::bustScheduleCache(
                $booking->schedule ?? null,
                $booking->returnSchedule ?? null
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
            if ($booking->schedule_accommodation_id) {
                ScheduleAccommodation::where('id', $booking->schedule_accommodation_id)
                    ->where('tickets_available', '>', 0)
                    ->decrement('tickets_available');
            }

            if ($booking->return_schedule_accommodation_id) {
                ScheduleAccommodation::where('id', $booking->return_schedule_accommodation_id)
                    ->where('tickets_available', '>', 0)
                    ->decrement('tickets_available');
            }

            if ($booking->schedule_id) {
                $booking->loadMissing('transportClasses');
                foreach ($booking->transportClasses as $tc) {
                    ScheduleTransportClass::where('schedule_id', $booking->schedule_id)
                        ->where('transport_class_id', $tc->id)
                        ->where('tickets_available', '>', 0)
                        ->decrement('tickets_available');
                    break;
                }
            }

            if ($booking->return_schedule_id) {
                $booking->loadMissing('transportClasses');
                $classes = $booking->transportClasses;
                if ($classes->count() > 1) {
                    $returnTc = $classes->skip(1)->first();
                    if ($returnTc) {
                        ScheduleTransportClass::where('schedule_id', $booking->return_schedule_id)
                            ->where('transport_class_id', $returnTc->id)
                            ->where('tickets_available', '>', 0)
                            ->decrement('tickets_available');
                    }
                }
            }

            Log::info('Tickets re-deducted after booking un-cancellation.', [
                'booking_id'         => $booking->id,
                'transaction_number' => $booking->transaction_number,
            ]);

            // Bust schedule cache so pages reflect re-deducted seats immediately
            CreateBookingAction::bustScheduleCache(
                $booking->schedule ?? null,
                $booking->returnSchedule ?? null
            );
        } catch (\Throwable $e) {
            Log::error('Failed to re-deduct tickets after booking un-cancellation.', [
                'booking_id' => $booking->id ?? null,
                'error'      => $e->getMessage(),
            ]);
        }
    }
}

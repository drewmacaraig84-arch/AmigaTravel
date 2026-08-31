<?php

namespace App\Jobs;

use App\Mail\BookingCreated;
use App\Models\Booking;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

/**
 * Queued job: generates the booking receipt PDF and emails it to the client.
 *
 * By moving this out of the HTTP request cycle, the API response returns instantly
 * and the heavy I/O work (PDF rendering + SMTP) happens in a background worker.
 */
class SendBookingConfirmationJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    /**
     * Maximum attempts before the job is marked as failed.
     */
    public int $tries = 3;

    /**
     * Seconds to wait before retrying a failed attempt.
     *
     * @var array<int, int>
     */
    public array $backoff = [30, 60, 120];

    /**
     * Maximum seconds this job may run.
     */
    public int $timeout = 90;

    public function __construct(public readonly Booking $booking) {}

    public function handle(): void
    {
        // Ensure relations are loaded so the Mailable/PDF view has everything it needs
        $this->booking->loadMissing(
            'passengers.discount',
            'accommodations',
            'transaction',
            'schedule',
            'returnSchedule',
            'transportClasses',
            'scheduleAccommodation',
            'returnScheduleAccommodation',
            'voucher',
        );

        // --- Send email ---
        Mail::to($this->booking->client_email)
            ->send(new BookingCreated($this->booking));
    }

    public function failed(\Throwable $exception): void
    {
        // Log the failure silently — a failed email should never break the booking flow.
        \Illuminate\Support\Facades\Log::error('SendBookingConfirmationJob failed', [
            'booking_id'         => $this->booking->id,
            'transaction_number' => $this->booking->transaction_number,
            'error'              => $exception->getMessage(),
        ]);
    }
}

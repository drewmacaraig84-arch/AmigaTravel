<?php

namespace App\Mail;

use App\Models\Booking;
use App\Models\ServiceCancellation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use Illuminate\Contracts\Queue\ShouldQueue;

class ServiceCancellationNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Booking $booking,
        public ServiceCancellation $cancellation,
        public bool $isResumption = false
    ) {}

    public function envelope(): Envelope
    {
        $carrierName = $this->cancellation->carrier 
            ?? $this->cancellation->ferryRoute?->operator 
            ?? $this->cancellation->vehicle?->operator 
            ?? 'the operator';

        $subject = $this->isResumption
            ? "🟢 GOOD NEWS: Travel Operations Resuming — Select New Date for Booking #{$this->booking->transaction_number}"
            : "🔴 IMPORTANT: Schedule Cancelled for Booking #{$this->booking->transaction_number} ({$carrierName})";

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        $baseUrl = config('app.url') ?: 'https://www.amigagracia.com';
        $cleanBase = rtrim($baseUrl, '/');
        $rescheduleUrl = $cleanBase . '/booking/reschedule/' . $this->booking->transaction_number;

        return new Content(
            view: 'emails.service-cancellation-notification',
            with: [
                'rescheduleUrl' => $rescheduleUrl,
                'isResumption'  => $this->isResumption,
            ],
        );
    }
}

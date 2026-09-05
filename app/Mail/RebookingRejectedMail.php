<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class RebookingRejectedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Booking $booking;
    public string $reason;
    public ?string $notes;
    public string $bookingUrl;

    public function __construct(Booking $booking, string $reason, ?string $notes = null)
    {
        $this->booking = $booking;
        $this->reason  = $reason;
        $this->notes   = $notes;

        $this->bookingUrl = url('/book/status') . '?' . http_build_query([
            'transaction_number' => $booking->transaction_number,
            'email'              => $booking->client_email,
        ]);
    }

    public function build(): self
    {
        return $this
            ->subject('Rebooking Request Update — Booking #' . $this->booking->transaction_number)
            ->view('emails.rebooking-rejected');
    }
}

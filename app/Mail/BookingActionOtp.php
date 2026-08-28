<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingActionOtp extends Mailable
{
    use Queueable, SerializesModels;

    public Booking $booking;
    public string $otp;
    public string $actionTitle;

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking, string $otp, string $actionTitle = 'Cancellation & Refund Request')
    {
        $this->booking = $booking;
        $this->otp = $otp;
        $this->actionTitle = $actionTitle;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject("[Amiga Gracia] Verification Code: {$this->otp} for your {$this->actionTitle}")
                    ->view('emails.booking-action-otp');
    }
}

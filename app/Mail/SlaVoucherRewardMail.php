<?php

namespace App\Mail;

use App\Models\Booking;
use App\Models\Voucher;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SlaVoucherRewardMail extends Mailable
{
    use Queueable, SerializesModels;

    public Booking $booking;
    public Voucher $voucher;

    public function __construct(Booking $booking, Voucher $voucher)
    {
        $this->booking = $booking;
        $this->voucher = $voucher;
    }

    public function build()
    {
        return $this->subject('Gift from Amiga Travel: Your Verification Guarantee Voucher (' . $this->voucher->code . ')')
            ->view('emails.sla-voucher-reward');
    }
}

<?php

namespace App\Mail;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class PaymentProofReceived extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Transaction $transaction;

    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    public function build()
    {
        $txNumber = $this->transaction->booking?->transaction_number ?? 'Booking';

        return $this->subject("Payment Proof Received - {$txNumber} (Under Review) | Amiga Travel")
            ->view('emails.payment-proof-received');
    }
}

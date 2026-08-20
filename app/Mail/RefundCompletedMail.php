<?php

namespace App\Mail;

use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Throwable;

class RefundCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Booking $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function build(): self
    {
        $mail = $this->subject("Refund Processed & Disbursed — Booking #{$this->booking->transaction_number}")
            ->view('emails.refund-completed')
            ->with([
                'booking' => $this->booking,
            ]);

        // Attach Refund Acknowledgement PDF
        try {
            $pdf = Pdf::loadView('pdf.refund-acknowledgement', [
                'booking' => $this->booking,
            ]);

            $mail->attachData(
                $pdf->output(),
                "Refund-Acknowledgement-{$this->booking->transaction_number}.pdf",
                ['mime' => 'application/pdf']
            );
        } catch (Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Failed attaching Refund Acknowledgement PDF: " . $e->getMessage());
        }

        // Attach Proof of Refund if uploaded
        if (! empty($this->booking->refund_proof)) {
            $disk = Storage::disk('public');
            if ($disk->exists($this->booking->refund_proof)) {
                $path = $disk->path($this->booking->refund_proof);
                $ext = pathinfo($this->booking->refund_proof, PATHINFO_EXTENSION);
                $mime = $ext === 'pdf' ? 'application/pdf' : 'image/jpeg';
                $mail->attach($path, [
                    'as' => "Refund-Proof-{$this->booking->transaction_number}.{$ext}",
                    'mime' => $mime,
                ]);
            }
        }

        return $mail;
    }
}

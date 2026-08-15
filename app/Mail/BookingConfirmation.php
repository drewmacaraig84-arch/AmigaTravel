<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class BookingConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Booking $booking;
    public ?string $ticketUrl;
    public ?string $receiptPath;
    public ?string $receiptDisk;

    public function __construct(Booking $booking, ?string $ticketUrl = null, ?string $receiptPath = null, ?string $receiptDisk = null)
    {
        $this->booking = $booking;
        $this->ticketUrl = $ticketUrl;
        $this->receiptPath = $receiptPath;
        $this->receiptDisk = $receiptDisk;
    }

    public function build()
    {
        $mail = $this->subject('Amiga Gracia Travel Booking Confirmation')
            ->view('emails.booking-confirmation');

        // Generate the official receipt PDF now that they have paid
        try {
            $receiptDir  = storage_path('app/receipts');
            $autoReceiptPath = $receiptDir . '/receipt-' . $this->booking->transaction_number . '.pdf';

            if (! is_dir($receiptDir)) {
                mkdir($receiptDir, 0755, true);
            }

            \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.receipt', ['booking' => $this->booking])
                ->setPaper('a4')
                ->save($autoReceiptPath);

            $mail->attach($autoReceiptPath, [
                'as' => 'Payment_Acknowledgement.pdf',
                'mime' => 'application/pdf',
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('BookingConfirmation: Failed to generate receipt PDF', [
                'booking_id' => $this->booking->id ?? null,
                'transaction_number' => $this->booking->transaction_number ?? null,
                'error' => $e->getMessage(),
            ]);
            // Email will still send, just without the auto-generated PDF
        }

        if ($this->receiptPath) {
            if ($this->receiptDisk && Storage::disk($this->receiptDisk)->exists($this->receiptPath)) {
                $mail->attachFromStorageDisk($this->receiptDisk, $this->receiptPath, 'Ticket_Confirmation.pdf', [
                    'mime' => 'application/pdf',
                ]);
            } elseif (file_exists($this->receiptPath)) {
                $mail->attach($this->receiptPath, [
                    'as' => 'Ticket_Confirmation.pdf',
                    'mime' => 'application/pdf',
                ]);
            }
        }

        return $mail;
    }

}

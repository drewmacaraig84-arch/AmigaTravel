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
    public bool $hasTicketAttachment = false;

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

        // Generate the official payment acknowledgement PDF
        try {
            $ackDir = storage_path('app/acknowledgements');
            $autoAckPath = $ackDir . '/acknowledgement-' . $this->booking->transaction_number . '.pdf';

            if (! is_dir($ackDir)) {
                mkdir($ackDir, 0755, true);
            }

            if (file_exists($autoAckPath)) {
                @unlink($autoAckPath);
            }

            \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.receipt', ['booking' => $this->booking])
                ->setPaper('a4')
                ->save($autoAckPath);

            $mail->attach($autoAckPath, [
                'as' => 'Payment_Acknowledgement.pdf',
                'mime' => 'application/pdf',
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('BookingConfirmation: Failed to generate payment acknowledgement PDF', [
                'booking_id' => $this->booking->id ?? null,
                'transaction_number' => $this->booking->transaction_number ?? null,
                'error' => $e->getMessage(),
            ]);
            // Email will still send, just without the auto-generated PDF
        }

        $transaction = $this->booking->transaction ?? \App\Models\Transaction::where('booking_id', $this->booking->id)->first();
        $receiptToAttach = $this->receiptPath ?: ($transaction?->confirmation_pdf ?? null);
        $diskToUse = $this->receiptDisk ?: 'public';

        \Illuminate\Support\Facades\Log::info('BookingConfirmation: building email', [
            'booking_id'      => $this->booking->id ?? null,
            'ticketUrl'       => $this->ticketUrl,
            'receiptToAttach' => $receiptToAttach,
            'diskToUse'       => $diskToUse,
        ]);

        if ($receiptToAttach) {
            $resolvedFullPath = $this->resolveAttachmentPath($receiptToAttach, $diskToUse);
            if ($resolvedFullPath) {
                $mail->attach($resolvedFullPath, [
                    'as' => 'Ticket_Confirmation.pdf',
                    'mime' => 'application/pdf',
                ]);
                $this->hasTicketAttachment = true;
            } else {
                // Cloud / virtual disk fallback via attachData
                try {
                    if (Storage::disk($diskToUse)->exists($receiptToAttach)) {
                        $fileData = Storage::disk($diskToUse)->get($receiptToAttach);
                        if ($fileData) {
                            $mail->attachData($fileData, 'Ticket_Confirmation.pdf', [
                                'mime' => 'application/pdf',
                            ]);
                            $this->hasTicketAttachment = true;
                        }
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('BookingConfirmation: ticket PDF could not be located on disk or storage.', [
                        'booking_id' => $this->booking->id ?? null,
                        'receiptPath' => $receiptToAttach,
                        'receiptDisk' => $diskToUse,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return $mail;
    }

    /**
     * Resolve a ticket/receipt path into a concrete filesystem path.
     * Returns null if file cannot be found on local filesystem.
     */
    private function resolveAttachmentPath(string $path, ?string $disk = 'public'): ?string
    {
        $cleanPath = ltrim($path, '/\\');
        $baseName = basename($path);

        // 1. Direct filesystem check
        if (file_exists($path) && is_file($path)) {
            return $path;
        }

        // 2. Storage disk 'public' path
        $disks = array_values(array_unique(array_filter([$disk, 'public', 'local'])));
        foreach ($disks as $d) {
            try {
                if (Storage::disk($d)->exists($path)) {
                    $p = Storage::disk($d)->path($path);
                    if (file_exists($p) && is_file($p)) {
                        return $p;
                    }
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        // 3. Common filesystem candidates
        $candidates = [
            storage_path('app/public/' . $cleanPath),
            storage_path('app/' . $cleanPath),
            public_path('storage/' . $cleanPath),
            storage_path('app/public/tickets/' . $baseName),
            storage_path('app/tickets/' . $baseName),
            public_path('tickets/' . $baseName),
            storage_path('app/public/receipts/' . $baseName),
            storage_path('app/receipts/' . $baseName),
            public_path('receipts/' . $baseName),
        ];

        foreach ($candidates as $candidate) {
            if (file_exists($candidate) && is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}

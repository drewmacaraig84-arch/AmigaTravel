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
            $attachInfo = $this->resolveAttachment($this->receiptPath, $this->receiptDisk);
            if ($attachInfo) {
                [$resolvedPath, $attachAsDisk, $resolvedDisk] = $attachInfo;
                if ($attachAsDisk) {
                    $mail->attachFromStorageDisk($resolvedDisk, $resolvedPath, 'Ticket_Confirmation.pdf', [
                        'mime' => 'application/pdf',
                    ]);
                } else {
                    $mail->attach($resolvedPath, [
                        'as' => 'Ticket_Confirmation.pdf',
                        'mime' => 'application/pdf',
                    ]);
                }
                $this->hasTicketAttachment = true;
            } else {
                \Illuminate\Support\Facades\Log::warning('BookingConfirmation: ticket PDF could not be located on disk.', [
                    'booking_id' => $this->booking->id ?? null,
                    'receiptPath' => $this->receiptPath,
                    'receiptDisk' => $this->receiptDisk,
                ]);
            }
        }

        return $mail;
    }

    /**
     * Resolve a receipt path + disk hint into one of:
     *   - [relativePath, true, diskName]   -> use attachFromStorageDisk
     *   - [absoluteFsPath, false, null]    -> use attach()
     * Returns null if file cannot be found.
     *
     * Correctly handles callers that accidentally pass an absolute filesystem
     * path together with a disk (e.g. ServiceCancellationManager).
     */
    private function resolveAttachment(string $path, ?string $disk): ?array
    {
        // Absolute path on filesystem: always go through attach()
        if (str_starts_with($path, DIRECTORY_SEPARATOR) ||
            (strlen($path) > 2 && ctype_alpha($path[0]) && $path[1] === ':')) {
            if (file_exists($path)) {
                return [$path, false, null];
            }
            return null;
        }

        // Relative path — try explicit disk first, then 'public' by default
        $disks = array_values(array_unique(array_filter([$disk, 'public'])));
        foreach ($disks as $d) {
            try {
                if (Storage::disk($d)->exists($path)) {
                    return [$path, true, $d];
                }
            } catch (\Throwable $e) {
                // Skip misconfigured disks
            }
        }

        // Last resort: treat as direct FS path
        if (file_exists($path)) {
            return [$path, false, null];
        }

        return null;
    }
}

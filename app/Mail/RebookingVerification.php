<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class RebookingVerification extends Mailable implements ShouldQueue
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
        $mail = $this->subject('Amiga Gracia Travel Rebooking Verified')
            ->view('emails.rebooking-verification');

        if ($this->receiptPath) {
            $attachInfo = $this->resolveAttachment($this->receiptPath, $this->receiptDisk);
            if ($attachInfo) {
                [$resolvedPath, $attachAsDisk, $resolvedDisk] = $attachInfo;
                if ($attachAsDisk) {
                    $mail->attachFromStorageDisk($resolvedDisk, $resolvedPath, 'rebooking-confirmation.pdf', [
                        'mime' => 'application/pdf',
                    ]);
                } else {
                    $mail->attach($resolvedPath, [
                        'as' => 'rebooking-confirmation.pdf',
                        'mime' => 'application/pdf',
                    ]);
                }
                $this->hasTicketAttachment = true;
            } else {
                \Illuminate\Support\Facades\Log::warning('RebookingVerification: ticket PDF could not be located on disk.', [
                    'booking_id' => $this->booking->id ?? null,
                    'receiptPath' => $this->receiptPath,
                    'receiptDisk' => $this->receiptDisk,
                ]);
            }
        }

        return $mail;
    }

    /**
     * Resolve a receipt path + disk hint similarly to BookingConfirmation.
     */
    private function resolveAttachment(string $path, ?string $disk): ?array
    {
        if (str_starts_with($path, DIRECTORY_SEPARATOR) ||
            (strlen($path) > 2 && ctype_alpha($path[0]) && $path[1] === ':')) {
            if (file_exists($path)) {
                return [$path, false, null];
            }
            return null;
        }

        $disks = array_values(array_unique(array_filter([$disk, 'public'])));
        foreach ($disks as $d) {
            try {
                if (Storage::disk($d)->exists($path)) {
                    return [$path, true, $d];
                }
            } catch (\Throwable $e) {
                // skip misconfigured disks
            }
        }

        if (file_exists($path)) {
            return [$path, false, null];
        }

        return null;
    }
}

<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class RebookingVerification extends Mailable
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
        $mail = $this->subject('Amiga Gracia Travel Rebooking Confirmed')
            ->view('emails.rebooking-verification');

        // Generate the official payment acknowledgement PDF (same as BookingConfirmation)
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
                'as'   => 'Payment_Acknowledgement.pdf',
                'mime' => 'application/pdf',
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('RebookingVerification: Failed to generate payment acknowledgement PDF', [
                'booking_id'         => $this->booking->id ?? null,
                'transaction_number' => $this->booking->transaction_number ?? null,
                'error'              => $e->getMessage(),
            ]);
        }

        $transaction = $this->booking->transaction ?? \App\Models\Transaction::where('booking_id', $this->booking->id)->first();
        $receiptToAttach = $this->receiptPath ?: ($transaction?->confirmation_pdf ?? null);
        $diskToUse = $this->receiptDisk ?: 'public';

        \Illuminate\Support\Facades\Log::info('RebookingVerification: building email', [
            'booking_id'      => $this->booking->id ?? null,
            'ticketUrl'       => $this->ticketUrl,
            'receiptToAttach' => $receiptToAttach,
            'diskToUse'       => $diskToUse,
        ]);

        $ticketAttached = false;
        if ($receiptToAttach) {
            $resolvedFullPath = $this->resolveAttachmentPath($receiptToAttach, $diskToUse);
            if ($resolvedFullPath) {
                $mail->attach($resolvedFullPath, [
                    'as'   => 'Ticket_Confirmation.pdf',
                    'mime' => 'application/pdf',
                ]);
                $this->hasTicketAttachment = true;
                $ticketAttached = true;
            } else {
                try {
                    if (Storage::disk($diskToUse)->exists($receiptToAttach)) {
                        $fileData = Storage::disk($diskToUse)->get($receiptToAttach);
                        if ($fileData) {
                            $mail->attachData($fileData, 'Ticket_Confirmation.pdf', [
                                'mime' => 'application/pdf',
                            ]);
                            $this->hasTicketAttachment = true;
                            $ticketAttached = true;
                        }
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('RebookingVerification: ticket PDF could not be located on disk or storage.', [
                        'booking_id'  => $this->booking->id ?? null,
                        'receiptPath' => $receiptToAttach,
                        'receiptDisk' => $diskToUse,
                        'error'       => $e->getMessage(),
                    ]);
                }
            }
        }

        // If no external ticket PDF was uploaded or found, auto-generate official e-ticket itinerary PDF
        if (! $ticketAttached) {
            try {
                $ticketDir = storage_path('app/tickets');
                $autoTicketPath = $ticketDir . '/itinerary-' . $this->booking->transaction_number . '.pdf';

                if (! is_dir($ticketDir)) {
                    mkdir($ticketDir, 0755, true);
                }

                if (file_exists($autoTicketPath)) {
                    @unlink($autoTicketPath);
                }

                \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.receipt', ['booking' => $this->booking, 'isTicket' => true])
                    ->setPaper('a4')
                    ->save($autoTicketPath);

                $mail->attach($autoTicketPath, [
                    'as'   => 'Ticket_Confirmation.pdf',
                    'mime' => 'application/pdf',
                ]);
                $this->hasTicketAttachment = true;
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('RebookingVerification: Failed to auto-generate fallback ticket itinerary PDF', [
                    'booking_id'         => $this->booking->id ?? null,
                    'transaction_number' => $this->booking->transaction_number ?? null,
                    'error'              => $e->getMessage(),
                ]);
            }
        }

        return $mail;
    }

    /**
     * Resolve a ticket/receipt path into a concrete filesystem path.
     */
    private function resolveAttachmentPath(string $path, ?string $disk = 'public'): ?string
    {
        return Booking::resolveAttachmentPath($path, $disk);
    }
}

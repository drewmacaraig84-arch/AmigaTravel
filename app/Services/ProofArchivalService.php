<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\PaymentSetting;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ProofArchivalService
{
    /**
     * Permanent storage directory for proof archives.
     */
    public const ARCHIVE_DIR = 'proof_archives';

    /**
     * Create a pre-retention ZIP archive for proofs and receipts expiring soon (e.g. 1 day before retention limit).
     */
    public function createPreRetentionArchive(?int $days = null): ?array
    {
        $retentionDays = $days ?? PaymentSetting::current()->proof_retention_days;

        if (! $retentionDays || $retentionDays <= 0) {
            return null; // Retention is disabled
        }

        // Target items that are (retentionDays - 1) days old or older
        $thresholdDate = now()->subDays(max(1, $retentionDays - 1));

        $transactions = Transaction::query()
            ->with(['booking.passengers', 'booking.schedule.ferryRoute'])
            ->where(function ($q) {
                $q->whereNotNull('proof_of_payment')
                  ->orWhereNotNull('rebooking_proof_of_payment');
            })
            ->where('updated_at', '<=', $thresholdDate)
            ->get();

        $bookings = Booking::query()
            ->with(['transaction', 'passengers', 'schedule.ferryRoute'])
            ->whereNotNull('refund_proof')
            ->where('updated_at', '<=', $thresholdDate)
            ->get();

        if ($transactions->isEmpty() && $bookings->isEmpty()) {
            return null;
        }

        $archiveDir = storage_path('app/' . self::ARCHIVE_DIR);
        if (! is_dir($archiveDir)) {
            mkdir($archiveDir, 0755, true);
        }

        $archiveFilename = 'proofs_archive_' . now()->format('Y-m-d_His') . '.zip';
        $archivePath = $archiveDir . '/' . $archiveFilename;

        $zip = new ZipArchive();
        if ($zip->open($archivePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            Log::error("ProofArchivalService: Could not open archive {$archivePath}");
            return null;
        }

        $filesAdded = 0;
        $disk = Storage::disk('public');

        // 1. Package transaction proofs and official receipts
        foreach ($transactions as $tx) {
            $booking = $tx->booking;
            $txNumber = $booking?->transaction_number ?? ('TX-' . $tx->id);

            // A. Confirmed Proof
            if ($tx->proof_of_payment && $disk->exists($tx->proof_of_payment)) {
                $content = $disk->get($tx->proof_of_payment);
                if ($content) {
                    $ext = pathinfo($tx->proof_of_payment, PATHINFO_EXTENSION) ?: 'jpg';
                    $entryName = "proofs/{$txNumber}_proof.{$ext}";
                    $zip->addFromString($this->resolveUniqueEntryName($zip, $entryName), $content);
                    $filesAdded++;
                }
            }

            // B. Rebooked Proof
            if ($tx->rebooking_proof_of_payment && $disk->exists($tx->rebooking_proof_of_payment)) {
                $content = $disk->get($tx->rebooking_proof_of_payment);
                if ($content) {
                    $ext = pathinfo($tx->rebooking_proof_of_payment, PATHINFO_EXTENSION) ?: 'jpg';
                    $entryName = "proofs/{$txNumber}-Rebooked_proof.{$ext}";
                    $zip->addFromString($this->resolveUniqueEntryName($zip, $entryName), $content);
                    $filesAdded++;
                }
            }

            // C. Official Receipt PDF
            if ($booking) {
                try {
                    $pdfContent = Pdf::loadView('pdf.receipt', [
                        'booking' => $booking,
                        'receiptType' => $booking->is_rebooked ? 'rebooked' : 'confirmed',
                        'isTicket' => false,
                    ])->setPaper('a4')->output();

                    $pdfName = "receipts/{$txNumber}" . ($booking->is_rebooked ? '-Rebooked' : '') . "_receipt.pdf";
                    $zip->addFromString($this->resolveUniqueEntryName($zip, $pdfName), $pdfContent);
                    $filesAdded++;
                } catch (\Throwable $e) {
                    Log::warning("ProofArchivalService: Failed to generate PDF for {$txNumber}: " . $e->getMessage());
                }
            }
        }

        // 2. Package refund disbursement proofs and refund receipts
        foreach ($bookings as $b) {
            $txNumber = $b->transaction_number ?? ('BK-' . $b->id);

            if ($b->refund_proof && $disk->exists($b->refund_proof)) {
                $content = $disk->get($b->refund_proof);
                if ($content) {
                    $ext = pathinfo($b->refund_proof, PATHINFO_EXTENSION) ?: 'jpg';
                    $entryName = "proofs/{$txNumber}-Refunded_proof.{$ext}";
                    $zip->addFromString($this->resolveUniqueEntryName($zip, $entryName), $content);
                    $filesAdded++;
                }
            }

            try {
                $pdfContent = Pdf::loadView('pdf.receipt', [
                    'booking' => $b,
                    'receiptType' => 'refunded',
                    'isTicket' => false,
                ])->setPaper('a4')->output();

                $pdfName = "receipts/{$txNumber}-Refunded_receipt.pdf";
                $zip->addFromString($this->resolveUniqueEntryName($zip, $pdfName), $pdfContent);
                $filesAdded++;
            } catch (\Throwable $e) {
                Log::warning("ProofArchivalService: Failed to generate refund PDF for {$txNumber}: " . $e->getMessage());
            }
        }

        $zip->close();

        if ($filesAdded === 0 || ! file_exists($archivePath)) {
            if (file_exists($archivePath)) {
                @unlink($archivePath);
            }
            return null;
        }

        return [
            'filename' => $archiveFilename,
            'path' => $archivePath,
            'files_count' => $filesAdded,
            'size' => filesize($archivePath),
            'formatted_size' => $this->formatBytes(filesize($archivePath)),
            'created_at' => now(),
        ];
    }

    /**
     * List all available pre-retention archives.
     */
    public function listArchives(): Collection
    {
        $archiveDir = storage_path('app/' . self::ARCHIVE_DIR);
        if (! is_dir($archiveDir)) {
            return collect();
        }

        $files = glob($archiveDir . '/*.zip');
        if (! $files) {
            return collect();
        }

        return collect($files)->map(function ($filepath) {
            $filename = basename($filepath);
            $size = file_exists($filepath) ? filesize($filepath) : 0;
            $time = file_exists($filepath) ? filemtime($filepath) : time();

            return (object) [
                'filename' => $filename,
                'path' => $filepath,
                'size' => $size,
                'formatted_size' => $this->formatBytes($size),
                'created_at' => \Carbon\Carbon::createFromTimestamp($time),
                'download_url' => route('admin.proofs.download-archive', ['filename' => $filename]),
            ];
        })->sortByDesc('created_at')->values();
    }

    /**
     * Ensure entry names inside ZIP do not collide.
     */
    private function resolveUniqueEntryName(ZipArchive $zip, string $name): string
    {
        if ($zip->statName($name) === false) {
            return $name;
        }

        $info = pathinfo($name);
        $dirname = $info['dirname'] && $info['dirname'] !== '.' ? $info['dirname'] . '/' : '';
        $filename = $info['filename'];
        $ext = isset($info['extension']) ? '.' . $info['extension'] : '';

        $counter = 1;
        do {
            $candidate = "{$dirname}{$filename}_{$counter}{$ext}";
            $counter++;
        } while ($zip->statName($candidate) !== false);

        return $candidate;
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

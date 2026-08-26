<?php

namespace App\Console\Commands;

use App\Models\PaymentSetting;
use App\Models\Transaction;
use Illuminate\Console\Command;

class PurgeExpiredProofs extends Command
{
    protected $signature = 'proofs:purge';

    protected $description = 'Delete payment proof images older than the configured retention period';

    public function handle(): int
    {
        $days = PaymentSetting::current()->proof_retention_days;

        if (! $days || $days <= 0) {
            $this->info('Proof retention is disabled (0 days). Skipping purge.');

            return self::SUCCESS;
        }

        // 1. Automatically create pre-retention ZIP backup 1 day before expiration
        try {
            $archive = app(\App\Services\ProofArchivalService::class)->createPreRetentionArchive($days);
            if ($archive) {
                $this->info("Created pre-retention backup ZIP: {$archive['filename']} ({$archive['files_count']} items, {$archive['formatted_size']}).");
            }
        } catch (\Throwable $e) {
            $this->warn("Failed to generate pre-retention ZIP archive: " . $e->getMessage());
        }

        // 2. Purge expired proofs older than retention limit
        $transactions = Transaction::query()
            ->whereNotNull('proof_of_payment')
            ->where('updated_at', '<=', now()->subDays($days))
            ->get();

        $count = 0;

        foreach ($transactions as $transaction) {
            $transaction->deleteProof();
            $count++;
        }

        $this->info("Purged {$count} expired proof(s).");

        return self::SUCCESS;
    }
}

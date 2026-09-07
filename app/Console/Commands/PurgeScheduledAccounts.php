<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserNotification;
use App\Models\GraciaUserBalance;
use App\Models\GraciaPointLedger;
use App\Models\UserLoginHistory;
use Illuminate\Console\Command;

class PurgeScheduledAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accounts:purge-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permanently purge accounts whose 14-day deletion grace period has expired';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $expiredUsers = User::whereNotNull('deletion_scheduled_at')
            ->where('deletion_scheduled_at', '<=', now())
            ->get();

        $count = $expiredUsers->count();

        if ($count === 0) {
            $this->info('No expired accounts scheduled for deletion.');
            return self::SUCCESS;
        }

        $this->info("Found {$count} account(s) ready for permanent deletion.");

        foreach ($expiredUsers as $user) {
            $userId = $user->id;
            $email = $user->email;

            // Delete associated personal records
            UserNotification::where('user_id', $userId)->delete();
            GraciaUserBalance::where('user_id', $userId)->delete();
            GraciaPointLedger::where('user_id', $userId)->delete();
            UserLoginHistory::where('user_id', $userId)->delete();

            if (method_exists($user, 'tokens')) {
                $user->tokens()->delete();
            }

            // Permanently delete user record
            $user->delete();

            $this->line("Purged user ID {$userId} ({$email}).");
        }

        $this->info("Successfully purged {$count} account(s).");
        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:cleanup-old-schedules')]
#[Description('Deactivates schedules that have departed more than 1 day ago (preserves historical bookings)')]
class CleanupOldSchedules extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = \App\Models\Schedule::where('is_active', true)
            ->where('departure_time', '<=', now()->subDay())
            ->update(['is_active' => false]);

        $this->info("Deactivated $count old schedules while preserving all booking relations.");
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:cleanup-old-schedules')]
#[Description('Deletes schedules that have departed more than 1 day ago')]
class CleanupOldSchedules extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = 0;
        \App\Models\Schedule::where('departure_time', '<=', now()->subDay())->each(function ($schedule) use (&$count) {
            $schedule->delete();
            $count++;
        });

        $this->info("Deleted $count old schedules.");
    }
}

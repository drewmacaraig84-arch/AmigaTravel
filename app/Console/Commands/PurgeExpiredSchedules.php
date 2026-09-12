<?php

namespace App\Console\Commands;

use App\Models\Schedule;
use Illuminate\Console\Command;

class PurgeExpiredSchedules extends Command
{
    protected $signature = 'schedules:purge-expired';

    protected $description = 'Deactivate schedules whose arrival or departure is older than 24 hours (preserves historical bookings)';

    public function handle(): int
    {
        $cutoff = now()->subDay();

        // Deactivate active schedules that arrived or departed > 24 hours ago in a single fast query
        $count = Schedule::query()
            ->where('is_active', true)
            ->where(function ($q) use ($cutoff) {
                $q->where(function ($sub) use ($cutoff) {
                    $sub->whereNotNull('arrival_time')
                        ->where('arrival_time', '<=', $cutoff);
                })->orWhere(function ($sub) use ($cutoff) {
                    $sub->whereNull('arrival_time')
                        ->where('departure_time', '<=', $cutoff);
                });
            })
            ->update(['is_active' => false]);

        $this->info("Deactivated {$count} expired schedule(s) while preserving all past booking relations.");

        return self::SUCCESS;
    }
}

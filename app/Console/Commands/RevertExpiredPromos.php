<?php

namespace App\Console\Commands;

use App\Models\PromotionalTicket;
use App\Models\ScheduleTransportClass;
use Illuminate\Console\Command;

class RevertExpiredPromos extends Command
{
    protected $signature = 'promos:revert-expired';

    protected $description = 'Revert expired temporary promotional fares back to regular fare and restore pre-promo original prices';

    public function handle(): int
    {
        // 1. Revert temporary promo transport classes whose end date has passed or tickets exhausted
        $revertedStcCount = ScheduleTransportClass::revertExpiredPromos();

        // 2. Deactivate expired schedule promotional tickets (airline/schedule level)
        $deactivatedTicketsCount = PromotionalTicket::query()
            ->where('is_active', true)
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', now())
            ->update(['is_active' => false]);

        $this->info("Reverted {$revertedStcCount} expired promotional transport class(es) to regular fare.");
        if ($deactivatedTicketsCount > 0) {
            $this->info("Deactivated {$deactivatedTicketsCount} expired promotional ticket(s).");
        }

        return self::SUCCESS;
    }
}

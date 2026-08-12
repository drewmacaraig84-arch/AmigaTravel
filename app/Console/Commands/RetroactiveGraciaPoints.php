<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Services\GraciaPointsService;

class RetroactiveGraciaPoints extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gracia:retroactive-points';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retroactively award Gracia Points for verified bookings that do not have them yet.';

    /**
     * Execute the console command.
     */
    public function handle(GraciaPointsService $pointsService)
    {
        $this->info('Starting retroactive Gracia Points check...');
        
        $bookings = Booking::where('status', 'confirmed')
            ->get();
            
        $awardedCount = 0;

        foreach ($bookings as $booking) {
            $idempotencyKey = "booking_{$booking->id}_verified";
            
            // Check if points were already awarded for this booking
            $alreadyAwarded = \App\Models\GraciaPointLedger::where('idempotency_key', $idempotencyKey)->exists();
            
            if (!$alreadyAwarded) {
                $this->info("Awarding points for Booking ID: {$booking->id} (Transaction: {$booking->transaction_number})");
                $pointsService->awardPointsForBooking($booking);
                $awardedCount++;
            }
        }
        
        $this->info("Finished! Retroactively awarded points for {$awardedCount} bookings.");
        return self::SUCCESS;
    }
}

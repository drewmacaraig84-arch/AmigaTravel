<?php

namespace App\Console\Commands;

use App\Mail\SlaVoucherRewardMail;
use App\Models\Booking;
use App\Models\PaymentSetting;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class IssueSlaVouchers extends Command
{
    protected $signature = 'bookings:issue-sla-vouchers';
    protected $description = 'Automatically issue non-expiring compensation vouchers for bookings not verified within the SLA window.';

    public function handle(): int
    {
        $settings = PaymentSetting::current();

        if (! $settings->isSlaVoucherEnabled()) {
            $this->info('SLA Voucher Guarantee is disabled in Payment Settings.');
            return Command::SUCCESS;
        }

        $hours = $settings->getSlaVoucherHours();
        $amount = $settings->getSlaVoucherAmount();

        if ($amount <= 0 || $hours <= 0) {
            $this->info('SLA Voucher amount or hours invalid.');
            return Command::SUCCESS;
        }

        $cutoff = Carbon::now()->subHours($hours);

        // Find bookings that:
        // 1. Are pending (unverified)
        // 2. Have not had an SLA voucher issued yet
        // 3. Have paid/submitted proof of payment older than the cutoff window (NOT unpaid)
        $qualifyingBookings = Booking::query()
            ->whereIn('status', ['pending', Booking::STATUS_PENDING_REBOOKING])
            ->whereNull('sla_voucher_issued_at')
            ->whereHas('transaction', function ($q) use ($cutoff) {
                $q->whereNotIn('payment_status', ['unpaid', 'cancelled'])
                  ->where(function ($sub) use ($cutoff) {
                      $sub->where(function ($s) use ($cutoff) {
                          $s->whereNotNull('proof_submitted_at')
                            ->where('proof_submitted_at', '<=', $cutoff);
                      })->orWhere(function ($s) use ($cutoff) {
                          $s->whereNull('proof_submitted_at')
                            ->whereNotNull('proof_of_payment')
                            ->where('created_at', '<=', $cutoff);
                      });
                  });
            })
            ->with(['transaction', 'user'])
            ->get();

        $issuedCount = 0;

        foreach ($qualifyingBookings as $booking) {
            try {
                // Generate a unique voucher code
                do {
                    $code = 'SLA-' . strtoupper(Str::random(8));
                } while (Voucher::where('code', $code)->exists());

                // Create the non-expiring flat-amount voucher
                $voucher = Voucher::create([
                    'name'                 => "SLA Guarantee Reward - {$booking->transaction_number}",
                    'code'                 => $code,
                    'description'          => "Automatic verification guarantee voucher for booking {$booking->transaction_number} (elapsed > {$hours}h without verification)",
                    'discount_type'        => 'fixed',
                    'discount_value'       => $amount,
                    'max_discount'         => $amount,
                    'min_booking_amount'   => 0,
                    'start_at'             => Carbon::now(),
                    'end_at'               => null, // Non-expiring
                    'is_active'            => true,
                    'total_usage_limit'    => 1,
                    'one_use_per_customer' => true,
                    'is_hidden'            => true,
                ]);

                // Stamp booking immediately to prevent duplicate issues on failure
                $booking->update([
                    'sla_voucher_issued_at' => Carbon::now(),
                ]);

                // Find or link user
                $user = $booking->user ?? User::where('email', $booking->client_email)->first();
                if ($user) {
                    // Link to user's hidden voucher list so it appears in app
                    $voucher->claimedByUsers()->syncWithoutDetaching([$user->id]);

                    // Send In-App / Push Notification
                    UserNotification::notify(
                        $user->id,
                        'Verification Guarantee Voucher Awarded!',
                        "Your booking {$booking->transaction_number} is taking a little longer than expected. We've added a ₱" . number_format($amount, 0) . " voucher ({$code}) to your account.",
                        'reward',
                        'card_giftcard',
                        [
                            'voucher_code'    => $code,
                            'discount_amount' => (string) $amount,
                            'booking_id'      => (string) $booking->id,
                        ]
                    );
                }

                // Send email notification with the voucher details
                if (filled($booking->client_email)) {
                    try {
                        Mail::to($booking->client_email)->send(new SlaVoucherRewardMail($booking, $voucher));
                    } catch (\Throwable $mailEx) {
                        Log::warning("Failed to send SLA voucher reward email for booking {$booking->transaction_number}: " . $mailEx->getMessage());
                    }
                }

                Log::info("Issued SLA guarantee voucher {$code} (₱{$amount}) for booking {$booking->transaction_number}.");
                $issuedCount++;
            } catch (\Throwable $e) {
                Log::error("Failed to issue SLA guarantee voucher for booking {$booking->id}: " . $e->getMessage());
            }
        }

        $this->info("Issued {$issuedCount} SLA guarantee voucher(s).");
        return Command::SUCCESS;
    }
}

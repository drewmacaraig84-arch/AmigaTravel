<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\GraciaEarningRule;
use App\Models\GraciaPointLedger;
use App\Models\GraciaUserBalance;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GraciaPointsService
{
    public function getActiveRule(): ?GraciaEarningRule
    {
        return GraciaEarningRule::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->latest('id')
            ->first();
    }

    public function awardPointsForBooking(Booking $booking, ?User $admin = null): void
    {
        if ($booking->status !== 'confirmed') return;
        
        if (!$booking->user_id) {
            $user = User::where('email', $booking->client_email)->first();
            if ($user) {
                $booking->user_id = $user->id;
                $booking->save();
            } else {
                return;
            }
        }
        
        $idempotencyKey = "booking_{$booking->id}_verified";

        DB::transaction(function () use ($booking, $admin, $idempotencyKey) {
            if (GraciaPointLedger::where('idempotency_key', $idempotencyKey)->exists()) {
                return;
            }

            $rule = $this->getActiveRule();
            if (!$rule || $rule->spend_threshold_centavos <= 0) {
                return;
            }

            $settings = \App\Models\PaymentSetting::current();
            $multiplier = max(1, $booking->passengers()->count());
            $isShortHaul = $booking->isShortHaul();
            $webAdminFee = $multiplier * $settings->getWebAdminFee($isShortHaul);
            $transactionFee = $multiplier * $settings->getTransactionFee($isShortHaul);
            
            $eligibleSpend = max(0, $booking->total_price - $webAdminFee - $transactionFee);
            $spendCentavos = (int) round($eligibleSpend * 100);
            
            $balance = GraciaUserBalance::firstOrCreate(
                ['user_id' => $booking->user_id],
                ['current_points' => 0, 'unconverted_spend_centavos' => 0]
            );

            $totalEligibleCentavos = $balance->unconverted_spend_centavos + $spendCentavos;
            
            $awardedMultiples = intdiv($totalEligibleCentavos, $rule->spend_threshold_centavos);
            $pointsEarned = $awardedMultiples * $rule->points_awarded;
            
            $remainderCentavos = $totalEligibleCentavos % $rule->spend_threshold_centavos;
            
            if ($pointsEarned > 0 || $spendCentavos > 0) {
                GraciaPointLedger::create([
                    'user_id' => $booking->user_id,
                    'booking_id' => $booking->id,
                    'gracia_earning_rule_id' => $rule->id,
                    'points' => $pointsEarned,
                    'entry_type' => 'earned',
                    'qualifying_spend_centavos' => $spendCentavos,
                    'reason' => 'Points earned for booking ' . $booking->transaction_number,
                    'admin_id' => $admin?->id,
                    'idempotency_key' => $idempotencyKey,
                ]);

                $balance->current_points += $pointsEarned;
                $balance->unconverted_spend_centavos = $remainderCentavos;
                $balance->save();
            }
        });
    }

    public function reversePointsForBooking(Booking $booking, ?User $admin = null): void
    {
        if (!$booking->user_id) return;

        $idempotencyKey = "booking_{$booking->id}_reversed";

        DB::transaction(function () use ($booking, $admin, $idempotencyKey) {
            if (GraciaPointLedger::where('idempotency_key', $idempotencyKey)->exists()) {
                return;
            }

            $earnedEntry = GraciaPointLedger::where('booking_id', $booking->id)
                ->where('entry_type', 'earned')
                ->first();

            if (!$earnedEntry) {
                return;
            }

            $balance = GraciaUserBalance::firstOrCreate(
                ['user_id' => $booking->user_id],
                ['current_points' => 0, 'unconverted_spend_centavos' => 0]
            );

            $reversedPoints = -$earnedEntry->points;
            
            $rule = $earnedEntry->rule;
            $unconvertedAdjustment = 0;
            if ($rule && $rule->points_awarded > 0) {
                $multiplesReversed = $earnedEntry->points / $rule->points_awarded;
                $centavosReversedFromPoints = $multiplesReversed * $rule->spend_threshold_centavos;
                $unconvertedAdjustment = $centavosReversedFromPoints - $earnedEntry->qualifying_spend_centavos;
            } else {
                $unconvertedAdjustment = -$earnedEntry->qualifying_spend_centavos;
            }

            GraciaPointLedger::create([
                'user_id' => $booking->user_id,
                'booking_id' => $booking->id,
                'gracia_earning_rule_id' => $earnedEntry->gracia_earning_rule_id,
                'points' => $reversedPoints,
                'entry_type' => 'reversed',
                'qualifying_spend_centavos' => -$earnedEntry->qualifying_spend_centavos,
                'reason' => 'Points reversed for cancelled/refunded booking ' . $booking->transaction_number,
                'admin_id' => $admin?->id,
                'idempotency_key' => $idempotencyKey,
            ]);

            $balance->current_points += $reversedPoints;
            $balance->unconverted_spend_centavos += $unconvertedAdjustment;
            $balance->save();
        });
    }

    public function deductPointsForPayment(Booking $booking): void
    {
        if (!$booking->user_id || $booking->points_used <= 0) return;

        $idempotencyKey = "booking_{$booking->id}_redeemed";

        DB::transaction(function () use ($booking, $idempotencyKey) {
            if (GraciaPointLedger::where('idempotency_key', $idempotencyKey)->exists()) {
                return;
            }

            $balance = GraciaUserBalance::firstOrCreate(
                ['user_id' => $booking->user_id],
                ['current_points' => 0, 'unconverted_spend_centavos' => 0]
            );

            // Assuming validation was done before, but we cap it to the available balance just in case
            $pointsToDeduct = min($booking->points_used, $balance->current_points);
            
            if ($pointsToDeduct > 0) {
                GraciaPointLedger::create([
                    'user_id' => $booking->user_id,
                    'booking_id' => $booking->id,
                    'points' => -$pointsToDeduct,
                    'entry_type' => 'redeemed',
                    'qualifying_spend_centavos' => 0,
                    'reason' => 'Points used for booking ' . $booking->transaction_number,
                    'idempotency_key' => $idempotencyKey,
                ]);

                $balance->current_points -= $pointsToDeduct;
                $balance->save();
            }
        });
    }

    public function refundRedeemedPoints(Booking $booking): void
    {
        if (!$booking->user_id || $booking->points_used <= 0) return;

        $idempotencyKey = "booking_{$booking->id}_refund_redeemed";

        DB::transaction(function () use ($booking, $idempotencyKey) {
            if (GraciaPointLedger::where('idempotency_key', $idempotencyKey)->exists()) {
                return;
            }

            $redeemedEntry = GraciaPointLedger::where('booking_id', $booking->id)
                ->where('entry_type', 'redeemed')
                ->first();

            if (!$redeemedEntry) {
                return;
            }

            $balance = GraciaUserBalance::firstOrCreate(
                ['user_id' => $booking->user_id],
                ['current_points' => 0, 'unconverted_spend_centavos' => 0]
            );

            $refundedPoints = abs($redeemedEntry->points); // Points were stored as negative in ledger

            GraciaPointLedger::create([
                'user_id' => $booking->user_id,
                'booking_id' => $booking->id,
                'points' => $refundedPoints,
                'entry_type' => 'refunded',
                'qualifying_spend_centavos' => 0,
                'reason' => 'Points refunded from cancelled booking ' . $booking->transaction_number,
                'idempotency_key' => $idempotencyKey,
            ]);

            $balance->current_points += $refundedPoints;
            $balance->save();
        });
    }

    public function awardPointsForRebookingFee(Booking $booking, float $rebookingFee, ?User $admin = null): void
    {
        if (!$booking->user_id) return;
        if ($rebookingFee <= 0) return;

        $idempotencyKey = "booking_{$booking->id}_rebooking_fee";

        DB::transaction(function () use ($booking, $rebookingFee, $admin, $idempotencyKey) {
            if (GraciaPointLedger::where('idempotency_key', $idempotencyKey)->exists()) {
                return;
            }

            $rule = $this->getActiveRule();
            if (!$rule || $rule->spend_threshold_centavos <= 0) {
                return;
            }

            $spendCentavos = (int) round($rebookingFee * 100);

            $balance = GraciaUserBalance::firstOrCreate(
                ['user_id' => $booking->user_id],
                ['current_points' => 0, 'unconverted_spend_centavos' => 0]
            );

            $totalEligibleCentavos = $balance->unconverted_spend_centavos + $spendCentavos;

            $awardedMultiples = intdiv($totalEligibleCentavos, $rule->spend_threshold_centavos);
            $pointsEarned = $awardedMultiples * $rule->points_awarded;

            $remainderCentavos = $totalEligibleCentavos % $rule->spend_threshold_centavos;

            if ($pointsEarned > 0 || $spendCentavos > 0) {
                GraciaPointLedger::create([
                    'user_id' => $booking->user_id,
                    'booking_id' => $booking->id,
                    'gracia_earning_rule_id' => $rule->id,
                    'points' => $pointsEarned,
                    'entry_type' => 'earned',
                    'qualifying_spend_centavos' => $spendCentavos,
                    'reason' => 'Points earned for rebooking fee on ' . $booking->transaction_number,
                    'admin_id' => $admin?->id,
                    'idempotency_key' => $idempotencyKey,
                ]);

                $balance->current_points += $pointsEarned;
                $balance->unconverted_spend_centavos = $remainderCentavos;
                $balance->save();
            }
        });
    }

    public function addManualAdjustment(User $user, int $points, string $reason, User $admin): void
    {
        DB::transaction(function () use ($user, $points, $reason, $admin) {
            GraciaPointLedger::create([
                'user_id' => $user->id,
                'points' => $points,
                'entry_type' => 'admin_adjustment',
                'reason' => $reason,
                'admin_id' => $admin->id,
            ]);

            $balance = GraciaUserBalance::firstOrCreate(
                ['user_id' => $user->id],
                ['current_points' => 0, 'unconverted_spend_centavos' => 0]
            );

            $balance->current_points += $points;
            $balance->save();
        });
    }
}

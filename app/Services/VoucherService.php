<?php

namespace App\Services;

use App\Models\Voucher;
use App\Models\Schedule;
use App\Models\ScheduleAccommodation;
use App\Models\TransportClass;
use App\Models\Accommodation;
use App\Models\Discount;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class VoucherService
{
    public function validateAndCalculate(string $code, array $bookingData): array
    {
        $code = strtoupper(trim($code));
        
        // Find voucher
        $voucher = Voucher::where('code', $code)->first();
        
        if (!$voucher) {
            return $this->error('Invalid voucher code');
        }
        
        // Validate voucher
        $validation = $this->validateVoucher($voucher, $bookingData);
        if (!$validation['valid']) {
            return $validation;
        }
        
        // Calculate discount
        $calculation = $this->calculateDiscount($voucher, $bookingData);
        
        return [
            'valid' => true,
            'message' => 'Voucher applied successfully',
            'voucher_code' => $voucher->code,
            'voucher_name' => $voucher->name,
            'discount_type' => $voucher->discount_type,
            'discount_value' => $voucher->discount_value,
            'eligible_scope' => $voucher->eligible_scope,
            'min_booking_amount' => $voucher->min_booking_amount,
            'max_discount' => $voucher->max_discount,
            'end_at' => $voucher->end_at?->toIso8601String(),
            'original_subtotal' => $calculation['base_amount'],
            'discount_amount' => $calculation['discount_amount'],
            'final_total' => $calculation['final_total'],
        ];
    }

    protected function validateVoucher(Voucher $voucher, array $bookingData): array
    {
        // Check active status
        if (!$voucher->is_active) {
            return $this->error('This voucher is not active');
        }
        
        // Check start date
        if ($voucher->start_at && $voucher->start_at > now()) {
            return $this->error('This voucher is not yet valid');
        }
        
        // Check end date
        if ($voucher->end_at && $voucher->end_at < now()) {
            return $this->error('This voucher has expired');
        }
        
        // Check usage limit
        if ($voucher->total_usage_limit !== null) {
            $used = $voucher->redemptions()->count();
            if ($used >= $voucher->total_usage_limit) {
                return $this->error('This voucher has reached its usage limit');
            }
        }
        
        // Check one use per customer
        if ($voucher->one_use_per_customer) {
            $email = strtolower(trim($bookingData['client_email'] ?? ''));
            $userId = $bookingData['user_id'] ?? null;
            
            $query = $voucher->redemptions()->where('normalized_email', $email);
            if ($userId) {
                $query->orWhere('user_id', $userId);
            }
            
            if ($query->exists()) {
                return $this->error('You have already used this voucher');
            }
        }
        
        // Check eligible origin/destination
        if ($voucher->eligible_origin && $bookingData['origin'] !== $voucher->eligible_origin) {
            return $this->error('This voucher is not valid for this origin');
        }
        
        if ($voucher->eligible_destination && $bookingData['destination'] !== $voucher->eligible_destination) {
            return $this->error('This voucher is not valid for this destination');
        }
        
        // Check eligible schedule
        if ($voucher->eligible_schedule_id) {
            $isDepartureEligible = !empty($bookingData['schedule_id']) && $bookingData['schedule_id'] == $voucher->eligible_schedule_id;
            $isReturnEligible = !empty($bookingData['return_schedule_id']) && $bookingData['return_schedule_id'] == $voucher->eligible_schedule_id;
            
            if (!$isDepartureEligible && !$isReturnEligible) {
                return $this->error('This voucher is not valid for the selected schedules');
            }
        }
        
        // Check minimum booking amount (we'll calculate base amount first)
        $calculation = $this->calculateDiscount($voucher, $bookingData);
        if ($voucher->min_booking_amount !== null && $calculation['base_amount'] < $voucher->min_booking_amount) {
            return $this->error('This voucher requires a minimum booking amount of ₱' . number_format($voucher->min_booking_amount, 2));
        }
        
        return ['valid' => true];
    }

    protected function calculateDiscount(Voucher $voucher, array $bookingData): array
    {
        $baseAmount = $this->calculateBaseAmount($voucher->eligible_scope, $bookingData);
        
        $discountAmount = 0;
        
        if ($voucher->discount_type === 'percentage') {
            $discountAmount = ($baseAmount * $voucher->discount_value) / 100;
            
            // Apply max discount if set
            if ($voucher->max_discount !== null && $discountAmount > $voucher->max_discount) {
                $discountAmount = $voucher->max_discount;
            }
        } else { // fixed
            $discountAmount = $voucher->discount_value;
            
            // Make sure discount doesn't exceed base amount
            if ($discountAmount > $baseAmount) {
                $discountAmount = $baseAmount;
            }
        }
        
        $finalTotal = max(0, $baseAmount - $discountAmount);
        
        return [
            'base_amount' => $baseAmount,
            'discount_amount' => $discountAmount,
            'final_total' => $finalTotal,
        ];
    }

    protected function calculateBaseAmount(string $scope, array $bookingData): float
    {
        /** @var Schedule|null $schedule */
        $schedule = !empty($bookingData['schedule_id']) ? Schedule::query()->where('id', $bookingData['schedule_id'])->first() : null;
        $scheduleAccommodationPrice = isset($bookingData['selected_schedule_accommodation_id'])
            ? (ScheduleAccommodation::query()->where('id', $bookingData['selected_schedule_accommodation_id'])->first()?->price ?? 0)
            : 0;
            
        $returnSchedule = !empty($bookingData['return_schedule_id']) ? Schedule::query()->where('id', $bookingData['return_schedule_id'])->first() : null;
        $returnScheduleAccommodationPrice = isset($bookingData['selected_return_schedule_accommodation_id'])
            ? (ScheduleAccommodation::query()->where('id', $bookingData['selected_return_schedule_accommodation_id'])->first()?->price ?? 0)
            : 0;
        
        $discounts = \Illuminate\Support\Facades\Cache::remember('discounts:all:keyed', now()->addHours(12), function () {
            return Discount::all()->keyBy('id');
        });
        
        $fareTotal = collect($bookingData['passengers'] ?? [])->sum(function (array $passenger) use (
            $schedule, $scheduleAccommodationPrice, 
            $returnSchedule, $returnScheduleAccommodationPrice,
            $discounts
        ) {
            $basePrice = $schedule ? $schedule->price : 0;
            $returnBasePrice = $returnSchedule ? $returnSchedule->price : 0;
            
            $departureFare = $basePrice + $scheduleAccommodationPrice;
            $returnFare = $returnBasePrice + $returnScheduleAccommodationPrice;
            $fare = $departureFare + $returnFare;
            
            if (!empty($passenger['discount_id'])) {
                $discount = $discounts->get($passenger['discount_id']);
                if ($discount) {
                    $fare -= $fare * (floatval($discount->percentage) / 100);
                }
            }
            
            return $fare;
        });
        
        $transportClassTotal = 0;
        if (!empty($bookingData['selected_transport_class_id'])) {
            /** @var TransportClass|null $transportClass */
            $transportClass = TransportClass::query()->where('id', $bookingData['selected_transport_class_id'])->first();
            if ($transportClass) {
                $transportClassTotal = floatval($transportClass->effective_price);
            }
        }
        
        $accommodationTotal = 0;
        if (!empty($bookingData['accommodation_ids'])) {
            /** @var Builder $accommodationQuery */
            $accommodationQuery = Accommodation::query();
            $accommodationTotal = $accommodationQuery->whereIn('id', $bookingData['accommodation_ids'])->sum('price');
        }
        
        $vehicleTotal = !empty($bookingData['has_vehicle']) ? floatval($bookingData['vehicle_price'] ?? 0) : 0;
        
        switch ($scope) {
            case 'ticket_fare':
                return $fareTotal;
            case 'booking_total':
                // Exclude service fees
                return $fareTotal + $transportClassTotal + $accommodationTotal + $vehicleTotal;
            case 'vehicle':
                return $vehicleTotal;
            case 'accommodation':
                return $accommodationTotal;
            default:
                return $fareTotal;
        }
    }

    public function redeemVoucher(Voucher $voucher, \App\Models\Booking $booking, array $calculation): \App\Models\VoucherRedemption
    {
        return DB::transaction(function () use ($voucher, $booking, $calculation) {
            // Lock voucher row to prevent race conditions
            $lockedVoucher = Voucher::lockForUpdate()->find($voucher->id);
            
            // Recheck usage limit (in case it was used since validation)
            if ($lockedVoucher->total_usage_limit !== null) {
                $used = $lockedVoucher->redemptions()->count();
                if ($used >= $lockedVoucher->total_usage_limit) {
                    throw new \Exception('This voucher has reached its usage limit');
                }
            }
            
            // Recheck one use per customer
            if ($lockedVoucher->one_use_per_customer) {
                $email = strtolower(trim($booking->client_email));
                $userId = $booking->user_id;
                
                $query = $lockedVoucher->redemptions()->where('normalized_email', $email);
                if ($userId) {
                    $query->orWhere('user_id', $userId);
                }
                
                if ($query->exists()) {
                    throw new \Exception('You have already used this voucher');
                }
            }
            
            // Create redemption record
            return \App\Models\VoucherRedemption::create([
                'voucher_id' => $voucher->id,
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
                'normalized_email' => strtolower(trim($booking->client_email)),
                'voucher_code_snapshot' => $voucher->code,
                'discount_amount' => $calculation['discount_amount'],
                'base_amount' => $calculation['base_amount'],
            ]);
        });
    }

    protected function error(string $message): array
    {
        return ['valid' => false, 'message' => $message];
    }
}

<?php

namespace App\Models;

use App\Mail\RebookingVerification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Throwable;

class Booking extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_OPERATOR_CANCELLED = 'operator_cancelled';

    protected $fillable = [
        'user_id',
        'transaction_number',
        'origin',
        'destination',
        'departure_date',
        'return_date',
        'schedule_id',
        'schedule_service',
        'schedule_departure_time',
        'schedule_arrival_time',
        'schedule_price',
        'schedule_accommodation_id',
        'schedule_accommodation_name',
        'schedule_accommodation_price',
        'schedule_accommodation_rate_code',
        'return_schedule_id',
        'return_schedule_service',
        'return_schedule_departure_time',
        'return_schedule_arrival_time',
        'return_schedule_price',
        'return_schedule_accommodation_id',
        'return_schedule_accommodation_name',
        'return_schedule_accommodation_price',
        'return_schedule_accommodation_rate_code',
        'status',
        'total_price',
        'client_email',
        'client_name',
        'client_phone',
        'has_vehicle',
        'vehicle_type',
        'vehicle_plate_number',
        'vehicle_price',
        'driver_name',
        'driver_birthday',
        'tour_id',
        'tour_date_id',
        'tour_inclusions',
        'cancellation_fee',
        'refund_amount',
        'refund_destination',
        'cancellation_window_expires_at',
        'is_rebooked',
        'rebooking_status',
        'rebooking_departure_date',
        'rebooking_return_date',
        'verified_by_user_id',
        'verified_at',
        'promotional_ticket_id',
        'promo_ticket_count',
        'voucher_id',
        'voucher_code',
        'voucher_discount_amount',
        'subtotal_before_voucher',
        'terms_accepted_at',
        'terms_version',
        'terms_accepted_ip',
        'terms_accepted_user_agent',
        'service_cancellation_id',
        'disruption_status',
        'preferred_replacement_schedule_id',
        'preferred_replacement_date',
        'disruption_notes',
        'points_used',
        'points_discount',
        'has_extra_baggage',
        'extra_baggage_weight',
        'extra_baggage_price',
    ];

    public function isUserCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isOperatorCancelled(): bool
    {
        return $this->status === self::STATUS_OPERATOR_CANCELLED;
    }

    public function isServiceCancellation(): bool
    {
        return $this->isOperatorCancelled() || filled($this->service_cancellation_id);
    }

    protected $casts = [
        'departure_date' => 'date',
        'return_date' => 'date',
        'rebooking_departure_date' => 'date',
        'rebooking_return_date' => 'date',
        'preferred_replacement_date' => 'date',
        'schedule_price' => 'decimal:2',
        'schedule_accommodation_price' => 'decimal:2',
        'return_schedule_price' => 'decimal:2',
        'return_schedule_accommodation_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'has_vehicle' => 'boolean',
        'vehicle_price' => 'decimal:2',
        'driver_birthday' => 'date',
        'tour_inclusions' => 'array',
        'cancellation_fee' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'cancellation_window_expires_at' => 'datetime',
        'is_rebooked' => 'boolean',
        'promo_ticket_count' => 'integer',
        'voucher_discount_amount' => 'decimal:2',
        'subtotal_before_voucher' => 'decimal:2',
        'terms_accepted_at' => 'datetime',
        'verified_at' => 'datetime',
        'points_used' => 'integer',
        'points_discount' => 'decimal:2',
        'has_extra_baggage' => 'boolean',
        'extra_baggage_price' => 'decimal:2',
    ];

    public function serviceCancellation(): BelongsTo
    {
        return $this->belongsTo(ServiceCancellation::class);
    }

    public function preferredReplacementSchedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'preferred_replacement_schedule_id');
    }

    public function passengers(): HasMany
    {
        return $this->hasMany(Passenger::class);
    }

    public function accommodations(): BelongsToMany
    {
        return $this->belongsToMany(Accommodation::class)
            ->withPivot('price')
            ->withTimestamps();
    }

    public function transportClasses(): BelongsToMany
    {
        return $this->belongsToMany(TransportClass::class, 'booking_transport_class')
            ->withPivot('price', 'is_promo', 'rate_code')
            ->withTimestamps();
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function scheduleAccommodation(): BelongsTo
    {
        return $this->belongsTo(ScheduleAccommodation::class);
    }

    public function returnSchedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'return_schedule_id');
    }

    public function returnScheduleAccommodation(): BelongsTo
    {
        return $this->belongsTo(ScheduleAccommodation::class, 'return_schedule_accommodation_id');
    }

    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class);
    }

    public function verifiedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }


    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function promotionalTicket(): BelongsTo
    {
        return $this->belongsTo(PromotionalTicket::class);
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    public function voucherRedemption(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(VoucherRedemption::class);
    }     

    public function getScheduleSummaryAttribute(): ?string
    {
        if (! $this->schedule_service) {
            return null;
        }

        $times = collect([$this->schedule_departure_time, $this->schedule_arrival_time])
            ->filter()
            ->implode(' → ');

        return trim("{$this->schedule_service}" . ($times ? " ({$times})" : ''));
    }

    public function verificationUnlockAt(): ?Carbon
    {
        return $this->transaction?->verificationUnlockAt();
    }

    public function isVerificationLocked(): bool
    {
        return $this->status === 'pending'
            && $this->transaction !== null
            && $this->transaction->isVerificationLocked();
    }

    public function verificationTimerLabel(): string
    {
        if ($this->status !== 'pending') {
            return '—';
        }

        if (! $this->transaction) {
            return 'No tx';
        }

        return $this->transaction->verificationTimerLabel();
    }

    public function verificationTimerTooltip(): ?string
    {
        if ($this->status !== 'pending') {
            return null;
        }

        if (! $this->transaction) {
            return 'No payment transaction found for this booking.';
        }

        return $this->transaction->verificationTimerTooltip();
    }

    /**
     * Cancellation/rebook allowed:
     *  - Ferry: up to departure time (Starlite also allows AFTER departure + 10 min grace)
     *  - Airline: only before departure (no after-departure refund)
     */
    public function hasPromoTicket(): bool
    {
        return $this->transportClasses()->wherePivot('is_promo', true)->exists();
    }

    public function canCancel(): bool
    {
        $isWithin5Mins = $this->created_at && $this->created_at->addMinutes(5)->isFuture();

        if ($this->hasPromoTicket() && !$isWithin5Mins) {
            return false;
        }

        if (! $this->transaction || ! in_array($this->transaction->payment_status, ['paid', 'pending', 'unpaid'])) {
            return false;
        }

        $departureDateTime = $this->getDepartureDateTime();
        if (! $departureDateTime) {
            return false;
        }

        // 1. Allowed for everyone if strictly before the 3-hour mark prior to departure
        return now()->isBefore($departureDateTime->copy()->subHours(3));
    }

    public function canRebook(): bool
    {
        if ($this->hasPromoTicket()) {
            return false;
        }

        if (! $this->transaction || ! in_array($this->transaction->payment_status, ['paid', 'pending', 'unpaid'])) {
            return false;
        }

        $departureDateTime = $this->getDepartureDateTime();
        if (! $departureDateTime) {
            return false;
        }

        // 1. Allowed for everyone if strictly before the 3-hour mark prior to departure
        if (now()->isBefore($departureDateTime->copy()->subHours(3))) {
            return true;
        }

        // 2. Reactivate for Starlite ONLY if 5 minutes after departure
        if ($this->isStarlite() && now()->isAfter($departureDateTime->copy()->addMinutes(5))) {
            return true;
        }

        // Otherwise blocked
        return false;
    }

    public function canCancelOrRebook(): bool
    {
        return $this->canCancel() || $this->canRebook();
    }

    public function getDepartureDateTime(): ?Carbon
    {
        if (! $this->departure_date) {
            return null;
        }

        if (! $this->schedule_departure_time) {
            return $this->departure_date->copy()->startOfDay();
        }

        try {
            $time = Carbon::parse($this->schedule_departure_time);
            return $this->departure_date->copy()->setTime($time->hour, $time->minute, $time->second);
        } catch (\Exception $e) {
            return $this->departure_date->copy()->startOfDay();
        }
    }

    public function isRefundEligible(): bool
    {
        // Promotional tickets are strictly non-refundable
        $hasPromoClass = $this->transportClasses()->wherePivot('is_promo', true)->exists();
        if ($hasPromoClass) {
            return false;
        }

        // Time window for refunds is identical to the cancellation window
        return $this->canCancel();
    }

    /**
     * Determine transport mode ('ferry' or 'airline') from the linked FerryRoute.
     */
    public function getMode(): string
    {
        if ($this->schedule_id) {
            $mode = \App\Models\FerryRoute::query()
                ->join('schedules', 'ferry_routes.id', '=', 'schedules.ferry_route_id')
                ->where('schedules.id', $this->schedule_id)
                ->value('ferry_routes.mode');

            if ($mode) {
                return strtolower($mode);
            }
        }

        return 'ferry'; // safe default
    }

    /**
     * True if the booking's departure service is Starlite.
     */
    public function isStarlite(): bool
    {
        return str_contains(strtolower((string) $this->schedule_service), 'starlite');
    }

    /**
     * True when actual departure time + 5-minute grace period has passed.
     */
    public function isAfterDeparture(): bool
    {
        $dt = $this->getDepartureDateTime();
        if (! $dt) {
            return false;
        }
        return now()->isAfter($dt->copy()->addMinutes(5));
    }

    /**
     * The refundable ticket base = total paid minus the non-refundable platform fees.
     * web_admin_fee (per passenger) + transaction_fee are always non-refundable.
     */
    public function getTicketBase(): float
    {
        $settings       = \App\Models\PaymentSetting::current();
        $passengerCount = max(1, $this->passengers()->count());
        $multiplier     = $passengerCount;

        $nonRefundable  = (floatval($settings->web_admin_fee) * $multiplier)
                        + (floatval($settings->transaction_fee) * $multiplier);

        return max(0, floatval($this->total_price) - $nonRefundable);
    }

    /**
     * Calculate the refund amount based on mode, timing, and configurable surcharge.
     *
     * Formula:
     *   ticketBase   = total_price - (web_admin_fee × pax) - transaction_fee
     *   surcharge    = ticketBase × surcharge_pct%
     *   refund       = ticketBase - surcharge
     *
     * Non-refundable cases return 0.
     */
    public function getRefundSurchargePercentage(): float
    {
        $settings = \App\Models\PaymentSetting::current();
        $mode = $this->getMode();
        $afterDepart = $this->isAfterDeparture();
        
        if ($mode === 'airline') {
            return (float) $settings->airline_before_departure_surcharge_pct;
        } elseif ($afterDepart) {
            return (float) $settings->ferry_after_departure_surcharge_pct;
        } else {
            return (float) $settings->ferry_before_departure_surcharge_pct;
        }
    }

    public function getRefundBreakdown(bool $isWithinGracePeriod = false): array
    {
        $settings       = \App\Models\PaymentSetting::current();
        $passengerCount = max(1, $this->passengers()->count());
        $multiplier     = $passengerCount;
        $webAdminFeeTotal    = floatval($settings->web_admin_fee) * $multiplier;
        $transactionFeeTotal = floatval($settings->transaction_fee) * $multiplier;
        $nonRefundableFees   = $webAdminFeeTotal + $transactionFeeTotal;

        $rebookingFeeTotal = $this->transaction ? (float) $this->transaction->rebooking_fee : 0.0;

        $rebookingSurcharge = 0;
        $rebookingRevalidationFee = 0;
        $rebookingRateDiff = 0;
        
        if (!empty($this->disruption_notes)) {
            $notes = json_decode($this->disruption_notes, true);
            $rebookingSurcharge = (float) ($notes['surcharge'] ?? 0);
            $rebookingRevalidationFee = (float) ($notes['revalidation_fee'] ?? 0);
            $rebookingRateDiff = (float) ($notes['rate_diff'] ?? 0);
        } else if ($rebookingFeeTotal > 0) {
            $rebookingRevalidationFee = $rebookingFeeTotal;
        }

        $totalNonRefundableFees = $nonRefundableFees + $rebookingFeeTotal;
        $totalPaid = (float) $this->total_price + $rebookingFeeTotal;

        if ($isWithinGracePeriod) {
            return [
                'base_ticket' => $totalPaid,
                'surcharge_pct' => 0,
                'surcharge_amount' => 0,
                'non_refundable_fees' => 0,
                'web_admin_fee' => 0,
                'transaction_fee' => 0,
                'rebooking_surcharge' => $rebookingSurcharge,
                'rebooking_revalidation_fee' => $rebookingRevalidationFee,
                'rebooking_rate_diff' => $rebookingRateDiff,
                'refundable_amount' => $totalPaid,
                'deduction_amount' => 0,
            ];
        }

        $mode = $this->getMode();
        $afterDepart = $this->isAfterDeparture();
        
        $ticketBase = $this->getTicketBase();

        if ($mode === 'airline' && $afterDepart) {
            return [
                'base_ticket' => $totalPaid,
                // NOTE: We force surcharge to 100% here so the UI breakdown accurately reflects 
                // that the entire ticket base is forfeited (since it is non-refundable).
                'surcharge_pct' => 100,
                'surcharge_amount' => $ticketBase,
                'non_refundable_fees' => $totalNonRefundableFees,
                'web_admin_fee' => $webAdminFeeTotal,
                'transaction_fee' => $transactionFeeTotal,
                'rebooking_surcharge' => 0,
                'rebooking_revalidation_fee' => $rebookingSurcharge + $rebookingRevalidationFee + $rebookingRateDiff,
                'rebooking_rate_diff' => 0,
                'refundable_amount' => 0,
                'deduction_amount' => $totalPaid,
            ];
        }

        if ($mode !== 'airline' && $afterDepart && ! $this->isStarlite()) {
            return [
                'base_ticket' => $totalPaid,
                // NOTE: We force surcharge to 100% here so the UI breakdown accurately reflects 
                // that the entire ticket base is forfeited (since it is non-refundable).
                'surcharge_pct' => 100,
                'surcharge_amount' => $ticketBase,
                'non_refundable_fees' => $totalNonRefundableFees,
                'web_admin_fee' => $webAdminFeeTotal,
                'transaction_fee' => $transactionFeeTotal,
                'rebooking_surcharge' => 0,
                'rebooking_revalidation_fee' => $rebookingSurcharge + $rebookingRevalidationFee + $rebookingRateDiff,
                'rebooking_rate_diff' => 0,
                'refundable_amount' => 0,
                'deduction_amount' => $totalPaid,
            ];
        }

        $surchargePct = $this->getRefundSurchargePercentage();
        $surcharge  = $ticketBase * ($surchargePct / 100);
        
        $refundable = max(0, round($totalPaid - $surcharge - $totalNonRefundableFees, 2));

        return [
            'base_ticket' => $totalPaid,
            'surcharge_pct' => $surchargePct,
            'surcharge_amount' => $surcharge,
            'non_refundable_fees' => $totalNonRefundableFees,
            'web_admin_fee' => $webAdminFeeTotal,
            'transaction_fee' => $transactionFeeTotal,
            'rebooking_surcharge' => 0,
            'rebooking_revalidation_fee' => $rebookingSurcharge + $rebookingRevalidationFee + $rebookingRateDiff,
            'rebooking_rate_diff' => 0,
            'refundable_amount' => $refundable,
            'deduction_amount' => $totalPaid - $refundable,
        ];
    }

    public function getRefundAmount(bool $isWithinGracePeriod = false): float
    {
        return $this->getRefundBreakdown($isWithinGracePeriod)['refundable_amount'];
    }

    /**
     * Total amount deducted (surcharge + non-refundable fees).
     */
    public function getCancellationFeeAmount(bool $isWithinGracePeriod = false): float
    {
        return $this->getRefundBreakdown($isWithinGracePeriod)['deduction_amount'];
    }

    public function getRebookingFeeAmount(): float
    {
        $created_at = $this->created_at ? \Carbon\Carbon::parse($this->created_at) : now();
        
        // No fee if rebooked within 5 minutes of booking creation
        if ($created_at->copy()->addMinutes(5)->isFuture()) {
            return 0.0;
        }

        $settings = \App\Models\PaymentSetting::current();
        $passengerCount = max(1, $this->passengers()->count());
        $isFerry        = $this->getMode() === 'ferry';
        $multiplier     = $passengerCount + ($isFerry ? $passengerCount : 0);
        $revalidationFee = floatval($settings->revalidation_fee ?? 0) * $multiplier;
        
        $originalFare = $this->getTicketBase();
        $surchargePct = 0;
        if ($this->getMode() === 'airline') {
            $surchargePct = (float) $settings->rebook_airline_before_departure_surcharge_pct;
        } else {
            if ($this->isAfterDeparture()) {
                $surchargePct = (float) $settings->rebook_ferry_after_departure_surcharge_pct;
            } else {
                $surchargePct = (float) $settings->rebook_ferry_before_departure_surcharge_pct;
            }
        }
        
        $surcharge = $originalFare * ($surchargePct / 100);
        
        return $revalidationFee + $surcharge;
    }

    public function verifyRebooking(?string $ticketUrl = null, ?string $receiptPath = null, ?string $receiptDisk = null): void
    {
        if (! $this->rebooking_departure_date || ! $this->rebooking_status) {
            return;
        }

        $staffId = $this->verified_by_user_id ?? \Illuminate\Support\Facades\Auth::id();
        $now = $this->verified_at ?? now();

        $this->update([
            'departure_date' => $this->rebooking_departure_date,
            'return_date' => $this->rebooking_return_date,
            'status' => 'confirmed',
            'is_rebooked' => true,
            'rebooking_status' => 'verified',
            'verified_by_user_id' => $staffId,
            'verified_at' => $now,
        ]);

        app(\App\Services\GraciaPointsService::class)->awardPointsForBooking($this, \App\Models\User::find($staffId));
        
        if ($this->transaction && $this->transaction->rebooking_fee > 0) {
            app(\App\Services\GraciaPointsService::class)->awardPointsForRebookingFee($this, $this->transaction->rebooking_fee, \App\Models\User::find($staffId));
        }

        if ($this->transaction) {
            $this->transaction->update([
                'payment_status' => 'paid',
                'verified_by_user_id' => $staffId,
                'verified_at' => $now,
            ]);
        }

        try {
            Mail::to($this->client_email)->send(new RebookingVerification($this, $ticketUrl, $receiptPath, $receiptDisk));
        } catch (Throwable $e) {
            Log::error('Failed sending rebooking verification email', [
                'booking_id' => $this->id ?? null,
                'email' => $this->client_email ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function getPriceBreakdown(): array
    {
        $breakdown = [];
        $passengers = $this->passengers;
        
        $depTicketTotal = 0;
        $depAccTotal = 0;
        $retTicketTotal = 0;
        $retAccTotal = 0;
        
        foreach ($passengers as $p) {
            if ($p->is_promo) {
                $depTicketTotal += (float) $p->promo_price;
            } else {
                $pDepTicket = (float) ($this->schedule_price ?? 0);
                $pDepAcc = (float) ($this->schedule_accommodation_price ?? 0);
                $pRetTicket = (float) ($this->return_schedule_price ?? 0);
                $pRetAcc = (float) ($this->return_schedule_accommodation_price ?? 0);
                
                if ($p->discount) {
                    $multiplier = 1 - ((float) $p->discount->percentage / 100);
                    $pDepTicket *= $multiplier;
                    $pDepAcc *= $multiplier;
                    $pRetTicket *= $multiplier;
                    $pRetAcc *= $multiplier;
                }
                
                $depTicketTotal += $pDepTicket;
                $depAccTotal += $pDepAcc;
                $retTicketTotal += $pRetTicket;
                $retAccTotal += $pRetAcc;
            }
        }
        
        foreach ($this->transportClasses as $index => $tc) {
            $price = (float) $tc->pivot->price;
            if ($index === 0) {
                $depTicketTotal += $price;
            } elseif ($index === 1) {
                $retTicketTotal += $price;
            }
        }
        
        // Combine ticket + accommodation/transport class into one line
        if ($depTicketTotal + $depAccTotal > 0) {
            $breakdown[] = [
                'label' => 'Departure Ticket & Transport Class (' . $passengers->count() . 'x)',
                'amount' => $depTicketTotal + $depAccTotal,
                'class' => ''
            ];
        }
        
        if ($retTicketTotal + $retAccTotal > 0) {
            $breakdown[] = [
                'label' => 'Return Ticket & Transport Class (' . $passengers->count() . 'x)',
                'amount' => $retTicketTotal + $retAccTotal,
                'class' => ''
            ];
        }

        foreach ($this->accommodations as $acc) {
            $breakdown[] = [
                'label' => $acc->name,
                'amount' => (float) $acc->pivot->price,
                'class' => ''
            ];
        }

        // Transport classes are now combined into the tickets above

        if ($this->has_vehicle && $this->vehicle_price > 0) {
            $breakdown[] = [
                'label' => 'Vehicle Freight (' . $this->vehicle_type . ')',
                'amount' => (float) $this->vehicle_price,
                'class' => ''
            ];
        }
        
        if ($this->has_extra_baggage && $this->extra_baggage_price > 0) {
            $breakdown[] = [
                'label' => 'Extra Baggage (' . $this->extra_baggage_weight . 'kg)',
                'amount' => (float) $this->extra_baggage_price,
                'class' => ''
            ];
        }

        if ($this->voucher_discount_amount > 0) {
            $breakdown[] = [
                'label' => 'Voucher Discount (' . $this->voucher_code . ')',
                'amount' => - (float) $this->voucher_discount_amount,
                'class' => 'text-green-600'
            ];
        }

        if ($this->points_discount > 0) {
            $breakdown[] = [
                'label' => 'Gracia Points Applied',
                'amount' => - (float) $this->points_discount,
                'class' => 'text-green-600'
            ];
        }

        $sumSoFar = array_sum(array_column($breakdown, 'amount'));
        $fees = (float) $this->total_price - $sumSoFar;
        
        if ($fees > 0.01) {
            $settings = \App\Models\PaymentSetting::current();
            
            // Replicate CreateBookingAction multiplier logic for display
            $isFerry    = optional($this->schedule)->route?->mode === 'ferry';
            $paxCount   = $this->passengers->count();
            // If mode isn't loaded, fallback to checking service name or just assume ferry multiplier
            if (!$this->relationLoaded('schedule') || !isset($isFerry)) {
                $isFerry = stripos($this->schedule_service ?? '', 'airline') === false;
            }
            $multiplier = $paxCount + ($isFerry ? $paxCount : 0);
            
            $transactionFee = $multiplier * (float) $settings->transaction_fee;
            $hotelFee       = $this->accommodations->count() > 0 ? (float) $settings->fee_per_accommodation : 0;
            
            if ($fees >= $transactionFee && $transactionFee > 0) {
                $breakdown[] = ['label' => 'Transaction Fee', 'amount' => $transactionFee, 'class' => 'text-slate-500'];
                $fees -= $transactionFee;
            }
            
            if ($fees >= $hotelFee && $hotelFee > 0) {
                $breakdown[] = ['label' => 'Hotel Service Fee', 'amount' => $hotelFee, 'class' => 'text-slate-500'];
                $fees -= $hotelFee;
            }

            if ($fees > 0.01) {
                $breakdown[] = ['label' => 'Web Admin Fee', 'amount' => $fees, 'class' => 'text-slate-500'];
            }
        }
        
        if ($this->transaction && (float) $this->transaction->rebooking_fee > 0) {
            $notes = $this->disruption_notes ? json_decode($this->disruption_notes, true) : [];
            $surcharge = (float) ($notes['surcharge'] ?? 0);
            $reval = (float) ($notes['revalidation_fee'] ?? 0);
            $rateDiff = (float) ($notes['rate_diff'] ?? 0);

            if ($surcharge > 0 || $reval > 0 || $rateDiff > 0) {
                $breakdown[] = [
                    'label' => 'Revalidation Fee',
                    'amount' => $surcharge + $reval + $rateDiff,
                    'class' => 'text-amber-600'
                ];
            } else {
                $breakdown[] = [
                    'label' => 'Revalidation Fee',
                    'amount' => (float) $this->transaction->rebooking_fee,
                    'class' => 'text-amber-600'
                ];
            }
        }

        return $breakdown;
    }
}

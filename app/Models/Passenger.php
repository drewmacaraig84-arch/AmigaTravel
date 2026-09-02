<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Passenger extends Model
{
    // ─── Item Status Constants ────────────────────────────────────────────────
    public const STATUS_PENDING              = 'pending';
    public const STATUS_CONFIRMED            = 'confirmed';
    public const STATUS_CANCELLED            = 'cancelled';
    public const STATUS_OPERATOR_CANCELLED   = 'operator_cancelled';
    public const STATUS_OPERATOR_REBOOKING   = 'operator_rebooking';
    public const STATUS_REFUND_PENDING       = 'refund_pending';
    public const STATUS_REFUNDED             = 'refunded';
    public const STATUS_REBOOKING_PENDING    = 'rebooking_pending';
    public const STATUS_REBOOKED             = 'rebooked';

    protected $fillable = [
        'booking_id',
        // Item identifier
        'item_number',
        'ticket_number',
        'status',
        // Passenger details
        'type',
        'name',
        'birthdate',
        'discount_id',
        'school_name',
        'id_number',
        'id_image_front',
        'id_image_back',
        // Passport details (for international flights)
        'passport_country',
        'passport_number',
        'passport_issuance_date',
        'passport_expiry_date',
        // Extra baggage (for airline flights)
        'extra_baggage_weight',
        'extra_baggage_price',
        // Seat assignment
        'seat_number',
        'seat_row',
        'seat_section',
        'return_seat_number',
        'return_seat_row',
        'return_seat_section',
        // Promo & Rate Tier
        'promotional_ticket_id',
        'is_promo',
        'rate_type',
        'promo_price',
        // Individual financials
        'fare_amount',
        'accommodation_amount',
        'discount_amount',
        'voucher_discount_share',
        'points_discount_share',
        'web_admin_fee_share',
        'transaction_fee_share',
        'item_total',
        // Cancellation & Refund
        'cancellation_fee',
        'refund_amount',
        'refund_status',
        'refund_destination',
        'refund_id_image',
        'refund_ticket_file',
        'refund_auth_letter',
        'refund_reference',
        'refund_proof',
        'refund_processed_at',
        'refund_processed_by_user_id',
        // Rebooking
        'is_rebooked',
        'rebooking_status',
        'rebooking_departure_date',
        'rebooking_return_date',
        'preferred_replacement_schedule_id',
        'disruption_notes',
        // Verification
        'verified_by_user_id',
        'verified_at',
        // E-ticket
        'ticket_pdf_path',
    ];

    protected $with = ['discount'];
    protected $appends = ['id_image_front_url', 'id_image_back_url'];

    protected $casts = [
        'is_promo'                  => 'boolean',
        'rate_type'                 => 'string',
        'is_rebooked'               => 'boolean',
        'promo_price'               => 'decimal:2',
        'extra_baggage_price'       => 'decimal:2',
        'fare_amount'               => 'decimal:2',
        'accommodation_amount'      => 'decimal:2',
        'discount_amount'           => 'decimal:2',
        'voucher_discount_share'    => 'decimal:2',
        'points_discount_share'     => 'decimal:2',
        'web_admin_fee_share'       => 'decimal:2',
        'transaction_fee_share'     => 'decimal:2',
        'item_total'                => 'decimal:2',
        'cancellation_fee'          => 'decimal:2',
        'refund_amount'             => 'decimal:2',
        'birthdate'                 => 'date:Y-m-d',
        'passport_issuance_date'    => 'date:Y-m-d',
        'passport_expiry_date'      => 'date:Y-m-d',
        'rebooking_departure_date'  => 'date',
        'rebooking_return_date'     => 'date',
        'refund_processed_at'       => 'datetime',
        'verified_at'               => 'datetime',
        'item_number'               => 'integer',
    ];

    // ─── Accessors ────────────────────────────────────────────────────────────

    public function isAdult(): bool
    {
        $type = strtolower($this->type ?? 'adult');
        if (in_array($type, ['driver', 'adult', 'senior', 'pwd'], true)) {
            return true;
        }
        if ($type === 'student') {
            if ($this->birthdate) {
                return \Carbon\Carbon::parse($this->birthdate)->diffInYears(now()) >= 12;
            }
            return true;
        }
        if ($this->birthdate) {
            $dob = \Carbon\Carbon::parse($this->birthdate);
            return $dob->diffInYears(now()) >= 12;
        }
        return false;
    }

    public function isNonAdult(): bool
    {
        return ! $this->isAdult();
    }

    public function hasPassportInfo(): bool
    {
        return filled($this->passport_number);
    }

    public function hasExtraBaggage(): bool
    {
        return filled($this->extra_baggage_weight) && (float) $this->extra_baggage_price > 0;
    }

    public function getIdImageFrontUrlAttribute(): ?string
    {
        if (! $this->id_image_front) {
            return null;
        }
        if (str_starts_with($this->id_image_front, 'http') || str_starts_with($this->id_image_front, 'data:image')) {
            return $this->id_image_front;
        }
        return storage_asset_path($this->id_image_front);
    }

    public function getIdImageBackUrlAttribute(): ?string
    {
        if (! $this->id_image_back) {
            return null;
        }
        if (str_starts_with($this->id_image_back, 'http') || str_starts_with($this->id_image_back, 'data:image')) {
            return $this->id_image_back;
        }
        return storage_asset_path($this->id_image_back);
    }

    /** Human-readable label e.g. "Item 2" */
    public function getItemLabelAttribute(): string
    {
        return 'Item ' . ($this->item_number ?? 1);
    }

    /** e.g. "#AGT-20260820-1234 – Item 2 (Juan Dela Cruz)" */
    public function getFullItemLabelAttribute(): string
    {
        $txn  = $this->getBookingModel()?->transaction_number ?? 'N/A';
        $name = $this->name ?? 'Passenger';
        return "#{$txn} – Item {$this->item_number} ({$name})";
    }

    // ─── Status Helpers ───────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
            self::STATUS_OPERATOR_REBOOKING,
        ], true);
    }

    public function isActiveBookingItem(): bool
    {
        if (in_array($this->status, [
            self::STATUS_CANCELLED,
            self::STATUS_OPERATOR_CANCELLED,
            self::STATUS_REFUND_PENDING,
            self::STATUS_REFUNDED,
            self::STATUS_REBOOKED,
        ], true)) {
            return false;
        }

        if ($this->refund_status === 'pending' || $this->refund_status === 'completed') {
            return false;
        }

        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
            self::STATUS_OPERATOR_REBOOKING,
        ], true);
    }

    public function isRefundItem(): bool
    {
        return in_array($this->status, [
            self::STATUS_REFUND_PENDING,
            self::STATUS_REFUNDED,
            self::STATUS_CANCELLED,
            self::STATUS_OPERATOR_CANCELLED,
        ], true) || (float) $this->refund_amount > 0 || in_array($this->refund_status, ['pending', 'completed'], true);
    }

    public function isRebookingHistoryItem(): bool
    {
        // Replaced/historical items explicitly have STATUS_REBOOKED or rebooking_status 'rescheduled'
        if ($this->status === self::STATUS_REBOOKED || $this->rebooking_status === 'rescheduled') {
            return true;
        }

        return false;
    }

    public function isCancelled(): bool
    {
        return in_array($this->status, [
            self::STATUS_CANCELLED,
            self::STATUS_OPERATOR_CANCELLED,
        ], true);
    }

    public function isRefundPending(): bool
    {
        return $this->refund_status === 'pending' && (float) $this->refund_amount > 0;
    }

    public function isRefundCompleted(): bool
    {
        return $this->refund_status === 'completed';
    }

    public function isRebookingPending(): bool
    {
        return $this->status === self::STATUS_REBOOKING_PENDING
            || $this->rebooking_status === 'pending';
    }

    public function isRebooked(): bool
    {
        return $this->is_rebooked || $this->rebooking_status === 'verified';
    }

    public function hasTicket(): bool
    {
        return in_array($this->status, [
            self::STATUS_CONFIRMED,
            self::STATUS_REBOOKED,
            self::STATUS_OPERATOR_REBOOKING,
        ], true);
    }

    public function getBookingModel(): ?Booking
    {
        if ($this->relationLoaded('booking')) {
            return $this->getRelation('booking');
        }
        if ($this->booking_id) {
            $booking = Booking::with(['transportClasses', 'accommodations'])->find($this->booking_id);
            if ($booking) {
                $this->setRelation('booking', $booking);
            }
            return $booking;
        }
        return null;
    }

    // ─── Financial Helpers ────────────────────────────────────────────────────

    /**
     * Get effective gross base fare amount (excluding accommodation fees).
     */
    public function getEffectiveFareAmount(): float
    {
        $booking = $this->getBookingModel();
        $isDriver = ($this->type === 'driver') && $booking?->has_vehicle;
        if ($isDriver) {
            return 0.0;
        }

        // If passenger record has fare_amount explicitly set (including 0)
        if (array_key_exists('fare_amount', $this->attributes) && $this->attributes['fare_amount'] !== null) {
            return (float) $this->attributes['fare_amount'];
        }

        if ($booking) {
            $schedPrice = (float) ($booking->schedule_price ?? 0);
            $retPrice   = (float) ($booking->return_schedule_price ?? 0);
            $isPromo    = in_array($this->rate_type ?? 'regular', ['promotional', 'super_promotional'], true)
                || (bool) ($this->is_promo ?? false);
            $paxMultiplier = (! $isPromo && in_array(strtolower($this->type ?? 'adult'), ['child', 'minor'], true)) ? 0.5 : 1.0;
            return ($schedPrice + $retPrice) * $paxMultiplier;
        }

        return 0.0;
    }

    /**
     * Get effective accommodation amount.
     */
    public function getEffectiveAccommodationAmount(): float
    {
        $booking = $this->getBookingModel();
        $isDriver = ($this->type === 'driver') && $booking?->has_vehicle;
        if ($isDriver) {
            return 0.0;
        }

        // If passenger record has accommodation_amount explicitly set (including 0)
        if (array_key_exists('accommodation_amount', $this->attributes) && $this->attributes['accommodation_amount'] !== null) {
            return (float) $this->attributes['accommodation_amount'];
        }

        if ($booking) {
            $schedAcc = (float) ($booking->schedule_accommodation_price ?? 0) + (float) ($booking->return_schedule_accommodation_price ?? 0);
            return $schedAcc;
        }

        return 0.0;
    }

    /**
     * Combined Fare & Transport Class / Accommodation for this passenger item.
     */
    public function getEffectiveFareAndClass(): float
    {
        return $this->getEffectiveFareAmount() + $this->getEffectiveAccommodationAmount();
    }

    /**
     * Get effective Web Admin Fee share.
     */
    public function getEffectiveWebAdminFee(): float
    {
        if ((float) ($this->attributes['web_admin_fee_share'] ?? 0) > 0) {
            return (float) $this->attributes['web_admin_fee_share'];
        }

        $booking = $this->getBookingModel();
        if ($booking) {
            $settings = \App\Models\PaymentSetting::current();
            return (float) $settings->getWebAdminFee($booking->isShortHaul());
        }

        return 0.0;
    }

    /**
     * Get effective Transaction Fee share.
     */
    public function getEffectiveTransactionFee(): float
    {
        if ((float) ($this->attributes['transaction_fee_share'] ?? 0) > 0) {
            return (float) $this->attributes['transaction_fee_share'];
        }

        $booking = $this->getBookingModel();
        if ($booking) {
            $settings = \App\Models\PaymentSetting::current();
            return (float) $settings->getTransactionFee($booking->isShortHaul());
        }

        return 0.0;
    }

    /**
     * Get effective item total with dynamic fallback calculation.
     */
    public function getEffectiveItemTotal(): float
    {
        if ((float) ($this->attributes['item_total'] ?? 0) > 0) {
            return (float) $this->attributes['item_total'];
        }

        $isSuperPromo = ($this->rate_type ?? 'regular') === 'super_promotional';
        $isPromo = in_array($this->rate_type ?? 'regular', ['promotional', 'super_promotional'], true) || (bool) ($this->is_promo ?? false);

        $disc    = ($isSuperPromo || $isPromo) ? 0.0 : (float) ($this->discount_amount ?? 0);
        if (! ($isSuperPromo || $isPromo) && $disc <= 0 && $this->discount) {
            $disc = $this->discount->computeDiscountAmount($gross);
        }
        $voucher = $isSuperPromo ? 0.0 : (float) ($this->voucher_discount_share ?? 0);
        $points  = $isSuperPromo ? 0.0 : (float) ($this->points_discount_share ?? 0);
        $webFee  = $this->getEffectiveWebAdminFee();
        $txFee   = $this->getEffectiveTransactionFee();
        $baggage = (float) ($this->extra_baggage_price ?? 0);

        return max(0.0, round($gross - $disc - $voucher - $points + $webFee + $txFee + $baggage, 2));
    }

    /** Amount retained by Amiga (non-refundable fees) for this passenger item */
    public function getRetainedAmount(): float
    {
        return max(0.0, $this->getEffectiveItemTotal() - (float) $this->refund_amount);
    }

    /** Refundable base = item_total minus non-refundable fees */
    public function getRefundableBase(): float
    {
        $nonRefundable = $this->getEffectiveWebAdminFee() + $this->getEffectiveTransactionFee();
        return max(0.0, $this->getEffectiveItemTotal() - $nonRefundable);
    }

    /**
     * Get individual refund breakdown for this passenger item.
     */
    public function getRefundBreakdown(bool $isWithinGracePeriod = false): array
    {
        $itemTotal = $this->getEffectiveItemTotal();

        if ($isWithinGracePeriod) {
            return [
                'item_total'          => $itemTotal,
                'refundable_amount'   => $itemTotal,
                'deduction_amount'    => 0.0,
                'surcharge_amount'    => 0.0,
                'non_refundable_fees' => 0.0,
            ];
        }

        $booking = $this->getBookingModel();
        $nonRefundableFees = $this->getEffectiveWebAdminFee() + $this->getEffectiveTransactionFee();
        $ticketBase = max(0.0, $itemTotal - $nonRefundableFees);
        $surchargePct = $booking ? $booking->getRefundSurchargePercentage() : 0;
        $surcharge = round($ticketBase * ($surchargePct / 100), 2);
        $refundable = max(0.0, round($ticketBase - $surcharge, 2));
        $deduction = round($itemTotal - $refundable, 2);

        return [
            'item_total'          => $itemTotal,
            'refundable_amount'   => $refundable,
            'deduction_amount'    => $deduction,
            'surcharge_amount'    => $surcharge,
            'non_refundable_fees' => $nonRefundableFees,
        ];
    }

    // ─── Status Label ─────────────────────────────────────────────────────────

    public function getStatusLabel(): string
    {
        if ($this->status === self::STATUS_REBOOKED || $this->rebooking_status === 'rescheduled') {
            return 'Rescheduled';
        }
        if ($this->rebooking_status === 'pending' || $this->status === self::STATUS_REBOOKING_PENDING) {
            return 'Rebooking Pending';
        }
        if ($this->status === self::STATUS_CONFIRMED && ($this->is_rebooked || $this->rebooking_status === 'verified')) {
            return 'Rebooked (Confirmed)';
        }
        return match ($this->status) {
            self::STATUS_PENDING            => 'Pending',
            self::STATUS_CONFIRMED          => 'Confirmed',
            self::STATUS_CANCELLED          => 'Cancelled',
            self::STATUS_OPERATOR_CANCELLED => 'Operator Cancelled',
            self::STATUS_OPERATOR_REBOOKING => 'Operator Rebooking',
            self::STATUS_REFUND_PENDING     => 'Refund Pending',
            self::STATUS_REFUNDED           => 'Refunded',
            self::STATUS_REBOOKING_PENDING  => 'Rebooking Pending',
            self::STATUS_REBOOKED           => 'Rescheduled',
            default                         => ucfirst($this->status ?? 'Unknown'),
        };
    }

    public function getStatusColor(): string
    {
        return match ($this->status) {
            self::STATUS_CONFIRMED          => 'success',
            self::STATUS_CANCELLED,
            self::STATUS_OPERATOR_CANCELLED => 'danger',
            self::STATUS_REFUND_PENDING     => 'warning',
            self::STATUS_REFUNDED           => 'info',
            self::STATUS_REBOOKING_PENDING,
            self::STATUS_OPERATOR_REBOOKING => 'primary',
            self::STATUS_REBOOKED           => 'success',
            default                         => 'gray',
        };
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    public function promotionalTicket(): BelongsTo
    {
        return $this->belongsTo(PromotionalTicket::class);
    }

    public function preferredReplacementSchedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'preferred_replacement_schedule_id');
    }

    public function verifiedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    public function refundProcessedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'refund_processed_by_user_id');
    }
}

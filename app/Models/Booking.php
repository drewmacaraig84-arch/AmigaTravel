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
use Illuminate\Support\Facades\Storage;
use Throwable;

class Booking extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PENDING_REBOOKING = 'pending_rebooking';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_OPERATOR_CANCELLED = 'operator_cancelled';
    public const STATUS_OPERATOR_REBOOKING = 'operator_rebooking';

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
        'refund_id_image',
        'refund_ticket_file',
        'refund_auth_letter',
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
        'sla_voucher_issued_at',
        'refund_status',
        'refund_proof',
        'refund_reference',
        'refund_processed_at',
        'refund_processed_by_user_id',
        'refund_notes',
    ];

    protected static function booted(): void
    {
        static::updated(function (Booking $booking) {
            if ($booking->wasChanged('status')) {
                $newStatus = $booking->status;
                if ($newStatus === self::STATUS_CONFIRMED) {
                    $booking->passengers()
                        ->where('status', Passenger::STATUS_PENDING)
                        ->update(['status' => Passenger::STATUS_CONFIRMED]);
                } elseif ($newStatus === self::STATUS_CANCELLED) {
                    $targetStatus = ((float) $booking->refund_amount > 0 && $booking->refund_status === 'pending')
                        ? Passenger::STATUS_REFUND_PENDING
                        : Passenger::STATUS_CANCELLED;
                    $booking->passengers()
                        ->whereIn('status', [Passenger::STATUS_PENDING, Passenger::STATUS_CONFIRMED])
                        ->update(['status' => $targetStatus]);
                } elseif ($newStatus === self::STATUS_OPERATOR_CANCELLED) {
                    $booking->passengers()
                        ->whereIn('status', [Passenger::STATUS_PENDING, Passenger::STATUS_CONFIRMED])
                        ->update(['status' => Passenger::STATUS_OPERATOR_CANCELLED]);
                } elseif ($newStatus === self::STATUS_OPERATOR_REBOOKING) {
                    $booking->passengers()
                        ->whereIn('status', [Passenger::STATUS_PENDING, Passenger::STATUS_CONFIRMED, Passenger::STATUS_OPERATOR_CANCELLED])
                        ->update(['status' => Passenger::STATUS_OPERATOR_REBOOKING]);
                }
            }

            if ($booking->wasChanged('refund_status') && $booking->refund_status === 'completed') {
                $booking->passengers()
                    ->whereIn('status', [Passenger::STATUS_REFUND_PENDING, Passenger::STATUS_CANCELLED, Passenger::STATUS_OPERATOR_CANCELLED])
                    ->update([
                        'status' => Passenger::STATUS_REFUNDED,
                        'refund_status' => 'completed',
                    ]);
            }
        });
    }

    public function isUserCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isOperatorCancelled(): bool
    {
        return $this->status === self::STATUS_OPERATOR_CANCELLED;
    }

    public function isOperatorRebooking(): bool
    {
        return $this->status === self::STATUS_OPERATOR_REBOOKING;
    }

    public function isServiceCancellation(): bool
    {
        return $this->isOperatorCancelled() || $this->isOperatorRebooking() || filled($this->service_cancellation_id);
    }

    public function isInternational(): bool
    {
        if ($this->schedule?->ferryRoute?->isInternational()) {
            return true;
        }
        if (strtolower($this->mode ?? '') !== 'airline' && strtolower($this->schedule?->ferryRoute?->mode ?? '') !== 'airline') {
            return false;
        }
        $domesticPorts = ['manila', 'batangas', 'calapan', 'caticlan', 'boracay', 'boracay (caticlan)', 'cebu', 'davao', 'roxas', 'puerto princesa', 'el nido', 'coron', 'bacolod', 'iloilo', 'tagbilaran', 'bohol', 'siargao', 'zamboanga', 'general santos', 'clark', 'laoag', 'legazpi', 'dumaguete', 'tacloban', 'cagayan de oro', 'butuan', 'ozamiz', 'dipolog', 'pagadian', 'surigao', 'tandag', 'camiguin', 'batanes', 'basco', 'busuanga', 'san jose'];
        return !in_array(strtolower(trim($this->origin ?? '')), $domesticPorts, true)
            || !in_array(strtolower(trim($this->destination ?? '')), $domesticPorts, true);
    }

    public function getNonRefundableFees(): float
    {
        return max(0, (float) $this->total_price - $this->getTicketBase());
    }

    public function isRefundPending(): bool
    {
        return in_array($this->status, [self::STATUS_CANCELLED, self::STATUS_OPERATOR_CANCELLED])
            && (float) $this->refund_amount > 0
            && ($this->refund_status === 'pending' || empty($this->refund_status));
    }

    public function isRefundCompleted(): bool
    {
        return in_array($this->status, [self::STATUS_CANCELLED, self::STATUS_OPERATOR_CANCELLED])
            && (float) $this->refund_amount > 0
            && $this->refund_status === 'completed';
    }

    public function getRefundStatusLabel(): string
    {
        if (! in_array($this->status, [self::STATUS_CANCELLED, self::STATUS_OPERATOR_CANCELLED]) || (float) $this->refund_amount <= 0) {
            return ucfirst($this->status);
        }

        return $this->isRefundCompleted() ? 'Refunded & Disbursed' : 'Refund Processing';
    }

    public function getRefundMessage(): ?string
    {
        if (! in_array($this->status, [self::STATUS_CANCELLED, self::STATUS_OPERATOR_CANCELLED]) || (float) $this->refund_amount <= 0) {
            return null;
        }

        $amountFormatted = '₱' . number_format((float) $this->refund_amount, 2);

        if ($this->isRefundCompleted()) {
            $refText = filled($this->refund_reference) ? " (Reference: {$this->refund_reference})" : '';
            return "Your refund of {$amountFormatted} has been successfully processed and disbursed to your designated account{$refText}. You may download your Refund Acknowledgement Receipt and proof of disbursement below.";
        }

        $dest = $this->getParsedRefundDestination();
        $destLines = [];
        if ($dest['method']) {
            $destLines[] = "Refund Method: " . $dest['method'] . ($dest['institution'] ? " ({$dest['institution']})" : "");
        }
        if ($dest['account_number']) {
            $destLines[] = "Account No.: " . $dest['account_number'];
        }
        if ($dest['account_name']) {
            $destLines[] = "Account Name: " . ucwords(strtolower($dest['account_name']));
        }

        $destBlock = ! empty($destLines) 
            ? " to your selected payment method:\n\n" . implode("\n", $destLines) . "\n\n"
            : (filled($this->refund_destination) ? " to {$this->refund_destination}.\n\n" : ".\n\n");

        return "Your refund request of {$amountFormatted} is currently being processed by our finance team. Please allow 24–48 hours for review and disbursement{$destBlock}Once your refund has been completed, you will receive an email confirmation and notification with the refund details.\n\nThank you for your patience and understanding.";
    }

    /**
     * Parse structured refund destination details.
     *
     * @return array{method: ?string, institution: ?string, account_number: ?string, account_name: ?string, raw: ?string}
     */
    public function getParsedRefundDestination(): array
    {
        $raw = trim($this->refund_destination ?? '');
        if (blank($raw)) {
            return [
                'method' => null,
                'institution' => null,
                'account_number' => null,
                'account_name' => null,
                'raw' => null,
            ];
        }

        $result = [
            'method' => null,
            'institution' => null,
            'account_number' => null,
            'account_name' => null,
            'raw' => $raw,
        ];

        if (str_contains($raw, '|')) {
            $parts = array_map('trim', explode('|', $raw));
            foreach ($parts as $part) {
                if (preg_match('/^Method:\s*(.+)$/i', $part, $m)) {
                    $result['method'] = trim($m[1]);
                } elseif (preg_match('/^Institution:\s*(.+)$/i', $part, $m)) {
                    $result['institution'] = trim($m[1]);
                } elseif (preg_match('/^Account\s*(?:No|Number)?:\s*(.+)$/i', $part, $m)) {
                    $result['account_number'] = trim($m[1]);
                } elseif (preg_match('/^Name:\s*(.+)$/i', $part, $m)) {
                    $result['account_name'] = trim($m[1]);
                }
            }
        } else {
            // Regex fallbacks for freeform text (e.g. "GCash - 09123456789 (Macaraig)")
            if (preg_match('/(gcash|maya|paymaya|bank\s*transfer|bank|bdo|bpi|metrobank|unionbank|landbank|rcbc|pnb)/i', $raw, $m)) {
                $matchedMethod = trim($m[1]);
                if (in_array(strtolower($matchedMethod), ['bdo', 'bpi', 'metrobank', 'unionbank', 'landbank', 'rcbc', 'pnb'], true)) {
                    $result['method'] = 'Bank Account';
                    $result['institution'] = strtoupper($matchedMethod);
                } else {
                    $result['method'] = ucfirst(strtolower($matchedMethod));
                }
            }
            if (preg_match('/(\d{9,16})/', $raw, $m)) {
                $result['account_number'] = $m[1];
            }
            $clean = preg_replace('/(gcash|maya|paymaya|bank\s*transfer|bank|bdo|bpi|metrobank|unionbank|landbank|rcbc|pnb|\d{9,16}|[-:|,()])/i', ' ', $raw);
            $cleanName = trim(preg_replace('/\s+/', ' ', $clean));
            if (! empty($cleanName) && strlen($cleanName) > 2) {
                $result['account_name'] = $cleanName;
            }
        }

        return $result;
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
        'sla_voucher_issued_at' => 'datetime',
        'refund_processed_at' => 'datetime',
    ];

    /**
     * Get the SLA guarantee note message.
     */
    public function getSlaVoucherNote(?PaymentSetting $settings = null, bool $forApp = false): ?string
    {
        $settings = $settings ?? PaymentSetting::current();

        if (! $settings->isSlaVoucherEnabled()) {
            return null;
        }

        // Only show if booking is pending
        if ($this->status !== 'pending' && $this->status !== self::STATUS_PENDING_REBOOKING) {
            return null;
        }

        // Only show if the customer has actually submitted payment / payment proof (not unpaid)
        $tx = $this->transaction;
        if (! $tx || $tx->payment_status === 'unpaid' || (! $tx->proof_submitted_at && ! $tx->proof_of_payment && $tx->payment_status !== 'paid')) {
            return null;
        }

        $hours = $settings->getSlaVoucherHours();
        $amount = number_format($settings->getSlaVoucherAmount(), 0);

        if ($forApp) {
            return "Our Verification Guarantee: If your booking is not processed within {$hours} hours of payment submission, you will automatically receive a ₱{$amount} travel voucher valid for 3 months in your account for your next booking.";
        }

        return "Our Verification Guarantee: If your booking is not processed within {$hours} hours of payment submission, you will automatically receive a ₱{$amount} travel voucher valid for 3 months towards your next trip. Download the Amiga Gracia app to easily claim and manage your reward vouchers!";
    }

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
            ->withPivot('price', 'is_promo', 'rate_type', 'rate_code', 'is_return')
            ->withTimestamps();
    }

    public function departureTransportClasses(): BelongsToMany
    {
        return $this->belongsToMany(TransportClass::class, 'booking_transport_class')
            ->withPivot('price', 'is_promo', 'rate_type', 'rate_code', 'is_return')
            ->wherePivot('is_return', false)
            ->withTimestamps();
    }

    public function returnTransportClasses(): BelongsToMany
    {
        return $this->belongsToMany(TransportClass::class, 'booking_transport_class')
            ->withPivot('price', 'is_promo', 'rate_type', 'rate_code', 'is_return')
            ->wherePivot('is_return', true)
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
        return $this->hasOne(Transaction::class)->latestOfMany();
    }

    public function verifiedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    public function refundProcessedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'refund_processed_by_user_id');
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
        return in_array($this->status, ['pending', 'unpaid'], true)
            && $this->transaction !== null
            && $this->transaction->isVerificationLocked();
    }

    public function verificationTimerLabel(): string
    {
        if (! in_array($this->status, ['pending', 'unpaid'], true)) {
            return '—';
        }

        if (! $this->transaction) {
            return 'No tx';
        }

        return $this->transaction->verificationTimerLabel();
    }

    public function verificationTimerTooltip(): ?string
    {
        if (! in_array($this->status, ['pending', 'unpaid'], true)) {
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
        return $this->transportClasses()
            ->where(function ($q) {
                $q->where('booking_transport_class.is_promo', true)
                  ->orWhereIn('booking_transport_class.rate_type', ['promotional', 'super_promotional']);
            })->exists()
            || $this->passengers()->where(function ($q) {
                $q->where('is_promo', true)
                  ->orWhereIn('rate_type', ['promotional', 'super_promotional']);
            })->exists();
    }

    public function hasSuperPromoTicket(): bool
    {
        return $this->transportClasses()->wherePivot('rate_type', 'super_promotional')->exists()
            || $this->passengers()->where('rate_type', 'super_promotional')->exists();
    }

    public function canCancel(): bool
    {
        if ($this->status === self::STATUS_PENDING_REBOOKING) {
            return false;
        }

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

        if ($this->is_rebooked || $this->hasBeenRebooked() || !empty($this->rebooking_status) || $this->status === self::STATUS_PENDING_REBOOKING) {
            return false;
        }

        if (! $this->transaction || ! in_array($this->transaction->payment_status, ['paid', 'pending', 'unpaid'])) {

            return false;
        }

        $departureDateTime = $this->getDepartureDateTime();
        if (! $departureDateTime) {
            return false;
        }

        // 1. Allowed for everyone if strictly before the 24-hour mark prior to departure
        if (now()->isBefore($departureDateTime->copy()->subHours(24))) {
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

        if (! $this->transaction || ! in_array($this->transaction->payment_status, ['paid', 'pending', 'unpaid'])) {
            return false;
        }

        $departureDateTime = $this->getDepartureDateTime();
        if (! $departureDateTime) {
            return false;
        }

        // Refund is strictly allowed at least 24 hours prior to departure
        return now()->isBefore($departureDateTime->copy()->subHours(24));
    }

    public function hasBeenRebooked(): bool
    {
        if ($this->is_rebooked) {
            return true;
        }

        if ($this->rebooking_status === 'verified') {
            return true;
        }

        if ($this->transaction && (float) $this->transaction->rebooking_fee > 0) {
            return true;
        }

        if (!empty($this->disruption_notes)) {
            $notes = json_decode($this->disruption_notes, true);
            if ((float)($notes['revalidation_fee'] ?? 0) > 0 || (float)($notes['surcharge'] ?? 0) > 0) {
                return true;
            }
        }

        return false;
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
     * Get the operator name for this booking (Ferry company or Airline carrier).
     */
    public function getOperatorName(): ?string
    {
        // 1. Direct from departure schedule relation
        if ($this->relationLoaded('schedule') && $this->schedule) {
            $route = $this->schedule->ferryRoute ?? $this->schedule->route;
            if ($route) {
                $name = $route->operatorRecord?->name ?? $route->operator;
                if (filled($name)) {
                    return $name;
                }
            }
        }

        // 2. Query via schedule_id
        if ($this->schedule_id) {
            try {
                $route = \App\Models\FerryRoute::query()
                    ->join('schedules', 'ferry_routes.id', '=', 'schedules.ferry_route_id')
                    ->where('schedules.id', $this->schedule_id)
                    ->with('operatorRecord')
                    ->first(['ferry_routes.*']);

                if ($route) {
                    $name = $route->operatorRecord?->name ?? $route->operator;
                    if (filled($name)) {
                        return $name;
                    }
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        // 3. Fallback matching airline flight code prefixes and standard ferry keywords
        $svc = strtoupper(trim((string) $this->schedule_service));
        if (str_starts_with($svc, '5J') || str_contains($svc, 'CEBU PACIFIC')) {
            return 'Cebu Pacific';
        }
        if (str_starts_with($svc, 'PR') || str_contains($svc, 'PHILIPPINE AIRLINES') || str_contains($svc, 'PAL')) {
            return 'Philippine Airlines';
        }
        if (str_starts_with($svc, 'Z2') || str_contains($svc, 'AIRASIA')) {
            return 'AirAsia';
        }
        if (str_starts_with($svc, 'DG')) {
            return 'Cebgo';
        }
        if (str_starts_with($svc, 'GAP') || str_contains($svc, 'AIR SWIFT') || str_contains($svc, 'AIRSWIFT')) {
            return 'AirSWIFT';
        }
        if (str_contains($svc, 'FASTCAT')) {
            return 'FastCat';
        }
        if (str_contains($svc, 'STARLITE')) {
            return 'Starlite Ferries';
        }
        if (str_contains($svc, 'MONTENEGRO')) {
            return 'Montenegro Shipping Lines';
        }
        if (str_contains($svc, '2GO')) {
            return '2GO Travel';
        }
        if (str_contains($svc, 'OCEANJET')) {
            return 'OceanJet';
        }
        if (str_contains($svc, 'SUPER CAT') || str_contains($svc, 'SUPERCAT')) {
            return 'SuperCat';
        }

        return filled($this->schedule_service) ? $this->schedule_service : null;
    }

    /**
     * Get return operator name for round-trip bookings.
     */
    public function getReturnOperatorName(): ?string
    {
        if (! $this->return_date && ! $this->rebooking_return_date) {
            return null;
        }

        if ($this->returnSchedule) {
            $route = $this->returnSchedule->ferryRoute ?? $this->returnSchedule->route;
            if ($route) {
                $name = $route->operatorRecord?->name ?? $route->operator;
                if (filled($name)) {
                    return $name;
                }
            }
        }

        if ($this->return_schedule_id) {
            try {
                $route = \App\Models\FerryRoute::query()
                    ->join('schedules', 'ferry_routes.id', '=', 'schedules.ferry_route_id')
                    ->where('schedules.id', $this->return_schedule_id)
                    ->with('operatorRecord')
                    ->first(['ferry_routes.*']);

                if ($route) {
                    $name = $route->operatorRecord?->name ?? $route->operator;
                    if (filled($name)) {
                        return $name;
                    }
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        $svc = strtoupper(trim((string) $this->return_schedule_service));
        if (str_starts_with($svc, '5J') || str_contains($svc, 'CEBU PACIFIC')) {
            return 'Cebu Pacific';
        }
        if (str_starts_with($svc, 'PR') || str_contains($svc, 'PHILIPPINE AIRLINES') || str_contains($svc, 'PAL')) {
            return 'Philippine Airlines';
        }
        if (str_starts_with($svc, 'Z2') || str_contains($svc, 'AIRASIA')) {
            return 'AirAsia';
        }
        if (str_starts_with($svc, 'DG')) {
            return 'Cebgo';
        }
        if (str_starts_with($svc, 'GAP') || str_contains($svc, 'AIR SWIFT') || str_contains($svc, 'AIRSWIFT')) {
            return 'AirSWIFT';
        }
        if (str_contains($svc, 'FASTCAT')) {
            return 'FastCat';
        }
        if (str_contains($svc, 'STARLITE')) {
            return 'Starlite Ferries';
        }
        if (str_contains($svc, 'MONTENEGRO')) {
            return 'Montenegro Shipping Lines';
        }
        if (str_contains($svc, '2GO')) {
            return '2GO Travel';
        }
        if (str_contains($svc, 'OCEANJET')) {
            return 'OceanJet';
        }

        return filled($this->return_schedule_service) ? $this->return_schedule_service : $this->getOperatorName();
    }

    /**
     * Check if a given Schedule belongs to the same operator as this booking's service.
     */
    public function matchesOperator(Schedule $schedule, bool $isReturn = false): bool
    {
        $origOperator = $isReturn ? ($this->getReturnOperatorName() ?: $this->getOperatorName()) : $this->getOperatorName();
        if (blank($origOperator)) {
            return true;
        }

        $scheduleOperator = $schedule->ferryRoute?->operatorRecord?->name 
            ?? $schedule->ferryRoute?->operator 
            ?? null;

        if (blank($scheduleOperator)) {
            $svc = strtoupper(trim((string) $schedule->service_name));
            if (str_starts_with($svc, '5J') || str_contains($svc, 'CEBU PACIFIC')) {
                $scheduleOperator = 'Cebu Pacific';
            } elseif (str_starts_with($svc, 'PR') || str_contains($svc, 'PHILIPPINE AIRLINES') || str_contains($svc, 'PAL')) {
                $scheduleOperator = 'Philippine Airlines';
            } elseif (str_starts_with($svc, 'Z2') || str_contains($svc, 'AIRASIA')) {
                $scheduleOperator = 'AirAsia';
            } elseif (str_starts_with($svc, 'DG')) {
                $scheduleOperator = 'Cebgo';
            } elseif (str_starts_with($svc, 'GAP') || str_contains($svc, 'AIR SWIFT') || str_contains($svc, 'AIRSWIFT')) {
                $scheduleOperator = 'AirSWIFT';
            } elseif (str_contains($svc, 'FASTCAT')) {
                $scheduleOperator = 'FastCat';
            } elseif (str_contains($svc, 'STARLITE')) {
                $scheduleOperator = 'Starlite Ferries';
            } elseif (str_contains($svc, 'MONTENEGRO')) {
                $scheduleOperator = 'Montenegro Shipping Lines';
            } elseif (str_contains($svc, '2GO')) {
                $scheduleOperator = '2GO Travel';
            } elseif (str_contains($svc, 'OCEANJET')) {
                $scheduleOperator = 'OceanJet';
            } elseif (str_contains($svc, 'SUPER CAT') || str_contains($svc, 'SUPERCAT')) {
                $scheduleOperator = 'SuperCat';
            }
        }

        if (blank($scheduleOperator)) {
            return true;
        }

        $normOrig = strtolower(preg_replace('/[^a-z0-9]/', '', (string)$origOperator));
        $normSch = strtolower(preg_replace('/[^a-z0-9]/', '', (string)$scheduleOperator));

        return str_contains($normOrig, $normSch) || str_contains($normSch, $normOrig);
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
     * Determine if this booking is short haul (< 5 hours / 300 minutes).
     */
    public function isShortHaul(): bool
    {
        if (strtolower($this->getMode()) === 'airline') {
            return false;
        }

        if (!empty($this->duration_days) && $this->duration_days > 0) {
            return false; // Tour package multi-day / full day trip
        }

        $depSchedule = $this->relationLoaded('schedule') ? $this->schedule : ($this->schedule_id ? \App\Models\Schedule::find($this->schedule_id) : null);
        $retSchedule = $this->relationLoaded('returnSchedule') ? $this->returnSchedule : ($this->return_schedule_id ? \App\Models\Schedule::find($this->return_schedule_id) : null);

        if ($depSchedule) {
            $depDuration = $depSchedule->duration_minutes;
            if ($retSchedule) {
                $retDuration = $retSchedule->duration_minutes;
                return max($depDuration, $retDuration) < 300;
            }
            return $depDuration < 300;
        }

        return false;
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
        $isShortHaul    = $this->isShortHaul();

        $webAdminFee    = $settings->getWebAdminFee($isShortHaul);
        $txFee          = $settings->getTransactionFee($isShortHaul);

        $nonRefundable  = ($webAdminFee * $multiplier)
                        + ($txFee * $multiplier);

        return max(0, floatval($this->total_price) - $nonRefundable);
    }

    /**
     * Resolve uploaded PDF path from Filament 3 FileUpload data.
     */
    public static function resolveUploadedPdfPath(mixed $rawPdf, ?string $transactionNumber = null): ?string
    {
        if (empty($rawPdf)) {
            return null;
        }

        $txPrefix = $transactionNumber ? preg_replace('/[^A-Za-z0-9_-]/', '', $transactionNumber) . '-' : '';
        $generateFilename = fn () => 'ticket-' . $txPrefix . uniqid() . '.pdf';

        // 1. Array handling (Filament 3 often passes ['livewire-tmp/xxx' => 'name.pdf'] or ['uuid' => File])
        if (is_array($rawPdf)) {
            foreach ($rawPdf as $k => $v) {
                $resolved = self::resolveUploadedPdfPath($v, $transactionNumber);
                if ($resolved) {
                    return $resolved;
                }
                if (is_string($k) && !is_numeric($k)) {
                    $resolved = self::resolveUploadedPdfPath($k, $transactionNumber);
                    if ($resolved) {
                        return $resolved;
                    }
                }
            }
            return null;
        }

        // 2. Object with storeAs / getRealPath / readStream
        if ($rawPdf instanceof \Illuminate\Http\UploadedFile || (is_object($rawPdf) && method_exists($rawPdf, 'storeAs'))) {
            $filename = $generateFilename();
            return $rawPdf->storeAs('tickets', $filename, 'public');
        }

        if (is_object($rawPdf) && method_exists($rawPdf, 'getRealPath')) {
            $realPath = $rawPdf->getRealPath();
            if ($realPath && file_exists($realPath)) {
                $filename = $generateFilename();
                Storage::disk('public')->put('tickets/' . $filename, file_get_contents($realPath));
                return 'tickets/' . $filename;
            }
        }

        if (is_object($rawPdf) && method_exists($rawPdf, 'readStream')) {
            $stream = $rawPdf->readStream();
            if ($stream) {
                $filename = $generateFilename();
                Storage::disk('public')->put('tickets/' . $filename, $stream);
                return 'tickets/' . $filename;
            }
        }

        // 3. String path handling
        if (is_string($rawPdf) && filled($rawPdf)) {
            $clean = ltrim($rawPdf, '/\\');
            $baseName = basename($clean);

            // If it is ALREADY permanently stored in tickets/ on the public disk:
            if (str_starts_with($clean, 'tickets/') && Storage::disk('public')->exists($clean)) {
                return $clean;
            }

            // If it's a relative filename in public disk tickets:
            if (Storage::disk('public')->exists('tickets/' . $baseName)) {
                return 'tickets/' . $baseName;
            }

            // Search across known storage disks (local, public, livewire temp) for temporary files
            $disksToCheck = array_values(array_unique(array_filter([
                config('livewire.temporary_file_upload.disk'),
                config('filesystems.default'),
                'local',
                'public',
            ])));

            foreach ($disksToCheck as $disk) {
                try {
                    $storage = Storage::disk($disk);
                    if ($storage->exists($clean)) {
                        $contents = $storage->get($clean);
                        if ($contents) {
                            $filename = $generateFilename();
                            Storage::disk('public')->put('tickets/' . $filename, $contents);
                            return 'tickets/' . $filename;
                        }
                    }
                    if ($storage->exists('livewire-tmp/' . $baseName)) {
                        $contents = $storage->get('livewire-tmp/' . $baseName);
                        if ($contents) {
                            $filename = $generateFilename();
                            Storage::disk('public')->put('tickets/' . $filename, $contents);
                            return 'tickets/' . $filename;
                        }
                    }
                } catch (\Throwable $e) {
                    // continue
                }
            }

            // Check candidate local paths
            $fileCandidates = [
                $rawPdf,
                storage_path('app/private/' . $clean),
                storage_path('app/' . $clean),
                storage_path('app/public/' . $clean),
                storage_path('app/private/livewire-tmp/' . $baseName),
                storage_path('app/livewire-tmp/' . $baseName),
                storage_path('app/public/livewire-tmp/' . $baseName),
                storage_path('framework/livewire-tmp/' . $baseName),
                sys_get_temp_dir() . DIRECTORY_SEPARATOR . $baseName,
                public_path('storage/' . $clean),
            ];

            foreach ($fileCandidates as $candidate) {
                if (file_exists($candidate) && is_file($candidate)) {
                    $filename = $generateFilename();
                    Storage::disk('public')->put('tickets/' . $filename, file_get_contents($candidate));
                    return 'tickets/' . $filename;
                }
            }

            if (Storage::disk('public')->exists($clean)) {
                return $clean;
            }

            return $clean;
        }

        return null;
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
        $isShortHaul    = $this->isShortHaul();

        $webAdminFeePerPax = $settings->getWebAdminFee($isShortHaul);
        $txFeePerPax       = $settings->getTransactionFee($isShortHaul);

        $webAdminFeeTotal    = $webAdminFeePerPax * $multiplier;
        $transactionFeeTotal = $txFeePerPax * $multiplier;
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

    public function getProcessedRefundBreakdown(): array
    {
        $allPassengers = $this->passengers->sortBy('item_number')->values();
        $refundedPax = $allPassengers->filter(fn ($p) => (float) $p->refund_amount > 0 || in_array($p->status, [Passenger::STATUS_REFUND_PENDING, Passenger::STATUS_REFUNDED, Passenger::STATUS_CANCELLED, Passenger::STATUS_OPERATOR_CANCELLED], true));

        $paxToUse = $refundedPax->isNotEmpty() ? $refundedPax : $allPassengers;
        $paxCount = max(1, $paxToUse->count());

        $originalAmount = $refundedPax->isNotEmpty() && $refundedPax->count() < $allPassengers->count()
            ? (float) $paxToUse->sum(fn ($p) => $p->getEffectiveItemTotal())
            : (float) $this->total_price;

        $refundAmount = (float) ($this->refund_amount > 0 ? $this->refund_amount : $paxToUse->sum('refund_amount'));
        $totalDeductions = max(0, round($originalAmount - $refundAmount, 2));

        $isFullRefund = ($totalDeductions <= 0) || filled($this->service_cancellation_id);

        if ($isFullRefund) {
            $webAdminFee = 0.0;
            $txFee = 0.0;
            $surcharge = 0.0;
            $totalDeductions = 0.0;
            $netRefundAmount = $originalAmount;
        } else {
            $webAdminFee = (float) $paxToUse->sum(fn ($p) => $p->getEffectiveWebAdminFee());
            if ($webAdminFee <= 0) {
                $settings = PaymentSetting::current();
                $webAdminFee = (float) $settings->getWebAdminFee($this->isShortHaul()) * $paxCount;
            }

            $txFee = (float) $paxToUse->sum(fn ($p) => $p->getEffectiveTransactionFee());
            if ($txFee <= 0) {
                $settings = PaymentSetting::current();
                $txFee = (float) $settings->getTransactionFee($this->isShortHaul()) * $paxCount;
            }

            // Surcharge and fees capped to total deductions
            $webAdminFee = min($webAdminFee, $totalDeductions);
            $txFee = min($txFee, max(0, round($totalDeductions - $webAdminFee, 2)));
            $surcharge = max(0, round($totalDeductions - $webAdminFee - $txFee, 2));
            $netRefundAmount = $refundAmount > 0 ? $refundAmount : max(0, round($originalAmount - $totalDeductions, 2));
        }

        $surchargePct = $this->getRefundSurchargePercentage();

        return [
            'original_amount'       => $originalAmount,
            'web_admin_fee'         => $webAdminFee,
            'transaction_fee'       => $txFee,
            'surcharge_amount'      => $surcharge,
            'surcharge_pct'         => $surchargePct,
            'total_deductions'      => $totalDeductions,
            'net_refund_amount'     => $netRefundAmount,
            'pax_count'             => $paxCount,
            'is_service_disruption' => filled($this->service_cancellation_id),
            'is_full_refund'        => $isFullRefund,
        ];
    }

    /**
     * Total amount deducted (surcharge + non-refundable fees).
     */
    public function getCancellationFeeAmount(bool $isWithinGracePeriod = false): float
    {
        return $this->getRefundBreakdown($isWithinGracePeriod)['deduction_amount'];
    }

    /**
     * Get a human-readable label for affected items e.g. "Item 1–5" or "Item 1 (Juan)"
     */
    public function getAffectedItemsLabel(?array $itemNumbers = null): string
    {
        $allPassengers = $this->passengers->sortBy('item_number')->values();
        $totalCount = $allPassengers->count();
        if ($totalCount === 0) {
            return '—';
        }

        if ($itemNumbers === null) {
            // Find active/affected passengers
            $affected = $allPassengers->filter(function ($p) {
                return in_array($p->status, [
                    Passenger::STATUS_REFUND_PENDING,
                    Passenger::STATUS_REFUNDED,
                    Passenger::STATUS_CANCELLED,
                    Passenger::STATUS_REBOOKING_PENDING,
                    Passenger::STATUS_REBOOKED,
                    Passenger::STATUS_OPERATOR_CANCELLED,
                    Passenger::STATUS_OPERATOR_REBOOKING,
                ], true) || (float) $p->refund_amount > 0 || ! empty($p->rebooking_status);
            })->values();

            if ($affected->isEmpty()) {
                // If the entire booking is cancelled / refund / rebooking, all are affected
                if (in_array($this->status, [
                    self::STATUS_CANCELLED,
                    self::STATUS_OPERATOR_CANCELLED,
                    self::STATUS_OPERATOR_REBOOKING,
                ], true) || (float) $this->refund_amount > 0 || ! empty($this->rebooking_status)) {
                    return $totalCount > 1 ? "Item 1–{$totalCount}" : 'Item 1';
                }
                return '—';
            }
            $targetPassengers = $affected;
        } else {
            $targetPassengers = $allPassengers->filter(fn ($p) => in_array((int) $p->item_number, array_map('intval', $itemNumbers), true))->values();
        }

        if ($targetPassengers->count() === $totalCount && $totalCount > 1) {
            return "Item 1–{$totalCount}";
        }

        return $targetPassengers->map(function ($p) {
            $name = $p->name ? " ({$p->name})" : '';
            return "Item {$p->item_number}{$name}";
        })->implode(', ');
    }

    /**
     * Get active travelling passengers on the current itinerary.
     */
    public function getActivePassengers(): \Illuminate\Support\Collection
    {
        return $this->passengers
            ->filter(fn ($p) => $p->isActiveBookingItem())
            ->values();
    }

    /**
     * Get cancelled or refunded passenger items.
     */
    public function getRefundedPassengers(): \Illuminate\Support\Collection
    {
        return $this->passengers
            ->filter(fn ($p) => $p->isRefundItem())
            ->values();
    }

    /**
     * Get historical rebooked items that were replaced by a new item.
     */
    public function getRebookedHistoryPassengers(): \Illuminate\Support\Collection
    {
        return $this->passengers
            ->filter(fn ($p) => $p->isRebookingHistoryItem())
            ->values();
    }

    /**
     * Get the next item number for this booking.
     */
    public function getNextItemNumber(): int
    {
        $max = (int) ($this->passengers()->max('item_number') ?? 0);
        return max(1, $max + 1);
    }

    /**
     * Calculate refund breakdown for a specific subset of passenger item numbers.
     */
    public function getPartialRefundBreakdown(array $itemNumbers, bool $isWithinGracePeriod = false): array
    {
        $allPassengers = $this->passengers->sortBy('item_number')->values();
        $selectedPassengers = $allPassengers->filter(fn ($p) => in_array((int) $p->item_number, array_map('intval', $itemNumbers), true));

        if ($selectedPassengers->isEmpty()) {
            return $this->getRefundBreakdown($isWithinGracePeriod);
        }

        $selectedCount = $selectedPassengers->count();
        $totalPaxCount = max(1, $allPassengers->count());
        $isAll = ($selectedCount === $totalPaxCount);

        if ($isAll) {
            return $this->getRefundBreakdown($isWithinGracePeriod);
        }

        $settings     = \App\Models\PaymentSetting::current();
        $isShortHaul  = $this->isShortHaul();
        $webFeePerPax = $settings->getWebAdminFee($isShortHaul);
        $txFeePerPax   = $settings->getTransactionFee($isShortHaul);

        $selectedItemTotal = $selectedPassengers->sum(fn ($p) => $p->getEffectiveItemTotal());
        $nonRefundableFees = ($webFeePerPax + $txFeePerPax) * $selectedCount;

        if ($isWithinGracePeriod) {
            return [
                'base_ticket'         => $selectedItemTotal,
                'surcharge_pct'       => 0,
                'surcharge_amount'    => 0,
                'non_refundable_fees' => 0,
                'web_admin_fee'       => 0,
                'transaction_fee'     => 0,
                'refundable_amount'   => $selectedItemTotal,
                'deduction_amount'    => 0,
                'selected_count'      => $selectedCount,
            ];
        }

        $surchargePct = $this->getRefundSurchargePercentage();
        $ticketBase   = max(0, $selectedItemTotal - $nonRefundableFees);
        $surcharge    = round($ticketBase * ($surchargePct / 100), 2);
        $refundable   = max(0, round($selectedItemTotal - $surcharge - $nonRefundableFees, 2));

        return [
            'base_ticket'         => $selectedItemTotal,
            'surcharge_pct'       => $surchargePct,
            'surcharge_amount'    => $surcharge,
            'non_refundable_fees' => $nonRefundableFees,
            'web_admin_fee'       => $webFeePerPax * $selectedCount,
            'transaction_fee'     => $txFeePerPax * $selectedCount,
            'refundable_amount'   => $refundable,
            'deduction_amount'    => $selectedItemTotal - $refundable,
            'selected_count'      => $selectedCount,
        ];
    }

    public function getPartialRebookingFeeAmount(array $itemNumbers): float
    {
        $allPassengers = $this->passengers->sortBy('item_number')->values();
        $selectedPassengers = $allPassengers->filter(fn ($p) => in_array((int) $p->item_number, array_map('intval', $itemNumbers), true));
        $selectedCount = max(1, $selectedPassengers->count());
        $totalCount    = max(1, $allPassengers->count());

        $fullFee = $this->getRebookingFeeAmount();
        if ($fullFee <= 0) {
            return 0.0;
        }

        return round(($fullFee / $totalCount) * $selectedCount, 2);
    }

    public function getPartialRebookingCalculation(?array $itemNumbers = null, ?int $depScheduleId = null, ?int $depAccommodationId = null, ?int $retScheduleId = null, ?int $retAccommodationId = null): array
    {
        $allPassengers = $this->passengers->sortBy('item_number')->values();
        if (!empty($itemNumbers)) {
            $selectedPassengers = $allPassengers->filter(fn ($p) => in_array((int) $p->item_number, array_map('intval', $itemNumbers), true))->values();
        } else {
            $selectedPassengers = $allPassengers;
        }

        if ($selectedPassengers->isEmpty()) {
            $selectedPassengers = $allPassengers;
        }

        $selectedCount = max(1, $selectedPassengers->count());
        $isFerry = $this->getMode() === 'ferry';
        $settings = \App\Models\PaymentSetting::current();

        $created_at = $this->created_at ? \Carbon\Carbon::parse($this->created_at) : now();
        $isWithin5Mins = $created_at->copy()->addMinutes(5)->isFuture();

        // 1. Original fare base for selected passengers
        $originalFareBase = (float) $selectedPassengers->sum(function ($p) {
            return (float) $p->getEffectiveFareAndClass();
        });

        // Fallback if passenger fields are 0
        if ($originalFareBase <= 0) {
            $totalBase = $this->getTicketBase();
            $originalFareBase = round(($totalBase / max(1, $allPassengers->count())) * $selectedCount, 2);
        }

        // 2. Rebooking Surcharge
        $surchargePct = 0;
        if (! $isWithin5Mins) {
            if ($this->getMode() === 'airline') {
                $surchargePct = (float) $settings->rebook_airline_before_departure_surcharge_pct;
            } else {
                if ($this->isAfterDeparture()) {
                    $surchargePct = (float) $settings->rebook_ferry_after_departure_surcharge_pct;
                } else {
                    $surchargePct = (float) $settings->rebook_ferry_before_departure_surcharge_pct;
                }
            }
        }
        $surcharge = round($originalFareBase * ($surchargePct / 100), 2);

        // 3. Revalidation fee (charged per passenger)
        $revalidationFee = $isWithin5Mins ? 0.0 : round(floatval($settings->revalidation_fee ?? 0) * $selectedCount, 2);

        // 4. Rate Difference calculation if replacement schedules provided
        $newFare = 0.0;
        if ($depScheduleId) {
            $depSched = \App\Models\Schedule::find($depScheduleId);
            $depSchedPrice = (float) ($depSched?->price ?? 0);
            $depAccPrice = 0.0;
            if ($depAccommodationId) {
                $acc = \App\Models\ScheduleAccommodation::find($depAccommodationId);
                $depAccPrice = (float) ($acc?->price ?? 0);
            }
            $newFare += ($depSchedPrice + $depAccPrice) * $selectedCount;
        }

        if ($retScheduleId) {
            $retSched = \App\Models\Schedule::find($retScheduleId);
            $retSchedPrice = (float) ($retSched?->price ?? 0);
            $retAccPrice = 0.0;
            if ($retAccommodationId) {
                $rAcc = \App\Models\ScheduleAccommodation::find($retAccommodationId);
                $retAccPrice = (float) ($rAcc?->price ?? 0);
            }
            $newFare += ($retSchedPrice + $retAccPrice) * $selectedCount;
        }

        $rateDiff = max(0.0, round($newFare - $originalFareBase, 2));
        $totalToPay = round($rateDiff + $surcharge + $revalidationFee, 2);

        return [
            'original_fare'         => $originalFareBase,
            'new_fare'              => $newFare,
            'rate_diff'             => $rateDiff,
            'surcharge_pct'         => $surchargePct,
            'surcharge'             => $surcharge,
            'revalidation_fee'      => $revalidationFee,
            'total_rebooking_fee'   => $totalToPay,
            'selected_count'        => $selectedCount,
            'affected_items'        => $this->getAffectedItemsLabel($selectedPassengers->pluck('item_number')->toArray()),
        ];
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
        $revalidationFee = floatval($settings->revalidation_fee ?? 0) * $passengerCount;
        
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
        $staffId = $this->verified_by_user_id ?? \Illuminate\Support\Facades\Auth::id();
        $now = $this->verified_at ?? now();

        $updateData = [
            'status'              => self::STATUS_CONFIRMED,
            'is_rebooked'         => true,
            'rebooking_status'    => 'verified',
            'verified_by_user_id' => $staffId,
            'verified_at'         => $now,
        ];

        if ($this->rebooking_departure_date) {
            $updateData['departure_date'] = $this->rebooking_departure_date;
        }
        if ($this->rebooking_return_date) {
            $updateData['return_date'] = $this->rebooking_return_date;
        }

        // Apply replacement departure/return schedule if present
        $depScheduleId = null;
        if (!empty($this->disruption_notes)) {
            $notes = is_array($this->disruption_notes) ? $this->disruption_notes : json_decode($this->disruption_notes, true);
            $depScheduleId = $notes['dep_schedule_id'] ?? null;
            $retScheduleId = $notes['ret_schedule_id'] ?? null;
            if ($retScheduleId) {
                $retSchedule = \App\Models\Schedule::find($retScheduleId);
                if ($retSchedule) {
                    $updateData['return_schedule_id'] = $retSchedule->id;
                    $updateData['return_schedule_service'] = $retSchedule->service_name;
                    $updateData['return_schedule_departure_time'] = $retSchedule->formatted_departure;
                    $updateData['return_schedule_arrival_time'] = $retSchedule->formatted_arrival;
                }
            }
        }
        $depScheduleId = $depScheduleId ?: $this->preferred_replacement_schedule_id;
        if ($depScheduleId) {
            $depSchedule = \App\Models\Schedule::find($depScheduleId);
            if ($depSchedule) {
                $updateData['schedule_id'] = $depSchedule->id;
                $updateData['schedule_service'] = $depSchedule->service_name;
                $updateData['schedule_departure_time'] = $depSchedule->formatted_departure;
                $updateData['schedule_arrival_time'] = $depSchedule->formatted_arrival;
            }
        }

        $this->update($updateData);

        foreach ($this->passengers as $p) {
            if ($p->status === Passenger::STATUS_REBOOKING_PENDING || $p->rebooking_status === 'pending' || $p->status === Passenger::STATUS_OPERATOR_REBOOKING) {
                $p->update([
                    'status'              => self::STATUS_CONFIRMED,
                    'is_rebooked'         => true,
                    'rebooking_status'    => 'verified',
                    'verified_by_user_id' => $staffId,
                    'verified_at'         => $now,
                ]);
            }
        }

        app(\App\Services\GraciaPointsService::class)->awardPointsForBooking($this, \App\Models\User::find($staffId));
        
        if ($this->transaction && $this->transaction->rebooking_fee > 0) {
            app(\App\Services\GraciaPointsService::class)->awardPointsForRebookingFee($this, $this->transaction->rebooking_fee, \App\Models\User::find($staffId));
        }

        if ($this->transaction) {
            $this->transaction->update([
                'payment_status'      => 'paid',
                'confirmation_url'    => $ticketUrl ?: $this->transaction->confirmation_url,
                'confirmation_pdf'    => $receiptPath ?: $this->transaction->confirmation_pdf,
                'verified_by_user_id' => $staffId,
                'verified_at'         => $now,
            ]);
        }

        try {
            Mail::to($this->client_email)->send(new RebookingVerification($this, $ticketUrl, $receiptPath, $receiptDisk));
        } catch (Throwable $e) {
            Log::error('Failed sending rebooking verification email', [
                'booking_id' => $this->id ?? null,
                'email'      => $this->client_email ?? null,
                'error'      => $e->getMessage(),
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

        $allTcs = $this->transportClasses;
        $depTcs = $allTcs->filter(fn ($tc) => ! (bool) $tc->pivot->is_return);
        $retTcs = $allTcs->filter(fn ($tc) => (bool) $tc->pivot->is_return);

        // Bidirectional fallback: if one bucket is empty AND we have exactly 2 TCs
        // total, split them by collection index order regardless of which bucket failed.
        // This handles:
        //   1. Old bookings with no is_return flag (both default to false → both in depTcs)
        //   2. Bugged bookings where both TCs got is_return=true (both in retTcs)
        if ($allTcs->count() === 2 && ($depTcs->isEmpty() || $retTcs->isEmpty())) {
            $tcArr = $allTcs->values();
            $depTcs = collect([$tcArr[0]]);
            $retTcs = collect([$tcArr[1]]);
        }

        $depTcPrice = $depTcs->sum(fn ($tc) => (float) $tc->pivot->price);
        $retTcPrice = $retTcs->sum(fn ($tc) => (float) $tc->pivot->price);

        $payingPassengers = $passengers->filter(function ($p) {
            return ! ($this->has_vehicle && $p->type === 'driver');
        });
        $payingCount = $payingPassengers->count();

        foreach ($passengers as $p) {
            if ($this->has_vehicle && $p->type === 'driver') {
                continue;
            }

            if ($p->is_promo) {
                $depTicketTotal += (float) $p->promo_price;
            } else {
                $pDepTicket = (float) ($this->schedule_price ?? 0);
                $pDepAcc    = (float) ($this->schedule_accommodation_price ?? 0);
                $pDepTc     = $depTcPrice;

                $pRetTicket = (float) ($this->return_schedule_price ?? 0);
                $pRetAcc    = (float) ($this->return_schedule_accommodation_price ?? 0);
                $pRetTc     = $retTcPrice;

                if ($p->discount) {
                    $multiplier = 1 - ((float) $p->discount->percentage / 100);
                    $pDepTicket *= $multiplier;
                    $pDepAcc    *= $multiplier;
                    $pDepTc     *= $multiplier;
                    $pRetTicket *= $multiplier;
                    $pRetAcc    *= $multiplier;
                    $pRetTc     *= $multiplier;
                }

                $depTicketTotal += $pDepTicket + $pDepTc;
                $depAccTotal    += $pDepAcc;
                $retTicketTotal += $pRetTicket + $pRetTc;
                $retAccTotal    += $pRetAcc;
            }
        }
        
        // Combine ticket + accommodation/transport class into one line
        if ($depTicketTotal + $depAccTotal > 0) {
            $breakdown[] = [
                'label' => 'Departure Ticket & Transport Class (' . $payingCount . 'x)',
                'amount' => $depTicketTotal + $depAccTotal,
                'class' => ''
            ];
        }
        
        if ($retTicketTotal + $retAccTotal > 0) {
            $breakdown[] = [
                'label' => 'Return Ticket & Transport Class (' . $payingCount . 'x)',
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
            $w = trim((string) $this->extra_baggage_weight);
            $label = 'Extra Baggage';
            if ($w !== '') {
                $label .= ' (' . (str_ends_with(strtolower($w), 'kg') ? $w : $w . ' kg') . ')';
            }
            $breakdown[] = [
                'label' => $label,
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
            $paxCount = max(1, $this->passengers->count());
            $isShortHaul = $this->isShortHaul();

            $expectedWebAdminFee = $paxCount * $settings->getWebAdminFee($isShortHaul);
            $expectedTransactionFee = $paxCount * $settings->getTransactionFee($isShortHaul);
            $expectedHotelFee = $this->accommodations->count() > 0 ? (float) $settings->fee_per_accommodation : 0;
            
            $hotelFee = 0;
            if ($expectedHotelFee > 0 && $fees >= $expectedHotelFee) {
                $hotelFee = $expectedHotelFee;
                $fees -= $expectedHotelFee;
            }

            $webAdminFee = 0;
            if ($expectedWebAdminFee > 0 && $fees >= $expectedWebAdminFee) {
                $webAdminFee = $expectedWebAdminFee;
                $fees -= $expectedWebAdminFee;
            }

            $transactionFee = 0;
            if ($expectedTransactionFee > 0 && $fees >= $expectedTransactionFee) {
                $transactionFee = $expectedTransactionFee;
                $fees -= $expectedTransactionFee;
            }

            // If there's remaining fees or exact match didn't trigger, distribute proportionally
            if ($fees > 0.01) {
                if ($webAdminFee === 0 && $transactionFee === 0) {
                    $defaultWeb = $settings->getWebAdminFee($isShortHaul);
                    $defaultTx  = $settings->getTransactionFee($isShortHaul);
                    $defaultSum = ($defaultWeb + $defaultTx) * $paxCount;
                    if ($defaultSum > 0) {
                        $webAdminFee = round($fees * (($defaultWeb * $paxCount) / $defaultSum), 2);
                        $transactionFee = round($fees - $webAdminFee, 2);
                    } else {
                        $webAdminFee = $fees;
                    }
                } else {
                    $webAdminFee += $fees;
                }
            }

            if ($hotelFee > 0) {
                $breakdown[] = ['label' => 'Hotel Service Fee', 'amount' => $hotelFee, 'class' => 'text-slate-500'];
            }
            if ($webAdminFee > 0) {
                $breakdown[] = ['label' => 'Web Admin Fee', 'amount' => $webAdminFee, 'class' => 'text-slate-500'];
            }
            if ($transactionFee > 0) {
                $breakdown[] = ['label' => 'Transaction Fee', 'amount' => $transactionFee, 'class' => 'text-slate-500'];
            }
        }
        
        if ($this->transaction && (float) $this->transaction->rebooking_fee > 0) {
            $notes = $this->disruption_notes ? json_decode($this->disruption_notes, true) : [];
            $surcharge = (float) ($notes['surcharge'] ?? 0);
            $reval = (float) ($notes['revalidation_fee'] ?? 0);
            $rateDiff = (float) ($notes['rate_diff'] ?? 0);
            $totalRebookFee = (float) $this->transaction->rebooking_fee;

            if ($rateDiff > 0) {
                $breakdown[] = [
                    'label' => 'Original Price',
                    'amount' => (float) $this->total_price,
                    'class' => 'text-slate-600',
                ];
                $breakdown[] = [
                    'label' => 'New Ticket Price',
                    'amount' => (float) $this->total_price + $rateDiff,
                    'class' => 'text-slate-900 font-semibold',
                ];
            }

            if ($reval > 0) {
                $breakdown[] = [
                    'label' => 'Revalidation Fee',
                    'amount' => $reval,
                    'class' => 'text-amber-700',
                ];
            }
            if ($surcharge > 0) {
                $breakdown[] = [
                    'label' => 'Revalidation Surcharge',
                    'amount' => $surcharge,
                    'class' => 'text-amber-700',
                ];
            }
            if ($rateDiff > 0) {
                $breakdown[] = [
                    'label' => 'Rate Diff (if applicable)',
                    'amount' => $rateDiff,
                    'class' => 'text-amber-700',
                ];
            }
            if ($totalRebookFee > 0) {
                $breakdown[] = [
                    'label' => 'Total Revalidation Fees',
                    'amount' => $totalRebookFee,
                    'class' => 'text-amber-800 font-bold',
                ];
            }
        }

        return $breakdown;
    }

    // ─── Item-Level Aggregate Helpers ─────────────────────────────────────────

    /**
     * Number of passengers whose item status is still active
     * (pending, confirmed, or operator_rebooking).
     */
    public function getActivePassengersCount(): int
    {
        return $this->passengers()
            ->whereIn('status', [
                Passenger::STATUS_PENDING,
                Passenger::STATUS_CONFIRMED,
                Passenger::STATUS_OPERATOR_REBOOKING,
            ])
            ->count();
    }

    /**
     * True when at least one passenger is cancelled/refunded while others are still active.
     */
    public function hasPartialCancellation(): bool
    {
        $cancelledCount = $this->passengers()
            ->whereIn('status', [
                Passenger::STATUS_CANCELLED,
                Passenger::STATUS_OPERATOR_CANCELLED,
                Passenger::STATUS_REFUND_PENDING,
                Passenger::STATUS_REFUNDED,
            ])
            ->count();

        $total = $this->passengers()->count();

        return $cancelledCount > 0 && $cancelledCount < $total;
    }

    /**
     * True when any passenger item has a pending refund.
     */
    public function hasPendingRefunds(): bool
    {
        return $this->passengers()
            ->where('refund_status', 'pending')
            ->where('refund_amount', '>', 0)
            ->exists();
    }

    /**
     * True when any passenger item has a pending rebooking.
     */
    public function hasPendingRebookings(): bool
    {
        return $this->passengers()
            ->whereIn('status', [
                Passenger::STATUS_REBOOKING_PENDING,
                Passenger::STATUS_OPERATOR_REBOOKING,
            ])
            ->exists();
    }
}

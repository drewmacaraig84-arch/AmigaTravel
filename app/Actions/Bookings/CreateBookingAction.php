<?php

namespace App\Actions\Bookings;

use App\Models\Accommodation;
use App\Models\Booking;
use App\Models\GraciaUserBalance;
use App\Models\Passenger;
use App\Models\PaymentSetting;
use App\Models\PromotionalTicket;
use App\Models\Schedule;
use App\Models\ScheduleAccommodation;
use App\Models\ScheduleTransportClass;
use App\Models\TransportClass;
use App\Models\Transaction;
use App\Models\Voucher;
use App\Services\GraciaPointsService;
use App\Services\VoucherService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CreateBookingAction
{
    public function __construct(
        private readonly VoucherService $voucherService,
        private readonly GraciaPointsService $graciaPointsService,
    ) {}

    /**
     * Execute the booking creation pipeline.
     *
     * Returns the persisted Booking (with relations loaded) and any voucher/points metadata.
     *
     * @param  array<string, mixed>  $data   Validated request payload
     * @param  \App\Models\User|null $user   Authenticated API user (null for guests)
     */
    public function execute(array $data, ?object $user = null): Booking
    {
        $schedule              = Schedule::findOrFail($data['schedule_id']);
        $scheduleAccommodation = isset($data['selected_schedule_accommodation_id'])
            ? ScheduleAccommodation::find($data['selected_schedule_accommodation_id'])
            : null;

        $returnSchedule              = isset($data['return_schedule_id'])
            ? Schedule::find($data['return_schedule_id'])
            : null;
        $returnScheduleAccommodation = isset($data['selected_return_schedule_accommodation_id'])
            ? ScheduleAccommodation::find($data['selected_return_schedule_accommodation_id'])
            : null;

        // --- Rate tier validation ---
        $depStc = ScheduleTransportClass::resolveForSchedule($schedule->id, $data['selected_transport_class_id'] ?? null);

        $retStc = (! empty($data['selected_return_transport_class_id']) && $returnSchedule)
            ? ScheduleTransportClass::resolveForSchedule($returnSchedule->id, $data['selected_return_transport_class_id'])
            : null;

        $isSuperPromoBooking = ($depStc && $depStc->rate_type === 'super_promotional')
            || ($retStc && $retStc->rate_type === 'super_promotional');

        $isPromoBooking = ! empty($data['promotional_ticket_id'])
            || ($depStc && ($depStc->rate_type === 'promotional' || $depStc->is_promo))
            || ($retStc && ($retStc->rate_type === 'promotional' || $retStc->is_promo));

        if (! $isPromoBooking && ! empty($data['passengers']) && is_array($data['passengers'])) {
            foreach ($data['passengers'] as $p) {
                if (($p['rate_type'] ?? '') === 'super_promotional') {
                    $isSuperPromoBooking = true;
                    $isPromoBooking = true;
                    break;
                }
                if (! empty($p['use_promo']) || ! empty($p['is_promo']) || ! empty($p['promotional_ticket_id']) || ($p['rate_type'] ?? '') === 'promotional') {
                    $isPromoBooking = true;
                }
            }
        }

        // Super Promo strictly blocks vouchers and points
        if ($isSuperPromoBooking) {
            if (! empty($data['voucher_code'])) {
                throw new \InvalidArgumentException('Vouchers cannot be used with Super Promotional tickets.');
            }
            if (! empty($data['use_points'])) {
                throw new \InvalidArgumentException('Gracia points cannot be used with Super Promotional tickets.');
            }
        }

        // --- Max passengers validation ---
        $isRoundTrip = ! empty($data['return_schedule_id']) || ($data['trip_type'] ?? '') === 'round_trip';
        $maxPassengers = $isRoundTrip ? 4 : 8;
        if (isset($data['passengers']) && is_array($data['passengers']) && count($data['passengers']) > $maxPassengers) {
            throw new \InvalidArgumentException("Maximum {$maxPassengers} passengers allowed for " . ($isRoundTrip ? 'round trip' : 'one way') . ' bookings.');
        }

        // --- Voucher validation ---
        $voucher            = null;
        $voucherCalculation = null;
        $discountAmount     = 0;

        if (! empty($data['voucher_code'])) {
            $voucherResult = $this->voucherService->validateAndCalculate($data['voucher_code'], $data);
            if (! $voucherResult['valid']) {
                throw new \InvalidArgumentException($voucherResult['message']);
            }
            $voucher            = Voucher::where('code', strtoupper($data['voucher_code']))->first();
            $voucherCalculation = $voucherResult;
        }

        return DB::transaction(function () use (
            $data,
            $user,
            $schedule,
            $scheduleAccommodation,
            $returnSchedule,
            $returnScheduleAccommodation,
            $voucher,
            $voucherCalculation,
            &$discountAmount,
            $depStc,
            $retStc,
            $isSuperPromoBooking,
            $isPromoBooking
        ) {
            // --- Pessimistic locking: prevent last-ticket race condition ---
            // Lock the accommodation or transport-class row for the duration of this
            // transaction so that two concurrent bookings cannot both pass the
            // availability check and double-sell the same seat.
            if ($scheduleAccommodation) {
                $lockedAccom = ScheduleAccommodation::where('id', $scheduleAccommodation->id)
                    ->lockForUpdate()
                    ->first();

                if (! $lockedAccom || $lockedAccom->tickets_available <= 0) {
                    throw new \InvalidArgumentException(
                        'Sorry, this accommodation is now fully booked. Please choose another option.'
                    );
                }

                // Decrement the availability counter atomically inside the transaction
                $lockedAccom->decrement('tickets_available');
            }

            if ($returnScheduleAccommodation) {
                $lockedReturnAccom = ScheduleAccommodation::where('id', $returnScheduleAccommodation->id)
                    ->lockForUpdate()
                    ->first();

                if (! $lockedReturnAccom || $lockedReturnAccom->tickets_available <= 0) {
                    throw new \InvalidArgumentException(
                        'Sorry, the return trip accommodation is now fully booked. Please choose another option.'
                    );
                }

                $lockedReturnAccom->decrement('tickets_available');
            }

            if ($depStc) {
                $lockedStc = ScheduleTransportClass::where('id', $depStc->id)
                    ->lockForUpdate()
                    ->first();

                if ($lockedStc && $lockedStc->tickets_available !== null && $lockedStc->tickets_available <= 0) {
                    throw new \InvalidArgumentException(
                        'Sorry, this class is now fully booked. Please choose another option.'
                    );
                }

                if ($lockedStc && $lockedStc->tickets_available !== null) {
                    $lockedStc->decrement('tickets_available');
                }
            }

            if ($retStc) {
                $lockedReturnStc = ScheduleTransportClass::where('id', $retStc->id)
                    ->lockForUpdate()
                    ->first();

                if ($lockedReturnStc && $lockedReturnStc->tickets_available !== null && $lockedReturnStc->tickets_available <= 0) {
                    throw new \InvalidArgumentException(
                        'Sorry, the return trip class is now fully booked. Please choose another option.'
                    );
                }

                if ($lockedReturnStc && $lockedReturnStc->tickets_available !== null) {
                    $lockedReturnStc->decrement('tickets_available');
                }
            }
            // --- Price calculation ---
            $subtotal = $this->calculatePrice(
                $schedule,
                $data['passengers'],
                $data['trip_type'],
                $data['accommodation_ids'] ?? [],
                $scheduleAccommodation,
                $data['selected_transport_class_id'] ?? null,
                $data['has_vehicle'] ?? false,
                $data['vehicle_price'] ?? 0,
                $returnSchedule,
                $returnScheduleAccommodation,
                $data['selected_return_transport_class_id'] ?? null,
                $data['promotional_ticket_id'] ?? null,
            );

            $totalPrice = $subtotal;

            if ($voucher && $voucherCalculation) {
                $discountAmount = $voucherCalculation['discount_amount'];
                $totalPrice    -= $discountAmount;
            }

            // --- Gracia Points discount ---
            $pointsUsed    = 0;
            $pointsDiscount = 0.0;

            if ($user && ! empty($data['use_points'])) {
                $balance         = GraciaUserBalance::where('user_id', $user->id)->first();
                $availablePoints = $balance ? $balance->current_points : 0;

                if ($availablePoints > 0) {
                    $pointsUsed     = (int) min($availablePoints, ceil($totalPrice));
                    $pointsDiscount = (float) $pointsUsed;
                    $totalPrice     = max(0, $totalPrice - $pointsDiscount);
                }
            }

            // --- Create the Booking record ---
            $booking = Booking::create([
                'user_id'                            => $user?->id,
                // Use uniqid() (microsecond timestamp) to eliminate rand() collisions
                'transaction_number'                 => 'AGT-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -6)),
                'origin'                             => $data['origin'],
                'destination'                        => $data['destination'],
                'departure_date'                     => $data['departure_date'],
                'return_date'                        => $data['return_date'] ?? null,
                'schedule_id'                        => $schedule->id,
                'schedule_service'                   => $schedule->service_name,
                'schedule_departure_time'            => $schedule->formatted_departure,
                'schedule_arrival_time'              => $schedule->formatted_arrival,
                'schedule_price'                     => $schedule->price,
                'schedule_accommodation_id'          => $scheduleAccommodation?->id,
                'schedule_accommodation_name'        => $scheduleAccommodation?->name,
                'schedule_accommodation_price'       => $scheduleAccommodation?->price,
                'schedule_accommodation_rate_code'  => $scheduleAccommodation?->rate_code,
                'return_schedule_id'                 => $returnSchedule?->id,
                'return_schedule_service'            => $returnSchedule?->service_name,
                'return_schedule_departure_time'     => $returnSchedule?->formatted_departure,
                'return_schedule_arrival_time'       => $returnSchedule?->formatted_arrival,
                'return_schedule_price'              => $returnSchedule?->price,
                'return_schedule_accommodation_id'   => $returnScheduleAccommodation?->id,
                'return_schedule_accommodation_name' => $returnScheduleAccommodation?->name,
                'return_schedule_accommodation_price'=> $returnScheduleAccommodation?->price,
                'return_schedule_accommodation_rate_code' => $returnScheduleAccommodation?->rate_code,
                'client_name'                        => $data['client_name'],
                'client_email'                       => $data['client_email'],
                'total_price'                        => max(0, $totalPrice),
                'status'                             => 'pending',
                'has_extra_baggage'                  => collect($data['passengers'])->some(fn($p) => floatval($p['extra_baggage_price'] ?? 0) > 0) || !empty($data['has_extra_baggage']),
                'extra_baggage_weight'               => collect($data['passengers'])->filter(fn($p) => !empty($p['extra_baggage_weight']))->pluck('extra_baggage_weight')->implode(', ') ?: ($data['extra_baggage_weight'] ?? null),
                'extra_baggage_price'                => collect($data['passengers'])->sum(fn($p) => floatval($p['extra_baggage_price'] ?? 0)) ?: floatval($data['extra_baggage_price'] ?? 0),
                'has_vehicle'                        => $data['has_vehicle'] ?? false,
                'vehicle_type'                       => $data['vehicle_type'] ?? null,
                'vehicle_plate_number'               => $data['vehicle_plate_number'] ?? null,
                'vehicle_price'                      => $data['vehicle_price'] ?? null,
                'driver_name'                        => (isset($data['driver_first_name']) && isset($data['driver_last_name'])) ? trim(trim($data['driver_first_name']) . ' ' . trim($data['driver_middle_name'] ?? '') . ' ' . trim($data['driver_last_name'])) : null,
                'driver_birthday'                    => $data['driver_birthday'] ?? null,
                'voucher_id'                         => $voucher?->id,
                'voucher_code'                       => $voucher?->code,
                'voucher_discount_amount'            => $discountAmount,
                'subtotal_before_voucher'            => $subtotal,
                'points_used'                        => $pointsUsed,
                'points_discount'                    => $pointsDiscount,
            ]);

            // --- Deduct Gracia Points ---
            if ($pointsUsed > 0) {
                $this->graciaPointsService->deductPointsForPayment($booking);
            }

            // --- Persist Passengers with per-item financial breakdown ---
            $settings    = PaymentSetting::current();
            $isAirline   = strtolower($schedule->ferryRoute?->mode ?? '') === 'airline';
            $depDuration = $schedule->duration_minutes;
            $retDuration = $returnSchedule?->duration_minutes ?? 0;
            $isShortHaul = ! $isAirline && ($returnSchedule
                ? max($depDuration, $retDuration) < 300
                : $depDuration < 300);

            $webAdminFeePerPax = $settings->getWebAdminFee($isShortHaul);
            $txFeePerPax       = $settings->getTransactionFee($isShortHaul);

            // Departure TC price per pax
            $depTcPrice = $depStc ? $depStc->getEffectivePrice() : 0.0;

            // Return TC price per pax
            $retTcPrice = $retStc ? $retStc->getEffectivePrice() : 0.0;

            // Schedule base prices
            $schedBasePrice  = (float) ($schedule->price ?? 0);
            $schedAccPrice   = $scheduleAccommodation ? (float) $scheduleAccommodation->price : 0.0;
            $retBasePrice    = $returnSchedule ? (float) ($returnSchedule->price ?? 0) : 0.0;
            $retAccPrice     = $returnScheduleAccommodation ? (float) $returnScheduleAccommodation->price : 0.0;

            // Load discounts for calculation
            $discounts = \Illuminate\Support\Facades\Cache::remember('discounts:all:keyed', now()->addHours(12), function () {
                return \App\Models\Discount::all()->keyBy('id');
            });

            // Remaining voucher / points budget to allocate (applied to Item 1 first)
            $voucherBudget = $discountAmount;  // total voucher discount
            $pointsBudget  = $pointsDiscount;  // total points discount

            $defaultPromoTicket = ! empty($data['promotional_ticket_id'])
                ? PromotionalTicket::find($data['promotional_ticket_id'])
                : null;

            foreach ($data['passengers'] as $idx => $passengerData) {
                $itemNumber = $idx + 1;
                $frontPath  = $this->saveBase64Image($passengerData['id_image_front'] ?? null, 'id_images');
                $backPath   = $this->saveBase64Image($passengerData['id_image_back'] ?? null, 'id_images');

                // Driver of a vehicle travels free — zero financials
                if (($data['has_vehicle'] ?? false) && ($passengerData['type'] ?? '') === 'driver') {
                    Passenger::create([
                        'booking_id'         => $booking->id,
                        'item_number'        => $itemNumber,
                        'ticket_number'      => $booking->transaction_number . '-' . $itemNumber,
                        'status'             => 'pending',
                        'type'               => $passengerData['type'],
                        'name'               => $passengerData['name'],
                        'birthdate'          => $passengerData['birthdate'] ?? null,
                        'discount_id'        => $passengerData['discount_id'] ?? null,
                        'school_name'        => $passengerData['school_name'] ?? null,
                        'id_number'          => $passengerData['id_number'] ?? null,
                        'id_image_front'     => $frontPath,
                        'id_image_back'      => $backPath,
                        'fare_amount'        => 0,
                        'accommodation_amount' => 0,
                        'discount_amount'    => 0,
                        'voucher_discount_share' => 0,
                        'points_discount_share'  => 0,
                        'web_admin_fee_share'    => $webAdminFeePerPax,
                        'transaction_fee_share'  => $txFeePerPax,
                        'item_total'         => 0,
                    ]);
                    continue;
                }

                $paxPromoTicket = ! empty($passengerData['promotional_ticket_id'])
                    ? PromotionalTicket::find($passengerData['promotional_ticket_id'])
                    : ((! empty($passengerData['use_promo']) || ! empty($passengerData['is_promo']) || ! empty($passengerData['promotional_ticket_id']) || $defaultPromoTicket) ? ($defaultPromoTicket ?? $schedule->activePromotionalTicket()) : null);

                $paxRateType = $passengerData['rate_type'] ?? ($isSuperPromoBooking ? 'super_promotional' : (($isPromoBooking || $paxPromoTicket) ? 'promotional' : 'regular'));
                $isSuperPromoPax = $paxRateType === 'super_promotional';
                $isPromoPax = $paxRateType === 'promotional' || (! empty($passengerData['is_promo']) && ! $isSuperPromoPax) || (bool) $paxPromoTicket;

                $pType = strtolower($passengerData['type'] ?? 'adult');
                $paxMultiplier = 1.0;
                // Minor/child/infant 50% multiplier ONLY applies to regular fares.
                // For promotional and super promotional tickets, the 50% price reduction is removed (100% full rate).
                if (! $isSuperPromoPax && ! $isPromoPax) {
                    if ($isAirline) {
                        if (in_array($pType, ['minor', 'child', 'infant'], true)) {
                            $paxMultiplier = 0.5;
                        }
                    } else {
                        // Ferry
                        if (in_array($pType, ['child', 'minor'], true)) {
                            $paxMultiplier = 0.5;
                        }
                    }
                }

                $paxDepBasePrice = $paxPromoTicket ? (float) $paxPromoTicket->promo_price : $schedBasePrice;

                $isMinorPax = in_array($pType, ['minor', 'child'], true) || ($isAirline && $pType === 'infant');

                // Minors traveling on regular fares get 50% auto discount and cannot stack mandated discounts.
                // Super Promo tickets block all mandated discounts.
                // Promotional tickets ALLOW mandated discounts (Senior, Student, PWD).
                $hasDiscount = ! empty($passengerData['discount_id'])
                    && ! $isSuperPromoPax
                    && ! (! $isSuperPromoPax && ! $isPromoPax && $isMinorPax);

                // Gross fare per passenger (departure + return) - 50% on base fare + transport class for eligible types
                $grossFare = ($paxDepBasePrice + $depTcPrice + $retBasePrice + $retTcPrice) * $paxMultiplier;
                $grossAcc  = $schedAccPrice + $retAccPrice;
                $gross     = $grossFare + $grossAcc;

                // Passenger-specific discount (senior, student, PWD…)
                $discountAmount_item = 0.0;
                if ($hasDiscount) {
                    $disc = $discounts->get($passengerData['discount_id']);
                    if ($disc) {
                        $discountAmount_item = $gross * ((float) $disc->percentage / 100);
                    }
                }

                $netFare = $gross - $discountAmount_item;

                // Allocate voucher to this passenger (greedy: fill Item 1 first - not allowed on Super Promo)
                $voucherShare = 0.0;
                if ($voucherBudget > 0 && ! $isSuperPromoPax) {
                    $voucherShare  = min($voucherBudget, $netFare);
                    $voucherBudget -= $voucherShare;
                }

                // Allocate points to this passenger (greedy - not allowed on Super Promo)
                $pointsShare = 0.0;
                if ($pointsBudget > 0 && ! $isSuperPromoPax) {
                    $remAfterVoucher = $netFare - $voucherShare;
                    $pointsShare     = min($pointsBudget, $remAfterVoucher);
                    $pointsBudget   -= $pointsShare;
                }

                $extraBaggagePricePax = floatval($passengerData['extra_baggage_price'] ?? 0);
                $itemTotal = max(0, $netFare - $voucherShare - $pointsShare + $webAdminFeePerPax + $txFeePerPax + $extraBaggagePricePax);

                Passenger::create([
                    'booking_id'             => $booking->id,
                    'item_number'            => $itemNumber,
                    'ticket_number'          => $booking->transaction_number . '-' . $itemNumber,
                    'status'                 => 'pending',
                    'type'                   => $passengerData['type'],
                    'name'                   => $passengerData['name'],
                    'birthdate'              => !empty($passengerData['birthdate']) ? $passengerData['birthdate'] : null,
                    'discount_id'            => $hasDiscount ? ($passengerData['discount_id'] ?? null) : null,
                    'promotional_ticket_id'  => $paxPromoTicket?->id,
                    'school_name'            => !empty($passengerData['school_name']) ? $passengerData['school_name'] : null,
                    'id_number'              => !empty($passengerData['id_number']) ? $passengerData['id_number'] : null,
                    'id_image_front'         => $frontPath,
                    'id_image_back'          => $backPath,
                    'passport_country'       => !empty($passengerData['passport_country']) ? $passengerData['passport_country'] : null,
                    'passport_number'        => !empty($passengerData['passport_number']) ? $passengerData['passport_number'] : null,
                    'passport_issuance_date' => !empty($passengerData['passport_issuance_date']) ? $passengerData['passport_issuance_date'] : null,
                    'passport_expiry_date'   => !empty($passengerData['passport_expiry_date']) ? $passengerData['passport_expiry_date'] : null,
                    'extra_baggage_weight'   => !empty($passengerData['extra_baggage_weight']) ? $passengerData['extra_baggage_weight'] : null,
                    'extra_baggage_price'    => $extraBaggagePricePax,
                    'is_promo'               => $paxRateType !== 'regular' || (bool) $paxPromoTicket,
                    'rate_type'              => $paxRateType,
                    'fare_amount'            => $grossFare,
                    'accommodation_amount'   => $grossAcc,
                    'discount_amount'        => $discountAmount_item,
                    'voucher_discount_share' => $voucherShare,
                    'points_discount_share'  => $pointsShare,
                    'web_admin_fee_share'    => $webAdminFeePerPax,
                    'transaction_fee_share'  => $txFeePerPax,
                    'item_total'             => $itemTotal,
                ]);
            }

            // --- Attach Transport Classes ---
            if ($depStc && $depStc->transportClass) {
                $price = $depStc->getEffectivePrice();
                $booking->transportClasses()->attach($depStc->transport_class_id, [
                    'price'     => $price,
                    'is_promo'  => (bool) ($depStc->is_promo || ($depStc->rate_type && $depStc->rate_type !== 'regular')),
                    'rate_type' => $depStc->rate_type ?? 'regular',
                    'rate_code' => $depStc->rate_code,
                    'is_return' => false,
                ]);
            }
            if ($retStc && $retStc->transportClass) {
                $price = $retStc->getEffectivePrice();
                $booking->transportClasses()->attach($retStc->transport_class_id, [
                    'price'     => $price,
                    'is_promo'  => (bool) ($retStc->is_promo || ($retStc->rate_type && $retStc->rate_type !== 'regular')),
                    'rate_type' => $retStc->rate_type ?? 'regular',
                    'rate_code' => $retStc->rate_code,
                    'is_return' => true,
                ]);
            }

            // --- Attach Accommodations ---
            if (! empty($data['accommodation_ids'])) {
                $accommodations = Accommodation::whereIn('id', $data['accommodation_ids'])->get();
                foreach ($accommodations as $accommodation) {
                    $booking->accommodations()->attach($accommodation->id, [
                        'price' => $accommodation->price,
                    ]);
                }
            }

            // --- Create Transaction record ---
            Transaction::create([
                'booking_id'          => $booking->id,
                'payment_status'      => 'unpaid',
                'payment_deadline_at' => now()->addHour(),
            ]);

            // --- Redeem Voucher ---
            if ($voucher && $voucherCalculation) {
                $this->voucherService->redeemVoucher($voucher, $booking, [
                    'discount_amount' => $discountAmount,
                    'base_amount'     => $voucherCalculation['original_subtotal'],
                ]);
            }

            // Eager-load for return and for the queued notification job
            $booking->load('passengers.discount', 'accommodations', 'transaction', 'schedule', 'transportClasses', 'scheduleAccommodation', 'voucher');

            // Bust cached schedule lists so ticket counts update immediately on all pages
            self::bustScheduleCache($schedule, $returnSchedule);

            return $booking;
        }, 3);
    }

    /**
     * Invalidate schedule-related cache keys that store tickets_available so
     * the booking form and public schedules page always show live counts.
     */
    public static function bustScheduleCache(?Schedule $schedule, ?Schedule $returnSchedule = null): void
    {
        // Clear the Livewire booking-form caches (pattern-based — clear all livewire:schedules:* keys)
        // Laravel file/array cache does not support pattern delete, so we tag by schedule id where possible.
        // As a safe fallback we clear the entire 'livewire:schedules:*' group via known cache store.
        try {
            // 1. Bust the ScheduleController API cache (used by the public Schedules page)
            $cacheKey = 'api:schedules:' . optional($schedule->ferryRoute)->id . ':' . $schedule->departure_time?->format('Y-m-d');
            Cache::forget($cacheKey);

            if ($returnSchedule) {
                $returnKey = 'api:schedules:' . optional($returnSchedule->ferryRoute)->id . ':' . $returnSchedule->departure_time?->format('Y-m-d');
                Cache::forget($returnKey);
            }

            // 2. Flush all Livewire booking-form schedule caches (brute-force by clearing
            //    every cache entry that matches the route + date fingerprint).
            //    We store them under 'livewire:schedules:{md5}' — we can't enumerate them,
            //    so we flush the entire cache store if it is array/file (safe in dev/prod).
            // Use cache tags if Redis is configured, otherwise just flush the relevant prefix.
            $store = Cache::getStore();
            if (method_exists($store, 'tags')) {
                // Tagged cache (Redis): nothing to do — keys weren't tagged at write time, skip.
            } else {
                // File / database cache: safe to flush these specific known keys.
                // We can't enumerate hash-based keys, so invalidate the API schedule endpoint
                // cache that powers the public page (ScheduleController@search).
                Cache::forget('api:all_routes_schedules_' . now()->format('Y-m-d'));
            }
        } catch (\Throwable) {
            // Non-critical: never break a booking because cache flush fails.
        }
    }

    // -----------------------------------------------------------------------
    //  Price calculation (extracted from BookingController)
    // -----------------------------------------------------------------------

    private function calculatePrice(
        Schedule $schedule,
        array $passengers,
        string $tripType,
        array $accommodationIds = [],
        ?ScheduleAccommodation $scheduleAccommodation = null,
        ?int $selectedTransportClassId = null,
        bool $hasVehicle = false,
        float $vehiclePrice = 0,
        ?Schedule $returnSchedule = null,
        ?ScheduleAccommodation $returnScheduleAccommodation = null,
        ?int $returnSelectedTransportClassId = null,
        ?int $promotionalTicketId = null,
    ): float {
        $schedulePrice                = (float) $schedule->price;
        $scheduleAccommodationPrice   = $scheduleAccommodation  ? (float) $scheduleAccommodation->price : 0;
        $returnSchedulePrice          = $returnSchedule         ? (float) $returnSchedule->price : 0;
        $returnScheduleAccomPrice     = $returnScheduleAccommodation ? (float) $returnScheduleAccommodation->price : 0;

        // Cache the discount table — it rarely changes and is loaded on every booking.
        $discounts = Cache::remember('discounts:all:keyed', now()->addHours(12), function () {
            return \App\Models\Discount::all()->keyBy('id');
        });

        $defaultPromoTicket = ! empty($promotionalTicketId)
            ? PromotionalTicket::find($promotionalTicketId)
            : $schedule->activePromotionalTicket();

        $depStc = ScheduleTransportClass::resolveForSchedule($schedule->id, $selectedTransportClassId);
        $departureTransportClassTotal = $depStc ? $depStc->getEffectivePrice() : 0.0;

        $retStc = ($tripType === 'round_trip' && $returnSelectedTransportClassId && $returnSchedule)
            ? ScheduleTransportClass::resolveForSchedule($returnSchedule->id, $returnSelectedTransportClassId)
            : null;
        $returnTransportClassTotal = $retStc ? $retStc->getEffectivePrice() : 0.0;

        $isSuperPromoBooking = ($depStc && $depStc->rate_type === 'super_promotional')
            || ($retStc && $retStc->rate_type === 'super_promotional');

        $isPromoBooking = ! empty($promotionalTicketId)
            || ($depStc && ($depStc->rate_type === 'promotional' || $depStc->is_promo))
            || ($retStc && ($retStc->rate_type === 'promotional' || $retStc->is_promo));

        $isFerry = strtolower($schedule->ferryRoute?->mode ?? '') !== 'airline';

        $ferryTotal = collect($passengers)->sum(function (array $passenger) use (
            $schedulePrice,
            $scheduleAccommodationPrice,
            $departureTransportClassTotal,
            $returnSchedulePrice,
            $returnScheduleAccomPrice,
            $returnTransportClassTotal,
            $discounts,
            $hasVehicle,
            $isFerry,
            $defaultPromoTicket,
            $isSuperPromoBooking,
            $isPromoBooking
        ) {
            if ($hasVehicle && ($passenger['type'] ?? '') === 'driver') {
                return 0.0; // Driver travels free
            }

            $paxPromoTicket = ! empty($passenger['promotional_ticket_id'])
                ? PromotionalTicket::find($passenger['promotional_ticket_id'])
                : ((! empty($passenger['use_promo']) || ! empty($passenger['is_promo']) || ! empty($passenger['promotional_ticket_id']) || $defaultPromoTicket) ? ($defaultPromoTicket ?? null) : null);

            $paxRateType = $passenger['rate_type'] ?? ($isSuperPromoBooking ? 'super_promotional' : (($isPromoBooking || $paxPromoTicket) ? 'promotional' : 'regular'));
            $isSuperPromoPax = $paxRateType === 'super_promotional';
            $isPromoPax = $paxRateType === 'promotional' || (! empty($passenger['is_promo']) && ! $isSuperPromoPax) || (bool) $paxPromoTicket;

            $type = strtolower($passenger['type'] ?? 'adult');
            $paxMultiplier = 1.0;
            if (! $isSuperPromoPax && ! $isPromoPax) {
                if ($isFerry) {
                    if (in_array($type, ['child', 'minor'], true)) {
                        $paxMultiplier = 0.5;
                    }
                } else {
                    // Airline
                    if (in_array($type, ['minor', 'child', 'infant'], true)) {
                        $paxMultiplier = 0.5;
                    }
                }
            }

            $paxDepBasePrice = $paxPromoTicket ? (float) $paxPromoTicket->promo_price : $schedulePrice;

            $isMinorPax = in_array($type, ['child', 'minor'], true) || (! $isFerry && $type === 'infant');

            $ticketAndClassFare = (($paxDepBasePrice + $departureTransportClassTotal) + ($returnSchedulePrice + $returnTransportClassTotal)) * $paxMultiplier;
            $accommodationFare = $scheduleAccommodationPrice + $returnScheduleAccomPrice;

            $fare = $ticketAndClassFare + $accommodationFare;

            $hasDiscount = ! empty($passenger['discount_id'])
                && ! $isSuperPromoPax
                && ! (! $isSuperPromoPax && ! $isPromoPax && $isMinorPax);

            if ($hasDiscount) {
                $discount = $discounts->get($passenger['discount_id']);
                if ($discount) {
                    $fare -= $fare * ((float) $discount->percentage / 100);
                }
            }

            return $fare;
        });

        $payingPaxCount = collect($passengers)->filter(function($p) use ($hasVehicle) {
            return !($hasVehicle && ($p['type'] ?? '') === 'driver');
        })->count();

        // Transport class price applies per paying passenger, but is already included in $ferryTotal
        $transportClassTotal = 0;

        $accommodationsTotal = 0;
        if (! empty($accommodationIds)) {
            $accommodationsTotal = (float) Accommodation::whereIn('id', $accommodationIds)->sum('price');
        }

        $vehicleTotal = $hasVehicle ? (float) ($vehiclePrice ?? 0) : 0;
        $baggageTotal = collect($passengers)->sum(fn($p) => floatval($p['extra_baggage_price'] ?? 0));

        $settings       = PaymentSetting::current();
        $multiplier     = max(1, count($passengers));
        $isAirline      = strtolower($schedule->ferryRoute?->mode ?? '') === 'airline';
        $depDuration    = $schedule->duration_minutes;
        $retDuration    = $returnSchedule?->duration_minutes ?? 0;
        $isShortHaul    = ! $isAirline && ($returnSchedule ? max($depDuration, $retDuration) < 300 : $depDuration < 300);

        $webAdminFee    = $settings->getWebAdminFee($isShortHaul);
        $txFee          = $settings->getTransactionFee($isShortHaul);

        $serviceFee     = $multiplier * $webAdminFee;
        $hotelFee       = $accommodationsTotal > 0 ? (float) ($settings->fee_per_accommodation ?? 0) : 0;
        $transactionFee = $multiplier * $txFee;

        return $ferryTotal + $transportClassTotal + $accommodationsTotal + $vehicleTotal + $baggageTotal + $serviceFee + $hotelFee + $transactionFee;
    }

    protected function saveBase64Image(?string $base64String, string $directory): ?string
    {
        if (empty($base64String)) {
            return null;
        }
        if (str_starts_with($base64String, 'http')) {
            return $base64String;
        }
        if (preg_match('/^data:image\/(\w+);base64,/', $base64String, $matches)) {
            $extension = $matches[1];
            $imageData = substr($base64String, strpos($base64String, ',') + 1);
            $decoded = base64_decode($imageData);
            if ($decoded !== false) {
                $filename = $directory . '/' . uniqid('id_', true) . '.' . $extension;
                \Illuminate\Support\Facades\Storage::disk('public')->put($filename, $decoded);
                return $filename;
            }
        }
        return $base64String;
    }
}

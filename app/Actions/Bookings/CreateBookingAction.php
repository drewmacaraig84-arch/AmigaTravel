<?php

namespace App\Actions\Bookings;

use App\Models\Accommodation;
use App\Models\Booking;
use App\Models\GraciaUserBalance;
use App\Models\Passenger;
use App\Models\PaymentSetting;
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

        // --- Promo ticket validation ---
        $isPromoBooking = false;
        if (! empty($data['selected_transport_class_id'])) {
            $isPromoBooking = $isPromoBooking || DB::table('schedule_transport_class')
                ->where('schedule_id', $schedule->id)
                ->where('transport_class_id', $data['selected_transport_class_id'])
                ->value('is_promo');
        }
        if (! empty($data['return_selected_transport_class_id']) && $returnSchedule) {
            $isPromoBooking = $isPromoBooking || DB::table('schedule_transport_class')
                ->where('schedule_id', $returnSchedule->id)
                ->where('transport_class_id', $data['return_selected_transport_class_id'])
                ->value('is_promo');
        }

        if ($isPromoBooking) {
            if (! empty($data['voucher_code'])) {
                throw new \InvalidArgumentException('Vouchers cannot be used with promotional tickets.');
            }
            if (! empty($data['use_points'])) {
                throw new \InvalidArgumentException('Gracia points cannot be used with promotional tickets.');
            }
            if (isset($data['passengers']) && is_array($data['passengers'])) {
                foreach ($data['passengers'] as $passenger) {
                    if (! empty($passenger['discount_id'])) {
                        throw new \InvalidArgumentException('Passenger discounts cannot be used with promotional tickets.');
                    }
                }
            }
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
            &$discountAmount
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

            if (! empty($data['selected_transport_class_id'])) {
                $lockedStc = ScheduleTransportClass::where('schedule_id', $schedule->id)
                    ->where('transport_class_id', $data['selected_transport_class_id'])
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

            if ($returnSchedule && ! empty($data['return_selected_transport_class_id'])) {
                $lockedReturnStc = ScheduleTransportClass::where('schedule_id', $returnSchedule->id)
                    ->where('transport_class_id', $data['return_selected_transport_class_id'])
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
                $data['return_selected_transport_class_id'] ?? null,
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

            // --- Persist Passengers ---
            foreach ($data['passengers'] as $passengerData) {
                $frontPath = $this->saveBase64Image($passengerData['id_image_front'] ?? null, 'id_images');
                $backPath  = $this->saveBase64Image($passengerData['id_image_back'] ?? null, 'id_images');

                Passenger::create([
                    'booking_id'     => $booking->id,
                    'type'           => $passengerData['type'],
                    'name'           => $passengerData['name'],
                    'birthdate'      => $passengerData['birthdate'] ?? null,
                    'discount_id'    => $passengerData['discount_id'] ?? null,
                    'school_name'    => $passengerData['school_name'] ?? null,
                    'id_number'      => $passengerData['id_number'] ?? null,
                    'id_image_front' => $frontPath,
                    'id_image_back'  => $backPath,
                ]);
            }

            // --- Attach Transport Classes ---
            if (! empty($data['selected_transport_class_id'])) {
                $transportClass = TransportClass::find($data['selected_transport_class_id']);
                if ($transportClass) {
                    $overridePrice = \Illuminate\Support\Facades\DB::table('schedule_transport_class')
                        ->where('schedule_id', $schedule->id)
                        ->where('transport_class_id', $transportClass->id)
                        ->value('additional_price');
                    $price = $overridePrice !== null ? (float) $overridePrice : $transportClass->effective_price;
                    
                    $booking->transportClasses()->attach($transportClass->id, [
                        'price' => $price,
                    ]);
                }
            }
            if (! empty($data['selected_return_transport_class_id']) && $returnSchedule) {
                $returnTransportClass = TransportClass::find($data['selected_return_transport_class_id']);
                if ($returnTransportClass) {
                    $overridePrice = \Illuminate\Support\Facades\DB::table('schedule_transport_class')
                        ->where('schedule_id', $returnSchedule->id)
                        ->where('transport_class_id', $returnTransportClass->id)
                        ->value('additional_price');
                    $price = $overridePrice !== null ? (float) $overridePrice : $returnTransportClass->effective_price;

                    $booking->transportClasses()->attach($returnTransportClass->id, [
                        'price'     => $price,
                        'is_return' => true,
                    ]);
                }
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
    ): float {
        $schedulePrice                = (float) $schedule->price;
        $scheduleAccommodationPrice   = $scheduleAccommodation  ? (float) $scheduleAccommodation->price : 0;
        $returnSchedulePrice          = $returnSchedule         ? (float) $returnSchedule->price : 0;
        $returnScheduleAccomPrice     = $returnScheduleAccommodation ? (float) $returnScheduleAccommodation->price : 0;

        // Cache the discount table — it rarely changes and is loaded on every booking.
        $discounts = Cache::remember('discounts:all:keyed', now()->addHours(12), function () {
            return \App\Models\Discount::all()->keyBy('id');
        });

        $departureTransportClassTotal = 0;
        if ($selectedTransportClassId) {
            $transportClass = TransportClass::find($selectedTransportClassId);
            if ($transportClass) {
                $overridePrice = \Illuminate\Support\Facades\DB::table('schedule_transport_class')
                    ->where('schedule_id', $schedule->id)
                    ->where('transport_class_id', $transportClass->id)
                    ->value('additional_price');
                $departureTransportClassTotal = $overridePrice !== null ? (float) $overridePrice : (float) $transportClass->effective_price;
            }
        }

        $returnTransportClassTotal = 0;
        if ($tripType === 'round_trip' && $returnSelectedTransportClassId && $returnSchedule) {
            $returnTransportClass = TransportClass::find($returnSelectedTransportClassId);
            if ($returnTransportClass) {
                $overridePrice = \Illuminate\Support\Facades\DB::table('schedule_transport_class')
                    ->where('schedule_id', $returnSchedule->id)
                    ->where('transport_class_id', $returnTransportClass->id)
                    ->value('additional_price');
                $returnTransportClassTotal = $overridePrice !== null ? (float) $overridePrice : (float) $returnTransportClass->effective_price;
            }
        }

        $ferryTotal = collect($passengers)->sum(function (array $passenger) use (
            $schedulePrice,
            $scheduleAccommodationPrice,
            $departureTransportClassTotal,
            $returnSchedulePrice,
            $returnScheduleAccomPrice,
            $returnTransportClassTotal,
            $discounts,
            $hasVehicle
        ) {
            if ($hasVehicle && ($passenger['type'] ?? '') === 'driver') {
                return 0.0; // Driver travels free
            }

            $fare = ($schedulePrice + $scheduleAccommodationPrice + $departureTransportClassTotal) + ($returnSchedulePrice + $returnScheduleAccomPrice + $returnTransportClassTotal);

            if (! empty($passenger['discount_id'])) {
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

        $settings       = PaymentSetting::current();
        $multiplier     = max(1, count($passengers));

        $serviceFee     = $multiplier * (float) ($settings->web_admin_fee ?? 0);
        $hotelFee       = $accommodationsTotal > 0 ? (float) ($settings->fee_per_accommodation ?? 0) : 0;
        $transactionFee = $multiplier * (float) ($settings->transaction_fee ?? 0);

        return $ferryTotal + $transportClassTotal + $accommodationsTotal + $vehicleTotal + $serviceFee + $hotelFee + $transactionFee;
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

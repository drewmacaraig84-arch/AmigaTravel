<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Transaction;
use App\Models\PaymentSetting;
use App\Models\VehicleRate;
use App\Services\VoucherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Kreait\Firebase\Contract\Messaging;

class BookingController extends Controller
{
    /**
     * Create a new booking.
     *
     * Validation lives here; all business logic is delegated to CreateBookingAction.
     * The email + PDF are dispatched to the queue so the response returns immediately.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'schedule_id'                               => 'required|integer|exists:schedules,id',
            'origin'                                    => 'required|string',
            'destination'                               => 'required|string',
            'departure_date'                            => 'required|date',
            'trip_type'                                 => 'required|string|in:one_way,round_trip',
            'return_date'                               => 'nullable|date',
            'client_name'                               => 'required|string|max:255',
            'client_email'                              => 'required|email',
            'selected_transport_class_id'               => 'nullable|integer|exists:transport_classes,id',
            'selected_schedule_accommodation_id'        => 'nullable|integer|exists:schedule_accommodations,id',
            'has_vehicle'                               => 'nullable|boolean',
            'vehicle_type'                              => 'required_if:has_vehicle,true|nullable|string|max:255',
            'vehicle_plate_number'                      => 'required_if:has_vehicle,true|nullable|string|max:255',
            'vehicle_price'                             => 'required_if:has_vehicle,true|nullable|numeric|min:0',
            'driver_first_name'                         => 'required_if:has_vehicle,true|nullable|string|max:255',
            'driver_middle_name'                        => 'nullable|string|max:255',
            'driver_last_name'                          => 'required_if:has_vehicle,true|nullable|string|max:255',
            'driver_birthday'                           => 'required_if:has_vehicle,true|nullable|date',
            'passengers'                                => 'required|array|min:1',
            'passengers.*.name'                         => 'required|string|max:255',
            'passengers.*.type'                         => 'required|string|in:adult,child,minor,infant,driver',
            'passengers.*.birthdate'                    => 'nullable|date',
            'passengers.*.discount_id'                  => 'nullable|integer|exists:discounts,id',
            'passengers.*.school_name'                  => 'nullable|string|max:255',
            'passengers.*.id_number'                    => 'nullable|string|max:255',
            'passengers.*.passport_country'             => 'nullable|string|max:100',
            'passengers.*.passport_number'              => 'nullable|string|max:50',
            'passengers.*.passport_issuance_date'       => 'nullable|date',
            'passengers.*.passport_expiry_date'         => 'nullable|date',
            'passengers.*.extra_baggage_weight'         => 'nullable|string|max:50',
            'passengers.*.extra_baggage_price'          => 'nullable|numeric|min:0',
            'accommodation_ids'                         => 'nullable|array',
            'accommodation_ids.*'                       => 'integer|exists:accommodations,id',
            'voucher_code'                              => 'nullable|string|max:50',
            'return_schedule_id'                        => 'nullable|integer|exists:schedules,id',
            'selected_return_schedule_accommodation_id' => 'nullable|integer|exists:schedule_accommodations,id',
            'selected_return_transport_class_id'        => 'nullable|integer|exists:transport_classes,id',
            'use_points'                                => 'nullable|boolean',
        ]);

        $maxPassengers = ($validated['trip_type'] ?? '') === 'round_trip' ? 4 : 8;
        if (count($validated['passengers'] ?? []) > $maxPassengers) {
            return response()->json([
                'status'  => 'error',
                'message' => "Maximum {$maxPassengers} passengers allowed for " . ($validated['trip_type'] === 'round_trip' ? 'round trip' : 'one way') . ' bookings.',
            ], 422);
        }

        try {
            /** @var \App\Models\Booking $booking */
            $booking = app(\App\Actions\Bookings\CreateBookingAction::class)
                ->execute($validated, auth()->guard('api')->user());

            // Dispatch the PDF generation + email to the queue (non-blocking)
            \App\Jobs\SendBookingConfirmationJob::dispatch($booking);

            return response()->json([
                'status'                  => 'success',
                'message'                 => 'Booking created successfully!',
                'booking_id'              => $booking->id,
                'transaction_number'      => $booking->transaction_number,
                'subtotal_before_voucher' => floatval($booking->subtotal_before_voucher),
                'voucher_code'            => $booking->voucher_code,
                'voucher_discount_amount' => floatval($booking->voucher_discount_amount),
                'points_used'             => $booking->points_used,
                'points_discount'         => floatval($booking->points_discount),
                'total_price'             => floatval($booking->total_price),
                'payment_deadline_at'     => $booking->transaction?->payment_deadline_at?->toIso8601String(),
            ]);

        } catch (\InvalidArgumentException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to create booking: ' . $e->getMessage()], 500);
        }
    }

    public function vehicleRates()
    {
        $rates = Cache::remember('api:vehicle_rates', now()->addHours(6), function () {
            return VehicleRate::query()->where('is_active', true)->orderBy('sort_order')->get();
        });

        return response()->json([
            'status'        => 'success',
            'vehicle_rates' => $rates,
        ]);
    }

    public function index(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'lookup_token' => 'nullable|string',
        ]);

        $isAuthenticated = false;
        if (auth('sanctum')->check() && auth('sanctum')->user()->email === $request->input('email')) {
            $isAuthenticated = true;
        } elseif (auth('api')->check() && auth('api')->user()->email === $request->input('email')) {
            $isAuthenticated = true;
        }

        if (!$isAuthenticated) {
            if (!$request->input('lookup_token')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Lookup token or authentication required.',
                ], 401);
            }

            $verifiedEmail = Cache::get('booking_lookup_token:' . hash('sha256', $request->input('lookup_token')));
            if (! $verifiedEmail || strtolower($verifiedEmail) !== strtolower($request->input('email'))) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Email verification is required before viewing bookings.',
                ], 401);
            }
        }

        $bookingsQuery = \App\Models\Booking::query();

        $bookingsQuery->where(function ($query) use ($request, $isAuthenticated) {
            $query->where('client_email', $request->input('email'));
            if ($isAuthenticated) {
                if (auth('sanctum')->check()) {
                    $query->orWhere('user_id', auth('sanctum')->id());
                } elseif (auth('api')->check()) {
                    $query->orWhere('user_id', auth('api')->id());
                }
            }
        });

        $bookings = $bookingsQuery->with(['passengers.discount', 'accommodations', 'transaction', 'transactions', 'schedule', 'returnSchedule', 'transportClasses', 'serviceCancellation'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        $bookings = $bookings->map(function (Booking $booking) {
            $data = $booking->toArray();
            $transaction = $booking->transactions->first(function ($t) {
                return !empty($t->confirmation_pdf) || !empty($t->confirmation_url);
            }) ?? $booking->transaction ?? $booking->transactions->last();
            $isConfirmed = in_array($booking->status, ['confirmed', Booking::STATUS_PENDING_REBOOKING]);
            if ($isConfirmed || $transaction?->confirmation_pdf || $transaction?->confirmation_url) {
                // Route through server-side route so the file is served directly
                // from the persistent volume, or redirected to the confirmation URL
                $data['confirmation_pdf_url'] = route('ticket.admin-pdf', ['transaction_number' => $booking->transaction_number]);
            }
            $data['confirmation_url'] = $transaction?->confirmation_url;
            $data['confirmation_pdf'] = $transaction?->confirmation_pdf;
            // Always allow payment acknowledgement download for confirmed/paid bookings
            $data['ticket_url'] = in_array($booking->status, ['confirmed', 'pending', Booking::STATUS_PENDING_REBOOKING])
                ? route('ticket.download', ['transaction_number' => $booking->transaction_number])
                : null;
            $data['mode'] = $booking->getMode();
            $data['operator_name'] = $booking->getOperatorName();
            $data['return_operator_name'] = $booking->getReturnOperatorName();
            $data['price_breakdown'] = $booking->getPriceBreakdown();
            $data['calculated_rebooking_fee'] = $booking->getRebookingFeeAmount();
            $data['can_cancel'] = $booking->canCancel();
            $data['can_rebook'] = $booking->canRebook();
            $data['sla_voucher_note'] = $booking->getSlaVoucherNote(null, true);
            $data['refund_status'] = $booking->refund_status ?? ($booking->isRefundPending() ? 'pending' : null);
            $data['refund_message'] = $booking->getRefundMessage();
            $data['refund_id_image_url'] = filled($booking->refund_id_image) ? storage_asset_path($booking->refund_id_image) : null;
            $data['refund_ticket_file_url'] = filled($booking->refund_ticket_file) ? storage_asset_path($booking->refund_ticket_file) : null;
            $data['refund_auth_letter_url'] = filled($booking->refund_auth_letter) ? storage_asset_path($booking->refund_auth_letter) : null;
            $data['refund_proof_url'] = filled($booking->refund_proof) ? storage_asset_path($booking->refund_proof) : null;
            $data['refund_acknowledgement_url'] = (in_array($booking->status, ['cancelled', 'operator_cancelled']) && (float) $booking->refund_amount > 0)
                ? route('ticket.refund-acknowledgement', ['transaction_number' => $booking->transaction_number])
                : null;
            $data['service_cancellation_id'] = $booking->service_cancellation_id;
            $data['service_cancellation'] = $booking->serviceCancellation ? [
                'id' => $booking->serviceCancellation->id,
                'cancellation_code' => $booking->serviceCancellation->cancellation_code,
                'carrier' => $booking->serviceCancellation->carrier,
                'reason_category' => $booking->serviceCancellation->reason_category,
                'customer_message' => $booking->serviceCancellation->customer_message,
                'resume_date' => $booking->serviceCancellation->resume_date ? $booking->serviceCancellation->resume_date->format('Y-m-d') : null,
                'status' => $booking->serviceCancellation->status,
                'resumed_at' => $booking->serviceCancellation->resumed_at?->toDateTimeString(),
            ] : null;

            // Add schedule times for full datetime display in the app
            if ($booking->schedule) {
                $depTime = \Carbon\Carbon::parse($booking->schedule->departure_time)->timezone('Asia/Manila');
                $data['departure_time'] = $depTime->format('h:i A');
                $arrTime = $booking->schedule->arrival_time
                    ? \Carbon\Carbon::parse($booking->schedule->arrival_time)->timezone('Asia/Manila')->format('h:i A')
                    : null;
                $data['schedule_arrival_formatted'] = $arrTime;
            }
            if ($booking->returnSchedule) {
                $retTime = \Carbon\Carbon::parse($booking->returnSchedule->departure_time)->timezone('Asia/Manila');
                $data['return_time'] = $retTime->format('h:i A');
                $retArrTime = $booking->returnSchedule->arrival_time
                    ? \Carbon\Carbon::parse($booking->returnSchedule->arrival_time)->timezone('Asia/Manila')->format('h:i A')
                    : null;
                $data['return_schedule_arrival_formatted'] = $retArrTime;
            }

            // Per-pax TC prices for Trip Details display in app
            // TC pivot price is already stored per-passenger (matching schedule_price semantics)
            $paxCount = max(1, $booking->passengers->count());
            $allTcs = $booking->transportClasses;
            $depTcPrice = $allTcs->filter(fn ($tc) => ! (bool) $tc->pivot->is_return)->sum(fn ($tc) => (float) $tc->pivot->price);
            $retTcPrice = $allTcs->filter(fn ($tc) => (bool) $tc->pivot->is_return)->sum(fn ($tc) => (float) $tc->pivot->price);
            // Bidirectional fallback: split by index if one bucket is empty AND we have exactly 2 TCs
            // Handles: (a) old bookings with no is_return flag (both false) and (b) bugged bookings (both true)
            if ($allTcs->count() === 2 && ($depTcPrice == 0 || $retTcPrice == 0)) {
                $tcArr = $allTcs->values();
                $depTcPrice = (float) $tcArr[0]->pivot->price;
                $retTcPrice = (float) $tcArr[1]->pivot->price;
            }
            $data['departure_tc_price_per_pax'] = round($depTcPrice, 2);
            $data['return_tc_price_per_pax']    = round($retTcPrice, 2);

            $data['passengers'] = $booking->passengers->sortBy('item_number')->values()->map(function ($p) {
                $pArr = $p->toArray();
                $pArr['fare_amount'] = $p->getEffectiveFareAmount();
                $pArr['accommodation_amount'] = $p->getEffectiveAccommodationAmount();
                $pArr['fare_and_class'] = $p->getEffectiveFareAndClass();
                $pArr['web_admin_fee_share'] = $p->getEffectiveWebAdminFee();
                $pArr['transaction_fee_share'] = $p->getEffectiveTransactionFee();
                $pArr['item_total'] = $p->getEffectiveItemTotal();
                $pArr['status_label'] = $p->getStatusLabel();
                $pArr['status_color'] = $p->getStatusColor();
                return $pArr;
            })->toArray();

            return $data;
        });

        return response()->json([
            'status' => 'success',
            'bookings' => array_values(is_array($bookings) ? $bookings : $bookings->values()->all()),
        ]);
    }

    public function show(Request $request, $transaction_number)
    {
        $isAuthenticated = auth('sanctum')->check() || auth('api')->check();
        if (auth('sanctum')->check()) {
            $userEmail = auth('sanctum')->user()->email;
            $userId = auth('sanctum')->id();
        } elseif (auth('api')->check()) {
            $userEmail = auth('api')->user()->email;
            $userId = auth('api')->id();
        } else {
            $userEmail = null;
            $userId = null;
        }
        $email = $isAuthenticated ? $userEmail : $request->input('email');

        if (!$email && $request->input('lookup_token')) {
            $email = Cache::get('booking_lookup_token:' . hash('sha256', $request->input('lookup_token')));
        }

        $booking = Booking::where('transaction_number', $transaction_number)
            ->with(['passengers.discount', 'schedule.route', 'returnSchedule', 'transaction', 'transactions', 'accommodations', 'transportClasses', 'accommodations.transportClass', 'serviceCancellation'])
            ->first();

        if (!$booking) {
            return response()->json(['status' => 'error', 'message' => 'Booking not found.'], 404);
        }

        if (!$isAuthenticated && empty($email)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Authentication, email, or a valid lookup token is required to view booking details.',
            ], 401);
        }

        // Verify booking ownership
        $matchesEmail = !empty($email) && strtolower($booking->client_email) === strtolower($email);
        $matchesUser = $userId && $booking->user_id === $userId;
        if (!$matchesEmail && !$matchesUser) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized to view this booking.'], 403);
        }

        // Apply same formatting as index
        $data = $booking->toArray();
        $transaction = $booking->transactions->first(function ($t) {
            return !empty($t->confirmation_pdf) || !empty($t->confirmation_url);
        }) ?? $booking->transaction ?? $booking->transactions->last();
        $isConfirmed = in_array($booking->status, ['confirmed', Booking::STATUS_PENDING_REBOOKING]);
        if ($isConfirmed || $transaction?->confirmation_pdf || $transaction?->confirmation_url) {
            // Route through server-side route so the file is served directly
            // from the persistent volume, or redirected to the confirmation URL
            $data['confirmation_pdf_url'] = route('ticket.admin-pdf', ['transaction_number' => $booking->transaction_number]);
        }
        $data['confirmation_url'] = $transaction?->confirmation_url;
        $data['confirmation_pdf'] = $transaction?->confirmation_pdf;
        // Always allow payment acknowledgement download for confirmed/paid bookings
        $data['ticket_url'] = in_array($booking->status, ['confirmed', 'pending', Booking::STATUS_PENDING_REBOOKING])
            ? route('ticket.download', ['transaction_number' => $booking->transaction_number])
            : null;
        $data['mode'] = $booking->getMode();
        $data['operator_name'] = $booking->getOperatorName();
        $data['return_operator_name'] = $booking->getReturnOperatorName();
        $data['price_breakdown'] = $booking->getPriceBreakdown();
        $data['calculated_rebooking_fee'] = $booking->getRebookingFeeAmount();
        $data['can_cancel'] = $booking->canCancel();
        $data['can_rebook'] = $booking->canRebook();
        $data['sla_voucher_note'] = $booking->getSlaVoucherNote(null, true);
        $data['refund_status'] = $booking->refund_status ?? ($booking->isRefundPending() ? 'pending' : null);
        $data['refund_message'] = $booking->getRefundMessage();
        $data['refund_reference'] = $booking->refund_reference;
        $data['refund_proof_url'] = filled($booking->refund_proof) ? storage_asset_path($booking->refund_proof) : null;
        $data['refund_acknowledgement_url'] = (in_array($booking->status, ['cancelled', 'operator_cancelled']) && (float) $booking->refund_amount > 0)
            ? route('ticket.refund-acknowledgement', ['transaction_number' => $booking->transaction_number])
            : null;
        $data['service_cancellation_id'] = $booking->service_cancellation_id;
        $data['service_cancellation'] = $booking->serviceCancellation ? [
            'id' => $booking->serviceCancellation->id,
            'cancellation_code' => $booking->serviceCancellation->cancellation_code,
            'carrier' => $booking->serviceCancellation->carrier,
            'reason_category' => $booking->serviceCancellation->reason_category,
            'customer_message' => $booking->serviceCancellation->customer_message,
            'resume_date' => $booking->serviceCancellation->resume_date ? $booking->serviceCancellation->resume_date->format('Y-m-d') : null,
            'status' => $booking->serviceCancellation->status,
            'resumed_at' => $booking->serviceCancellation->resumed_at?->toDateTimeString(),
        ] : null;

        if ($booking->schedule) {
            $depTime = \Carbon\Carbon::parse($booking->schedule->departure_time)->timezone('Asia/Manila');
            $data['departure_time'] = $depTime->format('h:i A');
            $data['schedule_arrival_formatted'] = $booking->schedule->arrival_time
                ? \Carbon\Carbon::parse($booking->schedule->arrival_time)->timezone('Asia/Manila')->format('h:i A')
                : null;
        }
        if ($booking->returnSchedule) {
            $retTime = \Carbon\Carbon::parse($booking->returnSchedule->departure_time)->timezone('Asia/Manila');
            $data['return_time'] = $retTime->format('h:i A');
            $data['return_schedule_arrival_formatted'] = $booking->returnSchedule->arrival_time
                ? \Carbon\Carbon::parse($booking->returnSchedule->arrival_time)->timezone('Asia/Manila')->format('h:i A')
                : null;
        }

        // Per-pax TC prices for Trip Details display in app
        // TC pivot price is already stored per-passenger (matching schedule_price semantics)
        $paxCount = max(1, $booking->passengers->count());
        $allTcs = $booking->transportClasses;
        $depTcPrice = $allTcs->filter(fn ($tc) => ! (bool) $tc->pivot->is_return)->sum(fn ($tc) => (float) $tc->pivot->price);
        $retTcPrice = $allTcs->filter(fn ($tc) => (bool) $tc->pivot->is_return)->sum(fn ($tc) => (float) $tc->pivot->price);
        // Bidirectional fallback: split by index if one bucket is empty AND we have exactly 2 TCs
        if ($allTcs->count() === 2 && ($depTcPrice == 0 || $retTcPrice == 0)) {
            $tcArr = $allTcs->values();
            $depTcPrice = (float) $tcArr[0]->pivot->price;
            $retTcPrice = (float) $tcArr[1]->pivot->price;
        }
        $data['departure_tc_price_per_pax'] = round($depTcPrice, 2);
        $data['return_tc_price_per_pax']    = round($retTcPrice, 2);

        $data['passengers'] = $booking->passengers->sortBy('item_number')->values()->map(function ($p) {
            $pArr = $p->toArray();
            $pArr['fare_amount'] = $p->getEffectiveFareAmount();
            $pArr['accommodation_amount'] = $p->getEffectiveAccommodationAmount();
            $pArr['fare_and_class'] = $p->getEffectiveFareAndClass();
            $pArr['web_admin_fee_share'] = $p->getEffectiveWebAdminFee();
            $pArr['transaction_fee_share'] = $p->getEffectiveTransactionFee();
            $pArr['item_total'] = $p->getEffectiveItemTotal();
            $pArr['status_label'] = $p->getStatusLabel();
            $pArr['status_color'] = $p->getStatusColor();
            $pArr['is_active_item'] = $p->isActiveBookingItem();
            $pArr['is_refund_item'] = $p->isRefundItem();
            $pArr['is_rebooked_history'] = $p->isRebookingHistoryItem();
            $pArr['refund_id_image_url'] = filled($p->refund_id_image) ? storage_asset_path($p->refund_id_image) : null;
            $pArr['refund_ticket_file_url'] = filled($p->refund_ticket_file) ? storage_asset_path($p->refund_ticket_file) : null;
            $pArr['refund_auth_letter_url'] = filled($p->refund_auth_letter) ? storage_asset_path($p->refund_auth_letter) : null;
            return $pArr;
        })->toArray();

        $data['active_passengers'] = $booking->getActivePassengers()->map(function ($p) {
            $pArr = $p->toArray();
            $pArr['status_label'] = $p->getStatusLabel();
            $pArr['status_color'] = $p->getStatusColor();
            return $pArr;
        })->values()->toArray();

        $data['refunded_passengers'] = $booking->getRefundedPassengers()->map(function ($p) {
            $pArr = $p->toArray();
            $pArr['status_label'] = $p->getStatusLabel();
            $pArr['status_color'] = $p->getStatusColor();
            return $pArr;
        })->values()->toArray();

        $data['rebooked_passengers'] = $booking->getRebookedHistoryPassengers()->map(function ($p) {
            $pArr = $p->toArray();
            $pArr['status_label'] = $p->getStatusLabel();
            $pArr['status_color'] = $p->getStatusColor();
            return $pArr;
        })->values()->toArray();

        return response()->json([
            'status' => 'success',
            'booking' => $data
        ]);
    }

    public function uploadProof(Request $request, $id)
    {
        $request->validate([
            'email' => 'required|email',
            'proof' => 'required|file|image|max:10240', // max 10MB file
            'reference_number' => 'required|string',
        ]);

        $booking = Booking::whereKey($id)
            ->where('client_email', $request->input('email'))
            ->firstOrFail();

        $path = $request->file('proof')->store('proofs', 'public');

        if ($booking->transaction) {
            $booking->transaction->update([
                'proof_of_payment' => $path,
                'payment_reference' => $request->input('reference_number'),
                'payment_status' => 'pending',
                'payment_deadline_at' => null, // Stop countdown timer
                'proof_submitted_at' => now(),
            ]);

            if ($booking->status === 'cancelled') {
                $booking->update(['status' => 'pending']);
            }

            try {
                \Illuminate\Support\Facades\Mail::to($booking->client_email)
                    ->queue(new \App\Mail\PaymentProofReceived($booking->transaction));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Failed queueing payment proof received email via API', [
                    'booking_id' => $booking->id,
                    'transaction_id' => $booking->transaction->id ?? null,
                    'email' => $booking->client_email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Proof of payment uploaded successfully.',
            'proof_url' => storage_asset_path($path),
        ]);
    }

    public function paymentSettings()
    {
        $data = Cache::remember('api:payment_settings', now()->addHours(6), function () {
            $settings = PaymentSetting::current();

            $qrCodeUrl = storage_asset_path($settings->qr_code_path);

            return [
                'qr_code_url'                => $qrCodeUrl,
                'web_admin_fee'              => floatval($settings->web_admin_fee),
                'short_haul_web_admin_fee'   => floatval($settings->short_haul_web_admin_fee ?? 30),
                'fee_per_accommodation'      => floatval($settings->fee_per_accommodation),
                'transaction_fee'            => floatval($settings->transaction_fee),
                'short_haul_transaction_fee' => floatval($settings->short_haul_transaction_fee ?? 70),
                'revalidation_fee'           => floatval($settings->revalidation_fee),
            ];
        });

        return response()->json(array_merge(['status' => 'success'], $data));
    }

    public function cancel(Request $request, $id)
    {
        $request->validate([
            'email' => 'required|email',
            'action' => 'nullable|string|in:start,confirm',
            'refund_destination' => 'nullable|string|max:255',
            'passenger_items' => 'nullable',
        ]);

        $booking = Booking::whereKey($id)
            ->where('client_email', $request->input('email'))
            ->with(['transaction', 'passengers.discount'])
            ->firstOrFail();

        if (! $booking->canCancel() || ! in_array($booking->status, ['pending', 'confirmed'], true)) {
            $message = 'This booking can no longer be cancelled.';
            if ($booking->hasPromoTicket() && !($booking->created_at && $booking->created_at->addMinutes(5)->isFuture())) {
                $message = 'Promotional tickets cannot be cancelled after the 5-minute grace period.';
            }

            return response()->json([
                'status' => 'error',
                'message' => $message
            ], 400);
        }

        $isWithinFiveMinutes = $booking->created_at->addMinutes(5)->isFuture();

        $eligiblePassengers = $booking->passengers->filter(function ($p) {
            return ! in_array($p->status, ['refund_pending', 'refunded', 'rebooking_pending', 'rebooked', 'cancelled', 'operator_cancelled'], true);
        });

        if ($eligiblePassengers->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No active passenger items are eligible for cancellation or refund on this booking.'
            ], 400);
        }

        $passengerItems = $request->input('passenger_items');
        if (is_string($passengerItems)) {
            $passengerItems = array_filter(array_map('intval', explode(',', $passengerItems)));
        }
        $rawSelected = (is_array($passengerItems) && !empty($passengerItems))
            ? $passengerItems
            : $eligiblePassengers->pluck('item_number')->toArray();

        $selectedItems = array_values(array_intersect(
            array_map('intval', $rawSelected),
            $eligiblePassengers->pluck('item_number')->map(fn ($n) => (int) $n)->toArray()
        ));

        if (empty($selectedItems)) {
            return response()->json([
                'status' => 'error',
                'message' => 'The selected passenger item(s) cannot be cancelled because their refund or rebooking is already pending or completed.'
            ], 400);
        }

        if ($request->input('action', 'confirm') === 'start') {
            if (! $isWithinFiveMinutes && ! $booking->isRefundEligible()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You cannot request a refund as it is less than 3 hours before the departure time.',
                ], 400);
            }

            $breakdown = $booking->getPartialRefundBreakdown($selectedItems, $isWithinFiveMinutes);

            return response()->json([
                'status'              => 'success',
                'message'             => 'Cancellation started.',
                'affected_items'      => $booking->getAffectedItemsLabel($selectedItems),
                'base_ticket'         => $breakdown['base_ticket'] ?? 0,
                'cancellation_fee'    => $breakdown['deduction_amount'],
                'refund_amount'       => $breakdown['refundable_amount'],
                'non_refundable_fees' => $breakdown['non_refundable_fees'] ?? 0,
                'transaction_fee'     => $breakdown['transaction_fee'],
                'web_admin_fee'       => $breakdown['web_admin_fee'],
                'surcharge_amount'    => $breakdown['surcharge_amount'],
                'surcharge_pct'       => $breakdown['surcharge_pct'],
                'rebooking_surcharge' => $breakdown['rebooking_surcharge'] ?? 0,
                'rebooking_revalidation_fee' => $breakdown['rebooking_revalidation_fee'] ?? 0,
                'rebooking_rate_diff' => $breakdown['rebooking_rate_diff'] ?? 0,
            ]);
        }

        if (! $isWithinFiveMinutes && ! $booking->isRefundEligible()) {
            return response()->json([
                'status' => 'error',
                'message' => 'You cannot request a refund as it is less than 3 hours before the departure time.',
            ], 400);
        }

        $request->validate([
            'refund_destination' => 'required|string|max:255',
            'id_image' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:10240',
            'refund_id_image' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:10240',
            'ticket_file' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:10240',
            'refund_ticket_file' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:10240',
            'auth_letter' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:10240',
            'refund_auth_letter' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:10240',
            'authorization_letter' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:10240',
        ]);

        $idImagePath = null;
        if ($request->hasFile('id_image')) {
            $idImagePath = $request->file('id_image')->store('refund_docs/ids', 'public');
        } elseif ($request->hasFile('refund_id_image')) {
            $idImagePath = $request->file('refund_id_image')->store('refund_docs/ids', 'public');
        }

        $ticketFilePath = null;
        if ($request->hasFile('ticket_file')) {
            $ticketFilePath = $request->file('ticket_file')->store('refund_docs/tickets', 'public');
        } elseif ($request->hasFile('refund_ticket_file')) {
            $ticketFilePath = $request->file('refund_ticket_file')->store('refund_docs/tickets', 'public');
        }

        $authLetterPath = null;
        if ($request->hasFile('auth_letter')) {
            $authLetterPath = $request->file('auth_letter')->store('refund_docs/auth_letters', 'public');
        } elseif ($request->hasFile('refund_auth_letter')) {
            $authLetterPath = $request->file('refund_auth_letter')->store('refund_docs/auth_letters', 'public');
        } elseif ($request->hasFile('authorization_letter')) {
            $authLetterPath = $request->file('authorization_letter')->store('refund_docs/auth_letters', 'public');
        }

        $partialBreakdown = $booking->getPartialRefundBreakdown($selectedItems, $isWithinFiveMinutes);
        $totalRefundAmount = $partialBreakdown['refundable_amount'];
        $totalCancellationFee = $partialBreakdown['deduction_amount'];

        $allPassengers = $booking->passengers->sortBy('item_number')->values();
        $selectedCount = count($selectedItems);
        $totalPaxCount = $allPassengers->count();
        $isFullCancellation = ($selectedCount >= $totalPaxCount);

        foreach ($allPassengers as $p) {
            if (in_array((int) $p->item_number, array_map('intval', $selectedItems), true)) {
                $pItemTotal = $p->getEffectiveItemTotal();
                $pRefund = $selectedCount > 0 ? round($totalRefundAmount / $selectedCount, 2) : $pItemTotal;
                $pFee    = $selectedCount > 0 ? round($totalCancellationFee / $selectedCount, 2) : 0;

                $p->update([
                    'status'             => \App\Models\Passenger::STATUS_REFUND_PENDING,
                    'refund_amount'      => $pRefund,
                    'cancellation_fee'   => $pFee,
                    'refund_destination' => $request->input('refund_destination'),
                    'refund_id_image'    => $idImagePath ?: $p->refund_id_image,
                    'refund_ticket_file' => $ticketFilePath ?: $p->refund_ticket_file,
                    'refund_auth_letter' => $authLetterPath ?: $p->refund_auth_letter,
                    'refund_status'      => 'pending',
                ]);
            }
        }

        $booking->load('passengers');
        $allCancelled = $booking->passengers->every(fn ($p) => in_array($p->status, ['cancelled', 'refund_pending', 'refunded', 'operator_cancelled'], true));

        if ($isFullCancellation || $allCancelled) {
            $booking->update([
                'status' => Booking::STATUS_CANCELLED,
                'cancellation_fee' => $totalCancellationFee,
                'refund_amount' => $booking->passengers->sum('refund_amount') ?: $totalRefundAmount,
                'refund_destination' => $request->input('refund_destination'),
                'refund_id_image' => $idImagePath ?: $booking->refund_id_image,
                'refund_ticket_file' => $ticketFilePath ?: $booking->refund_ticket_file,
                'refund_auth_letter' => $authLetterPath ?: $booking->refund_auth_letter,
                'refund_status' => 'pending',
                'cancellation_window_expires_at' => null,
            ]);
            if ($booking->transaction) {
                $booking->transaction->update(['payment_status' => 'cancelled']);
            }
        } else {
            $booking->update([
                'refund_amount'      => $booking->passengers->sum('refund_amount'),
                'refund_destination' => $request->input('refund_destination'),
                'refund_id_image'    => $idImagePath ?: $booking->refund_id_image,
                'refund_ticket_file' => $ticketFilePath ?: $booking->refund_ticket_file,
                'refund_auth_letter' => $authLetterPath ?: $booking->refund_auth_letter,
                'refund_status'      => 'pending',
            ]);
        }

        app(\App\Services\GraciaPointsService::class)->reversePointsForBooking($booking);
        app(\App\Services\GraciaPointsService::class)->refundRedeemedPoints($booking);

        $itemsLabel = $booking->getAffectedItemsLabel($selectedItems);

        // Send User Notification & FCM push notification to the cancelling user
        if ($booking->user_id) {
            \App\Models\UserNotification::notify(
                $booking->user_id,
                '💰 Refund Request Received',
                "Your refund request of ₱" . number_format((float) $totalRefundAmount, 2) . " for {$itemsLabel} (booking #{$booking->transaction_number}) is being processed. Please allow 24–48 hours for review and disbursement.",
                'booking',
                'money_off',
                ['transaction_number' => $booking->transaction_number, 'refund_status' => 'pending']
            );
        }

        dispatch(function () use ($booking, $totalRefundAmount, $itemsLabel) {
            try {
                $userTopic = 'user_' . md5(strtolower(trim($booking->client_email)));
                $messaging = app('firebase.messaging');
                $notification = \Kreait\Firebase\Messaging\Notification::create(
                    '💰 Refund Request Received',
                    "Your refund request of ₱" . number_format((float) $totalRefundAmount, 2) . " for {$itemsLabel} (booking #{$booking->transaction_number}) is being processed. Please allow 24–48 hours for disbursement."
                );
                $message = \Kreait\Firebase\Messaging\CloudMessage::new()
                    ->withTopic($userTopic)
                    ->withNotification($notification);
                $messaging->send($message);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('FCM cancellation push failed: ' . $e->getMessage());
            }
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Booking cancelled successfully.',
            'cancellation_fee' => (float) $booking->cancellation_fee,
            'refund_amount' => (float) $booking->refund_amount,
        ]);
    }

    public function rebook(Request $request, $id)
    {
        $request->validate([
            'email' => 'required|email',
            'departure_date' => 'required|date|after_or_equal:today',
            'return_date' => 'nullable|date|after_or_equal:departure_date',
            'proof' => 'required|file|image|max:10240',
            'dep_schedule_id' => 'required|exists:schedules,id',
            'dep_accommodation_id' => 'nullable|integer',
            'ret_schedule_id' => 'nullable|exists:schedules,id',
            'ret_accommodation_id' => 'nullable|integer',
            'rate_diff' => 'required|numeric',
            'surcharge' => 'required|numeric',
            'revalidation_fee' => 'required|numeric',
            'total_paid' => 'required|numeric',
        ]);

        $booking = Booking::whereKey($id)
            ->where('client_email', $request->input('email'))
            ->with('transaction')
            ->firstOrFail();

        if (! $booking->canRebook() || ! in_array($booking->status, ['pending', 'confirmed'], true)) {
            return response()->json([
                'status' => 'error',
                'message' => 'This booking can no longer be rebooked.',
            ], 400);
        }

        if ($booking->hasBeenRebooked()) {
            return response()->json([
                'status' => 'error',
                'message' => 'This booking has already been rebooked once.',
            ], 400);
        }

        if (!empty($booking->rebooking_status)) {
            return response()->json([
                'status' => 'error',
                'message' => 'A rebooking request is already in progress or completed.',
            ], 400);
        }

        $depSchedule = \App\Models\Schedule::with('ferryRoute.operatorRecord')->findOrFail($request->input('dep_schedule_id'));
        if (! $booking->matchesOperator($depSchedule, false)) {
            $expectedOp = $booking->getOperatorName() ?? 'original operator';
            return response()->json([
                'status' => 'error',
                'message' => "Rebooking is only permitted with the same operator ({$expectedOp}).",
            ], 422);
        }

        if ($request->input('ret_schedule_id')) {
            $retSchedule = \App\Models\Schedule::with('ferryRoute.operatorRecord')->findOrFail($request->input('ret_schedule_id'));
            if (! $booking->matchesOperator($retSchedule, true)) {
                $expectedOp = $booking->getReturnOperatorName() ?: $booking->getOperatorName();
                return response()->json([
                    'status' => 'error',
                    'message' => "Return rebooking is only permitted with the same operator ({$expectedOp}).",
                ], 422);
            }
        }

        $transaction = $booking->transaction ?: Transaction::create([
            'booking_id' => $booking->id,
            'payment_status' => 'unpaid',
        ]);
        $proofPath = null;
        if ($request->hasFile('proof')) {
            $extension = $request->file('proof')->extension();
            $safeReference = preg_replace('/[^A-Za-z0-9_-]/', '', $request->input('reference_number', uniqid()));
            $filename = 'rebook_proof_' . $booking->transaction_number . '_' . $safeReference . '.' . $extension;
            $proofPath = $request->file('proof')->storeAs('rebooking_proofs', $filename, 'public');
        }
        $passengerItems = $request->input('passenger_items');
        if (is_string($passengerItems)) {
            $passengerItems = array_filter(array_map('intval', explode(',', $passengerItems)));
        }
        $selectedItems = (is_array($passengerItems) && !empty($passengerItems))
            ? $passengerItems
            : $booking->passengers->pluck('item_number')->toArray();

        // Server-side compute rebooking fee to prevent fee manipulation
        $serverCalc = $booking->getPartialRebookingCalculation(
            $selectedItems,
            $request->input('dep_schedule_id'),
            $request->input('dep_accommodation_id'),
            $request->input('ret_schedule_id'),
            $request->input('ret_accommodation_id')
        );
        $rebookingFee = (float) ($serverCalc['total_rebooking_fee'] ?? $request->input('total_paid'));

        $transaction->update([
            'rebooking_fee' => $rebookingFee,
            'rebooking_proof_of_payment' => $proofPath,
            'payment_status' => 'pending',
            'proof_submitted_at' => now(),
        ]);

        $selectedCount = count($selectedItems);
        $totalPaxCount = $booking->passengers()->count();
        $isFullRebook = ($selectedCount >= $totalPaxCount);

        // Update selected passenger items
        foreach ($booking->passengers as $p) {
            if (in_array((int) $p->item_number, array_map('intval', $selectedItems), true)) {
                $p->update([
                    'status'                            => \App\Models\Passenger::STATUS_REBOOKING_PENDING,
                    'is_rebooked'                       => true,
                    'rebooking_status'                  => 'pending',
                    'rebooking_departure_date'          => $request->input('departure_date'),
                    'rebooking_return_date'             => $request->input('return_date'),
                    'preferred_replacement_schedule_id' => $request->input('dep_schedule_id'),
                    'disruption_notes'                  => json_encode([
                        'dep_schedule_id'      => $request->input('dep_schedule_id'),
                        'dep_accommodation_id' => $request->input('dep_accommodation_id'),
                        'ret_schedule_id'      => $request->input('ret_schedule_id'),
                        'ret_accommodation_id' => $request->input('ret_accommodation_id'),
                        'rate_diff'            => $request->input('rate_diff'),
                        'surcharge'            => $request->input('surcharge'),
                        'revalidation_fee'     => $request->input('revalidation_fee'),
                        'total_paid'           => $request->input('total_paid'),
                        'proof_path'           => $proofPath,
                    ]),
                ]);
            }
        }

        $booking->update([
            'status' => $isFullRebook ? Booking::STATUS_PENDING_REBOOKING : $booking->status,
            'is_rebooked' => true,
            'rebooking_status' => 'pending',
            'preferred_replacement_schedule_id' => $request->input('dep_schedule_id'),
            'preferred_replacement_date' => $request->input('departure_date'),
            'rebooking_departure_date' => $request->input('departure_date'),
            'rebooking_return_date' => $request->input('return_date'),
            'disruption_notes' => json_encode([
                'dep_schedule_id' => $request->input('dep_schedule_id'),
                'dep_accommodation_id' => $request->input('dep_accommodation_id'),
                'ret_schedule_id' => $request->input('ret_schedule_id'),
                'ret_accommodation_id' => $request->input('ret_accommodation_id'),
                'rate_diff' => $request->input('rate_diff'),
                'surcharge' => $request->input('surcharge'),
                'revalidation_fee' => $request->input('revalidation_fee'),
                'total_paid' => $request->input('total_paid'),
                'proof_path' => $proofPath,
                'affected_items' => $booking->getAffectedItemsLabel($selectedItems),
            ]),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Rebooking request submitted for verification.',
            'rebooking_fee' => (float) $rebookingFee,
            'rebooking_status' => 'pending',
            'affected_items' => $booking->getAffectedItemsLabel($selectedItems),
        ]);
    }

    public function rebookCalculation(Request $request, $id)
    {
        $request->validate([
            'email' => 'required|email',
            'dep_schedule_id' => 'required|exists:schedules,id',
            'dep_accommodation_id' => 'nullable|integer',
            'ret_schedule_id' => 'nullable|exists:schedules,id',
            'ret_accommodation_id' => 'nullable|integer',
            'is_round_trip' => 'required|boolean',
            'passenger_items' => 'nullable',
        ]);

        $booking = Booking::whereKey($id)
            ->where('client_email', $request->input('email'))
            ->firstOrFail();

        $depSchedule = \App\Models\Schedule::with('ferryRoute.operatorRecord')->findOrFail($request->input('dep_schedule_id'));
        if (! $booking->matchesOperator($depSchedule, false)) {
            $expectedOp = $booking->getOperatorName() ?? 'original operator';
            return response()->json([
                'status' => 'error',
                'message' => "Rebooking is only permitted with the same operator ({$expectedOp}).",
            ], 422);
        }

        $retSchedule = $request->input('ret_schedule_id') ? \App\Models\Schedule::with('ferryRoute.operatorRecord')->find($request->input('ret_schedule_id')) : null;
        if ($retSchedule && ! $booking->matchesOperator($retSchedule, true)) {
            $expectedOp = $booking->getReturnOperatorName() ?: $booking->getOperatorName();
            return response()->json([
                'status' => 'error',
                'message' => "Return rebooking is only permitted with the same operator ({$expectedOp}).",
            ], 422);
        }

        $passengerItems = $request->input('passenger_items');
        if (is_string($passengerItems)) {
            $passengerItems = array_filter(array_map('intval', explode(',', $passengerItems)));
        }
        $selectedItems = (is_array($passengerItems) && !empty($passengerItems))
            ? $passengerItems
            : $booking->passengers->pluck('item_number')->toArray();

        $calc = $booking->getPartialRebookingCalculation(
            $selectedItems,
            (int) $request->input('dep_schedule_id'),
            $request->input('dep_accommodation_id') ? (int) $request->input('dep_accommodation_id') : null,
            $request->input('ret_schedule_id') ? (int) $request->input('ret_schedule_id') : null,
            $request->input('ret_accommodation_id') ? (int) $request->input('ret_accommodation_id') : null
        );

        $settings = \App\Models\PaymentSetting::current();

        return response()->json([
            'status' => 'success',
            'breakdown' => [
                'original_ticket_price' => (float) $calc['original_fare'],
                'new_ticket_price'      => (float) $calc['new_fare'],
                'rate_diff'             => (float) $calc['rate_diff'],
                'surcharge'             => (float) $calc['surcharge'],
                'revalidation_fee'      => (float) $calc['revalidation_fee'],
                'total_to_pay'          => (float) $calc['total_rebooking_fee'],
                'selected_count'        => (int) $calc['selected_count'],
                'affected_items'        => $calc['affected_items'],
            ],
            'qr_code_url' => $settings->qr_code_path ? asset('storage/' . $settings->qr_code_path) : null,
        ]);
    }
    public function eligibleReplacements(Request $request, $id)
    {
        $booking = Booking::whereKey($id)
            ->where('client_email', $request->input('email'))
            ->with(['serviceCancellation', 'schedule.ferryRoute'])
            ->firstOrFail();

        $targetDate = $request->input('date');
        if (!$targetDate) {
            $resumeDate = $booking->serviceCancellation?->resume_date;
            $targetDate = $resumeDate ?: ($booking->departure_date ? $booking->departure_date->format('Y-m-d') : now()->format('Y-m-d'));
        }

        $origin = $booking->origin;
        $destination = $booking->destination;
        $mode = $booking->schedule?->ferryRoute?->mode ?? 'ferry';
        $operator = $booking->getOperatorName();

        $query = \App\Models\Schedule::with(['ferryRoute.operatorRecord', 'scheduleAccommodations.accommodation'])
            ->whereHas('ferryRoute', function ($q) use ($origin, $destination, $mode, $operator) {
                $q->where('origin', $origin)
                  ->where('destination', $destination);
                if ($mode) {
                    $q->where('mode', $mode);
                }
                if ($operator) {
                    $q->where(function($opQ) use ($operator) {
                        $opQ->where('operator', $operator)
                            ->orWhereHas('operatorRecord', function($sub) use ($operator) {
                                $sub->where('name', $operator);
                            });
                    });
                }
            })
            ->whereDate('departure_time', $targetDate)
            ->where('status', 'active');

        $schedules = $query->get();

        $results = [];
        foreach ($schedules as $schedule) {
            $accommodations = $schedule->scheduleAccommodations->map(function ($sa) {
                return [
                    'id' => $sa->id,
                    'name' => $sa->accommodation?->name ?? 'Standard',
                    'price' => (float)($sa->price ?? 0),
                    'available' => $sa->tickets_available > 0
                ];
            });

            $results[] = [
                'id' => $schedule->id,
                'vessel_name' => $schedule->ferryRoute?->operatorRecord?->name ?? $schedule->ferryRoute?->operator ?? 'Vessel',
                'departure_time' => $schedule->departure_time->format('H:i'),
                'arrival_time' => $schedule->arrival_time ? $schedule->arrival_time->format('H:i') : null,
                'formatted_departure' => $schedule->formatted_departure,
                'formatted_arrival' => $schedule->formatted_arrival,
                'price' => (float)$schedule->price,
                'accommodations' => $accommodations
            ];
        }
        
        return response()->json([
            'status' => 'success',
            'original_fare' => (float)$booking->getTicketBase(),
            'passengers_count' => max(1, $booking->passengers()->count()),
            'schedules' => $results
        ]);
    }

    public function submitReplacement(Request $request, $id)
    {
        $request->validate([
            'email' => 'required|email',
            'dep_date' => 'required|date',
            'dep_schedule_id' => 'required|integer',
            'dep_accommodation_id' => 'nullable|string',
            'ret_date' => 'nullable|date',
            'ret_schedule_id' => 'nullable|integer',
            'ret_accommodation_id' => 'nullable|string',
            'price_diff' => 'nullable|numeric',
            'passenger_items' => 'nullable',
        ]);

        $booking = Booking::whereKey($id)
            ->where('client_email', $request->input('email'))
            ->firstOrFail();

        // Check if there's actually a disruption
        if (!$booking->serviceCancellation) {
             return response()->json([
                 'status' => 'error',
                 'message' => 'This booking does not have an active disruption.'
             ], 400);
        }

        $resumeDate = $booking->serviceCancellation->resume_date;
        if (empty($resumeDate)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Service resume date is still to be announced. Please request a refund or wait until a resume date is published.',
            ], 400);
        }

        $requestedDepDate = \Carbon\Carbon::parse($request->input('dep_date'));
        if ($requestedDepDate->lt(\Carbon\Carbon::parse($resumeDate))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Departure date must be on or after the service resume date (' . \Carbon\Carbon::parse($resumeDate)->format('M d, Y') . ').',
            ], 400);
        }

        if ($request->filled('ret_date')) {
            $requestedRetDate = \Carbon\Carbon::parse($request->input('ret_date'));
            if ($requestedRetDate->lt(\Carbon\Carbon::parse($resumeDate))) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Return date must be on or after the service resume date (' . \Carbon\Carbon::parse($resumeDate)->format('M d, Y') . ').',
                ], 400);
            }
        }

        $proofPath = null;
        if ($request->input('price_diff', 0) > 0) {
            $request->validate(['proof' => 'required|image|max:10240']);
            if ($request->hasFile('proof')) {
                $proofPath = $request->file('proof')->store('proofs', 'public');
            }
        }

        $passengerItems = $request->input('passenger_items');
        if (is_string($passengerItems)) {
            $passengerItems = array_filter(array_map('intval', explode(',', $passengerItems)));
        }
        $selectedItems = (is_array($passengerItems) && !empty($passengerItems))
            ? $passengerItems
            : $booking->passengers->pluck('item_number')->toArray();

        foreach ($booking->passengers as $p) {
            if (in_array((int) $p->item_number, array_map('intval', $selectedItems), true)) {
                $p->update([
                    'status'                            => \App\Models\Passenger::STATUS_OPERATOR_REBOOKING,
                    'is_rebooked'                       => true,
                    'rebooking_status'                  => 'reschedule_requested',
                    'preferred_replacement_schedule_id' => $request->dep_schedule_id,
                    'rebooking_departure_date'          => $request->dep_date,
                    'rebooking_return_date'             => $request->input('ret_date'),
                    'disruption_notes'                  => json_encode([
                        'dep_schedule_id'      => $request->dep_schedule_id,
                        'dep_accommodation_id' => $request->dep_accommodation_id,
                        'ret_schedule_id'      => $request->input('ret_schedule_id'),
                        'ret_accommodation_id' => $request->input('ret_accommodation_id'),
                        'price_diff'           => $request->input('price_diff', 0),
                        'proof_path'           => $proofPath
                    ])
                ]);
            }
        }

        $booking->update([
            'status' => Booking::STATUS_OPERATOR_REBOOKING,
            'preferred_replacement_schedule_id' => $request->dep_schedule_id,
            'preferred_replacement_date' => $request->dep_date,
            'rebooking_departure_date' => $request->dep_date,
            'rebooking_return_date' => $request->input('ret_date'),
            'disruption_status' => 'reschedule_requested',
            'rebooking_status' => 'reschedule_requested',
            'disruption_notes' => json_encode([
                'dep_schedule_id' => $request->dep_schedule_id,
                'dep_accommodation_id' => $request->dep_accommodation_id,
                'ret_schedule_id' => $request->input('ret_schedule_id'),
                'ret_accommodation_id' => $request->input('ret_accommodation_id'),
                'price_diff' => $request->input('price_diff', 0),
                'proof_path' => $proofPath,
                'affected_items' => $booking->getAffectedItemsLabel($selectedItems),
            ])
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Your replacement travel selection has been submitted successfully and is awaiting staff approval.',
            'affected_items' => $booking->getAffectedItemsLabel($selectedItems),
        ]);
    }

    public function submitDisruptionRefund(Request $request, $id)
    {
        $request->validate([
            'email' => 'required|email',
            'refund_destination' => 'required|string|max:255',
            'passenger_items' => 'nullable',
        ]);

        $booking = Booking::whereKey($id)
            ->where('client_email', $request->input('email'))
            ->with(['transaction', 'passengers'])
            ->firstOrFail();

        if (!$booking->serviceCancellation) {
             return response()->json([
                 'status' => 'error',
                 'message' => 'This booking does not have an active disruption.'
             ], 400);
        }

        $passengerItems = $request->input('passenger_items');
        if (is_string($passengerItems)) {
            $passengerItems = array_filter(array_map('intval', explode(',', $passengerItems)));
        }
        $selectedItems = (is_array($passengerItems) && !empty($passengerItems))
            ? $passengerItems
            : $booking->passengers->pluck('item_number')->toArray();

        $selectedPassengers = $booking->passengers->filter(fn ($p) => in_array((int) $p->item_number, array_map('intval', $selectedItems), true));
        $netRefund = $selectedPassengers->sum(fn ($p) => $p->getRefundableBase()) ?: ($booking->getTicketBase() * (count($selectedItems) / max(1, $booking->passengers()->count())));
        $nonRefundable = $booking->getNonRefundableFees();

        foreach ($selectedPassengers as $p) {
            $pRefund = $p->getRefundableBase();
            $p->update([
                'status'             => \App\Models\Passenger::STATUS_OPERATOR_CANCELLED,
                'refund_status'      => 'pending',
                'refund_amount'      => $pRefund,
                'refund_destination' => $request->refund_destination,
            ]);
        }

        $allRefundedOrCancelled = $booking->passengers->every(fn ($p) => in_array($p->status, ['cancelled', 'operator_cancelled', 'refund_pending', 'refunded'], true));

        $booking->update([
            'status' => $allRefundedOrCancelled ? Booking::STATUS_OPERATOR_CANCELLED : $booking->status,
            'disruption_status' => 'refund_requested',
            'refund_destination' => $request->refund_destination,
            'refund_amount' => $booking->passengers->sum('refund_amount') ?: $netRefund,
        ]);

        if ($allRefundedOrCancelled && $booking->transaction) {
            $booking->transaction->update(['payment_status' => 'cancelled']);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Your cancellation has been recorded and a refund of ₱' . number_format($netRefund, 2) . ' has been requested. Our team will disburse it to your provided account within 24 to 48 hours.',
            'refund_amount' => $netRefund,
            'non_refundable_fees' => $nonRefundable,
            'affected_items' => $booking->getAffectedItemsLabel($selectedItems),
        ]);
    }
}

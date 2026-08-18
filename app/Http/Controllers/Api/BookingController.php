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
            'accommodation_ids'                         => 'nullable|array',
            'accommodation_ids.*'                       => 'integer|exists:accommodations,id',
            'voucher_code'                              => 'nullable|string|max:50',
            'return_schedule_id'                        => 'nullable|integer|exists:schedules,id',
            'selected_return_schedule_accommodation_id' => 'nullable|integer|exists:schedule_accommodations,id',
            'selected_return_transport_class_id'        => 'nullable|integer|exists:transport_classes,id',
            'use_points'                                => 'nullable|boolean',
        ]);

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

        $bookings = $bookingsQuery->with(['passengers.discount', 'accommodations', 'transaction', 'schedule', 'returnSchedule', 'transportClasses'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        $bookings = $bookings->map(function (Booking $booking) {
            $data = $booking->toArray();
            $isConfirmed = in_array($booking->status, ['confirmed', Booking::STATUS_PENDING_REBOOKING]);
            if ($isConfirmed || $transaction?->confirmation_pdf || $transaction?->confirmation_url) {
                // Route through server-side route so the file is served directly
                // from the persistent volume, or redirected to the confirmation URL
                $data['confirmation_pdf_url'] = route('ticket.admin-pdf', ['transaction_number' => $booking->transaction_number]);
            }
            $data['confirmation_url'] = $transaction?->confirmation_url;
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
            ->with(['passengers.discount', 'schedule.route', 'returnSchedule', 'transaction', 'accommodations', 'transportClasses', 'accommodations.transportClass'])
            ->first();

        if (!$booking) {
            return response()->json(['status' => 'error', 'message' => 'Booking not found.'], 404);
        }

        // If email was provided, verify ownership
        if (!empty($email)) {
            $matchesEmail = strtolower($booking->client_email) === strtolower($email);
            $matchesUser = $userId && $booking->user_id === $userId;
            if (!$matchesEmail && !$matchesUser) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized to view this booking.'], 403);
            }
        }

        // Apply same formatting as index
        $data = $booking->toArray();
        $isConfirmed = in_array($booking->status, ['confirmed', Booking::STATUS_PENDING_REBOOKING]);
        if ($isConfirmed || $transaction?->confirmation_pdf || $transaction?->confirmation_url) {
            // Route through server-side route so the file is served directly
            // from the persistent volume, or redirected to the confirmation URL
            $data['confirmation_pdf_url'] = route('ticket.admin-pdf', ['transaction_number' => $booking->transaction_number]);
        }
        $data['confirmation_url'] = $transaction?->confirmation_url;
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
            ->with('transaction')
            ->firstOrFail();
        $transaction = $booking->transaction;

        if (!$transaction) {
            $transaction = Transaction::create([
                'booking_id' => $booking->id,
                'payment_status' => 'unpaid',
            ]);
        }

        $extension = $request->file('proof')->extension();
        $safeReference = preg_replace('/[^A-Za-z0-9_-]/', '', $request->input('reference_number', uniqid()));
        $filename = $booking->transaction_number . '_' . $safeReference . '.' . $extension;
        $path = $request->file('proof')->storeAs('proofs', $filename, 'public');

        $transaction->update([
            'proof_of_payment' => $path,
            'payment_reference' => $request->input('reference_number'),
            'payment_status' => 'pending',
            'proof_submitted_at' => now(),
        ]);

        try {
            \Illuminate\Support\Facades\Mail::to($booking->client_email)->send(new \App\Mail\PaymentProofReceived($transaction));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed sending PaymentProofReceived email', [
                'booking_id' => $booking->id,
                'email' => $booking->client_email,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Proof of payment uploaded successfully!',
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
        ]);

        $booking = Booking::whereKey($id)
            ->where('client_email', $request->input('email'))
            ->with('transaction')
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

        if ($request->input('action', 'confirm') === 'start') {
            if (! $isWithinFiveMinutes && ! $booking->isRefundEligible()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You cannot request a refund as it is less than 3 hours before the departure time.',
                ], 400);
            }

            $breakdown = $booking->getRefundBreakdown($isWithinFiveMinutes);

            return response()->json([
                'status'           => 'success',
                'message'          => 'Cancellation started.',
                'base_ticket'      => $breakdown['base_ticket'] ?? 0,
                'cancellation_fee' => $breakdown['deduction_amount'],
                'refund_amount'    => $breakdown['refundable_amount'],
                'non_refundable_fees'=> $breakdown['non_refundable_fees'] ?? 0,
                'transaction_fee'  => $breakdown['transaction_fee'],
                'web_admin_fee'    => $breakdown['web_admin_fee'],
                'surcharge_amount' => $breakdown['surcharge_amount'],
                'surcharge_pct'    => $breakdown['surcharge_pct'],
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

        $request->validate(['refund_destination' => 'required|string|max:255']);

        $cancellationFee = $booking->getCancellationFeeAmount($isWithinFiveMinutes);
        $refundAmount    = $booking->getRefundAmount($isWithinFiveMinutes);

        $booking->update([
            'status' => Booking::STATUS_CANCELLED,
            'cancellation_fee' => $cancellationFee,
            'refund_amount' => $refundAmount,
            'refund_destination' => $request->input('refund_destination'),
            'cancellation_window_expires_at' => null,
        ]);

        app(\App\Services\GraciaPointsService::class)->reversePointsForBooking($booking);
        app(\App\Services\GraciaPointsService::class)->refundRedeemedPoints($booking);

        if ($booking->transaction) {
            $booking->transaction->update(['payment_status' => 'cancelled']);
        }

        // Send a user-specific FCM push notification to the cancelling user's phone asynchronously
        dispatch(function () use ($booking) {
            try {
                $userTopic = 'user_' . md5(strtolower(trim($booking->client_email)));
                $messaging = app('firebase.messaging');
                $notification = \Kreait\Firebase\Messaging\Notification::create(
                    '✈️ Booking Cancelled',
                    "Booking #{$booking->transaction_number} has been cancelled. Refund: ₱{$booking->refund_amount}. Please allow 3–5 business days for processing."
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
        $rebookingFee = $request->input('total_paid');

        $transaction->update([
            'rebooking_fee' => $rebookingFee,
            'rebooking_proof_of_payment' => $proofPath,
            'payment_status' => 'pending',
            'proof_submitted_at' => now(),
        ]);
        $booking->update([
            'status' => Booking::STATUS_PENDING_REBOOKING,
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
            ]),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Rebooking request submitted for verification.',
            'rebooking_fee' => (float) $rebookingFee,
            'rebooking_status' => 'pending',
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
            'is_round_trip' => 'required|boolean'
        ]);

        $booking = Booking::whereKey($id)
            ->where('client_email', $request->input('email'))
            ->firstOrFail();

        $passengerCount = $booking->passengers()->count() ?: 1;
        $mode = $booking->getMode();
        $isAirline = $mode === 'airline';

        $booking->loadMissing('transportClasses');
        $tcs = $booking->transportClasses->values();
        // Filter by is_return flag with bidirectional fallback: handle both
        // (a) old bookings where both TCs defaulted is_return=false, and
        // (b) bugged bookings where both TCs got is_return=true.
        $depTcs = $tcs->filter(fn ($tc) => ! (bool) $tc->pivot->is_return);
        $retTcs = $tcs->filter(fn ($tc) => (bool) $tc->pivot->is_return);
        if ($tcs->count() === 2 && ($depTcs->isEmpty() || $retTcs->isEmpty())) {
            $arr = $tcs->values();
            $depTcs = collect([$arr[0]]);
            $retTcs = collect([$arr[1]]);
        }
        $depTCPerPax = (float) $depTcs->sum(fn ($tc) => $tc->pivot->price);
        $retTCPerPax = (float) $retTcs->sum(fn ($tc) => $tc->pivot->price);

        $origDepPerPax = (float)($booking->schedule_price ?? 0)
                       + $depTCPerPax
                       + (float)($booking->schedule_accommodation_price ?? 0);
        $origRetPerPax = (float)($booking->return_schedule_price ?? 0)
                       + $retTCPerPax
                       + (float)($booking->return_schedule_accommodation_price ?? 0);

        $originalFare = ($origDepPerPax + $origRetPerPax) * $passengerCount;

        $newTotal = 0.0;
        
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
        
        $depAccPrice = 0;
        if ($request->input('dep_accommodation_id') && $depSchedule) {
            if ($isAirline) {
                $tc = $depSchedule->transportClasses()->where('transport_classes.id', $request->input('dep_accommodation_id'))->first();
                if ($tc) {
                    $pivotPrice = (float)($tc->pivot->additional_price ?? 0);
                    $depAccPrice = $pivotPrice > 0 ? $pivotPrice : ((float)($tc->is_on_sale && $tc->sale_price ? $tc->sale_price : $tc->price));
                } else {
                    $depAccPrice = 0;
                }
            } else {
                $acc = $depSchedule->scheduleAccommodations()->where('schedule_accommodations.id', $request->input('dep_accommodation_id'))->first();
                $depAccPrice = $acc ? $acc->price : 0;
            }
        }

        $retAccPrice = 0;
        if ($request->input('ret_accommodation_id') && $retSchedule) {
            if ($isAirline) {
                $tc = $retSchedule->transportClasses()->where('transport_classes.id', $request->input('ret_accommodation_id'))->first();
                if ($tc) {
                    $pivotPrice = (float)($tc->pivot->additional_price ?? 0);
                    $retAccPrice = $pivotPrice > 0 ? $pivotPrice : ((float)($tc->is_on_sale && $tc->sale_price ? $tc->sale_price : $tc->price));
                } else {
                    $retAccPrice = 0;
                }
            } else {
                $acc = $retSchedule->scheduleAccommodations()->where('schedule_accommodations.id', $request->input('ret_accommodation_id'))->first();
                $retAccPrice = $acc ? $acc->price : 0;
            }
        }

        if ($isAirline) {
            $depPerPax = $depAccPrice;
            $newTotal += $depPerPax * $passengerCount;
            if ($request->input('is_round_trip')) {
                $retPerPax = $retAccPrice;
                $newTotal += $retPerPax * $passengerCount;
            }
        } else {
            $depPerPax = ($depSchedule->price ?? 0) + $depAccPrice;
            $newTotal += $depPerPax * $passengerCount;
            if ($request->input('is_round_trip')) {
                $retPerPax = ($retSchedule->price ?? 0) + $retAccPrice;
                $newTotal += $retPerPax * $passengerCount;
            }
        }

        if ($booking->has_vehicle) {
            $newTotal += $booking->vehicle_price;
        }

        if ($newTotal < $originalFare) {
            return response()->json([
                'status' => 'error',
                'message' => 'Reminder: To proceed with rebooking, please select an accommodation or transport class that is equal to or higher than your original booking. Downgrades are not permitted.',
            ], 400);
        }

        $settings = \App\Models\PaymentSetting::current();
        $isAfterDeparture = $booking->isAfterDeparture();

        $revalidation_fee = floatval($settings->revalidation_fee) * $passengerCount;

        $surchargePct = 0;
        if ($isAirline) {
            $surchargePct = (float)$settings->rebook_airline_before_departure_surcharge_pct;
        } elseif ($isAfterDeparture) {
            $surchargePct = (float)$settings->rebook_ferry_after_departure_surcharge_pct;
        } else {
            $surchargePct = (float)$settings->rebook_ferry_before_departure_surcharge_pct;
        }
        
        $surcharge = $originalFare * ($surchargePct / 100);
        $rate_diff = max(0, $newTotal - $originalFare);
        $total_to_pay = $surcharge + $revalidation_fee + $rate_diff;

        return response()->json([
            'status' => 'success',
            'breakdown' => [
                'original_ticket_price' => (float) $originalFare,
                'new_ticket_price' => (float) $newTotal,
                'rate_diff' => (float) $rate_diff,
                'surcharge' => (float) $surcharge,
                'revalidation_fee' => (float) $revalidation_fee,
                'total_to_pay' => (float) $total_to_pay,
            ],
            'qr_code_url' => $settings->qr_code_path ? asset('storage/' . $settings->qr_code_path) : null,
        ]);
    }
    public function eligibleReplacements(Request $request, $id)
    {
        $booking = Booking::with('serviceCancellation')->findOrFail($id);
        
        // Ensure email matches to authorize
        if ($request->has('email') && $booking->client_email !== $request->input('email')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        if (!$booking->serviceCancellation) {
             return response()->json([
                 'status' => 'error',
                 'message' => 'This booking does not have an active disruption.'
             ], 400);
        }

        $cancellationId = $booking->serviceCancellation->id;
        
        $eligibleSchedules = \App\Models\Schedule::whereIn('id', function ($query) use ($cancellationId) {
            $query->select('schedule_id')
                  ->from('cancellation_replacements')
                  ->where('service_cancellation_id', $cancellationId);
        })->with(['ferryRoute', 'vehicle', 'scheduleAccommodations', 'transportClasses'])->get();

        $isAirline = $booking->getMode() === 'airline';
        
        $results = [];
        foreach ($eligibleSchedules as $schedule) {
            $accommodations = [];
            $schedulePrice = (float)($schedule->price ?? 0);
            
            $hasAccs = false;
            if (!$isAirline) {
                foreach ($schedule->scheduleAccommodations->where('is_active', true) as $acc) {
                    $hasAccs = true;
                    $price = $schedulePrice + (float)$acc->price;
                    $accommodations[] = [
                        'id' => 'acc_' . $acc->id,
                        'name' => $acc->name,
                        'price' => (float)$price
                    ];
                }
            }
            
            if ($isAirline || !$hasAccs) {
                foreach ($schedule->transportClasses->where('pivot.is_active', true) as $tc) {
                    $price = $schedulePrice + (float)$tc->pivot->additional_price;
                    $accommodations[] = [
                        'id' => 'tc_' . $tc->id,
                        'name' => $tc->name,
                        'price' => (float)$price
                    ];
                }
            }
            
            $results[] = [
                'id' => $schedule->id,
                'service_name' => $schedule->service_name,
                'departure_time' => $schedule->departure_time,
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
            'price_diff' => 'nullable|numeric'
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

        $booking->update([
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
                'proof_path' => $proofPath
            ])
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Your new travel dates and accommodations have been submitted successfully and are awaiting staff approval.'
        ]);
    }

    public function submitDisruptionRefund(Request $request, $id)
    {
        $request->validate([
            'email' => 'required|email',
            'refund_destination' => 'required|string|max:255',
        ]);

        $booking = Booking::whereKey($id)
            ->where('client_email', $request->input('email'))
            ->with('transaction')
            ->firstOrFail();

        if (!$booking->serviceCancellation) {
             return response()->json([
                 'status' => 'error',
                 'message' => 'This booking does not have an active disruption.'
             ], 400);
        }

        $booking->update([
            'status' => $booking->status === Booking::STATUS_OPERATOR_CANCELLED ? Booking::STATUS_OPERATOR_CANCELLED : Booking::STATUS_CANCELLED,
            'disruption_status' => 'refund_requested',
            'refund_destination' => $request->refund_destination,
            'refund_amount' => $booking->total_price, // 100% full refund
        ]);

        if ($booking->transaction) {
            $booking->transaction->update(['payment_status' => 'cancelled']);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Your booking has been cancelled and a full 100% refund has been requested. Our team will process it shortly to your provided account.',
            'refund_amount' => $booking->total_price,
        ]);
    }
}


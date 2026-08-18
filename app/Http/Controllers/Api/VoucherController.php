<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\VoucherService;

class VoucherController extends Controller
{
    public function index(Request $request)
    {
        $email = null;
        $userId = null;
        if (auth('api')->check()) {
            $userId = auth('api')->id();
            $email = auth('api')->user()->email;
        } elseif (auth('sanctum')->check()) {
            $userId = auth('sanctum')->id();
            $email = auth('sanctum')->user()->email;
        }
        if (!$email && $request->filled('email')) {
            $email = $request->input('email');
        }

        $vouchersQuery = \App\Models\Voucher::withCount('redemptions')
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_at')->orWhere('start_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_at')->orWhere('end_at', '>=', now());
            });

        // Vouchers should be either public (not hidden) OR claimed by the logged in user
        if ($userId) {
            $vouchersQuery->where(function($q) use ($userId) {
                $q->where('is_hidden', false)
                  ->orWhereHas('claimedByUsers', function($q2) use ($userId) {
                      $q2->where('user_id', $userId);
                  });
            });
        } else {
            $vouchersQuery->where('is_hidden', false);
        }

        $userRedeemedVoucherIds = [];
        if ($userId || $email) {
            $userRedeemedVoucherIds = \App\Models\VoucherRedemption::query()
                ->where(function ($q) use ($userId, $email) {
                    if ($userId) {
                        $q->where('user_id', $userId);
                    }
                    if ($email) {
                        $q->orWhere('normalized_email', strtolower(trim($email)));
                    }
                })
                ->pluck('voucher_id')
                ->toArray();
        }

        $vouchers = $vouchersQuery->orderBy('created_at', 'desc')->get();

        // Filter out vouchers that have reached their total_usage_limit OR already redeemed by user (even if cancelled)
        $vouchers = $vouchers->filter(function ($voucher) use ($userRedeemedVoucherIds) {
            if (in_array($voucher->id, $userRedeemedVoucherIds)) {
                return false;
            }

            if ($voucher->total_usage_limit !== null) {
                return $voucher->redemptions_count < $voucher->total_usage_limit;
            }
            return true;
        })->values();

        return response()->json([
            'status'   => 'success',
            'vouchers' => $vouchers,
        ]);
    }

    public function claim(Request $request)
    {
        $request->validate([
            'code' => 'required|string'
        ]);

        $user = auth('api')->user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
        }

        $voucher = \App\Models\Voucher::where('code', $request->code)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_at')->orWhere('start_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_at')->orWhere('end_at', '>=', now());
            })
            ->first();

        if (!$voucher) {
            return response()->json(['status' => 'error', 'message' => 'Invalid or expired voucher code.']);
        }

        // Must be a hidden voucher to be claimed like this? We can allow any voucher, but if it's already in their list, just say so.
        $alreadyClaimed = \Illuminate\Support\Facades\DB::table('user_hidden_vouchers')
            ->where('user_id', $user->id)
            ->where('voucher_id', $voucher->id)
            ->exists();

        if ($alreadyClaimed) {
            return response()->json(['status' => 'error', 'message' => 'You have already added this voucher.']);
        }

        if ($voucher->total_usage_limit !== null && $voucher->redemptions()->count() >= $voucher->total_usage_limit) {
            return response()->json(['status' => 'error', 'message' => 'This voucher has reached its usage limit.']);
        }

        \Illuminate\Support\Facades\DB::table('user_hidden_vouchers')->insert([
            'user_id' => $user->id,
            'voucher_id' => $voucher->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Voucher successfully added!',
        ]);
    }
    public function validateVoucher(Request $request, VoucherService $voucherService)
    {
        $request->validate([
            'voucher_code' => 'required|string|max:50',
            'schedule_id' => 'nullable|integer',
            'origin' => 'required|string',
            'destination' => 'required|string',
            'trip_type' => 'required|string|in:one_way,round_trip',
            'client_email' => 'required|email',
            'passengers' => 'required|array|min:1',
            'passengers.*.type' => 'required|string|in:adult,child,minor,infant,driver',
            'passengers.*.discount_id' => 'nullable|integer|exists:discounts,id',
            'selected_transport_class_id' => 'nullable|integer|exists:transport_classes,id',
            'selected_schedule_accommodation_id' => 'nullable|integer|exists:schedule_accommodations,id',
            'accommodation_ids' => 'nullable|array',
            'accommodation_ids.*' => 'integer|exists:accommodations,id',
            'has_vehicle' => 'nullable|boolean',
            'vehicle_price' => 'required_if:has_vehicle,true|nullable|numeric|min:0',
        ]);
        
        $result = $voucherService->validateAndCalculate($request->voucher_code, $request->all());
        
        if (!$result['valid']) {
            return response()->json([
                'status' => 'error',
                'message' => $result['message'],
            ], 422);
        }
        
        return response()->json([
            'status' => 'success',
            'data' => $result,
        ]);
    }
}

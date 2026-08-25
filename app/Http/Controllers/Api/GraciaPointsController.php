<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\GraciaPointsService;
use App\Models\GraciaUserBalance;

class GraciaPointsController extends Controller
{
    public function index(Request $request, GraciaPointsService $service)
    {
        $user = $request->user('sanctum') ?? $request->user('api') ?? $request->user();

        if (!$user) {
            $token = $request->bearerToken() ?: $request->input('api_token');
            if ($token) {
                $user = \App\Models\User::where('api_token', $token)->first();
            }
        }

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
        }

        $balance = GraciaUserBalance::firstOrCreate(
            ['user_id' => $user->id],
            ['current_points' => 0, 'unconverted_spend_centavos' => 0]
        );

        $activeRule = $service->getActiveRule();

        $ledger = $user->graciaPointLedgers()
            ->with('rule')
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        return response()->json([
            'status' => 'success',
            'current_points' => (int) $balance->current_points,
            'unconverted_spend_centavos' => (int) $balance->unconverted_spend_centavos,
            'active_rule' => $activeRule ? [
                'name' => $activeRule->name,
                'points_awarded' => (int) $activeRule->points_awarded,
                'spend_threshold_centavos' => (int) $activeRule->spend_threshold_centavos,
            ] : null,
            'history' => $ledger,
        ]);
    }
}

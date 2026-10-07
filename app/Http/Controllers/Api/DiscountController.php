<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Discount;

class DiscountController extends Controller
{
    public function index()
    {
        try {
            $discounts = \Illuminate\Support\Facades\Cache::remember('api:discounts', now()->addHours(6), function () {
                return Discount::orderBy('name')->get()->map(function ($d) {
                    return [
                        'id' => $d->id,
                        'name' => $d->name,
                        'percentage' => floatval($d->percentage),
                    ];
                });
            });
        } catch (\Throwable $e) {
            $discounts = Discount::orderBy('name')->get()->map(function ($d) {
                return [
                    'id' => $d->id,
                    'name' => $d->name,
                    'percentage' => floatval($d->percentage),
                ];
            });
        }

        return response()->json([
            'status' => 'success',
            'discounts' => array_values(is_array($discounts) ? $discounts : $discounts->values()->all()),
        ]);
    }
}

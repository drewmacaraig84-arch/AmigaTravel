<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TourController extends Controller
{
    public function index(Request $request)
    {
        $rows = Cache::remember('api:tours', now()->addHours(6), function () {
            $tours = Tour::with(['dates' => fn($q) => $q->where('is_active', true)])
                ->where('is_active', true)
                ->ordered()
                ->get();

            return $tours->map(function (Tour $tour) {
                $availableDates = $tour->activeDates->pluck('date')->map(fn($d) => $d->format('Y-m-d'))->toArray();

                return [
                    'id' => $tour->id,
                    'tour_name' => $tour->tour_name,
                    'promo' => $tour->promo,
                    'country' => $tour->country,
                    'destinations' => $tour->destinations,
                    'duration' => $tour->duration,
                    'duration_days' => $tour->duration_days,
                    'price_per_pax' => $tour->price_per_pax,
                    'airline' => $tour->airline,
                    'departure' => $tour->origin,
                    'origin' => $tour->origin,
                    'destination' => $tour->destination,
                    'mode' => $tour->mode,
                    'mode_of_transportation' => $tour->mode,
                    'hotel' => $tour->hotel,
                    'inclusions' => $tour->inclusions,
                    'exclusions' => $tour->exclusions,
                    'highlights' => $tour->highlights,
                    'day1' => $tour->day1,
                    'day2' => $tour->day2,
                    'day3' => $tour->day3,
                    'day4' => $tour->day4,
                    'day5' => $tour->day5,
                    'day6' => $tour->day6,
                    'meals' => $tour->meals,
                    'hand_carry' => $tour->hand_carry,
                    'check_in_baggage' => $tour->check_in_baggage,
                    'tour_guide' => $tour->tour_guide,
                    'travel_insurance' => $tour->travel_insurance,
                    'remarks' => $tour->remarks,
                    'image' => $tour->image,
                    'trip_type' => 'round_trip',
                    'available_dates_parsed' => $availableDates,
                    'available_dates' => implode(', ', $availableDates),
                    'departure_date' => $availableDates[0] ?? null,
                    'package_name' => $tour->tour_name,
                    'price' => $tour->price_per_pax,
                ];
            })->values()->all();
        });

        return response()->json($rows);
    }
}
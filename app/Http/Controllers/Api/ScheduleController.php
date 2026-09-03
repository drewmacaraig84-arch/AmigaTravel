<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FerryRoute;
use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /**
     * Ensure a collection or array is a cleanly re-indexed sequential PHP array
     * with integer keys so json_encode always outputs a JSON array [...]
     * rather than a JSON object {"0": ...} after cache unserialization.
     */
    private function ensureSequentialArray(mixed $data): array
    {
        if ($data instanceof \Illuminate\Support\Collection || $data instanceof \Illuminate\Database\Eloquent\Collection) {
            $data = $data->values()->all();
        } elseif (!is_array($data)) {
            return [];
        }

        return array_values($data);
    }

    public function origins(Request $request)
    {
        $mode = $request->input('mode', '');
        $operator = $request->input('operator', '');
        $cacheKey = "api:origins:{$mode}:{$operator}";

        $origins = \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addMinutes(10), function () use ($mode, $operator) {
            return FerryRoute::scheduleOrigins($mode ?: null, $operator ?: null);
        });

        return response()->json([
            'status' => 'success',
            'origins' => $this->ensureSequentialArray($origins)
        ]);
    }

    public function operators(Request $request)
    {
        $mode = $request->input('mode', '');
        $cacheKey = "api:operators:{$mode}";

        $operators = \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addMinutes(10), function () use ($mode) {
            return FerryRoute::scheduleOperatorsFor($mode ?: null);
        });

        return response()->json([
            'status' => 'success',
            'operators' => $this->ensureSequentialArray($operators)
        ]);
    }

    public function destinations(Request $request)
    {
        $request->validate([
            'origin' => 'required|string',
        ]);
        $origin = $request->input('origin');
        $mode = $request->input('mode', '');
        $operator = $request->input('operator', '');
        $tripType = $request->input('trip_type', 'one_way');
        $requireReturn = $tripType === 'round_trip' ? '1' : '0';
        $cacheKey = "api:destinations:{$origin}:{$mode}:{$operator}:{$requireReturn}";

        $destinations = \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addMinutes(10), function () use ($origin, $mode, $operator, $tripType) {
            return FerryRoute::scheduleDestinationsFor($origin, $mode ?: null, $operator ?: null, $tripType === 'round_trip');
        });

        return response()->json([
            'status' => 'success',
            'destinations' => $this->ensureSequentialArray($destinations)
        ]);
    }

    public function availableDates(Request $request)
    {
        $request->validate([
            'origin' => 'required|string',
            'destination' => 'required|string',
        ]);

        $origin = $request->input('origin');
        $destination = $request->input('destination');
        $mode = $request->input('mode', '');
        $operator = $request->input('operator', '');
        $cacheKey = "api:available_dates:{$origin}:{$destination}:{$mode}:{$operator}";

        $dates = \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addMinutes(5), function () use ($origin, $destination, $mode, $operator) {
            $query = FerryRoute::where('is_active', true)
                ->where('origin', $origin)
                ->where('destination', $destination);
                
            if ($mode) {
                $query->where('mode', $mode);
            }
            if ($operator) {
                $query->forOperator($operator);
            }

            $routes = $query->with(['schedules' => function($q) {
                $q->active()
                  ->where('departure_time', '>=', now()->startOfDay());
            }])->get();

            $datesList = [];
            foreach ($routes as $route) {
                foreach ($route->schedules as $schedule) {
                    $datesList[] = \Carbon\Carbon::parse($schedule->departure_time)->format('Y-m-d');
                }
            }

            $datesList = array_values(array_unique($datesList));
            sort($datesList);
            return $datesList;
        });

        return response()->json([
            'status' => 'success',
            'available_dates' => $this->ensureSequentialArray($dates)
        ]);
    }

    public function search(Request $request)
    {
        $request->validate([
            'origin' => 'required|string',
            'destination' => 'required|string',
            'date' => 'required|date_format:Y-m-d',
        ]);

        $origin      = $request->input('origin');
        $destination = $request->input('destination');
        $date        = $request->input('date');
        $mode        = $request->input('mode', null);
        $operator    = $request->input('operator', null);

        // Fetch the active earning rule (no need to cache a model instance to avoid unserialize errors)
        $activeRule = \Illuminate\Support\Facades\Cache::remember('gracia:active_rule', now()->addMinutes(15), function () {
            return \App\Models\GraciaEarningRule::where('is_active', true)
                ->where(function ($q) { $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()); })
                ->where(function ($q) { $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()); })
                ->latest('id')
                ->first();
        });

        // Cache schedule search results per route/date/mode/operator.
        $cacheKey = 'api:schedule:search:'
            . md5("{$origin}:{$destination}:{$date}:{$mode}:{$operator}");

        $schedules = \Illuminate\Support\Facades\Cache::remember(
            $cacheKey,
            now()->addMinutes(2),
            function () use ($origin, $destination, $date, $mode, $operator, $activeRule) {
                return Schedule::forRouteAndDate($origin, $destination, $date, $mode, $operator)
                    ->get()
                    ->map(function ($schedule) use ($date, $activeRule) {
                        $arr = $schedule->toBookingArray($date);
                        $pts = 0;
                        if ($activeRule && $activeRule->spend_threshold_centavos > 0) {
                            $pts = (int) floor(($arr['price'] * 100) / $activeRule->spend_threshold_centavos)
                                * $activeRule->points_awarded;
                        }
                        $arr['gracia_points'] = $pts;
                        return $arr;
                    })
                    ->values();
            }
        );

        if (\Carbon\Carbon::parse($date)->isToday()) {
            $now = now();
            $schedules = collect($schedules)->filter(function ($schedule) use ($now) {
                if (isset($schedule['departure_time_iso'])) {
                    return \Carbon\Carbon::parse($schedule['departure_time_iso'])->isAfter($now);
                }
                return true;
            })->values()->all();
        }

        return response()->json([
            'status'    => 'success',
            'schedules' => $this->ensureSequentialArray($schedules),
        ]);
    }
    public function allSchedules(Request $request)
    {
        $startDate = $request->query('start_date', \Carbon\Carbon::today()->format('Y-m-d'));
        $endDate   = $request->query('end_date',   \Carbon\Carbon::today()->addDays(6)->format('Y-m-d'));

        $cacheKey = 'api:all_schedules:' . $startDate . ':' . $endDate;

        $routes = \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addMinutes(10), function () use ($startDate, $endDate) {
            return FerryRoute::with([
                'schedules' => function ($query) use ($startDate, $endDate) {
                    $query->active()
                          ->whereBetween('departure_time', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                          ->orderBy('departure_time');
                },
                'schedules.scheduleAccommodations',
                'schedules.transportClasses',
            ])->where('is_active', true)->orderBy('origin')->orderBy('destination')->get()
              ->filter(fn ($route) => $route->schedules->isNotEmpty())
              ->values();
        });

        $routesArray = $this->ensureSequentialArray($routes);
        
        $now = now();
        $routesArray = array_map(function ($route) use ($now) {
            $arr = is_array($route) ? $route : $route->toArray();
            if (isset($arr['schedules'])) {
                $filtered = array_filter($arr['schedules'], function($schedule) use ($now) {
                    $dt = \Carbon\Carbon::parse(is_array($schedule) ? $schedule['departure_time'] : $schedule->departure_time);
                    return $dt->isAfter($now);
                });
                $arr['schedules'] = $this->ensureSequentialArray($filtered);
            }
            return $arr;
        }, $routesArray);

        // Remove routes that have no schedules left after filtering
        $routesArray = array_values(array_filter($routesArray, function($route) {
            return !empty($route['schedules']);
        }));

        return response()->json([
            'status' => 'success',
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'routes'     => $routesArray,
        ]);
    }

    public function baggageRules(Request $request)
    {
        $local = \App\Models\AirlineBaggageRule::getRatesForBooking('local');
        $international = \App\Models\AirlineBaggageRule::getRatesForBooking('international');

        return response()->json([
            'status' => 'success',
            'rules' => [
                'local' => $local,
                'international' => $international,
            ],
        ]);
    }
}

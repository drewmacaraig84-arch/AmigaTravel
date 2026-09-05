<?php

namespace App\Support;

use App\Models\Booking;
use App\Models\FerryRoute;
use App\Models\Inquiry;
use App\Models\Passenger;
use App\Models\Tour;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ReportingService
{
    private const CACHE_TTL = 120;

    // ─── Period Filter Helper ───────────────────────────────────

    private function applyPeriodFilter(
        Builder $query,
        ?string $period,
        ?string $startDate = null,
        ?string $endDate = null,
        string $dateColumn = 'created_at',
    ): Builder {
        if ($startDate && $endDate) {
            $hasStartTime = strlen($startDate) > 10;
            $hasEndTime = strlen($endDate) > 10;

            $start = $hasStartTime ? Carbon::parse($startDate) : Carbon::parse($startDate)->startOfDay();
            $end = $hasEndTime ? Carbon::parse($endDate) : Carbon::parse($endDate)->endOfDay();

            return $query->whereBetween($dateColumn, [
                $start,
                $end,
            ]);
        }

        return match ($period) {
            'today' => $query->whereDate($dateColumn, today()),
            'week' => $query->whereBetween($dateColumn, [now()->startOfWeek(), now()->endOfWeek()]),
            'month' => $query->whereMonth($dateColumn, now()->month)
                ->whereYear($dateColumn, now()->year),
            'year' => $query->whereYear($dateColumn, now()->year),
            default => $query,
        };
    }

    // ─── Dashboard KPIs ─────────────────────────────────────────

    public function getDashboardKpis(): array
    {
        return Cache::remember('dashboard_kpis', self::CACHE_TTL, function () {
            $today = today();
            $yesterday = today()->subDay();
            $thisMonthStart = now()->startOfMonth();
            $lastMonthStart = now()->subMonth()->startOfMonth();
            $lastMonthEnd = now()->subMonth()->endOfMonth();

            $todayBookings = Booking::whereDate('created_at', $today)->count();
            $yesterdayBookings = Booking::whereDate('created_at', $yesterday)->count();

            $monthRevenue = (float) Booking::where('created_at', '>=', $thisMonthStart)
                ->whereHas('transactions', fn (Builder $q) => $q->where('payment_status', 'paid'))
                ->sum('total_price');
            $lastMonthRevenue = (float) Booking::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
                ->whereHas('transactions', fn (Builder $q) => $q->where('payment_status', 'paid'))
                ->sum('total_price');

            $activeRoutes = FerryRoute::where('is_active', true)->count();
            $activeTours = Tour::where('is_active', true)->count();

            $pendingVerifications = Transaction::where('payment_status', 'pending')
                ->whereNotNull('proof_of_payment')
                ->count();

            $monthPassengers = Passenger::whereHas(
                'booking',
                fn (Builder $q) => $q->where('created_at', '>=', $thisMonthStart),
            )->count();

            $newInquiries = Inquiry::whereDate('created_at', $today)->count();

            return [
                'today_bookings' => $todayBookings,
                'yesterday_bookings' => $yesterdayBookings,
                'month_revenue' => $monthRevenue,
                'last_month_revenue' => $lastMonthRevenue,
                'active_routes' => $activeRoutes,
                'active_tours' => $activeTours,
                'pending_verifications' => $pendingVerifications,
                'month_passengers' => $monthPassengers,
                'new_inquiries' => $newInquiries,
            ];
        });
    }

    // ─── Sparkline data (last 7 days) ───────────────────────────

    public function getSparklineData(string $type): array
    {
        return Cache::remember("sparkline_{$type}", self::CACHE_TTL, function () use ($type) {
            $data = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                if ($type === 'bookings') {
                    $data[] = Booking::whereDate('created_at', $date)->count();
                } elseif ($type === 'revenue') {
                    $data[] = (float) Booking::whereDate('created_at', $date)
                        ->whereHas('transactions', fn (Builder $q) => $q->where('payment_status', 'paid'))
                        ->sum('total_price');
                }
            }

            return $data;
        });
    }

    // ─── Revenue Chart (daily for N days) ───────────────────────

    public function getRevenueChartData(int $days = 30): array
    {
        return Cache::remember("revenue_chart_{$days}", self::CACHE_TTL, function () use ($days) {
            $categories = [];
            $values = [];

            for ($i = $days - 1; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $categories[] = $date->format('M d');
                $values[] = (float) Booking::whereDate('created_at', $date->format('Y-m-d'))
                    ->whereHas('transactions', fn (Builder $q) => $q->where('payment_status', 'paid'))
                    ->sum('total_price');
            }

            return [
                'series' => [['name' => 'Revenue', 'data' => $values]],
                'categories' => $categories,
            ];
        });
    }

    // ─── Booking Status Distribution ────────────────────────────

    public function getBookingStatusDistribution(?string $period = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $key = "booking_status_{$period}_{$startDate}_{$endDate}";

        return Cache::remember($key, self::CACHE_TTL, function () use ($period, $startDate, $endDate) {
            $query = Booking::query();
            $this->applyPeriodFilter($query, $period, $startDate, $endDate);

            $counts = $query->select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            return [
                'series' => [
                    $counts['confirmed'] ?? 0,
                    $counts['pending'] ?? 0,
                    $counts['cancelled'] ?? 0,
                    $counts['rejected'] ?? 0,
                ],
                'labels' => ['Confirmed', 'Pending', 'Cancelled', 'Rejected'],
            ];
        });
    }

    // ─── Recent Activity ────────────────────────────────────────

    public function getRecentActivity(int $limit = 10): array
    {
        return Cache::remember("recent_activity_{$limit}", self::CACHE_TTL, function () use ($limit) {
            $bookings = Booking::query()
                ->orderByDesc(DB::raw('COALESCE(verified_at, rejected_at, rebooking_rejected_at, updated_at, created_at)'))
                ->take($limit)
                ->get(['id', 'transaction_number', 'client_name', 'origin', 'destination', 'status', 'total_price', 'created_at', 'updated_at', 'verified_at', 'rejected_at', 'is_rebooked', 'rebooking_status', 'rebooking_rejected_at'])
                ->map(fn (Booking $b) => [
                    'type' => 'booking',
                    'icon' => $b->status === 'rejected' ? 'heroicon-o-x-circle' : 'heroicon-o-ticket',
                    'title' => $b->status === 'cancelled'
                        ? "Booking cancelled {$b->transaction_number}"
                        : ($b->status === 'rejected'
                            ? "Payment rejected {$b->transaction_number}"
                            : ($b->is_rebooked && $b->rebooking_status === 'pending'
                                ? "Rebooking request for {$b->transaction_number}"
                                : ($b->is_rebooked && $b->rebooking_status === 'rejected'
                                    ? "Rebooking rejected {$b->transaction_number}"
                                    : ($b->status === 'confirmed' && $b->verified_at
                                        ? "Booking verified {$b->transaction_number}"
                                        : "New booking {$b->transaction_number}")))),
                    'description' => $b->status === 'cancelled'
                        ? "{$b->client_name} cancelled {$b->origin} → {$b->destination}"
                        : ($b->status === 'rejected'
                            ? "Payment rejected for {$b->client_name} · {$b->origin} → {$b->destination}"
                            : ($b->is_rebooked && $b->rebooking_status === 'rejected'
                                ? "Rebooking rejected for {$b->client_name} · {$b->origin} → {$b->destination}"
                                : ($b->status === 'confirmed' && $b->verified_at
                                    ? "Booking confirmed for {$b->client_name} · {$b->origin} → {$b->destination}"
                                    : "{$b->client_name} · {$b->origin} → {$b->destination}"))),
                    'status' => $b->status,
                    'amount' => $b->total_price,
                    'time' => with($b->verified_at ?? $b->rejected_at ?? $b->rebooking_rejected_at ?? $b->updated_at ?? $b->created_at, function ($value) {
                        if ($value === null) {
                            return null;
                        }

                        return $value instanceof \DateTimeInterface
                            ? $value->toIso8601String()
                            : \Illuminate\Support\Carbon::parse($value)->toIso8601String();
                    }),
                ]);

            $transactions = Transaction::with('booking:id,transaction_number,client_name')
                ->latest('created_at')
                ->take($limit)
                ->get()
                ->map(fn (Transaction $t) => [
                    'type' => 'transaction',
                    'icon' => 'heroicon-o-banknotes',
                    'title' => 'Payment ' . ucfirst($t->payment_status),
                    'description' => $t->booking?->transaction_number . ' · ' . ($t->booking?->client_name ?? 'Unknown'),
                    'status' => $t->payment_status,
                    'amount' => $t->rebooking_fee,
                    'time' => $t->created_at->toIso8601String(),
                ]);

            $inquiries = Inquiry::latest('created_at')
                ->take($limit)
                ->get()
                ->map(fn (Inquiry $i) => [
                    'type' => 'inquiry',
                    'icon' => 'heroicon-o-envelope',
                    'title' => "Inquiry: {$i->subject}",
                    'description' => $i->name . ' · ' . $i->email,
                    'status' => 'new',
                    'amount' => null,
                    'time' => $i->created_at->toIso8601String(),
                ]);

            return $bookings->concat($transactions)->concat($inquiries)
                ->sortByDesc('time')
                ->take($limit)
                ->values()
                ->toArray();
        });
    }

    // ─── Top Routes ─────────────────────────────────────────────

    public function getTopRoutes(int $limit = 5, ?string $period = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $key = "top_routes_{$limit}_{$period}_{$startDate}_{$endDate}";

        return Cache::remember($key, self::CACHE_TTL, function () use ($limit, $period, $startDate, $endDate) {
            $query = Booking::query()
                ->select(
                    'origin',
                    'destination',
                    DB::raw('COUNT(*) as booking_count'),
                    DB::raw('SUM(total_price) as total_revenue'),
                )
                ->whereNotNull('origin')
                ->whereNotNull('destination')
                ->groupBy('origin', 'destination')
                ->orderByDesc('booking_count')
                ->limit($limit);

            $this->applyPeriodFilter($query, $period, $startDate, $endDate);

            return $query->get()->toArray();
        });
    }

    // ─── Overall Stats (Reports Page) ───────────────────────────

    public function getOverallStats(?string $period = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $key = "overall_stats_{$period}_{$startDate}_{$endDate}";

        return Cache::remember($key, self::CACHE_TTL, function () use ($period, $startDate, $endDate) {
            $query = Booking::query();
            $this->applyPeriodFilter($query, $period, $startDate, $endDate);

            $total = (clone $query)->count();
            $confirmed = (clone $query)->where('status', 'confirmed')->count();
            $pending = (clone $query)->where('status', 'pending')->count();
            $cancelled = (clone $query)->where('status', 'cancelled')->count();
            $rejected = (clone $query)->where('status', 'rejected')->count();
            $rejectedRebookings = (clone $query)->where('is_rebooked', true)->where('rebooking_status', 'rejected')->count();

            $totalRevenue = (float) (clone $query)
                ->whereHas('transactions', fn (Builder $q) => $q->where('payment_status', 'paid'))
                ->sum('total_price');

            $pendingRevenue = (float) (clone $query)
                ->whereHas('transactions', fn (Builder $q) => $q->where('payment_status', 'pending'))
                ->sum('total_price');

            $cancelledFees = (float) (clone $query)
                ->where('status', 'cancelled')
                ->sum('cancellation_fee');

            $rebookingCount = (clone $query)->where('is_rebooked', true)->count();

            $avgBookingValue = $total > 0 ? $totalRevenue / $total : 0;
            $completionRate = $total > 0 ? round(($confirmed / $total) * 100, 1) : 0;
            $cancellationRate = $total > 0 ? round(($cancelled / $total) * 100, 1) : 0;
            $rejectionRate = $total > 0 ? round(($rejected / $total) * 100, 1) : 0;

            // Previous period stats for trend comparison
            $prevStats = $this->getPreviousPeriodStats($period, $startDate, $endDate);

            // Passenger Item level metrics
            $paxQuery = Passenger::query();
            if ($period || $startDate) {
                $paxQuery->whereHas('booking', function (Builder $q) use ($period, $startDate, $endDate) {
                    $this->applyPeriodFilter($q, $period, $startDate, $endDate);
                });
            }
            $totalPassengers = (clone $paxQuery)->count();
            $confirmedPassengers = (clone $paxQuery)->where('status', 'confirmed')->count();
            $cancelledPassengers = (clone $paxQuery)->whereIn('status', ['cancelled', 'operator_cancelled'])->count();
            $rejectedPassengers = (clone $paxQuery)->where('status', 'rejected')->count();
            $refundedPassengers = (clone $paxQuery)->where('status', 'refunded')->count();
            $rebookedPassengers = (clone $paxQuery)->where('status', 'rebooked')->count();
            $totalRefundDisbursed = (float) (clone $paxQuery)->where('status', 'refunded')->sum('refund_amount');

            return [
                'total_bookings' => $total,
                'completed_bookings' => $confirmed,
                'pending_bookings' => $pending,
                'cancelled_bookings' => $cancelled,
                'rejected_bookings' => $rejected,
                'rejected_rebookings' => $rejectedRebookings,
                'total_revenue' => $totalRevenue,
                'pending_revenue' => $pendingRevenue,
                'cancelled_revenue' => $cancelledFees,
                'rebooking_count' => $rebookingCount,
                'avg_booking_value' => $avgBookingValue,
                'completion_rate' => $completionRate,
                'cancellation_rate' => $cancellationRate,
                'rejection_rate' => $rejectionRate,
                'prev_total_bookings' => $prevStats['total'],
                'prev_total_revenue' => $prevStats['revenue'],
                // Item-level passenger metrics
                'total_passengers' => $totalPassengers,
                'confirmed_passengers' => $confirmedPassengers,
                'cancelled_passengers' => $cancelledPassengers,
                'rejected_passengers' => $rejectedPassengers,
                'refunded_passengers' => $refundedPassengers,
                'rebooked_passengers' => $rebookedPassengers,
                'total_refund_disbursed' => $totalRefundDisbursed,
            ];
        });
    }

    // ─── Passenger Status Distribution (Item-Level) ──────────────

    public function getPassengerStatusDistribution(?string $period = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $key = "passenger_status_dist_{$period}_{$startDate}_{$endDate}";

        return Cache::remember($key, self::CACHE_TTL, function () use ($period, $startDate, $endDate) {
            $query = Passenger::query();
            if ($period || $startDate) {
                $query->whereHas('booking', function (Builder $q) use ($period, $startDate, $endDate) {
                    $this->applyPeriodFilter($q, $period, $startDate, $endDate);
                });
            }

            $counts = $query->select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            return [
                'confirmed' => $counts['confirmed'] ?? 0,
                'pending' => $counts['pending'] ?? 0,
                'cancelled' => ($counts['cancelled'] ?? 0) + ($counts['operator_cancelled'] ?? 0),
                'rejected' => $counts['rejected'] ?? 0,
                'refund_pending' => $counts['refund_pending'] ?? 0,
                'refunded' => $counts['refunded'] ?? 0,
                'rebooking_pending' => $counts['rebooking_pending'] ?? 0,
                'rebooked' => $counts['rebooked'] ?? 0,
            ];
        });
    }

    private function getPreviousPeriodStats(?string $period, ?string $startDate, ?string $endDate): array
    {
        $query = Booking::query();

        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);
            $diff = $start->diffInDays($end);
            $prevStart = $start->copy()->subDays($diff + 1);
            $prevEnd = $start->copy()->subDay();
            $query->whereBetween('created_at', [$prevStart->startOfDay(), $prevEnd->endOfDay()]);
        } else {
            match ($period) {
                'today' => $query->whereDate('created_at', today()->subDay()),
                'week' => $query->whereBetween('created_at', [
                    now()->subWeek()->startOfWeek(),
                    now()->subWeek()->endOfWeek(),
                ]),
                'month' => $query->whereMonth('created_at', now()->subMonth()->month)
                    ->whereYear('created_at', now()->subMonth()->year),
                'year' => $query->whereYear('created_at', now()->subYear()->year),
                default => null,
            };
        }

        return [
            'total' => $query->count(),
            'revenue' => (float) (clone $query)
                ->whereHas('transactions', fn (Builder $q) => $q->where('payment_status', 'paid'))
                ->sum('total_price'),
        ];
    }

    // ─── Revenue by Period (Reports chart) ──────────────────────

    public function getRevenueByPeriod(?string $period = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $key = "revenue_by_period_{$period}_{$startDate}_{$endDate}";

        return Cache::remember($key, self::CACHE_TTL, function () use ($period, $startDate, $endDate) {
            $days = match ($period) {
                'today' => 1,
                'week' => 7,
                'month' => 30,
                'year' => 365,
                default => 30,
            };

            if ($startDate && $endDate) {
                $start = Carbon::parse($startDate);
                $end = Carbon::parse($endDate);
                $days = max($start->diffInDays($end), 1);
            }

            $groupBy = $days > 90 ? 'month' : 'day';
            $categories = [];
            $revenue = [];
            $bookings = [];

            if ($groupBy === 'month') {
                $months = (int) ceil($days / 30);
                for ($i = $months - 1; $i >= 0; $i--) {
                    $monthStart = now()->subMonths($i)->startOfMonth();
                    $monthEnd = now()->subMonths($i)->endOfMonth();
                    $categories[] = $monthStart->format('M Y');

                    $revenue[] = (float) Booking::whereBetween('created_at', [$monthStart, $monthEnd])
                        ->whereHas('transactions', fn (Builder $q) => $q->where('payment_status', 'paid'))
                        ->sum('total_price');

                    $bookings[] = Booking::whereBetween('created_at', [$monthStart, $monthEnd])->count();
                }
            } else {
                $baseDate = ($startDate) ? Carbon::parse($startDate) : now()->subDays($days - 1);
                for ($i = 0; $i < $days; $i++) {
                    $date = $baseDate->copy()->addDays($i);
                    $dateStr = $date->format('Y-m-d');
                    $categories[] = $date->format('M d');

                    $revenue[] = (float) Booking::whereDate('created_at', $dateStr)
                        ->whereHas('transactions', fn (Builder $q) => $q->where('payment_status', 'paid'))
                        ->sum('total_price');

                    $bookings[] = Booking::whereDate('created_at', $dateStr)->count();
                }
            }

            return [
                'revenue' => [
                    'series' => [['name' => 'Revenue (₱)', 'data' => $revenue]],
                    'categories' => $categories,
                ],
                'bookingVolume' => [
                    'series' => [['name' => 'Bookings', 'data' => $bookings]],
                    'categories' => $categories,
                ],
            ];
        });
    }

    // ─── Bookings by Transport Mode ─────────────────────────────

    public function getBookingsByTransportMode(?string $period = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $key = "bookings_by_mode_{$period}_{$startDate}_{$endDate}";

        return Cache::remember($key, self::CACHE_TTL, function () use ($period, $startDate, $endDate) {
            $query = Booking::query()
                ->join('schedules', 'bookings.schedule_id', '=', 'schedules.id')
                ->join('ferry_routes', 'schedules.ferry_route_id', '=', 'ferry_routes.id');
            $this->applyPeriodFilter($query, $period, $startDate, $endDate, 'bookings.created_at');

            $byMode = $query->select('ferry_routes.mode', DB::raw('COUNT(*) as count'))
                ->groupBy('ferry_routes.mode')
                ->pluck('count', 'mode')
                ->toArray();

            // Tour bookings (those with tour_id set)
            $tourQuery = Booking::query()->whereNotNull('tour_id');
            $this->applyPeriodFilter($tourQuery, $period, $startDate, $endDate);
            $tourCount = $tourQuery->count();

            $series = [];
            $labels = [];

            if (! empty($byMode['ferry'] ?? 0)) {
                $series[] = $byMode['ferry'];
                $labels[] = 'Ferry';
            }
            if (! empty($byMode['airline'] ?? 0)) {
                $series[] = $byMode['airline'];
                $labels[] = 'Airline';
            }
            if ($tourCount > 0) {
                $series[] = $tourCount;
                $labels[] = 'Tour';
            }

            // Fallback if no data
            if (empty($series)) {
                $series = [0];
                $labels = ['No Data'];
            }

            return [
                'series' => $series,
                'labels' => $labels,
            ];
        });
    }

    // ─── Top Routes by Revenue ──────────────────────────────────

    public function getTopRoutesByRevenue(int $limit = 8, ?string $period = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $key = "top_routes_revenue_{$limit}_{$period}_{$startDate}_{$endDate}";

        return Cache::remember($key, self::CACHE_TTL, function () use ($limit, $period, $startDate, $endDate) {
            $query = Booking::query()
                ->select(
                    DB::raw("CONCAT(origin, ' → ', destination) as route"),
                    DB::raw('SUM(total_price) as revenue'),
                )
                ->whereNotNull('origin')
                ->whereNotNull('destination')
                ->groupBy('origin', 'destination')
                ->orderByDesc('revenue')
                ->limit($limit);

            $this->applyPeriodFilter($query, $period, $startDate, $endDate);
            $results = $query->get();

            return [
                'series' => [['name' => 'Revenue (₱)', 'data' => $results->pluck('revenue')->map(fn ($v) => (float) $v)->toArray()]],
                'categories' => $results->pluck('route')->toArray(),
            ];
        });
    }

    // ─── Passenger Demographics ─────────────────────────────────

    public function getPassengerDemographics(?string $period = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $key = "passenger_demographics_{$period}_{$startDate}_{$endDate}";

        return Cache::remember($key, self::CACHE_TTL, function () use ($period, $startDate, $endDate) {
            $query = Passenger::query();
            if ($period || $startDate) {
                $query->whereHas('booking', function (Builder $q) use ($period, $startDate, $endDate) {
                    $this->applyPeriodFilter($q, $period, $startDate, $endDate);
                });
            }

            $byType = $query->select('type', DB::raw('COUNT(*) as count'))
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray();

            $types = ['adult', 'child', 'infant', 'senior', 'student'];
            $series = [];
            $labels = [];

            foreach ($types as $type) {
                $count = $byType[$type] ?? 0;
                if ($count > 0) {
                    $series[] = $count;
                    $labels[] = ucfirst($type);
                }
            }

            // Include any unlabeled
            $labeledTotal = array_sum($series);
            $allTotal = array_sum($byType);
            if ($allTotal > $labeledTotal) {
                $series[] = $allTotal - $labeledTotal;
                $labels[] = 'Other';
            }

            if (empty($series)) {
                $series = [0];
                $labels = ['No Data'];
            }

            return [
                'series' => [['name' => 'Passengers', 'data' => $series]],
                'categories' => $labels,
            ];
        });
    }

    // ─── Staff Leaderboard ──────────────────────────────────────

    public function getStaffLeaderboard(?string $period = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $key = "staff_leaderboard_{$period}_{$startDate}_{$endDate}";

        return Cache::remember($key, self::CACHE_TTL, function () use ($period, $startDate, $endDate) {
            $query = Booking::query()
                ->select(
                    'verified_by_user_id',
                    DB::raw('COUNT(*) as verifications'),
                    DB::raw('SUM(total_price) as total_revenue'),
                )
                ->whereNotNull('verified_by_user_id')
                ->groupBy('verified_by_user_id')
                ->orderByDesc('verifications')
                ->limit(5);

            $this->applyPeriodFilter($query, $period, $startDate, $endDate, 'verified_at');
            $results = $query->get();

            $userIds = $results->pluck('verified_by_user_id')->toArray();
            $users = User::whereIn('id', $userIds)->pluck('name', 'id');

            return $results->map(fn ($row) => [
                'name' => $users[$row->verified_by_user_id] ?? 'Unknown',
                'verifications' => $row->verifications,
                'revenue' => (float) $row->total_revenue,
            ])->toArray();
        });
    }

    // ─── Tour Performance ───────────────────────────────────────

    public function getTourPerformance(?string $period = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $key = "tour_performance_{$period}_{$startDate}_{$endDate}";

        return Cache::remember($key, self::CACHE_TTL, function () use ($period, $startDate, $endDate) {
            $tours = Tour::where('is_active', true)->get();

            return $tours->map(function (Tour $tour) use ($period, $startDate, $endDate) {
                $query = Booking::where('tour_id', $tour->id);
                $this->applyPeriodFilter($query, $period, $startDate, $endDate);

                $bookingCount = (clone $query)->count();
                $revenue = (float) (clone $query)->sum('total_price');
                $upcomingDates = $tour->activeDates()->where('date', '>=', today())->count();

                return [
                    'name' => $tour->tour_name,
                    'bookings' => $bookingCount,
                    'revenue' => $revenue,
                    'upcoming_dates' => $upcomingDates,
                ];
            })->sortByDesc('bookings')->values()->toArray();
        });
    }

    // ─── Payment Analytics ──────────────────────────────────────

    public function getPaymentAnalytics(?string $period = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $key = "payment_analytics_{$period}_{$startDate}_{$endDate}";

        return Cache::remember($key, self::CACHE_TTL, function () use ($period, $startDate, $endDate) {
            $query = Transaction::query();
            $this->applyPeriodFilter($query, $period, $startDate, $endDate);

            $byStatus = (clone $query)->select('payment_status', DB::raw('COUNT(*) as count'))
                ->groupBy('payment_status')
                ->pluck('count', 'payment_status')
                ->toArray();

            $totalTransactions = array_sum($byStatus);
            $withProof = (clone $query)->whereNotNull('proof_of_payment')->count();
            $proofUploadRate = $totalTransactions > 0 ? round(($withProof / $totalTransactions) * 100, 1) : 0;

            return [
                'paid' => $byStatus['paid'] ?? 0,
                'pending' => $byStatus['pending'] ?? 0,
                'failed' => $byStatus['failed'] ?? 0,
                'rejected' => $byStatus['rejected'] ?? 0,
                'total' => $totalTransactions,
                'proof_upload_rate' => $proofUploadRate,
            ];
        });
    }

    // ─── Recent Bookings (for Reports page table) ───────────────

    public function getRecentBookings(int $limit = 8, ?string $period = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = Booking::query()->latest('created_at');
        $this->applyPeriodFilter($query, $period, $startDate, $endDate);

        return $query->take($limit)
            ->get(['id', 'transaction_number', 'client_name', 'origin', 'destination', 'departure_date', 'return_date', 'status', 'total_price', 'created_at'])
            ->map(fn (Booking $b) => [
                'transaction_number' => $b->transaction_number,
                'client_name' => $b->client_name,
                'route' => $b->origin . ' → ' . $b->destination,
                'travel_dates' => $b->departure_date?->format('M d, Y') . ($b->return_date ? ' → ' . $b->return_date->format('M d, Y') : ''),
                'status' => $b->status,
                'total_price' => $b->total_price,
                'created_at' => $b->created_at->format('M d, Y'),
            ])
            ->toArray();
    }

    // ─── Recent Transactions (for Reports page table) ───────────

    public function getRecentTransactions(int $limit = 8, ?string $period = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = Transaction::with('booking:id,transaction_number,client_name')->latest('created_at');
        $this->applyPeriodFilter($query, $period, $startDate, $endDate);

        return $query->take($limit)
            ->get()
            ->map(fn (Transaction $t) => [
                'transaction_number' => $t->booking?->transaction_number ?? 'N/A',
                'client_name' => $t->booking?->client_name ?? 'Unknown',
                'payment_status' => $t->payment_status,
                'rebooking_fee' => $t->rebooking_fee,
                'proof_uploaded' => filled($t->proof_of_payment),
                'created_at' => $t->created_at->format('M d, Y'),
            ])
            ->toArray();
    }

    // ─── Staff Stats (kept for StaffPerformance page) ───────────

    public function getStaffStats(?string $period = null, ?string $startDate = null, ?string $endDate = null, ?string $date = null): Collection
    {
        $staffUsers = User::where('is_staff', true)
            ->orWhere('is_admin', true)
            ->orderBy('name')
            ->get();

        return $staffUsers->map(function (User $user) use ($period, $startDate, $endDate, $date) {
            $baseQuery = Booking::query()->where(function ($q) use ($user) {
                $q->where('verified_by_user_id', $user->id)
                  ->orWhere('refund_processed_by_user_id', $user->id)
                  ->orWhere('rejected_by_user_id', $user->id)
                  ->orWhere('rebooking_rejected_by_user_id', $user->id)
                  ->orWhereHas('transactions', fn (Builder $tq) => $tq->where('verified_by_user_id', $user->id))
                  ->orWhereHas('passengers', fn (Builder $pq) => $pq->where('verified_by_user_id', $user->id)->orWhere('refund_processed_by_user_id', $user->id));
            });

            if ($date) {
                $baseQuery->where(function ($dq) use ($date) {
                    $dq->whereDate('verified_at', $date)
                       ->orWhereDate('refund_processed_at', $date)
                       ->orWhereDate('rejected_at', $date)
                       ->orWhereDate('rebooking_rejected_at', $date)
                       ->orWhere(function ($sub) use ($date) {
                           $sub->whereNull('verified_at')
                               ->whereNull('rejected_at')
                               ->whereDate('created_at', $date);
                       });
                });
            } elseif ($startDate && $endDate) {
                $hasStartTime = strlen($startDate) > 10;
                $hasEndTime   = strlen($endDate) > 10;
                $start = $hasStartTime ? Carbon::parse($startDate) : Carbon::parse($startDate)->startOfDay();
                $end   = $hasEndTime   ? Carbon::parse($endDate)   : Carbon::parse($endDate)->endOfDay();

                $baseQuery->where(function ($dq) use ($start, $end) {
                    $dq->whereBetween('verified_at', [$start, $end])
                       ->orWhereBetween('refund_processed_at', [$start, $end])
                       ->orWhereBetween('rejected_at', [$start, $end])
                       ->orWhereBetween('rebooking_rejected_at', [$start, $end])
                       ->orWhere(function ($sub) use ($start, $end) {
                           $sub->whereNull('verified_at')
                               ->whereNull('rejected_at')
                               ->whereBetween('created_at', [$start, $end]);
                       });
                });
            } elseif ($period) {
                $this->applyPeriodFilter($baseQuery, $period, null, null, 'created_at');
            }

            $total = (clone $baseQuery)->count();
            $confirmed = (clone $baseQuery)->where('status', Booking::STATUS_CONFIRMED)->count();
            $pending = (clone $baseQuery)->whereIn('status', [Booking::STATUS_PENDING, Booking::STATUS_PENDING_REBOOKING, 'refund_pending'])->count();
            $cancelled = (clone $baseQuery)->whereIn('status', [Booking::STATUS_CANCELLED, Booking::STATUS_OPERATOR_CANCELLED])->count();
            $rejected = (clone $baseQuery)->where(function ($rq) use ($user) {
                $rq->where('status', Booking::STATUS_REJECTED)
                   ->orWhere('rejected_by_user_id', $user->id)
                   ->orWhere('rebooking_rejected_by_user_id', $user->id);
            })->count();
            $refunded = (clone $baseQuery)->where(function ($rq) {
                $rq->where('refund_amount', '>', 0)->orWhere('refund_status', 'completed');
            })->count();

            $revenue = (float) (clone $baseQuery)
                ->where(function ($q) {
                    $q->where('status', Booking::STATUS_CONFIRMED)
                      ->orWhereHas('transactions', fn (Builder $tq) => $tq->where('payment_status', 'paid'));
                })
                ->sum('total_price');

            $completionRate = $total > 0 ? round(($confirmed / $total) * 100, 1) : 0;

            $latestAction = (clone $baseQuery)
                ->orderByDesc(DB::raw('COALESCE(verified_at, refund_processed_at, rejected_at, rebooking_rejected_at, updated_at, created_at)'))
                ->first();

            $latestActionAt = $latestAction ? ($latestAction->verified_at ?? $latestAction->refund_processed_at ?? $latestAction->rejected_at ?? $latestAction->rebooking_rejected_at ?? $latestAction->updated_at ?? $latestAction->created_at) : null;

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => (bool) $user->is_admin,
                'is_staff' => (bool) $user->is_staff,
                'total_bookings_handled' => $total,
                'completed_bookings' => $confirmed,
                'pending_bookings' => $pending,
                'cancelled_bookings' => $cancelled,
                'rejected_bookings' => $rejected,
                'refunded_bookings' => $refunded,
                'total_revenue_handled' => $revenue,
                'completion_rate' => $completionRate,
                'latest_action_at' => $latestActionAt,
                'created_at' => $user->created_at,
            ];
        });
    }

    // ─── Booking Status Breakdown (kept for compatibility) ──────

    public function getBookingStatusBreakdown(?string $period = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = Booking::query();
        $this->applyPeriodFilter($query, $period, $startDate, $endDate);

        $counts = $query->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'pending' => $counts['pending'] ?? 0,
            'confirmed' => $counts['confirmed'] ?? 0,
            'cancelled' => $counts['cancelled'] ?? 0,
            'rejected' => $counts['rejected'] ?? 0,
        ];
    }
}

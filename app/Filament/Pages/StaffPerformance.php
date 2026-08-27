<?php

namespace App\Filament\Pages;

use App\Models\Booking;
use App\Models\User;
use App\Support\ReportingService;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;

class StaffPerformance extends Page
{
    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user instanceof User && $user->hasAdminPermission('staff_performance');
    }

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?int $navigationSort = 20;
    protected static ?string $navigationLabel = 'Staff Performance';
    protected static ?string $title = 'Staff Performance Reports';
    protected static string $view = 'filament.pages.staff-performance';

    #[Url]
    public string $period = 'all_time';

    #[Url]
    public ?string $startDate = null;

    #[Url]
    public ?string $endDate = null;

    public Collection $staffStats;

    public array $summaryKpis = [
        'total_bookings' => 0,
        'total_revenue' => 0.0,
        'total_completed' => 0,
        'active_staff_count' => 0,
        'top_staff_name' => 'None',
        'top_staff_count' => 0,
    ];

    public function mount(): void
    {
        $this->staffStats = collect();
        if ($this->period === 'custom' && (! $this->startDate || ! $this->endDate)) {
            $this->startDate = now()->startOfMonth()->format('Y-m-d 00:00:00');
            $this->endDate = now()->endOfMonth()->format('Y-m-d 23:59:59');
        }
        $this->loadStats();
    }

    public function setPeriod(string $period): void
    {
        $this->period = $period;

        switch ($period) {
            case 'today':
                $this->startDate = now()->startOfDay()->format('Y-m-d H:i:s');
                $this->endDate = now()->endOfDay()->format('Y-m-d H:i:s');
                break;
            case 'yesterday':
                $this->startDate = now()->subDay()->startOfDay()->format('Y-m-d H:i:s');
                $this->endDate = now()->subDay()->endOfDay()->format('Y-m-d H:i:s');
                break;
            case 'this_week':
                $this->startDate = now()->startOfWeek()->startOfDay()->format('Y-m-d H:i:s');
                $this->endDate = now()->endOfWeek()->endOfDay()->format('Y-m-d H:i:s');
                break;
            case 'last_7_days':
                $this->startDate = now()->subDays(6)->startOfDay()->format('Y-m-d H:i:s');
                $this->endDate = now()->endOfDay()->format('Y-m-d H:i:s');
                break;
            case 'this_month':
                $this->startDate = now()->startOfMonth()->startOfDay()->format('Y-m-d H:i:s');
                $this->endDate = now()->endOfMonth()->endOfDay()->format('Y-m-d H:i:s');
                break;
            case 'last_month':
                $this->startDate = now()->subMonth()->startOfMonth()->startOfDay()->format('Y-m-d H:i:s');
                $this->endDate = now()->subMonth()->endOfMonth()->endOfDay()->format('Y-m-d H:i:s');
                break;
            case 'this_year':
                $this->startDate = now()->startOfYear()->startOfDay()->format('Y-m-d H:i:s');
                $this->endDate = now()->endOfYear()->endOfDay()->format('Y-m-d H:i:s');
                break;
            case 'all_time':
            default:
                $this->startDate = null;
                $this->endDate = null;
                break;
        }

        $this->loadStats();
    }

    public function updateDateRange(?string $start, ?string $end): void
    {
        $this->period = 'custom';
        $this->startDate = $start;
        $this->endDate = $end;
        $this->loadStats();
    }

    public function loadStats(): void
    {
        $service = app(ReportingService::class);
        $periodParam = ($this->period !== 'custom' && $this->period !== 'all_time') ? $this->period : null;

        $this->staffStats = $service->getStaffStats($periodParam, $this->startDate, $this->endDate);

        $totalBookings = (int) $this->staffStats->sum('total_bookings_handled');
        $totalRevenue = (float) $this->staffStats->sum('total_revenue_handled');
        $totalCompleted = (int) $this->staffStats->sum('completed_bookings');
        $activeStaff = $this->staffStats->filter(fn ($s) => $s['total_bookings_handled'] > 0)->count();

        $topStaff = $this->staffStats->sortByDesc('total_bookings_handled')->first();

        $this->summaryKpis = [
            'total_bookings' => $totalBookings,
            'total_revenue' => $totalRevenue,
            'total_completed' => $totalCompleted,
            'active_staff_count' => $activeStaff,
            'top_staff_name' => ($topStaff && $topStaff['total_bookings_handled'] > 0) ? $topStaff['name'] : 'None',
            'top_staff_count' => $topStaff['total_bookings_handled'] ?? 0,
        ];
    }

    public function getStaffBookings(int $staffId): Collection
    {
        $query = Booking::query()->where(function ($q) use ($staffId) {
            $q->where('verified_by_user_id', $staffId)
              ->orWhere('refund_processed_by_user_id', $staffId)
              ->orWhereHas('transactions', fn ($tq) => $tq->where('verified_by_user_id', $staffId))
              ->orWhereHas('passengers', fn ($pq) => $pq->where('verified_by_user_id', $staffId)->orWhere('refund_processed_by_user_id', $staffId));
        });

        if ($this->startDate && $this->endDate) {
            $start = strlen($this->startDate) > 10 ? Carbon::parse($this->startDate) : Carbon::parse($this->startDate)->startOfDay();
            $end   = strlen($this->endDate) > 10   ? Carbon::parse($this->endDate)   : Carbon::parse($this->endDate)->endOfDay();
            $query->where(function ($dq) use ($start, $end) {
                $dq->whereBetween('verified_at', [$start, $end])
                   ->orWhereBetween('refund_processed_at', [$start, $end])
                   ->orWhere(function ($sub) use ($start, $end) {
                       $sub->whereNull('verified_at')->whereBetween('created_at', [$start, $end]);
                   });
            });
        } elseif ($this->period && $this->period !== 'all_time' && $this->period !== 'custom') {
            app(ReportingService::class)->applyPeriodFilter($query, $this->period, null, null, 'created_at');
        }

        return $query->with(['transaction', 'schedule.ferryRoute', 'passengers'])
            ->orderByDesc(DB::raw('COALESCE(verified_at, refund_processed_at, updated_at, created_at)'))
            ->get();
    }

    public function exportCsv()
    {
        $filename = 'staff_performance_' . now()->format('Y-m-d_H-i-s') . '.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Staff ID',
                'Staff Member',
                'Email',
                'Role',
                'Total Bookings Handled',
                'Completed Bookings',
                'Pending Bookings',
                'Cancelled Bookings',
                'Refunded Bookings',
                'Total Revenue Handled (PHP)',
                'Success Rate (%)',
                'Last Action Timestamp',
            ]);

            foreach ($this->staffStats as $staff) {
                $role = $staff['is_admin'] ? 'Administrator' : ($staff['is_staff'] ? 'Staff' : 'User');
                fputcsv($handle, [
                    $staff['id'],
                    $staff['name'],
                    $staff['email'],
                    $role,
                    $staff['total_bookings_handled'],
                    $staff['completed_bookings'],
                    $staff['pending_bookings'],
                    $staff['cancelled_bookings'],
                    $staff['refunded_bookings'],
                    number_format($staff['total_revenue_handled'], 2, '.', ''),
                    $staff['completion_rate'] . '%',
                    $staff['latest_action_at'] ? Carbon::parse($staff['latest_action_at'])->format('Y-m-d H:i:s') : 'N/A',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}

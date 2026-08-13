<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Support\ReportingService;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class OverallReports extends Page
{

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user instanceof User && $user->hasAdminPermission('overall_reports');
    }

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Overall Reports';

    protected static ?string $title = 'Overall Reports';

    protected static string $view = 'filament.pages.overall-reports';

    public ?string $startDate = null;

    public ?string $endDate = null;

    public array $stats = [];

    public array $breakdown = [];

    public array $chartData = [];

    public array $recentBookings = [];

    public array $recentTransactions = [];

    public array $transportModeData = [];

    public array $topRoutesData = [];

    public array $passengerData = [];

    public array $paymentAnalytics = [];

    public $staffLeaderboard;

    public $tourPerformance;

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->toDateString();
        $this->endDate = now()->endOfMonth()->toDateString();
        $this->loadStats();
    }

    public function updateDateRange($start, $end)
    {
        $this->startDate = $start;
        $this->endDate = $end;
        $this->loadStats();
    }

    public function loadStats(): void
    {
        $service = app(ReportingService::class);
        $period = null;
        
        $start = $this->startDate;
        $end = $this->endDate;

        $this->stats = $service->getOverallStats($period, $start, $end);
        $this->breakdown = $service->getBookingStatusBreakdown($period, $start, $end);

        $periodCharts = $service->getRevenueByPeriod($period, $start, $end);
        $statusDist = $service->getBookingStatusDistribution($period, $start, $end);
        $transportMode = $service->getBookingsByTransportMode($period, $start, $end);
        $topRoutes = $service->getTopRoutesByRevenue(8, $period, $start, $end);
        $passengers = $service->getPassengerDemographics($period, $start, $end);

        $this->chartData = [
            'revenue' => $periodCharts['revenue'],
            'bookingVolume' => $periodCharts['bookingVolume'],
            'statusDistribution' => $statusDist,
            'transportMode' => $transportMode,
            'topRoutes' => $topRoutes,
            'passengers' => $passengers,
        ];

        $this->transportModeData = $transportMode;
        $this->topRoutesData = $topRoutes;
        $this->passengerData = $passengers;
        $this->paymentAnalytics = $service->getPaymentAnalytics($period, $start, $end);
        $this->staffLeaderboard = $service->getStaffLeaderboard($period, $start, $end);
        $this->tourPerformance = $service->getTourPerformance($period, $start, $end);
        $this->recentBookings = $service->getRecentBookings(8, $period, $start, $end);
        $this->recentTransactions = $service->getRecentTransactions(8, $period, $start, $end);

        $this->dispatch('report-charts-updated', chartData: $this->chartData);
    }

    public function refreshData(): void
    {
        $this->loadStats();
    }
}

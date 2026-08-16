<?php

namespace App\Filament\Widgets;

use App\Support\ReportingService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '5m';

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $service = app(ReportingService::class);
        $kpis = $service->getDashboardKpis();

        $bookingTrend = $kpis['yesterday_bookings'] > 0
            ? round((($kpis['today_bookings'] - $kpis['yesterday_bookings']) / $kpis['yesterday_bookings']) * 100, 1)
            : ($kpis['today_bookings'] > 0 ? 100 : 0);

        $revenueTrend = $kpis['last_month_revenue'] > 0
            ? round((($kpis['month_revenue'] - $kpis['last_month_revenue']) / $kpis['last_month_revenue']) * 100, 1)
            : ($kpis['month_revenue'] > 0 ? 100 : 0);

        return [
            Stat::make("Today's Bookings", number_format($kpis['today_bookings']))
                ->description(abs($bookingTrend) . '% ' . ($bookingTrend >= 0 ? 'increase' : 'decrease') . ' vs yesterday')
                ->descriptionIcon($bookingTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart($service->getSparklineData('bookings'))
                ->color($bookingTrend >= 0 ? 'success' : 'danger'),

            Stat::make('Monthly Revenue', '₱' . number_format($kpis['month_revenue'], 2))
                ->description(abs($revenueTrend) . '% vs last month')
                ->descriptionIcon($revenueTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart($service->getSparklineData('revenue'))
                ->color($revenueTrend >= 0 ? 'success' : 'danger'),

            Stat::make('Active Routes & Tours', number_format($kpis['active_routes'] + $kpis['active_tours']))
                ->description($kpis['active_routes'] . ' routes · ' . $kpis['active_tours'] . ' tours')
                ->descriptionIcon('heroicon-m-map-pin')
                ->color('info'),

            Stat::make('Pending Verifications', number_format($kpis['pending_verifications']))
                ->description('Awaiting proof review')
                ->descriptionIcon('heroicon-m-clock')
                ->color($kpis['pending_verifications'] > 0 ? 'warning' : 'success'),

            Stat::make('Monthly Passengers', number_format($kpis['month_passengers']))
                ->description('This month')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make('New Inquiries', number_format($kpis['new_inquiries']))
                ->description('Today')
                ->descriptionIcon('heroicon-m-envelope')
                ->color($kpis['new_inquiries'] > 0 ? 'info' : 'gray'),
        ];
    }
}

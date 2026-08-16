<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ApkUserResource;
use App\Filament\Resources\FerryRouteResource;
use App\Filament\Resources\VehicleResource;
use App\Models\Schedule;
use App\Models\User;
use App\Models\Vehicle;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SystemStatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '5m';

    protected static ?int $sort = 0; // Rank it first, at the top

    protected function getStats(): array
    {
        $ferriesCount = Vehicle::where('type', 'ferry')->where('is_active', true)->count();
        $airlinesCount = Vehicle::where('type', 'airline')->where('is_active', true)->count();
        $schedulesCount = Schedule::where('is_active', true)->count();
        $apkUsersCount = User::whereNotNull('api_token')->count();

        return [
            Stat::make('Ferries', number_format($ferriesCount))
                ->description('Active ferries')
                ->descriptionIcon('heroicon-m-map')
                ->color('info')
                ->url(VehicleResource::getUrl('index') . '?vehicleType=ferry'),

            Stat::make('Airlines', number_format($airlinesCount))
                ->description('Active airlines')
                ->descriptionIcon('heroicon-m-paper-airplane')
                ->color('success')
                ->url(VehicleResource::getUrl('index') . '?vehicleType=airline'),

            Stat::make('Schedules', number_format($schedulesCount))
                ->description('Active departures')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('warning')
                ->url(FerryRouteResource::getUrl('index')),

            Stat::make('APK Users', number_format($apkUsersCount))
                ->description('Registered via mobile')
                ->descriptionIcon('heroicon-m-device-phone-mobile')
                ->color('primary')
                ->url(ApkUserResource::getUrl('index')),
        ];
    }
}

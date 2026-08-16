<?php

namespace App\Filament\Widgets;

use App\Support\ReportingService;
use Filament\Widgets\Widget;

class BookingStatusChart extends Widget
{
    protected static string $view = 'filament.widgets.booking-status-chart';

    protected static ?string $pollingInterval = '10m';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 1;

    public array $chartData = [];

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $this->chartData = app(ReportingService::class)->getBookingStatusDistribution();
        $this->dispatch('booking-status-chart-updated', chartData: $this->chartData);
    }
}

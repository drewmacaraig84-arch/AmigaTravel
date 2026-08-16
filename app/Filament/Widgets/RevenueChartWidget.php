<?php

namespace App\Filament\Widgets;

use App\Support\ReportingService;
use Filament\Widgets\Widget;

class RevenueChartWidget extends Widget
{
    protected static string $view = 'filament.widgets.revenue-chart';

    protected static ?string $pollingInterval = '10m';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 1;

    public array $chartData = [];

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $this->chartData = app(ReportingService::class)->getRevenueChartData(30);
        $this->dispatch('revenue-chart-updated', chartData: $this->chartData);
    }
}

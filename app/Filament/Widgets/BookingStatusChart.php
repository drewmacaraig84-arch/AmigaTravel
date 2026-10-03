<?php

namespace App\Filament\Widgets;

use App\Support\ReportingService;
use Filament\Widgets\Widget;
use Livewire\Attributes\On;

class BookingStatusChart extends Widget
{
    protected static string $view = 'filament.widgets.booking-status-chart';

    protected static ?string $pollingInterval = '30s';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 1;

    public array $chartData = [];

    public function mount(): void
    {
        $this->loadData();
    }

    #[On('admin-data-updated')]
    #[On('refresh')]
    public function loadData(): void
    {
        $this->chartData = app(ReportingService::class)->getBookingStatusDistribution();
        $this->dispatch('booking-status-chart-updated', chartData: $this->chartData);
    }
}

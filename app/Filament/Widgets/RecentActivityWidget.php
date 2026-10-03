<?php

namespace App\Filament\Widgets;

use App\Support\ReportingService;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;

class RecentActivityWidget extends Widget
{
    protected static string $view = 'filament.widgets.recent-activity-widget';

    protected static ?string $pollingInterval = '5s';

    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 1;

    public array $activities = [];

    public function mount(): void
    {
        $this->activities = app(ReportingService::class)->getRecentActivity(8);
    }

    #[On('admin-data-updated')]
    #[On('refresh')]
    public function loadData(): void
    {
        $this->activities = app(ReportingService::class)->getRecentActivity(8);
    }
}

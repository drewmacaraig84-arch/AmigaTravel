<?php

namespace App\Filament\Widgets;

use App\Support\ReportingService;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;

class TopRoutesWidget extends Widget
{
    protected static string $view = 'filament.widgets.top-routes-widget';

    protected static ?string $pollingInterval = '30s';

    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 1;

    public array $routes = [];

    public function mount(): void
    {
        $this->routes = app(ReportingService::class)->getTopRoutes(5);
    }

    #[On('admin-data-updated')]
    #[On('refresh')]
    public function loadData(): void
    {
        $this->routes = app(ReportingService::class)->getTopRoutes(5);
    }
}

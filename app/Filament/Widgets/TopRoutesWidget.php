<?php

namespace App\Filament\Widgets;

use App\Support\ReportingService;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class TopRoutesWidget extends Widget
{
    protected static string $view = 'filament.widgets.top-routes-widget';

    protected static ?string $pollingInterval = '10m';

    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 1;

    public array $routes = [];

    public function mount(): void
    {
        $this->routes = app(ReportingService::class)->getTopRoutes(5);
    }

    public function loadData(): void
    {
        $this->routes = app(ReportingService::class)->getTopRoutes(5);
    }
}

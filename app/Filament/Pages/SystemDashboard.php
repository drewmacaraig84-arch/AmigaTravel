<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Services\SystemHealthService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class SystemDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?string $navigationLabel = 'System Dashboard';

    protected static ?string $title = 'System & Health Dashboard';

    protected static ?string $slug = 'system-dashboard';

    protected static ?int $navigationSort = -100;

    protected static ?string $navigationGroup = null;

    protected static string $view = 'filament.pages.system-dashboard';

    public string $activeTab = 'health'; // 'health', 'logs', 'alerts', 'audits', 'database'

    public array $metrics = [];

    public array $chartData = [];

    public array $loginAudits = [];

    public array $databaseTables = [];

    public array $logData = [
        'entries' => [],
        'counts' => [
            'all' => 0,
            'emergency' => 0,
            'critical' => 0,
            'error' => 0,
            'warning' => 0,
            'info' => 0,
            'debug' => 0,
        ],
        'file_size_formatted' => '0 B',
    ];

    public string $levelFilter = 'all';

    public string $searchQuery = '';

    public ?string $selectedLogId = null;

    public ?array $selectedLog = null;

    public string $alertEmail = '';

    public bool $isSendingAlert = false;

    /**
     * Strict Super Admin security check.
     */
    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user instanceof User && $user->isSuperAdmin();
    }

    public function mount(SystemHealthService $service): void
    {
        $this->alertEmail = Auth::user()?->email ?? 'superadmin@amigatravel.com';
        $this->loadAllData($service);
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->dispatch('system-charts-updated', chartData: $this->chartData);
    }

    public function setLevelFilter(string $level): void
    {
        $this->levelFilter = $level;
        $this->logData = app(SystemHealthService::class)->getLogEntries(150, $this->levelFilter, $this->searchQuery);
    }

    public function updatedSearchQuery(): void
    {
        $this->logData = app(SystemHealthService::class)->getLogEntries(150, $this->levelFilter, $this->searchQuery);
    }

    public function loadAllData(SystemHealthService $service): void
    {
        $this->metrics = $service->getHealthMetrics();
        $this->logData = $service->getLogEntries(150, $this->levelFilter, $this->searchQuery);
        $this->chartData = $service->getAnalyticsChartsData();
        $this->loginAudits = $service->getRecentLoginAudits(8);
        $this->databaseTables = $service->getDatabaseTables(8);

        $this->dispatch('system-charts-updated', chartData: $this->chartData);
    }

    public function refreshAll(): void
    {
        $service = app(SystemHealthService::class);
        $this->loadAllData($service);

        Notification::make()
            ->title('System Metrics Refreshed')
            ->body('Latest server telemetry, charts, and error logs have been refreshed.')
            ->success()
            ->send();
    }

    public function viewLogDetails(string $id): void
    {
        $this->selectedLogId = $id;
        $this->selectedLog = collect($this->logData['entries'])->firstWhere('id', $id);
    }

    public function closeLogDetails(): void
    {
        $this->selectedLogId = null;
        $this->selectedLog = null;
    }

    public function downloadLog(): StreamedResponse
    {
        $logPath = storage_path('logs/laravel.log');

        if (! file_exists($logPath)) {
            Notification::make()
                ->title('Log File Missing')
                ->body('No laravel.log file was found on the server.')
                ->warning()
                ->send();
        }

        $filename = 'amiga_system_log_' . now()->format('Y-m-d_His') . '.log';

        return response()->streamDownload(function () use ($logPath) {
            $stream = fopen($logPath, 'r');
            fpassthru($stream);
            fclose($stream);
        }, $filename, [
            'Content-Type' => 'text/plain',
        ]);
    }

    public function clearLogFile(SystemHealthService $service): void
    {
        $success = $service->clearLog();

        if ($success) {
            $this->loadAllData($service);
            $this->closeLogDetails();

            Notification::make()
                ->title('Log Purged')
                ->body('The laravel.log file was cleared successfully.')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Clear Failed')
                ->body('Could not clear log file. Please check filesystem write permissions.')
                ->danger()
                ->send();
        }
    }

    public function sendTestAlert(SystemHealthService $service): void
    {
        if (empty(trim($this->alertEmail)) || ! filter_var($this->alertEmail, FILTER_VALIDATE_EMAIL)) {
            Notification::make()
                ->title('Invalid Email')
                ->body('Please provide a valid recipient email address.')
                ->warning()
                ->send();
            return;
        }

        $this->isSendingAlert = true;
        $result = $service->sendTestCrashAlert($this->alertEmail);
        $this->isSendingAlert = false;

        if ($result['success']) {
            Notification::make()
                ->title('Test Crash Alert Dispatched')
                ->body($result['message'])
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Alert Dispatch Failed')
                ->body($result['message'])
                ->danger()
                ->send();
        }
    }
}

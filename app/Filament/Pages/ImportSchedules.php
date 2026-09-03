<?php

namespace App\Filament\Pages;

use App\Models\Operator;
use App\Models\User;
use App\Services\LocationCodeResolver;
use App\Services\ScheduleCsvImportService;
use App\Services\StarliteScheduleIngestionService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ImportSchedules extends Page
{
    use WithFileUploads;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';
    protected static ?string $navigationGroup = 'Travel & Tours';
    protected static ?int $navigationSort = 25;
    protected static ?string $navigationLabel = 'Import Schedules';
    protected static ?string $title = 'Schedule Import Center';
    protected static string $view = 'filament.pages.import-schedules';

    public string $selectedOperator = 'Starlite';
    public string $mode = 'ferry';
    public string $customOperatorName = '';
    public string $importPreset = 'starlite_timetable'; // 'starlite_timetable' or 'standard_file'
    public ?string $startDate = null;
    public ?string $endDate = null;
    public $uploadedFile = null;
    public ?array $importSummary = null;

    public const DEFAULT_OPERATORS = [
        ['name' => 'Starlite', 'displayName' => 'Starlite Ferries', 'mode' => 'ferry', 'logo' => 'Starlite_Logo.png', 'description' => 'Fastcraft, Ropax, and LCT ferry schedules & rates'],
        ['name' => '2GO', 'displayName' => '2GO Travel', 'mode' => 'ferry', 'logo' => '2GO-Logo.png', 'description' => 'Voyage, cabin, and inter-island passenger lines'],
        ['name' => 'Philippine Airlines', 'displayName' => 'Philippine Airlines', 'mode' => 'airline', 'logo' => 'Pal-Logo.jfif', 'description' => 'Domestic and international flight routes'],
        ['name' => 'Cebu Pacific', 'displayName' => 'Cebu Pacific', 'mode' => 'airline', 'logo' => 'CebuPecific-Logo.png', 'description' => 'Domestic and regional flight network'],
        ['name' => 'AirAsia', 'displayName' => 'AirAsia', 'mode' => 'airline', 'logo' => 'AirAsia-Logo.png', 'description' => 'Domestic point-to-point airline schedules'],
    ];

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user instanceof User && (
            $user->isSuperAdmin() ||
            $user->hasAdminPermission('schedules') ||
            $user->hasAdminPermission('travel_routes') ||
            $user->hasAdminPermission('ferry_airline')
        );
    }

    public function mount(): void
    {
        $this->selectedOperator = 'Starlite';
        $this->mode = 'ferry';
        $this->importPreset = 'starlite_timetable';
        $this->startDate = Carbon::today()->toDateString();
        $this->endDate = Carbon::today()->addDays(60)->toDateString();
    }

    public function setDatePreset(int $days): void
    {
        $this->startDate = Carbon::today()->toDateString();
        $this->endDate = Carbon::today()->addDays($days)->toDateString();
    }

    public function selectOperator(string $operator): void
    {
        $this->selectedOperator = $operator;
        $this->importSummary = null;

        if ($operator === 'Starlite') {
            $this->mode = 'ferry';
            $this->importPreset = 'starlite_timetable';
        } elseif ($operator === '2GO') {
            $this->mode = 'ferry';
            $this->importPreset = 'twogo_timetable';
            $this->startDate = '2026-09-03';
            $this->endDate = '2026-12-31';
        } elseif (in_array($operator, ['Philippine Airlines', 'Cebu Pacific', 'AirAsia'], true)) {
            $this->mode = 'airline';
            $this->importPreset = 'standard_file';
        } else {
            // Custom new operator
            $this->selectedOperator = 'custom';
            $this->importPreset = 'standard_file';
        }
    }

    /**
     * Run the schedule import.
     */
    public function runImport(
        StarliteScheduleIngestionService $starliteService,
        TwoGoScheduleIngestionService $twoGoService,
        ScheduleCsvImportService $csvImportService
    ): void {
        $operatorName = $this->selectedOperator === 'custom'
            ? trim($this->customOperatorName)
            : $this->selectedOperator;

        if (empty($operatorName)) {
            Notification::make()
                ->title('Operator Required')
                ->body('Please specify or select an operator.')
                ->danger()
                ->send();
            return;
        }

        $start = filled($this->startDate) ? Carbon::parse($this->startDate) : Carbon::today();
        $end = filled($this->endDate) ? Carbon::parse($this->endDate) : Carbon::today()->addDays(60);

        if ($end->lessThan($start)) {
            Notification::make()
                ->title('Invalid Date Horizon')
                ->body('End date must be greater than or equal to start date.')
                ->warning()
                ->send();
            return;
        }

        try {
            // Case 1: Starlite Timetable & Rate Matrix
            if ($operatorName === 'Starlite' && ($this->importPreset === 'starlite_timetable' || ! $this->uploadedFile)) {
                $defaultPath = file_exists(base_path('starlite_example_schedule/VESSEL ROUTE.xlsx'))
                    ? base_path('starlite_example_schedule/VESSEL ROUTE.xlsx')
                    : base_path('starlite_schedules/VESSEL ROUTE.xlsx');

                $filePath = $this->uploadedFile
                    ? $this->uploadedFile->getRealPath()
                    : $defaultPath;

                $result = $starliteService->ingest($filePath, $start, $end);

                if ($result['success']) {
                    $this->importSummary = [
                        'operator' => 'Starlite',
                        'mode' => 'ferry',
                        'routes_count' => $result['routes_count'],
                        'schedules_count' => $result['schedules_count'],
                        'accommodations_count' => $result['accommodations_count'],
                        'vessels_count' => $result['vessels_count'],
                        'start_date' => $result['start_date'],
                        'end_date' => $result['end_date'],
                        'timestamp' => now()->format('M d, Y h:i A'),
                    ];

                    Notification::make()
                        ->title('Starlite Schedules Synced')
                        ->body("Successfully generated {$result['schedules_count']} schedules across {$result['routes_count']} routes with June 2026 tariff.")
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                        ->title('Import Notice')
                        ->body($result['message'])
                        ->warning()
                        ->send();
                }

                $this->reset('uploadedFile');
                return;
            }

            // Case 2: 2GO Travel Timetable & Rate Matrix
            if ($operatorName === '2GO' && ($this->importPreset === 'twogo_timetable' || ! $this->uploadedFile)) {
                $defaultPath = base_path('2go_schedules/2GO_TIMETABLE.xlsx');

                $filePath = $this->uploadedFile
                    ? $this->uploadedFile->getRealPath()
                    : $defaultPath;

                $result = $twoGoService->ingest($filePath, $start, $end);

                if ($result['success']) {
                    $this->importSummary = [
                        'operator' => '2GO',
                        'mode' => 'ferry',
                        'routes_count' => $result['routes_count'],
                        'schedules_count' => $result['schedules_count'],
                        'accommodations_count' => $result['schedules_count'] * 6,
                        'vessels_count' => $result['vessels_count'] ?? 11,
                        'start_date' => $start->format('M d, Y'),
                        'end_date' => $end->format('M d, Y'),
                        'timestamp' => now()->format('M d, Y h:i A'),
                    ];

                    Notification::make()
                        ->title('2GO Timetable Ingested Successfully!')
                        ->body("Successfully generated {$result['schedules_count']} schedules across {$result['routes_count']} routes from {$start->format('M d, Y')} to {$end->format('M d, Y')}.")
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                        ->title('Import Notice')
                        ->body($result['message'])
                        ->warning()
                        ->send();
                }

                $this->reset('uploadedFile');
                return;
            }

            // Case 3: File Upload (Standard CSV or Excel for any operator)
            if (! $this->uploadedFile) {
                Notification::make()
                    ->title('File Required')
                    ->body('Please select and upload a CSV or XLSX spreadsheet to import.')
                    ->warning()
                    ->send();
                return;
            }

            $realPath = $this->uploadedFile->getRealPath();
            $result = $csvImportService->import($realPath, $operatorName, $start, $end);

            if (! empty($result['starlite_result'])) {
                $starliteRes = $result['starlite_result'];
                $this->importSummary = [
                    'operator' => 'Starlite',
                    'mode' => 'ferry',
                    'routes_count' => $starliteRes['routes_count'] ?? 0,
                    'schedules_count' => $starliteRes['schedules_count'] ?? 0,
                    'accommodations_count' => $starliteRes['accommodations_count'] ?? 0,
                    'vessels_count' => $starliteRes['vessels_count'] ?? 0,
                    'start_date' => $start->toDateString(),
                    'end_date' => $end->toDateString(),
                    'timestamp' => now()->format('M d, Y h:i A'),
                ];
            } elseif (! empty($result['twogo_result'])) {
                $twogoRes = $result['twogo_result'];
                $this->importSummary = [
                    'operator' => '2GO',
                    'mode' => 'ferry',
                    'routes_count' => $twogoRes['routes_count'] ?? 0,
                    'schedules_count' => $twogoRes['schedules_count'] ?? 0,
                    'accommodations_count' => ($twogoRes['schedules_count'] ?? 0) * 6,
                    'vessels_count' => $twogoRes['vessels_count'] ?? 11,
                    'start_date' => $start->toDateString(),
                    'end_date' => $end->toDateString(),
                    'timestamp' => now()->format('M d, Y h:i A'),
                ];
            } else {
                $this->importSummary = [
                    'operator' => $operatorName,
                    'mode' => $this->mode,
                    'schedules_count' => $result['imported'] ?? 0,
                    'skipped_count' => $result['skipped'] ?? 0,
                    'errors_count' => count($result['errors'] ?? []),
                    'errors' => array_slice($result['errors'] ?? [], 0, 5),
                    'start_date' => $start->toDateString(),
                    'end_date' => $end->toDateString(),
                    'timestamp' => now()->format('M d, Y h:i A'),
                ];
            }

            $errorCount = count($result['errors'] ?? []);
            if ($errorCount > 0) {
                Notification::make()
                    ->title('Import Completed with Warnings')
                    ->body("Imported {$result['imported']} schedules, skipped {$result['skipped']}, encountered {$errorCount} errors.")
                    ->warning()
                    ->send();
            } else {
                Notification::make()
                    ->title('Import Successful')
                    ->body("Successfully imported {$result['imported']} schedule items for {$operatorName}.")
                    ->success()
                    ->send();
            }

            $this->reset('uploadedFile');
        } catch (Throwable $e) {
            Notification::make()
                ->title('Import Failed')
                ->body('An error occurred during import: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Download sample standard CSV template.
     */
    public function downloadSampleTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="amiga_schedule_import_template.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');

            // Header Row
            fputcsv($handle, [
                'Operator',
                'Mode',
                'Vehicle',
                'Origin',
                'Destination',
                'Departure Date',
                'Departure Time',
                'Arrival Time',
                'Return Date',
                'Accommodation',
                'Price',
                'Tickets Available',
                'Rate Tier',
                'Has Bed',
            ]);

            // Sample Rows
            $sampleRows = [
                ['Starlite', 'ferry', 'ANNAPOLIS', 'Batangas', 'Calapan', Carbon::today()->addDays(5)->format('d/m/Y'), '07:30', '10:30', '', 'Reclining Seat', '680', '100', 'regular', '0'],
                ['Starlite', 'ferry', 'ANNAPOLIS', 'Batangas', 'Calapan', Carbon::today()->addDays(5)->format('d/m/Y'), '07:30', '10:30', '', 'Economy Bed Bunk', '680', '120', 'regular', '1'],
                ['Starlite', 'ferry', 'ANNAPOLIS', 'Batangas', 'Calapan', Carbon::today()->addDays(5)->format('d/m/Y'), '07:30', '10:30', '', 'Tourist Bed Bunk', '680', '80', 'regular', '1'],
                ['2GO', 'ferry', 'St. Michael The Archangel', 'Manila', 'Cebu', Carbon::today()->addDays(7)->format('d/m/Y'), '18:00', '14:00', '', 'Super Value Class', '1650', '200', 'regular', '1'],
                ['2GO', 'ferry', 'St. Michael The Archangel', 'Manila', 'Cebu', Carbon::today()->addDays(7)->format('d/m/Y'), '18:00', '14:00', '', 'Tourist Class', '2150', '150', 'regular', '1'],
                ['Philippine Airlines', 'airline', 'PR 1845', 'MNL', 'CEB', Carbon::today()->addDays(10)->format('d/m/Y'), '08:45', '10:15', '', 'Economy Class', '3250', '140', 'regular', '0'],
                ['Cebu Pacific', 'airline', '5J 567', 'MNL', 'MPH', Carbon::today()->addDays(12)->format('d/m/Y'), '11:20', '12:30', '', 'Go Basic', '2890', '180', 'regular', '0'],
                ['AirAsia', 'airline', 'Z2 225', 'MNL', 'DVO', Carbon::today()->addDays(14)->format('d/m/Y'), '15:10', '17:05', '', 'Low Fare', '2450', '180', 'regular', '0'],
            ];

            foreach ($sampleRows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, 200, $headers);
    }
}

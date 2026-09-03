<?php

namespace App\Filament\Resources\FerryRouteResource\Pages;

use App\Filament\Resources\FerryRouteResource;
use App\Services\ScheduleCsvImportService;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListFerryRoutes extends ListRecords
{
    protected static string $resource = FerryRouteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('importCsv')
                ->label('Import CSV')
                ->icon('heroicon-m-arrow-up-tray')
                ->color('success')
                ->form([
                    FileUpload::make('csv_file')
                        ->label('Flight / Ferry Schedule File (CSV or Excel XLSX)')
                        ->acceptedFileTypes([
                            'text/csv',
                            'text/plain',
                            'application/vnd.ms-excel',
                            'application/csv',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ])
                        ->disk('local')
                        ->directory('temp-csv-imports')
                        ->required()
                        ->helperText('Upload a CSV or Excel (.xlsx) file with columns: Mode, Operator, Vehicle Tail No., Origin, Destination, Departure Date, Departure Time, Arrival Time, Return Date, Transport Class, Rate. Use DD/MM/YYYY for Departure Date and Return Date.'),
                ])
                ->action(function (array $data, ScheduleCsvImportService $importService): void {
                    try {
                        $relativeFilePath = $data['csv_file'] ?? null;
                        $disk = Storage::disk('local');

                        if (! $relativeFilePath) {
                            Notification::make()
                                ->title('Import Failed')
                                ->body('No file uploaded.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $fullPath = $disk->path($relativeFilePath);

                        if (! $disk->exists($relativeFilePath) || ! is_readable($fullPath)) {
                            Notification::make()
                                ->title('Import Failed')
                                ->body("Uploaded file could not be found on disk: {$relativeFilePath}")
                                ->danger()
                                ->persistent()
                                ->send();

                            return;
                        }

                        $result = $importService->import($fullPath);

                        // Clean up temp file
                        if ($disk->exists($relativeFilePath)) {
                            $disk->delete($relativeFilePath);
                        }

                        if (! empty($result['errors'])) {
                            $errorMsg = implode('; ', array_slice($result['errors'], 0, 3));
                            Notification::make()
                                ->title('Import Completed with Warnings')
                                ->body("Imported: {$result['imported']} schedules | Skipped: {$result['skipped']} duplicates. Errors: {$errorMsg}")
                                ->warning()
                                ->persistent()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Schedules Imported Successfully')
                                ->body("Successfully imported {$result['imported']} schedule(s)! Skipped {$result['skipped']} duplicate(s).")
                                ->success()
                                ->send();
                        }
                        \App\Models\Schedule::bust();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Import Processing Error')
                            ->body('An error occurred while processing the import: ' . $e->getMessage())
                            ->danger()
                            ->persistent()
                            ->send();
                    }
                }),
            Actions\CreateAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\VoucherResource\Pages;

use App\Filament\Resources\VoucherResource;
use App\Models\PaymentSetting;
use App\Services\VoucherBulkImportService;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ListVouchers extends ListRecords
{
    protected static string $resource = VoucherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('downloadVoucherTemplate')
                ->label('Download Template')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(fn () => $this->downloadCsvTemplate()),

            Actions\Action::make('importMultipleVouchers')
                ->label('Import Multiple')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->modalWidth('4xl')
                ->modalHeading('Import Multiple Vouchers via CSV / Excel')
                ->modalDescription('Configure the common voucher properties below, then upload a spreadsheet with "Code" and "Name" columns to generate all vouchers in bulk.')
                ->modalSubmitActionLabel('Import & Generate Vouchers')
                ->form([
                    Section::make('1. Spreadsheet Upload')
                        ->description('Upload a CSV or Excel (.xlsx) file containing at least Code and Name columns.')
                        ->schema([
                            FileUpload::make('file')
                                ->label('Voucher Spreadsheet File')
                                ->acceptedFileTypes([
                                    'text/csv',
                                    'text/plain',
                                    'application/vnd.ms-excel',
                                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                    '.csv',
                                    '.xlsx',
                                ])
                                ->disk('local')
                                ->directory('temp-voucher-imports')
                                ->required()
                                ->helperText('Required columns: "Code" (or "Voucher Code") and "Name" (or "Voucher Name").'),
                        ]),

                    Section::make('2. Discount & Pricing Configuration')
                        ->description('These settings will be applied to all vouchers in the uploaded file.')
                        ->schema([
                            Grid::make(2)->schema([
                                Select::make('discount_type')
                                    ->label('Discount Type')
                                    ->options([
                                        'percentage' => 'Percentage (%)',
                                        'fixed' => 'Fixed Amount (PHP)',
                                    ])
                                    ->default('percentage')
                                    ->required()
                                    ->live(),

                                TextInput::make('discount_value')
                                    ->label('Discount Value')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0.01)
                                    ->step(0.01)
                                    ->default(10)
                                    ->suffix(fn (Forms\Get $get) => $get('discount_type') === 'percentage' ? '%' : ' PHP'),

                                TextInput::make('max_discount')
                                    ->label('Max Discount (PHP)')
                                    ->numeric()
                                    ->minValue(0.01)
                                    ->step(0.01)
                                    ->helperText('Optional: Maximum discount cap for percentage vouchers')
                                    ->visible(fn (Forms\Get $get) => $get('discount_type') === 'percentage'),

                                TextInput::make('min_booking_amount')
                                    ->label('Minimum Booking Amount (PHP)')
                                    ->numeric()
                                    ->minValue(0.01)
                                    ->step(0.01)
                                    ->helperText('Optional: Minimum total amount required to apply voucher'),

                                Select::make('eligible_scope')
                                    ->label('Discount Scope')
                                    ->options([
                                        'booking_total' => 'Entire Booking Total (Tickets + Stay + Vehicle)',
                                        'ticket_fare' => 'Ticket Fare Only',
                                        'vehicle' => 'Vehicle Only',
                                        'accommodation' => 'Accommodation Only',
                                    ])
                                    ->default('booking_total')
                                    ->required(),

                                TextInput::make('total_usage_limit')
                                    ->label('Total Usage Limit per Voucher')
                                    ->numeric()
                                    ->minValue(1)
                                    ->helperText('Optional: Maximum uses across all users (blank for unlimited)'),

                                DateTimePicker::make('start_at')
                                    ->label('Start Date & Time')
                                    ->helperText('Optional: Leave blank to activate immediately'),

                                DateTimePicker::make('end_at')
                                    ->label('Expiration Date & Time')
                                    ->helperText('Optional: Leave blank for no expiration'),
                            ]),
                        ]),

                    Section::make('3. Restrictions & Settings')
                        ->collapsible()
                        ->collapsed()
                        ->schema([
                            Grid::make(2)->schema([
                                Toggle::make('one_use_per_customer')
                                    ->label('One Use Per Customer')
                                    ->default(true),

                                Toggle::make('is_active')
                                    ->label('Active Immediately')
                                    ->default(true),

                                Toggle::make('is_hidden')
                                    ->label('Hidden Voucher')
                                    ->helperText('If enabled, will not appear in mobile voucher list (manual code entry only)')
                                    ->default(false),

                                Select::make('eligible_operator_id')
                                    ->label('Eligible Operator')
                                    ->relationship(name: 'eligibleOperator', titleAttribute: 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Optional: Restrict to a specific operator'),

                                TextInput::make('eligible_origin')
                                    ->label('Eligible Origin')
                                    ->maxLength(255)
                                    ->helperText('Optional origin port/airport'),

                                TextInput::make('eligible_destination')
                                    ->label('Eligible Destination')
                                    ->maxLength(255)
                                    ->helperText('Optional destination port/airport'),
                            ]),

                            Textarea::make('description')
                                ->label('Internal Notes')
                                ->columnSpanFull(),
                        ]),
                ])
                ->action(function (array $data, VoucherBulkImportService $importService): void {
                    $uploadedFilePath = $data['file'] ?? null;

                    if (! $uploadedFilePath) {
                        Notification::make()
                            ->title('File Required')
                            ->body('Please select a spreadsheet file to upload.')
                            ->danger()
                            ->send();
                        return;
                    }

                    try {
                        $fullPath = Storage::disk('local')->path($uploadedFilePath);

                        $result = $importService->import($fullPath, $data);

                        // Clean up temp file
                        Storage::disk('local')->delete($uploadedFilePath);

                        if ($result['created'] > 0 && $result['skipped'] === 0) {
                            Notification::make()
                                ->title('Vouchers Imported Successfully')
                                ->body("Successfully created {$result['created']} vouchers.")
                                ->success()
                                ->send();
                        } elseif ($result['created'] > 0 && $result['skipped'] > 0) {
                            $sampleErrors = implode(', ', array_slice($result['errors'], 0, 3));
                            Notification::make()
                                ->title('Import Completed with Notices')
                                ->body("Created {$result['created']} vouchers. Skipped {$result['skipped']} invalid/existing items. ({$sampleErrors})")
                                ->warning()
                                ->send();
                        } else {
                            $errorMsg = ! empty($result['errors']) ? implode(' ', array_slice($result['errors'], 0, 3)) : 'No valid voucher rows found in file.';
                            Notification::make()
                                ->title('Import Failed')
                                ->body($errorMsg)
                                ->danger()
                                ->send();
                        }
                    } catch (Throwable $e) {
                        Notification::make()
                            ->title('Import Error')
                            ->body('An error occurred while processing the file: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\Action::make('slaVoucherSettings')
                ->label('2-Hour Guarantee Settings')
                ->icon('heroicon-o-clock')
                ->color('warning')
                ->modalHeading('Verification SLA Guarantee Voucher Settings')
                ->modalDescription('Configure the automatic 90-day voucher reward given to clients when their booking is not handled/verified within the SLA window.')
                ->modalSubmitActionLabel('Save Settings')
                ->fillForm(function (): array {
                    $settings = PaymentSetting::current();
                    return [
                        'sla_voucher_enabled' => $settings->isSlaVoucherEnabled(),
                        'sla_voucher_hours' => $settings->getSlaVoucherHours(),
                        'sla_voucher_amount' => $settings->getSlaVoucherAmount(),
                    ];
                })
                ->form([
                    Toggle::make('sla_voucher_enabled')
                        ->label('Enable Verification Guarantee Reward')
                        ->helperText('Automatically issue a compensation voucher if booking is not handled within the time limit.')
                        ->default(true),
                    TextInput::make('sla_voucher_hours')
                        ->label('Handling Window (Hours)')
                        ->helperText('Hours after proof submission before reward triggers.')
                        ->numeric()
                        ->minValue(1)
                        ->suffix('hours')
                        ->required(),
                    TextInput::make('sla_voucher_amount')
                        ->label('Voucher Reward Amount (₱)')
                        ->helperText('Flat amount for the generated 90-day voucher.')
                        ->numeric()
                        ->prefix('₱')
                        ->minValue(0)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    PaymentSetting::current()->update([
                        'sla_voucher_enabled' => $data['sla_voucher_enabled'] ?? true,
                        'sla_voucher_hours' => $data['sla_voucher_hours'] ?? 2,
                        'sla_voucher_amount' => $data['sla_voucher_amount'] ?? 500,
                    ]);
                    PaymentSetting::bust();

                    Notification::make()
                        ->title('Guarantee voucher settings saved')
                        ->success()
                        ->send();
                }),

            Actions\CreateAction::make(),
        ];
    }

    /**
     * Download sample CSV template for bulk voucher import.
     */
    protected function downloadCsvTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="amiga_bulk_vouchers_template.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');

            // Header
            fputcsv($handle, ['Code', 'Name', 'Notes']);

            // Sample rows
            fputcsv($handle, ['SUMMER2026_01', 'Summer Holiday Promo 01', 'Special partner campaign']);
            fputcsv($handle, ['SUMMER2026_02', 'Summer Holiday Promo 02', 'Special partner campaign']);
            fputcsv($handle, ['SUMMER2026_03', 'Summer Holiday Promo 03', 'Special partner campaign']);
            fputcsv($handle, ['PROMO_FERRY_VIP', 'VIP Passenger Discount', 'Exclusive voucher code']);
            fputcsv($handle, ['AMIGA_TRAVEL_100', 'Travel Expo 2026 Promo', 'Event giveaway']);

            fclose($handle);
        }, 200, $headers);
    }
}

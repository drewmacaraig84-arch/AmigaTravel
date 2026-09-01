<?php

namespace App\Filament\Resources\VoucherResource\Pages;

use App\Filament\Resources\VoucherResource;
use App\Models\Voucher;
use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewVoucher extends ViewRecord
{
    protected static string $resource = VoucherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('clone')
                ->label('Clone Voucher')
                ->icon('heroicon-o-document-duplicate')
                ->color('primary')
                ->modalHeading(fn (Voucher $record) => "Clone Voucher: {$record->name}")
                ->modalDescription('Specify only the new Voucher Code and Name. All other properties (discount value, type, limits, dates, restrictions) will be automatically copied.')
                ->modalSubmitActionLabel('Clone Voucher')
                ->form([
                    TextInput::make('name')
                        ->label('New Voucher Name')
                        ->required()
                        ->maxLength(255)
                        ->default(fn (Voucher $record) => $record->name . ' (Copy)'),
                    TextInput::make('code')
                        ->label('New Voucher Code')
                        ->required()
                        ->unique(table: Voucher::class, column: 'code')
                        ->maxLength(50)
                        ->regex('/^[A-Za-z0-9_-]+$/')
                        ->helperText('Only letters, numbers, underscores, and hyphens (automatically capitalized)')
                        ->dehydrateStateUsing(fn ($state) => strtoupper(trim((string) $state)))
                        ->default(fn (Voucher $record) => strtoupper($record->code . '_COPY')),
                ])
                ->action(function (Voucher $record, array $data): void {
                    $clone = $record->replicate([
                        'created_at',
                        'updated_at',
                    ]);

                    $clone->name = trim($data['name']);
                    $clone->code = strtoupper(trim($data['code']));
                    $clone->save();

                    Notification::make()
                        ->title('Voucher Cloned Successfully')
                        ->body("Voucher '{$clone->name}' ({$clone->code}) has been created with all settings duplicated.")
                        ->success()
                        ->send();

                    $this->redirect(VoucherResource::getUrl('view', ['record' => $clone]));
                }),
            Actions\DeleteAction::make(),
        ];
    }
}

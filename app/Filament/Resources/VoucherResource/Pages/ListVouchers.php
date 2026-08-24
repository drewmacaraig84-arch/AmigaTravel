<?php

namespace App\Filament\Resources\VoucherResource\Pages;

use App\Filament\Resources\VoucherResource;
use App\Models\PaymentSetting;
use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListVouchers extends ListRecords
{
    protected static string $resource = VoucherResource::class;

    protected function getHeaderActions(): array
    {
        return [
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
}


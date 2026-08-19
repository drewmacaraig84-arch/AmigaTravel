<?php

namespace App\Filament\Pages;

use App\Models\PaymentSetting;
use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class ManagePaymentSettings extends Page implements HasForms
{
    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user instanceof User && $user->hasAdminPermission('payment_settings');
    }
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 20;
    protected static ?string $navigationLabel = 'Payment Settings';

    protected static ?string $title = 'Payment Settings';

    protected static string $view = 'filament.pages.manage-payment-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = PaymentSetting::current();

        $this->form->fill([
            'web_admin_fee'                         => $settings->web_admin_fee,
            'short_haul_web_admin_fee'              => $settings->short_haul_web_admin_fee,
            'fee_per_accommodation'                 => $settings->fee_per_accommodation,
            'transaction_fee'                       => $settings->transaction_fee,
            'short_haul_transaction_fee'            => $settings->short_haul_transaction_fee,
            'revalidation_fee'                      => $settings->revalidation_fee,
            'qr_code_path'                          => $settings->qr_code_path,
            'ferry_before_departure_surcharge_pct'  => $settings->ferry_before_departure_surcharge_pct,
            'ferry_after_departure_surcharge_pct'   => $settings->ferry_after_departure_surcharge_pct,
            'airline_before_departure_surcharge_pct' => $settings->airline_before_departure_surcharge_pct,
            'rebook_ferry_before_departure_surcharge_pct' => $settings->rebook_ferry_before_departure_surcharge_pct,
            'rebook_ferry_after_departure_surcharge_pct'  => $settings->rebook_ferry_after_departure_surcharge_pct,
            'rebook_airline_before_departure_surcharge_pct' => $settings->rebook_airline_before_departure_surcharge_pct,
            'sla_voucher_enabled'                   => $settings->isSlaVoucherEnabled(),
            'sla_voucher_hours'                     => $settings->getSlaVoucherHours(),
            'sla_voucher_amount'                    => $settings->getSlaVoucherAmount(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Service Fee')
                    ->description('Added to every booking\'s total on the final review page, before payment. Short haul applies to trips < 5 hours; Long haul applies to trips ≥ 5 hours.')
                    ->schema([
                        TextInput::make('web_admin_fee')
                            ->label('Web Admin Fee (Long Haul ≥ 5h) (₱)')
                            ->helperText('Charged for every adult and child on trips 5 hours or longer. Default: ₱175.00.')
                            ->numeric()
                            ->prefix('₱')
                            ->minValue(0)
                            ->required(),
                        TextInput::make('short_haul_web_admin_fee')
                            ->label('Web Admin Fee (Short Haul < 5h) (₱)')
                            ->helperText('Charged for every adult and child on trips under 5 hours. Default: ₱30.00.')
                            ->numeric()
                            ->prefix('₱')
                            ->minValue(0)
                            ->required(),
                        TextInput::make('transaction_fee')
                            ->label('Transaction Fee (Long Haul ≥ 5h) (₱)')
                            ->helperText('Charged per booking transaction on trips 5 hours or longer. Default: ₱345.00.')
                            ->numeric()
                            ->prefix('₱')
                            ->minValue(0)
                            ->required(),
                        TextInput::make('short_haul_transaction_fee')
                            ->label('Transaction Fee (Short Haul < 5h) (₱)')
                            ->helperText('Charged per booking transaction on trips under 5 hours. Default: ₱70.00.')
                            ->numeric()
                            ->prefix('₱')
                            ->minValue(0)
                            ->required(),
                        TextInput::make('fee_per_accommodation')
                            ->label('Fee per hotel (₱)')
                            ->helperText('Charged for each hotel the client selects.')
                            ->numeric()
                            ->prefix('₱')
                            ->minValue(0)
                            ->required(),
                        TextInput::make('revalidation_fee')
                            ->label('Revalidation Fee (₱)')
                            ->helperText('Charged per passenger for rebooking.')
                            ->numeric()
                            ->prefix('₱')
                            ->minValue(0)
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Refund Policy')
                    ->description('Surcharge percentages applied to the ticket base price when a customer cancels. Web Admin Fee and Transaction Fee are always non-refundable.')
                    ->schema([
                        TextInput::make('ferry_before_departure_surcharge_pct')
                            ->label('Ferry — Before Departure Surcharge (%)')
                            ->helperText('Applies to 2GO and Starlite cancellations before departure. Default: 25%.')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->required(),
                        TextInput::make('ferry_after_departure_surcharge_pct')
                            ->label('Ferry — After Departure Surcharge (%) — Starlite Only')
                            ->helperText('Applies to Starlite-only cancellations after departure (within 10 min grace). 2GO has no after-departure refund. Default: 40%.')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->required(),
                        TextInput::make('airline_before_departure_surcharge_pct')
                            ->label('Airline — Before Departure Surcharge (%)')
                            ->helperText('Applies to airline cancellations before departure. No refund is given after departure for airlines. Default: 40%.')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->required(),
                    ])
                    ->columns(3),

                Section::make('Rebooking Policy')
                    ->description('Surcharge percentages applied to the ticket base price when a customer reschedules their booking.')
                    ->schema([
                        TextInput::make('rebook_ferry_before_departure_surcharge_pct')
                            ->label('Ferry — Before Departure Rebook Surcharge (%)')
                            ->helperText('Applies to 2GO and Starlite rebookings before departure. Default: 15%.')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->required(),
                        TextInput::make('rebook_ferry_after_departure_surcharge_pct')
                            ->label('Ferry — After Departure Rebook Surcharge (%)')
                            ->helperText('Applies to Starlite-only rebookings after departure. Default: 35%.')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->required(),
                        TextInput::make('rebook_airline_before_departure_surcharge_pct')
                            ->label('Airline — Before Departure Rebook Surcharge (%)')
                            ->helperText('Applies to airline rebookings before departure. Default: 15%.')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->required(),
                    ])
                    ->columns(3),

                Section::make('Verification SLA Guarantee')
                    ->description('Automatically issues a flat-amount compensation voucher to the customer if their submitted booking is not confirmed/verified within the specified time window.')
                    ->schema([
                        \Filament\Forms\Components\Toggle::make('sla_voucher_enabled')
                            ->label('Enable Verification SLA Guarantee Reward')
                            ->helperText('When enabled, unverified bookings past the handling window receive an automatic non-expiring voucher.')
                            ->default(true),
                        TextInput::make('sla_voucher_hours')
                            ->label('Handling Window (Hours)')
                            ->helperText('Number of hours after proof of payment submission before voucher reward triggers. Default: 2 hours.')
                            ->numeric()
                            ->minValue(1)
                            ->suffix('hours')
                            ->required(),
                        TextInput::make('sla_voucher_amount')
                            ->label('Voucher Reward Amount (₱)')
                            ->helperText('Flat amount of the non-expiring voucher issued to the client. Default: ₱500.00.')
                            ->numeric()
                            ->prefix('₱')
                            ->minValue(0)
                            ->required(),
                    ])
                    ->columns(3),

                Section::make('Payment QR Code')
                    ->description('This single QR code (e.g. your GCash QR) is shown to every client on the payment page.')
                    ->schema([
                        FileUpload::make('qr_code_path')
                            ->label('QR code image')
                            ->image()
                            ->disk('public')
                            ->directory('payment-qr')
                            ->visibility('public')
                            ->maxFiles(1)
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg', 'image/webp'])
                            ->dehydrateStateUsing(fn ($state) => is_array($state) ? ($state[0] ?? null) : $state),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        PaymentSetting::current()->update([
            'web_admin_fee'                         => $state['web_admin_fee'],
            'short_haul_web_admin_fee'              => $state['short_haul_web_admin_fee'],
            'fee_per_accommodation'                 => $state['fee_per_accommodation'],
            'transaction_fee'                       => $state['transaction_fee'],
            'short_haul_transaction_fee'            => $state['short_haul_transaction_fee'],
            'revalidation_fee'                      => $state['revalidation_fee'],
            'qr_code_path'                          => $state['qr_code_path'],
            'ferry_before_departure_surcharge_pct'  => $state['ferry_before_departure_surcharge_pct'],
            'ferry_after_departure_surcharge_pct'   => $state['ferry_after_departure_surcharge_pct'],
            'airline_before_departure_surcharge_pct' => $state['airline_before_departure_surcharge_pct'],
            'rebook_ferry_before_departure_surcharge_pct' => $state['rebook_ferry_before_departure_surcharge_pct'],
            'rebook_ferry_after_departure_surcharge_pct'  => $state['rebook_ferry_after_departure_surcharge_pct'],
            'rebook_airline_before_departure_surcharge_pct' => $state['rebook_airline_before_departure_surcharge_pct'],
            'sla_voucher_enabled'                   => $state['sla_voucher_enabled'],
            'sla_voucher_hours'                     => $state['sla_voucher_hours'],
            'sla_voucher_amount'                    => $state['sla_voucher_amount'],
        ]);
        PaymentSetting::bust(); // Clear cached payment settings

        Notification::make()
            ->title('Payment settings saved')
            ->success()
            ->send();
    }
}

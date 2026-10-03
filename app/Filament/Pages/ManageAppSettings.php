<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Models\WebsiteSetting;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class ManageAppSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-device-phone-mobile';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 25;
    protected static ?string $navigationLabel = 'Mobile App Settings';
    protected static ?string $title = 'Mobile App & Maintenance Break';
    protected static string $view = 'filament.pages.manage-app-settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user instanceof User && ($user->isAdmin() || $user->hasAdminPermission('website_settings'));
    }

    public function mount(): void
    {
        $settings = WebsiteSetting::getAppMaintenanceSettings();

        $this->form->fill([
            'is_active' => $settings['is_active'],
            'title' => $settings['title'],
            'message' => $settings['message'],
            'duration_value' => $settings['duration_value'],
            'duration_unit' => $settings['duration_unit'],
            'starts_at' => $settings['starts_at'] ?? now()->toDateTimeString(),
            'allow_browsing' => $settings['allow_browsing'],
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('App Maintenance Break')
                    ->description('Temporarily pause mobile app transactions and schedules during system updates or server maintenance.')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Enable Maintenance Break')
                            ->helperText('When enabled, the mobile app displays an announcement. Booking, rebooking, cancellations, schedule lookups, and ticket actions are temporarily stopped while allowing users to browse.')
                            ->onColor('danger')
                            ->offColor('gray')
                            ->reactive(),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('duration_value')
                                    ->label('Estimated Duration Value')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(999)
                                    ->default(45)
                                    ->required(),

                                Select::make('duration_unit')
                                    ->label('Duration Unit')
                                    ->options([
                                        'minutes' => 'Minutes',
                                        'hours' => 'Hours',
                                    ])
                                    ->default('minutes')
                                    ->required(),

                                DateTimePicker::make('starts_at')
                                    ->label('Maintenance Start Time')
                                    ->default(now())
                                    ->seconds(false),
                            ]),

                        TextInput::make('title')
                            ->label('Announcement Title')
                            ->default('Scheduled System Maintenance')
                            ->placeholder('e.g. Scheduled System Maintenance')
                            ->maxLength(100)
                            ->required(),

                        Textarea::make('message')
                            ->label('Maintenance Message')
                            ->default('We are currently updating our systems to serve you better. Schedules, bookings, and ticket actions are temporarily paused. Thank you for your patience!')
                            ->rows(3)
                            ->maxLength(500)
                            ->required(),

                        Toggle::make('allow_browsing')
                            ->label('Allow General Browsing')
                            ->helperText('Allow mobile app travelers to view destinations, tour cards, and company info without mutating data.')
                            ->default(true),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        WebsiteSetting::setAppMaintenanceSettings($state);

        $isActive = (bool) ($state['is_active'] ?? false);
        $statusText = $isActive ? 'Maintenance Break is now ACTIVE' : 'Maintenance Break has been DEACTIVATED';

        Notification::make()
            ->title($statusText)
            ->body($isActive
                ? 'Mobile app operations, schedules, and bookings are now safely paused. Estimated duration: ' . $state['duration_value'] . ' ' . $state['duration_unit'] . '.'
                : 'Mobile app operations have resumed normally.')
            ->color($isActive ? 'danger' : 'success')
            ->send();
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceCancellationResource\Pages;
use App\Models\FerryRoute;
use App\Models\Schedule;
use App\Models\ServiceCancellation;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ServiceCancellationResource extends Resource
{
    protected static ?string $model = ServiceCancellation::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationGroup = 'Bookings';

    protected static ?string $navigationLabel = 'Service Cancellations';

    protected static ?int $navigationSort = 40;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user instanceof User && (
            $user->canAccessFeature('service_cancellations') ||
            $user->canAccessFeature('schedules') ||
            $user->canAccessFeature('ferry_airline')
        );
    }

    public static function form(Form $form): Form
    {
        $activeOperators = FerryRoute::activeOperatorsFor();

        return $form
            ->schema([
                Section::make('Cancellation Scope & Details')
                    ->description('Specify the carrier, service type, and disruption scope.')
                    ->schema([
                        Select::make('autofill_schedule_id')
                            ->label('Quick Fill from Schedule (Optional)')
                            ->placeholder('Select a schedule to auto-fill the fields below')
                            ->options(function () {
                                return Schedule::query()
                                    ->active()
                                    ->with('ferryRoute')
                                    ->get()
                                    ->mapWithKeys(fn (Schedule $s) => [
                                        $s->id => "{$s->ferryRoute?->origin} → {$s->ferryRoute?->destination} | {$s->service_name} ({$s->departure_time->format('M d, Y H:i')})",
                                    ]);
                            })
                            ->searchable()
                            ->dehydrated(false)
                            ->live()
                            ->afterStateUpdated(function ($state, \Filament\Forms\Set $set, Get $get) {
                                if ($state) {
                                    $schedule = Schedule::with('ferryRoute')->find($state);
                                    if ($schedule && $schedule->ferryRoute) {
                                        $set('service_type', $schedule->ferryRoute->mode);
                                        $set('carrier', $schedule->ferryRoute->operatorRecord?->name);
                                        $set('ferry_route_id', $schedule->ferryRoute->id);
                                        $set('vehicle_id', $schedule->ferryRoute->vehicle_id);
                                        if ($get('scope') === 'specific_schedule') {
                                            $set('schedule_id', $schedule->id);
                                            $set('affected_date', $schedule->departure_time->format('Y-m-d'));
                                        }
                                    }
                                }
                            })
                            ->columnSpanFull(),

                        Grid::make(3)->schema([
                            Select::make('service_type')
                                ->label('Service Type')
                                ->options([
                                    'ferry' => '🚢 Ferry Voyage',
                                    'airline' => '✈️ Airline Flight',
                                ])
                                ->required()
                                ->default('ferry')
                                ->live(),

                            Select::make('carrier')
                                ->label('Carrier / Operator')
                                ->options(function (Get $get) {
                                    $mode = $get('service_type');
                                    return \App\Models\Operator::query()
                                        ->when($mode, fn($q) => $q->where('mode', $mode))
                                        ->where('is_active', true)
                                        ->pluck('name', 'name')
                                        ->toArray();
                                })
                                ->searchable()
                                ->required(fn (Get $get) => empty($get('ferry_route_id')) && empty($get('vehicle_id')))
                                ->live(),

                            Select::make('scope')
                                ->label('Cancellation Scope')
                                ->options([
                                    'specific_schedule' => 'Specific Schedule & Date',
                                    'carrier_date' => 'Carrier on Single Date',
                                    'carrier_date_range' => 'Carrier across Date Range',
                                ])
                                ->required()
                                ->default('specific_schedule')
                                ->live(),
                        ]),

                        Grid::make(2)->schema([
                            Select::make('ferry_route_id')
                                ->label('Specific Route (Optional)')
                                ->options(function (Get $get) {
                                    $mode = $get('service_type');
                                    $carrier = $get('carrier');
                                    return FerryRoute::query()
                                        ->active()
                                        ->when($mode, fn($q) => $q->where('mode', $mode))
                                        ->when($carrier, fn($q) => $q->whereHas('operatorRecord', fn($qop) => $qop->where('name', $carrier)))
                                        ->get()
                                        ->mapWithKeys(fn (FerryRoute $r) => [$r->id => $r->label]);
                                })
                                ->searchable()
                                ->live(),

                            Select::make('vehicle_id')
                                ->label('Specific Vehicle (Optional)')
                                ->options(function (Get $get) {
                                    $mode = $get('service_type');
                                    $carrier = $get('carrier');
                                    return \App\Models\Vehicle::query()
                                        ->active()
                                        ->when($mode, fn($q) => $q->where('type', $mode))
                                        ->when($carrier, fn($q) => $q->whereHas('operatorRecord', fn($qop) => $qop->where('name', $carrier)))
                                        ->get()
                                        ->mapWithKeys(fn (\App\Models\Vehicle $v) => [$v->id => $v->full_name]);
                                })
                                ->searchable()
                                ->live(),
                        ]),

                        Grid::make(2)->schema([
                            Select::make('schedule_id')
                                ->label('Target Schedule')
                                ->options(function (Get $get) {
                                    $carrier = $get('carrier');
                                    $mode = $get('service_type');

                                    return Schedule::query()
                                        ->active()
                                        ->whereHas('ferryRoute', function (Builder $q) use ($carrier, $mode) {
                                            if ($carrier) $q->whereHas('operatorRecord', fn($qop) => $qop->where('name', $carrier));
                                            if ($mode) $q->where('mode', $mode);
                                        })
                                        ->get()
                                        ->mapWithKeys(fn (Schedule $s) => [
                                            $s->id => "{$s->ferryRoute?->origin} → {$s->ferryRoute?->destination} | {$s->service_name} ({$s->formatted_departure})",
                                        ]);
                                })
                                ->visible(fn (Get $get) => $get('scope') === 'specific_schedule')
                                ->required(fn (Get $get) => $get('scope') === 'specific_schedule')
                                ->searchable()
                                ->live(),

                            DatePicker::make('affected_date')
                                ->label('Affected Date')
                                ->required(fn (Get $get) => in_array($get('scope'), ['specific_schedule', 'carrier_date']))
                                ->visible(fn (Get $get) => in_array($get('scope'), ['specific_schedule', 'carrier_date']))
                                ->default(now()->toDateString())
                                ->live(),

                            DatePicker::make('start_date')
                                ->label('Start Date')
                                ->required(fn (Get $get) => $get('scope') === 'carrier_date_range')
                                ->visible(fn (Get $get) => $get('scope') === 'carrier_date_range')
                                ->default(now()->toDateString())
                                ->live(),

                            DatePicker::make('end_date')
                                ->label('End Date')
                                ->required(fn (Get $get) => $get('scope') === 'carrier_date_range')
                                ->visible(fn (Get $get) => $get('scope') === 'carrier_date_range')
                                ->default(now()->addDays(2)->toDateString())
                                ->live(),
                        ]),
                    ]),

                Section::make('Disruption Reason & Resume Date')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('reason_category')
                                ->label('Reason Category')
                                ->options([
                                    'weather' => 'Severe Weather',
                                    'storm' => 'Typhoon / Storm Advisory',
                                    'carrier_cancellation' => 'Carrier Technical Cancellation',
                                    'safety_issue' => 'Maritime / Aviation Safety Issue',
                                    'other' => 'Other Operational Disruptions',
                                ])
                                ->required()
                                ->default('weather'),

                            DatePicker::make('resume_date')
                                ->label('Service Resume Date (Optional / TBA)')
                                ->helperText('Leave empty if resumption date is unknown. You can declare the resume date later when travel clears to notify customers.')
                                ->nullable()
                                ->default(null),
                        ]),

                        Textarea::make('customer_message')
                            ->label('Customer-Facing Cancellation Message')
                            ->helperText('This message will be sent in emails, push notifications, and shown on the customer reschedule page.')
                            ->required()
                            ->rows(3)
                            ->default('Due to severe weather and safety advisories, your scheduled trip has been cancelled. Please choose a new travel date below at zero extra cost.'),

                        Textarea::make('internal_notes')
                            ->label('Internal Staff Notes')
                            ->helperText('Visible only to authorized admin/staff members.')
                            ->rows(2),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cancellation_code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                BadgeColumn::make('service_type')
                    ->label('Service')
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->colors([
                        'info' => 'ferry',
                        'success' => 'airline',
                    ]),

                TextColumn::make('carrier')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('scope')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'specific_schedule' => 'Specific Schedule',
                        'carrier_date' => 'Single Date',
                        'carrier_date_range' => 'Date Range',
                        default => $state,
                    })
                    ->badge(),

                TextColumn::make('reason_category')
                    ->label('Reason')
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state))),

                TextColumn::make('resume_date')
                    ->label('Resume Date')
                    ->date()
                    ->placeholder('To Be Announced (TBA)')
                    ->sortable(),

                TextColumn::make('affected_bookings_count')
                    ->label('Bookings')
                    ->counts('affectedBookings')
                    ->badge()
                    ->color('danger'),

                BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'active',
                        'success' => 'resolved',
                        'secondary' => 'cancelled',
                    ]),

                TextColumn::make('createdBy.name')
                    ->label('Created By')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('service_type')
                    ->options([
                        'ferry' => 'Ferry Voyage',
                        'airline' => 'Airline Flight',
                    ]),

                SelectFilter::make('scope')
                    ->options([
                        'specific_schedule' => 'Specific Schedule',
                        'carrier_date' => 'Single Date',
                        'carrier_date_range' => 'Date Range',
                    ]),

                SelectFilter::make('reason_category')
                    ->options([
                        'weather' => 'Severe Weather',
                        'storm' => 'Typhoon / Storm Advisory',
                        'carrier_cancellation' => 'Carrier Technical Cancellation',
                        'safety_issue' => 'Safety Issue',
                        'other' => 'Other',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('declareResumeDate')
                    ->label('Declare Resume Date')
                    ->icon('heroicon-m-megaphone')
                    ->color('success')
                    ->visible(fn (ServiceCancellation $record): bool => empty($record->resume_date))
                    ->form([
                        DatePicker::make('resume_date')
                            ->label('Official Service Resume Date')
                            ->helperText('Customers will be notified via email that operations are resuming and can pick replacement dates starting from this date.')
                            ->required()
                            ->minDate(now()),
                    ])
                    ->action(function (ServiceCancellation $record, array $data): void {
                        app(ServiceCancellationManager::class)->declareResumeDate($record, $data['resume_date']);
                        Notification::make()
                            ->title('Service Resume Date Declared')
                            ->body('Notification emails have been queued for all affected customers.')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServiceCancellations::route('/'),
            'create' => Pages\CreateServiceCancellation::route('/create'),
            'view' => Pages\ViewServiceCancellation::route('/{record}'),
            'edit' => Pages\EditServiceCancellation::route('/{record}/edit'),
        ];
    }
}

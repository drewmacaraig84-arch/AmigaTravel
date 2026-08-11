<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FerryRouteResource\Pages;
use App\Models\FerryRoute;
use App\Models\User;
use App\Models\Vehicle;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Database\Eloquent\Relations\Relation;

class FerryRouteResource extends Resource
{
    protected static ?string $model = FerryRoute::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationGroup = 'Travel';
    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user instanceof User && $user->hasAdminPermission('travel_routes');
    }

    public static function canCreate(): bool
    {
        return static::canAccess();
    }

    public static function canEdit($record): bool
    {
        return static::canAccess();
    }

    public static function canDelete($record): bool
    {
        return static::canAccess();
    }

    public static function canDeleteAny(): bool
    {
        return static::canAccess();
    }

    protected static ?string $navigationLabel = 'Routes and Schedule';

    protected static ?string $modelLabel = 'Route and Schedule';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['vehicle', 'schedules' => function ($query) {
                $query->select(['id', 'ferry_route_id', 'vehicle_name', 'departure_time', 'arrival_time', 'price', 'is_active'])
                    ->orderBy('departure_time');
            }]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('origin')
                    ->placeholder('e.g. Manila')
                    ->required()
                    ->maxLength(255),

                TextInput::make('destination')
                    ->placeholder('e.g. Boracay')
                    ->required()
                    ->maxLength(255),

                Select::make('mode')
                    ->label('Mode')
                    ->options([
                        'ferry' => 'Ferry',
                        'airline' => 'Airline',
                    ])
                    ->default('ferry')
                    ->reactive()
                    ->required()
                    ->afterStateUpdated(function (?string $state, callable $set) {
                        $set('vehicle_id', null);
                        $set('operator', null);
                    }),

                Select::make('trip_type')
                    ->label('Flight Scope (Domestic / International)')
                    ->options([
                        'local' => 'Local / Domestic',
                        'international' => 'International',
                    ])
                    ->default('local')
                    ->visible(fn (callable $get) => $get('mode') === 'airline')
                    ->required(fn (callable $get) => $get('mode') === 'airline')
                    ->helperText('Determines whether Local/Domestic or International baggage rates and rules apply to schedules under this airline route.'),

                Select::make('vehicle_id')
                    ->label('Vehicle')
                    ->options(fn (callable $get) => Vehicle::query()
                        ->when($get('mode'), fn ($query, $mode) => $query->where('type', $mode))
                        ->when($get('operator'), fn ($query, $operator) => $query->where('operator', $operator))
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->get()
                        ->mapWithKeys(fn (Vehicle $vehicle) => [$vehicle->id => "{$vehicle->name} ({$vehicle->vehicle_id}) - {$vehicle->operator}"])
                        ->toArray())
                    ->nullable()
                    ->reactive()
                    ->searchable()
                    ->afterStateHydrated(function ($state, callable $set, callable $get) {
                        if ($state) {
                            $set('operator', optional(Vehicle::find($state))->operator);

                            $schedules = $get('schedules') ?? [];
                            $vehicleName = optional(Vehicle::find($state))->name;
                            foreach ($schedules as $index => $schedule) {
                                $schedules[$index]['vehicle_name'] = $vehicleName;
                            }
                            $set('schedules', $schedules);
                        }
                    })
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        if ($state) {
                            $set('operator', optional(Vehicle::find($state))->operator);

                            $schedules = $get('schedules') ?? [];
                            $vehicleName = optional(Vehicle::find($state))->name;
                            foreach ($schedules as $index => $schedule) {
                                $schedules[$index]['vehicle_name'] = $vehicleName;
                            }
                            $set('schedules', $schedules);
                        } else {

                            $schedules = $get('schedules') ?? [];
                            foreach ($schedules as $index => $schedule) {
                                $schedules[$index]['vehicle_name'] = null;
                            }
                            $set('schedules', $schedules);
                        }
                    })
                    ->hint('Select a vehicle from the ferry/airline list'),

                Select::make('operator')
                    ->label('Operator')
                    ->options(fn (callable $get) => Vehicle::query()
                        ->when($get('mode'), fn ($query, $mode) => $query->where('type', $mode))
                        ->whereNotNull('operator')
                        ->orderBy('operator')
                        ->pluck('operator', 'operator')
                        ->unique()
                        ->toArray()
                    )
                    ->searchable()
                    ->reactive()
                    ->nullable()
                    ->afterStateUpdated(function (?string $state, callable $set) {
                        $set('vehicle_id', null);
                    }),

                Toggle::make('is_active')
                    ->label('Available for booking')
                    ->default(true),

                Section::make('Schedules for this Route')
                    ->description('Manage the schedules that belong to this route here. Changes are saved with the route.')
                    ->headerActions([
                        Action::make('add_schedule')
                            ->label('Add schedule')
                            ->icon('heroicon-m-plus')
                            ->button()
                            ->action(function (callable $get, callable $set) {
                                $schedules = $get('schedules') ?? [];
                                $vehicleId = $get('vehicle_id');
                                $vehicleName = $vehicleId ? optional(Vehicle::find($vehicleId))->name : null;
                                $key = \Illuminate\Support\Str::uuid()->toString();
                                $schedules[$key] = [
                                    'vehicle_name' => $vehicleName,
                                    'is_active' => true,
                                ];
                                $set('schedules', $schedules);
                            }),
                    ])
                    ->schema([
                        Repeater::make('schedules')
                            ->relationship('schedules')
                            ->label('')
                            ->schema(static::scheduleFormSchema())
                            ->defaultItems(0)
                            ->cloneable()
                            ->deletable()
                            ->addable(false)
                            ->collapsible()
                            ->collapsed()
                            ->extraItemActions([
                                Action::make('add_transport_class')
                                    ->icon('heroicon-m-plus-circle')
                                    ->label('Add transport class')
                                    ->tooltip('Add transport class')
                                    ->action(function (array $arguments, Repeater $component, callable $get): void {
                                        $itemKey = $arguments['item'];
                                        $state = $component->getState();

                                        $classes = $state[$itemKey]['scheduleTransportClasses'] ?? [];
                                        $classes[\Illuminate\Support\Str::uuid()->toString()] = [
                                            'additional_price' => 0,
                                            'tickets_available' => 50,
                                            'is_active' => true,
                                            'has_bed' => false,
                                        ];
                                        $state[$itemKey]['scheduleTransportClasses'] = $classes;

                                        $component->state($state);
                                        $component->collapsed(false, shouldMakeComponentCollapsible: false);
                                    })
                                    ->visible(fn (callable $get): bool => in_array($get('../../mode'), ['airline', 'ferry'], true)),
                                // Only use transport classes directly now; accommodations are handled through transport classes.
                            ])
                            ->itemLabel(function (array $state): ?string {
                                $parts = [];
                                $parts[] = $state['vehicle_name'] ?? 'New schedule';

                                if (!empty($state['departure_time']) && !empty($state['arrival_time'])) {
                                    $dep = \Carbon\Carbon::parse($state['departure_time'])->format('h:i A');
                                    $arr = \Carbon\Carbon::parse($state['arrival_time'])->format('h:i A');
                                    $parts[] = "{$dep} - {$arr}";
                                } elseif (!empty($state['departure_time'])) {
                                    $dep = \Carbon\Carbon::parse($state['departure_time'])->format('h:i A');
                                    $parts[] = "Dep: {$dep}";
                                }

                                if (isset($state['price']) && $state['price'] !== '') {
                                    $price = number_format((float) $state['price'], 2);
                                    $parts[] = "₱{$price}";
                                }

                                $classCount = is_array($state['scheduleTransportClasses'] ?? null) ? count($state['scheduleTransportClasses']) : 0;

                                if ($classCount > 0) {
                                    $parts[] = "{$classCount} " . ($classCount === 1 ? 'Class' : 'Classes');
                                }

                                return implode('  •  ', $parts);
                            })
                            ->mutateRelationshipDataBeforeFillUsing(function (array $data, callable $get): array {
                                if ($vehicleId = $get('../../vehicle_id')) {
                                    $vehicleName = optional(Vehicle::find($vehicleId))->name;
                                    $data['vehicle_name'] = $vehicleName;
                                    $data['service_name'] = $data['service_name'] ?? $vehicleName;
                                }

                                return $data;
                            })
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data, callable $get): array {
                                if ($vehicleId = $get('../../vehicle_id')) {
                                    $vehicleName = optional(Vehicle::find($vehicleId))->name;
                                    $data['vehicle_name'] = $vehicleName;
                                    $data['service_name'] = $data['service_name'] ?? $vehicleName;
                                }

                                return $data;
                            })
                            ->mutateRelationshipDataBeforeSaveUsing(function (array $data, callable $get): array {
                                if ($vehicleId = $get('../../vehicle_id')) {
                                    $vehicleName = optional(Vehicle::find($vehicleId))->name;
                                    $data['vehicle_name'] = $vehicleName;
                                    $data['service_name'] = $data['service_name'] ?? $vehicleName;
                                }

                                return $data;
                            })
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    protected static function scheduleFormSchema(): array
    {
        return [
            TextInput::make('vehicle_name')
                ->label('Vehicle')
                ->disabled()
                ->reactive()
                ->afterStateHydrated(function ($state, callable $set, callable $get) {
                    $vehicleId = $get('../../vehicle_id');

                    if ($vehicleId) {
                        $set('vehicle_name', optional(Vehicle::find($vehicleId))->name);
                    }
                })
                ->visible(fn (callable $get) => filled($get('../../vehicle_id')))
                ->nullable()
                ->maxLength(255),
                
            TextInput::make('plate_no')
                ->label('Plate No.')
                ->nullable()
                ->maxLength(255),

            DateTimePicker::make('departure_time')
                ->label('Departure time')
                ->seconds(false)
                ->required(),

            DateTimePicker::make('arrival_time')
                ->label('Arrival time')
                ->seconds(false)
                ->required(),

            TextInput::make('duration_minutes')
                ->label('Duration (minutes)')
                ->helperText('Optional — calculated from times if left blank.')
                ->numeric()
                ->minValue(1),

            TextInput::make('price')
                ->label('Reseller price per passenger (₱)')
                ->numeric()
                ->prefix('₱')
                ->minValue(0)
                ->required(),


            TextInput::make('availability_label')
                ->label('Availability note')
                ->placeholder('e.g. Available, Limited availability')
                ->maxLength(255),

            Toggle::make('is_active')
                ->label('Visible to clients when booking')
                ->default(true),
                
            Repeater::make('scheduleTransportClasses')
                ->relationship('scheduleTransportClasses')
                ->label('Transport Classes')
                ->schema([
                    Select::make('transport_class_id')
                        ->label('Transport Class')
                        ->options(fn (callable $get) => \App\Models\TransportClass::query()
                            ->when($get('../../../../operator'), fn ($query, $operator) => $query->where('operator', $operator))
                            // Intentionally skipping mode filter because the DB has ferry classes seeded as 'airline'
                            ->where('is_active', true)
                            ->orderBy('name')
                            ->get()
                            ->mapWithKeys(fn ($item) => [$item->id => $item->operator ? "{$item->operator} - {$item->name}" : $item->name])
                            ->toArray())
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if ($state) {
                                $tc = \App\Models\TransportClass::find($state);
                                if ($tc) {
                                    $set('transport_class_name', $tc->name);
                                    $price = ($tc->is_on_sale && $tc->sale_price !== null && $tc->sale_price > 0) ? $tc->sale_price : $tc->price;
                                    $set('additional_price', $price ?? 0);
                                    if ($tc->description) {
                                        $set('description', $tc->description);
                                    }
                                }
                            }
                        })
                        ->columnSpanFull(),

                    \Filament\Forms\Components\Textarea::make('description')
                        ->placeholder('Details about this transport class option')
                        ->rows(2)
                        ->columnSpanFull(),

                    TextInput::make('additional_price')
                        ->label('Additional Price (₱)')
                        ->numeric()
                        ->prefix('₱')
                        ->default(0)
                        ->minValue(0),

                    TextInput::make('rate_code')
                        ->label('Promo Rate Code')
                        ->placeholder('e.g. PROMO, EARLYBIRD')
                        ->maxLength(255),

                    TextInput::make('tickets_available')
                        ->label('Tickets Available')
                        ->numeric()
                        ->minValue(0)
                        ->default(50)
                        ->required(),

                    Toggle::make('is_promo')
                        ->label('Promotional Ticket (Non-refundable)')
                        ->helperText('Tickets in this class will not be eligible for refunds.')
                        ->live(),

                    \Filament\Forms\Components\DateTimePicker::make('promo_duration_start')
                        ->label('Promo Start Date & Time')
                        ->visible(fn (callable $get) => $get('is_promo') === true)
                        ->required(fn (callable $get) => $get('is_promo') === true),

                    \Filament\Forms\Components\DateTimePicker::make('promo_duration_end')
                        ->label('Promo End Date & Time')
                        ->visible(fn (callable $get) => $get('is_promo') === true)
                        ->required(fn (callable $get) => $get('is_promo') === true)
                        ->after('promo_duration_start'),

                    Toggle::make('has_bed')
                        ->label('Includes bed / berth')
                        ->helperText('Enable for transport classes that include sleeping berths.'),

                    Toggle::make('is_active')
                        ->label('Visible to clients when booking')
                        ->default(true),

                    TextInput::make('transport_class_name')->hidden(),
                ])
                ->columns(2)
                ->collapsible()
                ->columnSpanFull()
                ->itemLabel(fn (array $state): ?string => $state['transport_class_name'] ?? null)
                ->visible(fn (callable $get): bool => in_array($get('../../mode'), ['airline', 'ferry'], true)),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('origin')
                    ->sortable(),
                TextColumn::make('destination')
                    ->sortable(),
                TextColumn::make('vehicle.full_name')
                    ->label('Vehicle')
                    ->sortable(['name', 'vehicle_id']),
                TextColumn::make('operator')
                    ->label('Operator')
                    ->getStateUsing(fn (FerryRoute $record): ?string => $record->vehicle?->operator ?: $record->operator)
                    ->sortable(),
                TextColumn::make('mode')
                    ->label('Mode')
                    ->sortable(),
                TextColumn::make('trip_type')
                    ->label('Scope')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'international' => 'info',
                        'local' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'international' => 'International',
                        'local' => 'Domestic / Local',
                        default => 'Domestic / Local',
                    })
                    ->sortable(),
                TextColumn::make('schedules_count')
                    ->counts('schedules')
                    ->label('Schedules'),
                ToggleColumn::make('is_active')
                    ->label('Active'),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('global_search')
                    ->form([
                        TextInput::make('search')
                            ->placeholder('Search...')
                            ->prefixIcon('heroicon-m-magnifying-glass')
                            ->hiddenLabel(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['search'],
                            function (Builder $query, $search): Builder {
                                return $query->where(function ($q) use ($search) {
                                    $q->where('origin', 'like', "%{$search}%")
                                      ->orWhere('destination', 'like', "%{$search}%")
                                      ->orWhere('operator', 'like', "%{$search}%")
                                      ->orWhereHas('vehicle', fn($qv) => $qv->where('name', 'like', "%{$search}%")->orWhere('vehicle_id', 'like', "%{$search}%"));
                                });
                            }
                        );
                    }),
                Filter::make('origin_filter')
                    ->form([
                        TextInput::make('origin')
                            ->placeholder('Search origin...')
                            ->hiddenLabel(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['origin'],
                            fn (Builder $query, $origin): Builder => $query->where('origin', 'like', "%{$origin}%"),
                        );
                    }),
                Filter::make('destination_filter')
                    ->form([
                        TextInput::make('destination')
                            ->placeholder('Search destination...')
                            ->hiddenLabel(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['destination'],
                            fn (Builder $query, $destination): Builder => $query->where('destination', 'like', "%{$destination}%"),
                        );
                    }),
                Filter::make('vehicle_filter')
                    ->form([
                        TextInput::make('vehicle')
                            ->placeholder('Search vehicle...')
                            ->hiddenLabel(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['vehicle'],
                            fn (Builder $query, $vehicle): Builder => $query->whereHas('vehicle', fn ($q) => $q->where('name', 'like', "%{$vehicle}%")->orWhere('vehicle_id', 'like', "%{$vehicle}%")),
                        );
                    }),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->actionsColumnLabel('Action')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFerryRoutes::route('/'),
            'create' => Pages\CreateFerryRoute::route('/create'),
            'edit' => Pages\EditFerryRoute::route('/{record}/edit'),
        ];
    }
}

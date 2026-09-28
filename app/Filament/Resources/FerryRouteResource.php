<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FerryRouteResource\Pages;
use App\Filament\Resources\FerryRouteResource\RelationManagers\SchedulesRelationManager;
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

    protected static ?string $navigationGroup = 'Travel & Tours';
    protected static ?int $navigationSort = 20;

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
            ->with(['operatorRecord', 'vehicle.operatorRecord', 'schedules' => fn ($q) => $q->select('id', 'ferry_route_id', 'vehicle_name')]);
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
                        $set('operator_id', null);
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
                        ->with('operatorRecord')
                        ->when($get('mode'), fn ($query, $mode) => $query->where('type', $mode))
                        ->when($get('operator_id'), fn ($query, $operatorId) => $query->where('operator_id', $operatorId))
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->get()
                        ->mapWithKeys(fn (Vehicle $vehicle) => [$vehicle->id => "{$vehicle->name} ({$vehicle->vehicle_id}) - " . optional($vehicle->operatorRecord)->name])
                        ->toArray())
                    ->nullable()
                    ->reactive()
                    ->searchable()
                    ->afterStateHydrated(function ($state, callable $set) {
                        if ($state) {
                            $set('operator_id', optional(Vehicle::find($state))->operator_id);
                        }
                    })
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $set('operator_id', optional(Vehicle::find($state))->operator_id);
                        }
                    })
                    ->hint('Select a vehicle from the ferry/airline list'),

                Select::make('operator_id')
                    ->relationship('operatorRecord', 'name')
                    ->label('Operator')
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->nullable()
                    ->afterStateUpdated(function (?string $state, callable $set) {
                        $set('vehicle_id', null);
                    }),

                Toggle::make('is_active')
                    ->label('Available for booking')
                    ->default(true),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('origin')
                    ->sortable(),
                TextColumn::make('destination')
                    ->sortable(),
                TextColumn::make('vehicles_list')
                    ->label('Vessel / Vehicle')
                    ->getStateUsing(function (FerryRoute $record) {
                        $schedVehicles = $record->schedules
                            ->pluck('vehicle_name')
                            ->filter()
                            ->unique()
                            ->values();

                        if ($schedVehicles->isNotEmpty()) {
                            return $schedVehicles->all();
                        }

                        $defaultVehicle = optional($record->vehicle)->full_name;
                        return $defaultVehicle ? [$defaultVehicle] : ['—'];
                    })
                    ->badge()
                    ->color('info')
                    ->separator(', ')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('vehicle', fn ($vq) => $vq->where('name', 'like', "%{$search}%")->orWhere('vehicle_id', 'like', "%{$search}%"))
                            ->orWhereHas('schedules', fn ($sq) => $sq->where('vehicle_name', 'like', "%{$search}%"));
                    }),
                TextColumn::make('operator_display')
                    ->label('Operator')
                    ->getStateUsing(fn (FerryRoute $record) => $record->operator_display_name)
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function ($q) use ($search) {
                            $q->where('operator', 'like', "%{$search}%")
                              ->orWhereHas('operatorRecord', fn ($oq) => $oq->where('name', 'like', "%{$search}%"))
                              ->orWhereHas('vehicle', fn ($vq) => $vq->where('operator', 'like', "%{$search}%")->orWhereHas('operatorRecord', fn ($voq) => $voq->where('name', 'like', "%{$search}%")));
                        });
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy(
                            \App\Models\Operator::select('name')
                                ->whereColumn('operators.id', 'ferry_routes.operator_id'),
                            $direction
                        );
                    }),
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
                                      ->orWhereHas('operatorRecord', fn($qop) => $qop->where('name', 'like', "%{$search}%"))
                                      ->orWhereHas('vehicle', fn($qv) => $qv->where('name', 'like', "%{$search}%")->orWhere('vehicle_id', 'like', "%{$search}%"))
                                      ->orWhereHas('schedules', fn($qs) => $qs->where('service_name', 'like', "%{$search}%")->orWhere('vehicle_name', 'like', "%{$search}%")->orWhere('plate_no', 'like', "%{$search}%"));
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
                            fn (Builder $query, $vehicle): Builder => $query->where(function ($q) use ($vehicle) {
                                $q->whereHas('vehicle', fn ($qv) => $qv->where('name', 'like', "%{$vehicle}%")->orWhere('vehicle_id', 'like', "%{$vehicle}%"))
                                  ->orWhereHas('schedules', fn ($qs) => $qs->where('service_name', 'like', "%{$vehicle}%")->orWhere('vehicle_name', 'like', "%{$vehicle}%")->orWhere('plate_no', 'like', "%{$vehicle}%"));
                            }),
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
            SchedulesRelationManager::class,
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

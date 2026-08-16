<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleResource\Pages;
use App\Models\User;
use App\Models\Vehicle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Filament\Forms\Get;
use Illuminate\Support\Facades\Auth;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Transport Master Data';
    protected static ?int $navigationSort = 40;

    protected static ?string $navigationLabel = 'Ferry & Airline';
    protected static ?string $modelLabel = 'Vehicle';
    protected static ?string $pluralModelLabel = 'Ferry & Airline';

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user instanceof User && ($user->hasAdminPermission('ferry_airline') || $user->isSuperAdmin());
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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                ToggleButtons::make('type')
                    ->label('Vehicle Type')
                    ->options([
                        'ferry' => 'Ferry',
                        'airline' => 'Airline',
                    ])
                    ->grouped()
                    ->required()
                    ->live()
                    ->columnSpanFull(),

                TextInput::make('name')
                    ->label(fn (Get $get) => $get('type') === 'airline' ? 'Vehicle Model' : 'Vessel Name')
                    ->placeholder(fn (Get $get) => $get('type') === 'airline' ? 'e.g. Airbus A320' : 'e.g. MV Superferry 16')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                TextInput::make('vehicle_id')
                    ->label(fn (Get $get) => $get('type') === 'airline' ? 'Tail No.' : 'IMO Number')
                    ->placeholder(fn (Get $get) => $get('type') === 'airline' ? 'e.g. RP-C1234' : 'e.g. IMO 1234567')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                Select::make('operator_id')
                    ->relationship('operatorRecord', 'name')
                    ->label('Operator')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('capacity')
                    ->label('Passenger Capacity')
                    ->numeric()
                    ->minValue(1),

                Textarea::make('description')
                    ->label('Description')
                    ->placeholder('Additional details about this vehicle')
                    ->rows(3)
                    ->columnSpanFull(),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->label(fn ($livewire) => $livewire->vehicleType === 'airline' ? 'Vehicle Type' : 'Vessel Type')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'ferry' => '🚢 Ferry',
                        'airline' => '✈️ Airline',
                        default => ucfirst($state),
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ferry' => 'info',
                        'airline' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('name')
                    ->label(fn ($livewire) => $livewire->vehicleType === 'airline' ? 'Model' : 'Vessel Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('vehicle_id')
                    ->label(fn ($livewire) => $livewire->vehicleType === 'airline' ? 'Tail No.' : 'IMO No.')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('operatorRecord.name')
                    ->label('Operator')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('capacity')
                    ->label('Capacity')
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Active'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actionsColumnLabel('Action')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->disabled(fn (Vehicle $record) => $record->ferryRoutes()->exists()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVehicles::route('/'),
            'create' => Pages\CreateVehicle::route('/create'),
            'edit' => Pages\EditVehicle::route('/{record}/edit'),
        ];
    }
}

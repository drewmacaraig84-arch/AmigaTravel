<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AirlineBaggageRuleResource\Pages;
use App\Models\AirlineBaggageRule;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class AirlineBaggageRuleResource extends Resource
{
    protected static ?string $model = AirlineBaggageRule::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'Airline';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Baggage Settings';

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user instanceof User && (
            $user->hasAdminPermission('airline_baggage') ||
            $user->hasAdminPermission('airline_seats') ||
            $user->hasAdminPermission('ferry_airline') ||
            $user->isSuperAdmin()
        );
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
                Select::make('operator_id')
                    ->relationship('operatorRecord', 'name', fn ($query) => $query->where('mode', 'airline'))
                    ->label('Airline Operator')
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        $operator = \App\Models\Operator::find($state);
                        if ($operator) {
                            $set('operator_name', $operator->name);
                            $set('code', strtoupper($operator->name));
                            $set('logo', $operator->logo_path);
                        }
                    })
                    ->columnSpan(1),

                Select::make('trip_type')
                    ->label('Flight Scope')
                    ->options([
                        'local' => 'Local / Domestic',
                        'international' => 'International',
                    ])
                    ->default('local')
                    ->required()
                    ->columnSpan(1),

                TextInput::make('operator_name')
                    ->label('Operator Display Name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(1),

                TextInput::make('code')
                    ->label('Airline Code')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(1),

                TextInput::make('logo')
                    ->label('Logo Filename')
                    ->placeholder('e.g. operators/Pal-Logo.jfif')
                    ->maxLength(255)
                    ->columnSpanFull(),

                TextInput::make('weight')
                    ->label('Baggage Weight (e.g. 20 kg)')
                    ->placeholder('20 kg')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        $numeric = (int) filter_var($state, FILTER_SANITIZE_NUMBER_INT);
                        if ($numeric > 0) {
                            $set('weight_kg', $numeric);
                        }
                    })
                    ->columnSpan(1),

                TextInput::make('weight_kg')
                    ->label('Weight in KG (Numeric for sorting)')
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->columnSpan(1),

                TextInput::make('price')
                    ->label('Rate (₱)')
                    ->numeric()
                    ->prefix('₱')
                    ->minValue(0)
                    ->required()
                    ->columnSpan(1),

                TextInput::make('sort_order')
                    ->label('Sort order')
                    ->numeric()
                    ->default(0)
                    ->helperText('Lower numbers appear first')
                    ->columnSpan(1),

                Toggle::make('is_active')
                    ->label('Active / Visible to users')
                    ->default(true)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultGroup('operator_name')
            ->columns([
                TextColumn::make('operator_name')
                    ->label('Airline')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('trip_type')
                    ->label('Scope')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'local' => 'success',
                        'international' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'local' => 'Local / Domestic',
                        'international' => 'International',
                        default => ucfirst($state),
                    })
                    ->sortable(),

                TextColumn::make('weight')
                    ->label('Baggage Weight')
                    ->sortable(),

                TextColumn::make('price')
                    ->label('Rate (₱)')
                    ->money('PHP')
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),

                ToggleColumn::make('is_active')
                    ->label('Active')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('trip_type')
                    ->label('Filter by Flight Scope')
                    ->options([
                        'local' => 'Local / Domestic',
                        'international' => 'International',
                    ])
                    ->placeholder('All Scopes (Domestic & International)'),

                SelectFilter::make('operator_id')
                    ->relationship('operatorRecord', 'name', fn ($query) => $query->where('mode', 'airline'))
                    ->label('Filter by Airline Operator')
                    ->placeholder('All Operators'),
            ], layout: \Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->filtersFormColumns(2)
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('weight_kg');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAirlineBaggageRules::route('/'),
            'create' => Pages\CreateAirlineBaggageRule::route('/create'),
            'edit' => Pages\EditAirlineBaggageRule::route('/{record}/edit'),
        ];
    }
}

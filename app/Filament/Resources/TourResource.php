<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TourResource\Pages;
use App\Filament\Resources\TourResource\RelationManagers\DatesRelationManager;
use App\Models\Tour;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TourResource extends Resource
{
    protected static ?string $model = Tour::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Tour Packages';
    protected static ?string $pluralModelLabel = 'Tour Packages';
    protected static ?int $navigationSort = 10;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user instanceof User && $user->hasAdminPermission('tour_packages');
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
                Forms\Components\Tabs::make('Tour Details')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Basic Info')
                            ->schema([
                                Forms\Components\TextInput::make('tour_name')
                                    ->label('Tour Name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('promo')
                                    ->label('Promo Tag')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('country')
                                    ->label('Country')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('destinations')
                                    ->label('Destinations (semicolon-separated)')
                                    ->maxLength(500)
                                    ->nullable()
                                    ->default('')
                                    ->helperText('E.g., Singapore; Kuala Lumpur'),
                                Forms\Components\FileUpload::make('image')
                                    ->label('Image')
                                    ->image()
                                    ->disk('public')
                                    ->directory('tours')
                                    ->maxSize(7168) // 7MB
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                                    ->helperText('Max 7 MB. Accepted: JPG, PNG, WebP, GIF.')
                                    ->nullable(),
                                Forms\Components\TextInput::make('origin')
                                    ->label('Departure City')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('destination')
                                    ->label('Primary Destination')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Select::make('mode')
                                    ->label('Mode of Transport')
                                    ->options([
                                        'airline' => 'Airline',
                                        'ferry' => 'Ferry',
                                    ])
                                    ->required(),
                                Forms\Components\TextInput::make('airline')
                                    ->label('Airline')
                                    ->maxLength(255)
                                    ->helperText('Only for airline mode'),
                                Forms\Components\TextInput::make('price_per_pax')
                                    ->label('Price Per Pax')
                                    ->numeric()
                                    ->prefix('₱')
                                    ->required(),
                                Forms\Components\TextInput::make('sort_order')
                                    ->label('Sort Order')
                                    ->integer()
                                    ->default(0),
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true),
                                Forms\Components\Toggle::make('is_international')
                                    ->label('International Package')
                                    ->default(false),
                            ]),
                        Forms\Components\Tabs\Tab::make('Duration & Itinerary')
                            ->schema([
                                Forms\Components\TextInput::make('duration')
                                    ->label('Duration (e.g., 3D2N)')
                                    ->required()
                                    ->maxLength(20),
                                Forms\Components\TextInput::make('duration_days')
                                    ->label('Duration (days)')
                                    ->numeric()
                                    ->required()
                                    ->helperText('Number of days (used for return date calculation)')
                                    ->minValue(1),
                                Forms\Components\Textarea::make('day1')
                                    ->label('Day 1')
                                    ->rows(2),
                                Forms\Components\Textarea::make('day2')
                                    ->label('Day 2')
                                    ->rows(2),
                                Forms\Components\Textarea::make('day3')
                                    ->label('Day 3')
                                    ->rows(2),
                                Forms\Components\Textarea::make('day4')
                                    ->label('Day 4')
                                    ->rows(2),
                                Forms\Components\Textarea::make('day5')
                                    ->label('Day 5')
                                    ->rows(2),
                                Forms\Components\Textarea::make('day6')
                                    ->label('Day 6')
                                    ->rows(2),
                            ]),
                        Forms\Components\Tabs\Tab::make('Inclusions & Details')
                            ->schema([
                                Forms\Components\Textarea::make('inclusions')
                                    ->label('Inclusions')
                                    ->rows(4),
                                Forms\Components\Textarea::make('exclusions')
                                    ->label('Exclusions')
                                    ->rows(4),
                                Forms\Components\Textarea::make('highlights')
                                    ->label('Highlights')
                                    ->rows(4),
                                Forms\Components\TextInput::make('hotel')
                                    ->label('Hotel')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('meals')
                                    ->label('Meals')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('hand_carry')
                                    ->label('Hand Carry')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('check_in_baggage')
                                    ->label('Check-in Baggage')
                                    ->maxLength(255),
                                Forms\Components\Select::make('tour_guide')
                                    ->label('Tour Guide')
                                    ->options([
                                        'Yes' => 'Yes',
                                        'No' => 'No',
                                    ]),
                                Forms\Components\TextInput::make('travel_insurance')
                                    ->label('Travel Insurance')
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('remarks')
                                    ->label('Remarks')
                                    ->rows(3),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tour_name')
                    ->label('Tour Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('promo')
                    ->label('Promo')
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('country')
                    ->label('Country')
                    ->searchable(),
                Tables\Columns\TextColumn::make('origin')
                    ->label('Departure')
                    ->sortable(),
                Tables\Columns\TextColumn::make('destination')
                    ->label('Destination')
                    ->sortable(),
                Tables\Columns\TextColumn::make('mode')
                    ->label('Mode')
                    ->badge()
                    ->colors([
                        'primary' => 'airline',
                        'success' => 'ferry',
                    ]),
                Tables\Columns\TextColumn::make('duration')
                    ->label('Duration'),
                Tables\Columns\TextColumn::make('price_per_pax')
                    ->label('Price / Pax')
                    ->money('PHP')
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Sort')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('country', 'asc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
                Tables\Filters\SelectFilter::make('mode')
                    ->options([
                        'airline' => 'Airline',
                        'ferry' => 'Ferry',
                    ]),
                Tables\Filters\SelectFilter::make('country')
                    ->options(fn() => Tour::reorder()->whereNotNull('country')->where('country', '!=', '')->distinct()->orderBy('country')->pluck('country', 'country')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            DatesRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->ordered();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTours::route('/'),
            'create' => Pages\CreateTour::route('/create'),
            'edit' => Pages\EditTour::route('/{record}/edit'),
        ];
    }
}

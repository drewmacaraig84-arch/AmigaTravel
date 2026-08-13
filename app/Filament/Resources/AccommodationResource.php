<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccommodationResource\Pages;
use App\Models\Accommodation;
use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class AccommodationResource extends Resource
{
    protected static ?string $model = Accommodation::class;
    protected static ?string $slug = 'hotel';

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    protected static ?string $navigationGroup = 'Travel';
    protected static ?int $navigationSort = 1;
    protected static ?string $label = 'Hotel';
    protected static ?string $pluralLabel = 'Hotels';
    protected static bool $shouldRegisterNavigation = false;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user instanceof User && $user->hasAdminPermission('hotels');
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

    protected static ?string $navigationLabel = 'Hotels';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Hotel name')
                    ->placeholder('e.g. Deluxe Beachfront Room')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                TextInput::make('destination')
                    ->placeholder('e.g. Boracay, Manila')
                    ->maxLength(255),

                Select::make('operator_id')
                    ->relationship('operatorRecord', 'name')
                    ->label('Operator')
                    ->searchable()
                    ->preload(),

                Textarea::make('description')
                    ->placeholder('Room details, features, etc.')
                    ->rows(3)
                    ->columnSpanFull(),

                Textarea::make('amenities')
                    ->placeholder('e.g., WiFi, Air Conditioning, TV, Private Bathroom')
                    ->rows(2)
                    ->columnSpanFull()
                    ->helperText('Comma-separated amenities'),

                TextInput::make('price')
                    ->label('Price (₱)')
                    ->numeric()
                    ->prefix('₱')
                    ->minValue(0)
                    ->required(),

                Toggle::make('is_active')
                    ->label('Visible to clients when booking')
                    ->default(true),

                FileUpload::make('images')
                    ->label('Photos')
                    ->image()
                    ->disk('public')
                    ->multiple()
                    ->reorderable()
                    ->panelLayout('grid')
                    ->directory('accommodations')
                    ->visibility('public')
                    ->maxFiles(8)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('images')
                    ->label('Photo')
                    ->getStateUsing(fn (Accommodation $record) => $record->cover_image)
                    ->square(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('destination')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('operatorRecord.name')
                    ->label('Operator')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amenities')
                    ->limit(30)
                    ->tooltip(fn (Accommodation $record) => $record->amenities),
                TextColumn::make('price')
                    ->money('PHP')
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Active'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListAccommodations::route('/'),
            'create' => Pages\CreateAccommodation::route('/create'),
            'edit' => Pages\EditAccommodation::route('/{record}/edit'),
        ];
    }
}

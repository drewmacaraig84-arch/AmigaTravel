<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GraciaEarningRuleResource\Pages;
use App\Filament\Resources\GraciaEarningRuleResource\RelationManagers;
use App\Models\GraciaEarningRule;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class GraciaEarningRuleResource extends Resource
{
    protected static ?string $model = GraciaEarningRule::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static ?string $navigationGroup = 'Promotions & Rewards';
    protected static ?string $navigationLabel = 'Gracia Rules';
    protected static ?int $navigationSort = 30;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user instanceof User && $user->hasAdminPermission('gracia_rules');
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
                Forms\Components\Section::make('Rule Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('spend_threshold_centavos')
                            ->label('Spend Threshold (in centavos)')
                            ->numeric()
                            ->required()
                            ->default(100000)
                            ->helperText('100000 centavos = 1000 PHP'),
                        Forms\Components\TextInput::make('points_awarded')
                            ->numeric()
                            ->required()
                            ->default(5),
                        Forms\Components\Toggle::make('is_active')
                            ->required()
                            ->default(false),
                    ])->columns(2),
                Forms\Components\Section::make('Schedule & Notes')
                    ->schema([
                        Forms\Components\DateTimePicker::make('starts_at'),
                        Forms\Components\DateTimePicker::make('ends_at'),
                        Forms\Components\Textarea::make('internal_notes')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('spend_threshold_centavos')
                    ->label('Spend Threshold (PHP)')
                    ->formatStateUsing(fn (int $state): string => '₱' . number_format($state / 100, 2))
                    ->sortable(),
                Tables\Columns\TextColumn::make('points_awarded')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('starts_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ends_at')
                    ->dateTime()
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
            'index' => Pages\ListGraciaEarningRules::route('/'),
            'create' => Pages\CreateGraciaEarningRule::route('/create'),
            'edit' => Pages\EditGraciaEarningRule::route('/{record}/edit'),
        ];
    }
}

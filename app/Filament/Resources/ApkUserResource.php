<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApkUserResource\Pages;
use App\Filament\Resources\ApkUserResource\RelationManagers;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ApkUserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-device-phone-mobile';

    protected static ?string $navigationLabel = 'Mobile APK Users';

    protected static ?string $modelLabel = 'APK User';

    protected static ?string $pluralModelLabel = 'APK Users';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 20;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user instanceof User && $user->hasAdminPermission('mobile_apk_users');
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereNotNull('api_token');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('User Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Full Name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\DateTimePicker::make('created_at')
                            ->label('Date Registered')
                            ->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('4s')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('graciaBalance.current_points')
                    ->label('Gracia Points')
                    ->default(0)
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Full Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date Registered')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->getStateUsing(function (Model $record) {
                        $latestLogin = $record->loginHistories()->latest()->first();
                        if ($latestLogin && $latestLogin->updated_at > now()->subMinutes(15)) {
                            return 'Online';
                        }
                        return 'Offline';
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Online' => 'success',
                        'Offline' => 'gray',
                    }),
                Tables\Columns\TextColumn::make('install_source')
                    ->label('Download Root')
                    ->getStateUsing(function (Model $record) {
                        $latestLogin = $record->loginHistories()->whereNotNull('metadata')->latest()->first();
                        $source = $latestLogin?->metadata['install_source'] ?? null;
                        return match ($source) {
                            'play_store' => 'Google Play',
                            'app_gallery' => 'AppGallery',
                            'website' => 'Amiga Website',
                            'app_store' => 'App Store',
                            default => 'Amiga Website',
                        };
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Google Play' => 'success',
                        'AppGallery' => 'danger',
                        'Amiga Website' => 'info',
                        'App Store' => 'warning',
                        default => 'gray',
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('adjust_points')
                    ->label('Adjust Points')
                    ->icon('heroicon-o-plus-circle')
                    ->form([
                        Forms\Components\TextInput::make('points')
                            ->label('Points (use negative to deduct)')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('reason')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->action(function (User $record, array $data) {
                        app(\App\Services\GraciaPointsService::class)->addManualAdjustment(
                            $record,
                            (int) $data['points'],
                            $data['reason'],
                            auth()->user()
                        );
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\BookingsRelationManager::class,
            RelationManagers\GraciaPointLedgersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApkUsers::route('/'),
            'view' => Pages\ViewApkUser::route('/{record}'),
        ];
    }
}

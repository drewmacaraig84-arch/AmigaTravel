<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VoucherResource\Pages;
use App\Filament\Resources\VoucherResource\RelationManagers\RedemptionsRelationManager;
use App\Models\Voucher;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class VoucherResource extends Resource
{
    protected static ?string $model = Voucher::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'Promotions & Rewards';
    protected static ?int $navigationSort = 10;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user instanceof User && $user->hasAdminPermission('vouchers');
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
                TextInput::make('name')
                    ->label('Voucher Name (Internal)')
                    ->required()
                    ->maxLength(255),
                TextInput::make('code')
                    ->label('Voucher Code')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(50)
                    ->regex('/^[A-Z0-9_-]+$/')
                    ->helperText('Only letters, numbers, underscores, and hyphens are allowed (case-insensitive)'),
                Textarea::make('description')
                    ->label('Internal Notes')
                    ->maxLength(65535),
                Select::make('discount_type')
                    ->label('Discount Type')
                    ->options([
                        'percentage' => 'Percentage',
                        'fixed' => 'Fixed Amount (PHP)',
                    ])
                    ->default('percentage')
                    ->required()
                    ->live(),
                TextInput::make('discount_value')
                    ->label('Discount Value')
                    ->required()
                    ->numeric()
                    ->minValue(0.01)
                    ->step(0.01)
                    ->suffix(fn (Forms\Get $get) => $get('discount_type') === 'percentage' ? '%' : ' PHP'),
                TextInput::make('max_discount')
                    ->label('Max Discount (PHP)')
                    ->numeric()
                    ->minValue(0.01)
                    ->step(0.01)
                    ->helperText('Optional: Only applicable for percentage discounts')
                    ->visible(fn (Forms\Get $get) => $get('discount_type') === 'percentage'),
                TextInput::make('min_booking_amount')
                    ->label('Minimum Booking Amount (PHP)')
                    ->numeric()
                    ->minValue(0.01)
                    ->step(0.01)
                    ->helperText('Optional'),
                DateTimePicker::make('start_at')
                    ->label('Start Date & Time')
                    ->helperText('Optional: Leave empty to start immediately'),
                DateTimePicker::make('end_at')
                    ->label('End Date & Time')
                    ->helperText('Optional: Leave empty for no expiration'),
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
                Toggle::make('is_hidden')
                    ->label('Hidden Voucher')
                    ->helperText('If enabled, this voucher will not appear in the mobile app\'s voucher list, but can still be applied if the code is typed manually.')
                    ->default(false),
                TextInput::make('total_usage_limit')
                    ->label('Total Usage Limit')
                    ->numeric()
                    ->minValue(1)
                    ->helperText('Optional: Leave empty for unlimited usage')
                    ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'The maximum number of times this voucher can be used across all customers. Leave empty for unlimited.'),
                Toggle::make('one_use_per_customer')
                    ->label('One Use Per Customer')
                    ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'If checked, each user account or email can only use this voucher once.')
                    ->default(true),
                TextInput::make('eligible_origin')
                    ->label('Eligible Origin')
                    ->maxLength(255)
                    ->helperText('Optional: Leave empty for all origins'),
                TextInput::make('eligible_destination')
                    ->label('Eligible Destination')
                    ->maxLength(255)
                    ->helperText('Optional: Leave empty for all destinations'),
                Select::make('eligible_schedule_id')
                    ->label('Eligible Schedule')
                    ->relationship(name: 'eligibleSchedule', titleAttribute: 'service_name')
                    ->getOptionLabelFromRecordUsing(fn (\App\Models\Schedule $record) => ($record->service_name ?? 'Schedule #' . $record->id) . ' (' . ($record->ferryRoute?->origin ?? '') . ' - ' . ($record->ferryRoute?->destination ?? '') . ')')
                    ->searchable(['service_name', 'origin', 'destination'])
                    ->preload()
                    ->helperText('Optional: Leave empty for all schedules'),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('code')
                    ->label('Code')
                    ->copyable()
                    ->copyMessage('Voucher code copied')
                    ->weight('bold'),
                Infolists\Components\TextEntry::make('name')
                    ->label('Name'),
                Infolists\Components\TextEntry::make('description')
                    ->label('Internal Notes'),
                Infolists\Components\TextEntry::make('discount_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'percentage' => 'info',
                        'fixed' => 'warning',
                        default => 'gray',
                    }),
                Infolists\Components\TextEntry::make('discount_value')
                    ->label('Discount')
                    ->formatStateUsing(fn (Voucher $record) => $record->discount_type === 'percentage' 
                        ? "{$record->discount_value}%" 
                        : "₱" . number_format($record->discount_value, 2)),
                Infolists\Components\TextEntry::make('max_discount')
                    ->label('Max Discount')
                    ->money('PHP')
                    ->visible(fn (Voucher $record) => $record->discount_type === 'percentage'),
                Infolists\Components\TextEntry::make('min_booking_amount')
                    ->label('Min Booking Amount')
                    ->money('PHP'),
                Infolists\Components\TextEntry::make('start_at')
                    ->label('Starts')
                    ->dateTime(),
                Infolists\Components\TextEntry::make('end_at')
                    ->label('Ends')
                    ->dateTime(),
                Infolists\Components\IconEntry::make('is_active')
                    ->label('Active')
                    ->boolean(),
                Infolists\Components\TextEntry::make('total_usage_limit')
                    ->label('Usage Limit')
                    ->default('Unlimited'),
                Infolists\Components\IconEntry::make('one_use_per_customer')
                    ->label('One Use Per Customer')
                    ->boolean(),

                Infolists\Components\TextEntry::make('eligible_origin')
                    ->label('Eligible Origin'),
                Infolists\Components\TextEntry::make('eligible_destination')
                    ->label('Eligible Destination'),
                Infolists\Components\TextEntry::make('eligibleSchedule.service_name')
                    ->label('Eligible Schedule'),
                Infolists\Components\Section::make('Statistics')
                    ->schema([
                        Infolists\Components\TextEntry::make('total_used')
                            ->label('Total Redemptions')
                            ->getStateUsing(fn (Voucher $record) => $record->total_used),
                        Infolists\Components\TextEntry::make('remaining_uses')
                            ->label('Remaining Redemptions')
                            ->getStateUsing(fn (Voucher $record) => $record->remaining_uses ?? 'Unlimited'),
                        Infolists\Components\TextEntry::make('total_discount_granted')
                            ->label('Total Discount Granted')
                            ->getStateUsing(fn (Voucher $record) => '₱' . number_format($record->total_discount_granted, 2)),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Voucher code copied')
                    ->weight('bold'),
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('discount_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'percentage' => 'info',
                        'fixed' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('discount_value')
                    ->label('Discount')
                    ->formatStateUsing(fn (Voucher $record) => $record->discount_type === 'percentage' 
                        ? "{$record->discount_value}%" 
                        : "₱" . number_format($record->discount_value, 2)),

                ToggleColumn::make('is_active')
                    ->label('Active'),
                ToggleColumn::make('is_hidden')
                    ->label('Hidden'),
                TextColumn::make('total_used')
                    ->label('Used')
                    ->sortable()
                    ->getStateUsing(fn (Voucher $record) => $record->total_used),
                TextColumn::make('remaining_uses')
                    ->label('Remaining')
                    ->sortable()
                    ->getStateUsing(fn (Voucher $record) => $record->remaining_uses ?? 'Unlimited'),
                TextColumn::make('total_discount_granted')
                    ->label('Total Discount')
                    ->getStateUsing(fn (Voucher $record) => '₱' . number_format($record->total_discount_granted, 2)),
                TextColumn::make('start_at')
                    ->label('Starts')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('end_at')
                    ->label('Ends')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Active Status'),
                SelectFilter::make('discount_type')
                    ->label('Discount Type')
                    ->options([
                        'percentage' => 'Percentage',
                        'fixed' => 'Fixed Amount',
                    ]),

            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalDescription('Are you sure you want to delete this voucher? This will not affect existing redemptions.'),
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
            RedemptionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVouchers::route('/'),
            'create' => Pages\CreateVoucher::route('/create'),
            'view' => Pages\ViewVoucher::route('/{record}'),
            'edit' => Pages\EditVoucher::route('/{record}/edit'),
        ];
    }
}

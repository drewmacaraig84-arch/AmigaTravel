<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Filament\Resources\BookingResource\RelationManagers\TransportClassesRelationManager;
use App\Filament\Resources\BookingResource\RelationManagers\AccommodationsRelationManager;
use App\Filament\Resources\BookingResource\RelationManagers\PassengersRelationManager;
use App\Models\Booking;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use App\Mail\BookingConfirmation;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Throwable;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Bookings';
    protected static ?int $navigationSort = 10;
    protected static ?string $navigationLabel = 'Bookings';

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user instanceof User && $user->hasAdminPermission('bookings');
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
                Forms\Components\Toggle::make('has_vehicle')
                    ->label('Has Vehicle')
                    ->default(false),
                Forms\Components\TextInput::make('vehicle_type')
                    ->label('Vehicle Type')
                    ->placeholder('e.g. Car, Motorcycle')
                    ->maxLength(255)
                    ->nullable(),
                Forms\Components\TextInput::make('vehicle_plate_number')
                    ->label('Plate Number')
                    ->placeholder('e.g. ABC123')
                    ->maxLength(255)
                    ->nullable(),
                Forms\Components\TextInput::make('vehicle_price')
                    ->label('Vehicle Price (₱)')
                    ->numeric()
                    ->prefix('₱')
                    ->nullable()
                    ->minValue(0),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->poll('10s')
            ->columns([
                Tables\Columns\TextColumn::make('transaction_number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('client_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('client_phone')
                    ->label('Contact No.')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('client_email')
                    ->label('Email')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('origin')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('destination')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('schedule_service')
                    ->label('Schedule')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('operator_name')
                    ->label('Operator')
                    ->state(fn (Booking $record): string => $record->getOperatorName() ?? '—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created Date')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\IconColumn::make('has_vehicle')
                    ->label('Vehicle')
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('vehicle_type')
                    ->label('Vehicle Type')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('vehicle_plate_number')
                    ->label('Plate Number')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')
                    ->label('Booking Status')
                    ->badge()
                    ->formatStateUsing(function (string $state) {
                        if ($state === 'operator_cancelled') {
                            return 'Cancelled by Operator';
                        }
                        if ($state === 'operator_rebooking') {
                            return 'Operator Rebooking';
                        }
                        return ucfirst(str_replace('_', ' ', $state));
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'success',
                        'operator_rebooking' => 'info',
                        'cancelled', 'operator_cancelled' => 'danger',
                        default => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('transaction.payment_status')
                    ->label('Payment Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => $state ? ucfirst($state) : null)
                    ->color(fn (?string $state): string => match ($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('transaction.payment_reference')
                    ->label('Payment Ref No.')
                    ->searchable()
                    ->placeholder('N/A')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('total_price')
                    ->money('PHP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('verification_timer')
                    ->label('Lock Timer')
                    ->badge()
                    ->icon(fn (Booking $record): string => match (true) {
                        $record->status !== 'pending' => 'heroicon-m-check-circle',
                        ! $record->transaction => 'heroicon-m-clock',
                        ! $record->isVerificationLocked() => 'heroicon-m-check-badge',
                        default => 'heroicon-m-clock',
                    })
                    ->color(fn (Booking $record): string => match (true) {
                        $record->status !== 'pending' => 'gray',
                        ! $record->transaction => 'warning',
                        ! $record->isVerificationLocked() => 'success',
                        default => 'warning',
                    })
                    ->state(fn (Booking $record): string => $record->verificationTimerLabel())
                    ->tooltip(fn (Booking $record): ?string => $record->verificationTimerTooltip()),
                Tables\Columns\TextColumn::make('review_status')
                    ->label('Review Lock')
                    ->badge()
                    ->state(fn (Booking $record): string => $record->getReviewClaimStatusLabel(Auth::user()))
                    ->icon(fn (Booking $record): ?string => $record->isReviewClaimed() ? 'heroicon-m-lock-closed' : null)
                    ->color(fn (Booking $record): string => match (true) {
                        ! $record->isReviewClaimed() => 'gray',
                        $record->isReviewClaimedBy(Auth::user()) => 'warning',
                        default => 'danger',
                    })
                    ->tooltip(fn (Booking $record): ?string => $record->getReviewClaimTooltip(Auth::user()))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Booking status')
                    ->options([
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'operator_rebooking' => 'Operator Rebooking',
                        'operator_cancelled' => 'Cancelled by Operator',
                        'cancelled' => 'Cancelled',
                    ]),
                SelectFilter::make('transaction_payment_status')
                    ->label('Payment status')
                    ->options([
                        'unpaid' => 'Unpaid',
                        'pending' => 'Pending verification',
                        'paid' => 'Paid',
                        'cancelled' => 'Cancelled',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (blank($data['value'] ?? null)) {
                            return;
                        }
                        $query->whereHas('transaction', function ($q) use ($data) {
                            $q->where('payment_status', $data['value']);
                        });
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('reviewBooking')
                    ->label(fn (Booking $record): string => $record->isReviewClaimedBy(Auth::user())
                        ? 'Resume Review'
                        : ($record->isReviewClaimedByOther(Auth::user())
                            ? 'In Review'
                            : 'Review'))
                    ->icon(fn (Booking $record): string => $record->isReviewClaimedByOther(Auth::user())
                        ? 'heroicon-m-lock-closed'
                        : 'heroicon-m-clipboard-document-check')
                    ->color(fn (Booking $record): string => $record->isReviewClaimedBy(Auth::user())
                        ? 'warning'
                        : ($record->isReviewClaimedByOther(Auth::user())
                            ? 'gray'
                            : 'amber'))
                    ->button()
                    ->visible(fn (Booking $record): bool => $record->status === 'pending')
                    ->disabled(fn (Booking $record): bool => $record->isReviewClaimedByOther(Auth::user()))
                    ->tooltip(fn (Booking $record): ?string => $record->getReviewClaimTooltip(Auth::user()))
                    ->action(function (Booking $record): void {
                        $user = Auth::user();
                        if (! $user instanceof \App\Models\User) {
                            return;
                        }

                        if ($record->isReviewClaimedByOther($user)) {
                            \Filament\Notifications\Notification::make()
                                ->title('Booking In Review')
                                ->body($record->getReviewClaimTooltip($user))
                                ->warning()
                                ->send();
                            return;
                        }

                        $record->claimReview($user, 'booking');

                        \Filament\Notifications\Notification::make()
                            ->title('Review Claimed')
                            ->body("You have claimed booking #{$record->transaction_number} for review. Exclusive lock active for 10 minutes.")
                            ->info()
                            ->send();

                        redirect(BookingResource::getUrl('view', ['record' => $record]));
                    }),
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
            TransportClassesRelationManager::class,
            AccommodationsRelationManager::class,
            PassengersRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->with(['transaction', 'user'])
            ->reorder();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
            'view' => Pages\ViewBooking::route('/{record}'),
        ];
    }
}

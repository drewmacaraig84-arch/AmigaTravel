<?php

namespace App\Filament\Pages;

use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use App\Models\User;
use App\Services\ServiceCancellationManager;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ManageRebookings extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationGroup = 'Bookings';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Rebookings';

    protected static ?string $title = 'Rebookings';

    protected static string $view = 'filament.pages.manage-rebookings';

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user instanceof User && $user->hasAdminPermission('bookings');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Booking::query()
                    ->where(function (Builder $query) {
                        $query->where('is_rebooked', true)
                            ->orWhereNotNull('rebooking_status')
                            ->orWhereNotNull('disruption_status');
                    })
                    ->with(['transaction', 'user', 'schedule.ferryRoute'])
            )
            ->defaultSort('updated_at', 'desc')
            ->poll('10s')
            ->columns([
                Tables\Columns\TextColumn::make('transaction_number')
                    ->label('Transaction #')
                    ->searchable()
                    ->sortable()
                    ->url(fn (Booking $record): string => BookingResource::getUrl('view', ['record' => $record])),
                Tables\Columns\TextColumn::make('client_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('route')
                    ->label('Route')
                    ->state(fn (Booking $record): string => $record->origin . ' → ' . $record->destination),
                Tables\Columns\TextColumn::make('departure_date')
                    ->label('Original Departure')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rebooking_departure_date')
                    ->label('Rebook Departure')
                    ->date()
                    ->sortable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('rebooking_return_date')
                    ->label('Rebook Return')
                    ->date()
                    ->sortable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('rebooking_status')
                    ->label('Rebooking Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'rebooking_required' => 'Rebooking Required',
                        'reschedule_requested' => 'Reschedule Requested',
                        'verified' => 'Rebooked',
                        'pending' => 'Pending',
                        default => $state ? ucfirst(str_replace('_', ' ', $state)) : null,
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'rebooking_required' => 'danger',
                        'reschedule_requested' => 'info',
                        'verified' => 'success',
                        'pending' => 'warning',
                        default => 'gray',
                    })
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('disruption_status')
                    ->label('Disruption')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'cancelled_by_operator_rescheduling_required' => 'Reschedule Required',
                        'reschedule_requested' => 'Reschedule Requested',
                        'rescheduled_approved' => 'Approved',
                        'rescheduled_declined' => 'Declined',
                        'contact_support_required' => 'Contact Support',
                        default => $state ? ucfirst(str_replace('_', ' ', $state)) : null,
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'cancelled_by_operator_rescheduling_required' => 'danger',
                        'reschedule_requested' => 'info',
                        'rescheduled_approved' => 'success',
                        'rescheduled_declined' => 'danger',
                        'contact_support_required' => 'warning',
                        default => 'gray',
                    })
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('transaction.rebooking_fee')
                    ->label('Rebooking Fee')
                    ->money('PHP')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Booking Status')
                    ->badge()
                    ->formatStateUsing(function (string $state, Booking $record) {
                        if (in_array($state, ['cancelled', 'operator_cancelled']) && $record->refund_amount > 0) {
                            return 'Refunded';
                        }
                        if ($state === 'operator_cancelled') {
                            return 'Cancelled by Operator';
                        }
                        return ucfirst($state);
                    })
                    ->color(fn (?string $state, Booking $record): string => match (true) {
                        $state === 'pending' => 'warning',
                        $state === 'confirmed' => 'success',
                        in_array($state, ['cancelled', 'operator_cancelled']) && $record->refund_amount > 0 => 'info',
                        in_array($state, ['cancelled', 'operator_cancelled']) => 'danger',
                        default => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('rebooking_status')
                    ->label('Rebooking status')
                    ->options([
                        'pending' => 'Pending',
                        'rebooking_required' => 'Rebooking Required',
                        'reschedule_requested' => 'Reschedule Requested',
                        'verified' => 'Rebooked / Verified',
                    ]),
                SelectFilter::make('disruption_status')
                    ->label('Disruption status')
                    ->options([
                        'cancelled_by_operator_rescheduling_required' => 'Reschedule Required',
                        'reschedule_requested' => 'Reschedule Requested',
                        'rescheduled_approved' => 'Approved',
                        'rescheduled_declined' => 'Declined',
                        'contact_support_required' => 'Contact Support',
                    ]),
                SelectFilter::make('status')
                    ->label('Booking status')
                    ->options([
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'cancelled' => 'Cancelled',
                        'operator_cancelled' => 'Cancelled by Operator',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('viewBooking')
                    ->label('View')
                    ->icon('heroicon-m-eye')
                    ->url(fn (Booking $record): string => BookingResource::getUrl('view', ['record' => $record])),

                Tables\Actions\Action::make('verifyRebookingPayment')
                    ->label('Verify & Approve')
                    ->icon('heroicon-m-check-badge')
                    ->color('success')
                    ->button()
                    ->requiresConfirmation()
                    ->modalHeading('Verify Rebooking Payment & Approve')
                    ->modalDescription(fn (Booking $record): string => "This will verify the rebooking payment for booking #{$record->transaction_number} and automatically assign a replacement schedule.")
                    ->form([
                        Forms\Components\TextInput::make('confirmation_url')
                            ->label('Confirmation/Ticket URL')
                            ->placeholder('https://...')
                            ->url()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('confirmation_pdf')
                            ->label('Upload Itinerary/Ticket PDF')
                            ->disk('public')
                            ->directory('receipts')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->columnSpanFull(),
                    ])
                    ->action(function (Booking $record, array $data): void {
                        try {
                            if ($record->transaction) {
                                if (!empty($data['confirmation_url'])) {
                                    $record->transaction->confirmation_url = $data['confirmation_url'];
                                }
                                if (!empty($data['confirmation_pdf'])) {
                                    $record->transaction->confirmation_pdf = $data['confirmation_pdf'];
                                }
                                $record->transaction->save();
                            }

                            app(ServiceCancellationManager::class)->processAutomaticRebookingApproval(
                                $record,
                                auth()->user()
                            );

                            Notification::make()
                                ->title('Rebooking Verified & Approved')
                                ->body("Rebooking for #{$record->transaction_number} has been verified and schedule assigned.")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Rebooking Verification Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn (Booking $record): bool => $record->status === 'confirmed' && $record->rebooking_status === 'pending'),

                Tables\Actions\Action::make('approveReschedule')
                    ->label('Approve Reschedule')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->button()
                    ->visible(fn (Booking $record): bool => in_array($record->disruption_status, ['reschedule_requested', 'cancelled_by_operator_rescheduling_required']) && filled($record->preferred_replacement_schedule_id))
                    ->form([
                        Forms\Components\Textarea::make('staff_note')
                            ->label('Internal / Customer Staff Note')
                            ->placeholder('e.g., Approved replacement schedule per customer selection.')
                            ->rows(2),
                    ])
                    ->action(function (Booking $record, array $data): void {
                        app(ServiceCancellationManager::class)->processStaffApproval(
                            $record,
                            true,
                            $data['staff_note'] ?? null,
                            Auth::user()
                        );

                        Notification::make()
                            ->title('Reschedule Approved')
                            ->body("Booking #{$record->transaction_number} date updated and customer notified.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('verifyRebooking')
                    ->label('Verify Rebooking')
                    ->icon('heroicon-m-arrow-path')
                    ->button()
                    ->color('secondary')
                    ->visible(fn (Booking $record): bool => $record->is_rebooked && $record->rebooking_status === 'pending')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\TextInput::make('confirmation_url')
                            ->label('Confirmation/Ticket URL')
                            ->placeholder('https://...')
                            ->url()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('confirmation_pdf')
                            ->label('Upload Itinerary/Ticket PDF')
                            ->disk('public')
                            ->directory('receipts')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->columnSpanFull(),
                    ])
                    ->action(function (Booking $record, array $data): void {
                        if ($record->transaction) {
                            if (!empty($data['confirmation_url'])) {
                                $record->transaction->confirmation_url = $data['confirmation_url'];
                            }
                            if (!empty($data['confirmation_pdf'])) {
                                $record->transaction->confirmation_pdf = $data['confirmation_pdf'];
                            }
                            $record->transaction->save();
                        }

                        $record->verifyRebooking(
                            $record->transaction?->confirmation_url,
                            $record->transaction?->confirmation_pdf ?? null,
                            $record->transaction?->confirmation_pdf ? 'public' : null,
                        );

                        Notification::make()
                            ->title('Rebooking Verified')
                            ->body("Rebooking for #{$record->transaction_number} has been verified.")
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([])
            ->emptyStateHeading('No rebookings')
            ->emptyStateDescription('There are no rebooking requests at this time.')
            ->emptyStateIcon('heroicon-o-arrow-path');
    }
}

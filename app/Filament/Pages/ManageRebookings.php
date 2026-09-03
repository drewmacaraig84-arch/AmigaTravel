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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ManageRebookings extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationGroup = 'Bookings';

    protected static ?int $navigationSort = 30;

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
                            ->orWhere('status', 'operator_rebooking')
                            ->orWhereNotNull('rebooking_status')
                            ->orWhereNotNull('disruption_status')
                            ->orWhereHas('passengers', function (Builder $pq) {
                                $pq->whereNotNull('rebooking_status')
                                   ->orWhereIn('status', ['rebooked', 'rebook_pending']);
                            });
                    })
                    ->with(['transaction', 'user', 'schedule.ferryRoute', 'passengers'])
            )
            ->defaultSort('updated_at', 'desc')
            ->poll('10s')
            ->columns([
                Tables\Columns\TextColumn::make('transaction_number')
                    ->label('Transaction #')
                    ->searchable()
                    ->sortable()
                    ->url(fn (Booking $record): string => BookingResource::getUrl('view', ['record' => $record])),
                Tables\Columns\TextColumn::make('affected_items')
                    ->label('Affected Item(s)')
                    ->state(fn (Booking $record): string => $record->getAffectedItemsLabel())
                    ->wrap()
                    ->badge()
                    ->color('info')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('client_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('route')
                    ->label('Route')
                    ->state(fn (Booking $record): string => $record->origin . ' → ' . $record->destination),
                Tables\Columns\TextColumn::make('operator_name')
                    ->label('Operator')
                    ->state(fn (Booking $record): string => $record->getOperatorName() ?? '—')
                    ->toggleable(),
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
                        'pending_rebooking' => 'Pending Rebooking',
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

                Tables\Actions\Action::make('reviewRebookingPayment')
                    ->label(fn (Booking $record): string => $record->isReviewClaimedBy(Auth::user())
                        ? 'Resume Review'
                        : ($record->isReviewClaimedByOther(Auth::user())
                            ? 'In Review'
                            : 'Review & Approve'))
                    ->icon(fn (Booking $record): string => $record->isReviewClaimedByOther(Auth::user())
                        ? 'heroicon-m-lock-closed'
                        : 'heroicon-m-clipboard-document-check')
                    ->color(fn (Booking $record): string => $record->isReviewClaimedBy(Auth::user())
                        ? 'warning'
                        : ($record->isReviewClaimedByOther(Auth::user())
                            ? 'gray'
                            : 'amber'))
                    ->button()
                    ->modalWidth('3xl')
                    ->modalHeading('Review Rebooking Request & Verify')
                    ->modalDescription(fn (Booking $record): string => "Review passenger and payment details for booking #{$record->transaction_number} before verifying and issuing replacement tickets.")
                    ->modalSubmitActionLabel('Verify & Approve')
                    ->mountUsing(function (Booking $record) {
                        $user = Auth::user();
                        if ($user instanceof \App\Models\User && ! $record->isReviewClaimedByOther($user)) {
                            $record->claimReview($user, 'rebooking');
                        }
                    })
                    ->form([
                        Forms\Components\Placeholder::make('review_claim_notice')
                            ->label('')
                            ->content(function (Booking $record): \Illuminate\Support\HtmlString {
                                $user = Auth::user();
                                $remaining = e($record->getReviewClaimTimerRemainingLabel() ?? '10m');
                                return new \Illuminate\Support\HtmlString('
                                    <div class="p-3 rounded-lg border border-amber-300 dark:border-amber-700 bg-amber-50 dark:bg-amber-950/30 text-amber-900 dark:text-amber-200 text-xs flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold">🔒 Exclusive Review Lock Active</span>
                                            <span>— Other staff members cannot verify while you are reviewing (' . $remaining . ').</span>
                                        </div>
                                    </div>
                                ');
                            })
                            ->columnSpanFull(),

                        Forms\Components\Placeholder::make('rebooking_summary')
                            ->label('Rebooking Information')
                            ->content(function (Booking $record): \Illuminate\Support\HtmlString {
                                $prefSchedule = $record->preferredReplacementSchedule;
                                $prefDate = $record->preferred_replacement_date ? $record->preferred_replacement_date->format('M d, Y') : ($record->rebooking_departure_date ? $record->rebooking_departure_date->format('M d, Y') : 'Customer requested next available');
                                $fee = $record->transaction?->rebooking_fee ? '₱' . number_format((float) $record->transaction->rebooking_fee, 2) : 'None / Waived';
                                $origSchedule = $record->schedule ? ($record->schedule->service_name . ' (' . $record->schedule->formatted_departure . ' → ' . $record->schedule->formatted_arrival . ')') : '—';
                                $newSchedule = $prefSchedule ? ($prefSchedule->service_name . ' (' . $prefSchedule->formatted_departure . ' → ' . $prefSchedule->formatted_arrival . ')') : 'Auto-assign matching schedule';

                                return new \Illuminate\Support\HtmlString('
                                    <div class="grid grid-cols-2 gap-3 p-3.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 text-xs">
                                        <div><span class="text-gray-500">Client:</span> <span class="font-medium">' . e($record->client_name) . ' (' . e($record->client_phone ?? 'No phone') . ')</span></div>
                                        <div><span class="text-gray-500">Rebooking Fee:</span> <span class="font-bold text-amber-600">' . $fee . '</span></div>
                                        <div><span class="text-gray-500">Original Departure:</span> <span class="font-medium">' . e($record->departure_date ? $record->departure_date->format('M d, Y') : '—') . ' ' . e($origSchedule) . '</span></div>
                                        <div><span class="text-gray-500">Target Rebook Date:</span> <span class="font-bold text-blue-600 dark:text-blue-400">' . e($prefDate) . '</span></div>
                                        <div class="col-span-2"><span class="text-gray-500">Target Schedule:</span> <span class="font-medium">' . e($newSchedule) . '</span></div>
                                    </div>
                                ');
                            })
                            ->columnSpanFull(),

                        Forms\Components\Placeholder::make('proof_preview')
                            ->label('Rebooking Proof of Payment')
                            ->content(function (Booking $record): \Illuminate\Support\HtmlString {
                                $proof = $record->transaction?->rebooking_proof_of_payment;
                                if (! $proof) {
                                    return new \Illuminate\Support\HtmlString('<span class="text-xs text-rose-500 italic">No proof of payment uploaded.</span>');
                                }
                                $url = storage_asset_path($proof);
                                return new \Illuminate\Support\HtmlString('
                                    <div class="p-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900">
                                        <a href="' . e($url) . '" target="_blank" class="block group text-center">
                                            <img src="' . e($url) . '" class="max-h-56 mx-auto rounded-lg object-contain shadow-sm border border-gray-200 dark:border-gray-800 group-hover:opacity-90 transition" alt="Rebooking Proof" />
                                            <span class="text-[11px] text-blue-600 dark:text-blue-400 font-medium underline mt-1.5 inline-block">Click to open full size</span>
                                        </a>
                                    </div>
                                ');
                            })
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('confirmation_url')
                            ->label('Confirmation / Ticket URL')
                            ->placeholder('https://...')
                            ->url()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('confirmation_pdf')
                            ->label('Upload Replacement Itinerary / Ticket PDF')
                            ->disk('public')
                            ->directory('tickets')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->columnSpanFull(),
                    ])
                    ->extraModalFooterActions([
                        Tables\Actions\Action::make('releaseClaim')
                            ->label('Release Review')
                            ->color('gray')
                            ->action(function (Booking $record) {
                                $record->releaseReview(Auth::user());
                                Notification::make()
                                    ->title('Review Claim Released')
                                    ->body('This rebooking request is now available for other staff.')
                                    ->info()
                                    ->send();
                            }),
                    ])
                    ->disabled(fn (Booking $record): bool => $record->isReviewClaimedByOther(Auth::user()) || ! $record->transaction || $record->transaction->payment_status === 'unpaid' || blank($record->transaction->rebooking_proof_of_payment))
                    ->tooltip(fn (Booking $record): ?string => match (true) {
                        $record->isReviewClaimedByOther(Auth::user()) => $record->getReviewClaimTooltip(Auth::user()),
                        ! $record->transaction => 'No payment transaction found.',
                        $record->transaction->payment_status === 'unpaid' => 'Cannot verify: Rebooking payment status is unpaid.',
                        blank($record->transaction->rebooking_proof_of_payment) => 'Cannot verify: Rebooking proof of payment is missing.',
                        default => null,
                    })
                    ->action(function (Booking $record, array $data): void {
                        $alreadyVerifiedBy = null;

                        try {
                            $ticketUrl = !empty($data['confirmation_url']) ? trim($data['confirmation_url']) : null;
                            $confirmationPdfPath = Booking::resolveUploadedPdfPath($data['confirmation_pdf'] ?? null, $record->transaction_number);

                            DB::transaction(function () use ($record, $ticketUrl, $confirmationPdfPath, &$alreadyVerifiedBy) {
                                $lockedBooking = Booking::where('id', $record->id)
                                    ->with(['transaction', 'verifiedBy'])
                                    ->lockForUpdate()
                                    ->first();

                                if (! $lockedBooking || $lockedBooking->rebooking_status === 'verified') {
                                    $alreadyVerifiedBy = $lockedBooking?->verifiedBy?->name ?? 'another staff member';
                                    return;
                                }

                                if ($lockedBooking->transaction) {
                                    if (!empty($ticketUrl)) {
                                        $lockedBooking->transaction->confirmation_url = $ticketUrl;
                                    }
                                    if (!empty($confirmationPdfPath)) {
                                        $lockedBooking->transaction->confirmation_pdf = $confirmationPdfPath;
                                    }
                                    $lockedBooking->transaction->save();
                                }

                                $receiptPath = $confirmationPdfPath ?: ($lockedBooking->transaction?->confirmation_pdf ?? null);
                                $receiptDisk = $receiptPath ? 'public' : null;

                                $lockedBooking->verifyRebooking($ticketUrl, $receiptPath, $receiptDisk);
                                $lockedBooking->releaseReview(null, true);
                            });

                            if ($alreadyVerifiedBy !== null) {
                                Notification::make()
                                    ->title('Already Verified')
                                    ->body("This rebooking was already verified by {$alreadyVerifiedBy}.")
                                    ->warning()
                                    ->send();
                                return;
                            }

                            Notification::make()
                                ->title('Rebooking Verified & Approved')
                                ->body("Rebooking for #{$record->transaction_number} has been verified, schedule assigned, and confirmation email sent.")
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
                    ->visible(fn (Booking $record): bool => in_array($record->status, ['confirmed', 'pending_rebooking', 'pending']) && $record->rebooking_status === 'pending'),

                Tables\Actions\Action::make('approveReschedule')
                    ->label('Approve Reschedule')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->button()
                    ->visible(fn (Booking $record): bool => (in_array($record->disruption_status, ['reschedule_requested', 'cancelled_by_operator_rescheduling_required']) || $record->status === 'operator_rebooking') && (filled($record->preferred_replacement_schedule_id) || filled($record->disruption_notes)))
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
            ])
            ->bulkActions([])
            ->emptyStateHeading('No rebookings')
            ->emptyStateDescription('There are no rebooking requests at this time.')
            ->emptyStateIcon('heroicon-o-arrow-path');
    }
}

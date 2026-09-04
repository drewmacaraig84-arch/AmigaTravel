<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use App\Mail\BookingConfirmation;
use App\Models\Booking;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ViewTransaction extends ViewRecord
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('verify')
                ->label('Verify payment')
                ->form([
                    TextInput::make('confirmation_url')
                        ->label('Confirmation URL')
                        ->url()
                        ->placeholder('https://example.com/ticket/ABC123'),
                    FileUpload::make('confirmation_pdf')
                        ->label('Confirmation PDF')
                        ->directory('tickets')
                        ->disk('public')
                        ->acceptedFileTypes(['application/pdf'])
                        ->maxSize(10240),
                ])
                ->disabled(fn (): bool => $this->record->payment_status === 'unpaid' || $this->record->isVerificationLocked() || ($this->record->booking && $this->record->booking->isReviewClaimedByOther(Auth::user())))
                ->tooltip(fn (): ?string => match (true) {
                    $this->record->booking && $this->record->booking->isReviewClaimedByOther(Auth::user()) => $this->record->booking->getReviewClaimTooltip(Auth::user()),
                    $this->record->payment_status === 'unpaid' => 'Cannot verify: Payment status is Unpaid.',
                    default => $this->record->verificationTimerTooltip(),
                })
                ->action(function (array $data): void {
                    $record = $this->record;

                    if (empty($data['confirmation_url']) && empty($data['confirmation_pdf'])) {
                        throw new \Exception('Please provide either a confirmation URL or upload a PDF before verifying.');
                    }

                    $alreadyVerifiedBy = null;
                    $shouldSendEmail = false;
                    $ticketUrl = !empty($data['confirmation_url']) ? trim($data['confirmation_url']) : null;
                    $txNumber = $record->booking?->transaction_number ?? (string) $record->id;
                    $confirmationPdfPath = \App\Models\Booking::resolveUploadedPdfPath($data['confirmation_pdf'] ?? null, $txNumber);

                    $pdfPath = $confirmationPdfPath;
                    $receiptPath = $confirmationPdfPath;
                    $receiptDisk = $confirmationPdfPath ? 'public' : null;

                    DB::transaction(function () use (
                        $record, $ticketUrl, $pdfPath,
                        &$alreadyVerifiedBy, &$shouldSendEmail
                    ) {
                        $lockedTx = \App\Models\Transaction::where('id', $record->id)
                            ->with(['booking', 'verifiedBy'])
                            ->lockForUpdate()
                            ->first();

                        if (! $lockedTx || $lockedTx->payment_status === 'paid' || $lockedTx->verified_by_user_id !== null) {
                            $alreadyVerifiedBy = $lockedTx?->verifiedBy?->name ?? 'another staff member';
                            return;
                        }

                        $booking = $lockedTx->booking ? \App\Models\Booking::where('id', $lockedTx->booking->id)->lockForUpdate()->first() : null;
                        if ($booking && ($booking->status === 'confirmed' || $booking->verified_by_user_id !== null)) {
                            $alreadyVerifiedBy = $booking->verifiedBy?->name ?? $lockedTx->verifiedBy?->name ?? 'another staff member';
                            return;
                        }

                        $staffUserId = Auth::id();
                        $now = now();

                        $lockedTx->update([
                            'payment_status' => 'paid',
                            'confirmation_url' => $ticketUrl,
                            'confirmation_pdf' => $pdfPath,
                            'verified_by_user_id' => $staffUserId,
                            'verified_at' => $now,
                        ]);

                        if ($booking) {
                            $booking->update([
                                'status' => 'confirmed',
                                'verified_by_user_id' => $staffUserId,
                                'verified_at' => $now,
                            ]);
                            $booking->setRelation('transaction', $lockedTx);

                            app(\App\Services\GraciaPointsService::class)->awardPointsForBooking($booking, Auth::user());
                            $shouldSendEmail = true;
                        }
                    });

                    if ($alreadyVerifiedBy !== null) {
                        Notification::make()
                            ->title('Already Verified')
                            ->body("This transaction was already verified by {$alreadyVerifiedBy}.")
                            ->warning()
                            ->send();
                        return;
                    }

                    if ($shouldSendEmail && $record->booking) {
                        $booking = $record->booking->fresh();
                        try {
                            Mail::to($booking->client_email)->send(
                                new BookingConfirmation($booking, $ticketUrl, $receiptPath, $receiptDisk)
                            );

                            Notification::make()
                                ->title('Payment verified')
                                ->body('Payment verified and confirmation email sent.')
                                ->success()
                                ->send();
                        } catch (Throwable $e) {
                            Log::error('Failed sending booking confirmation email (transaction detail verify)', [
                                'transaction_id' => $record->id ?? null,
                                'booking_id' => $booking->id ?? null,
                                'email' => $booking->client_email ?? null,
                                'error' => $e->getMessage(),
                            ]);

                            Notification::make()
                                ->title('Payment verified with warning')
                                ->body('Payment was verified, but the confirmation email failed to send.')
                                ->warning()
                                ->send();
                        }
                    }
                })
                ->requiresConfirmation()
                ->color('success')
                ->visible(fn (): bool => $this->record->payment_status === 'pending' && $this->record->booking?->status !== 'cancelled'),

            Actions\Action::make('reject')
                ->label('Reject Payment')
                ->icon('heroicon-m-x-circle')
                ->color('danger')
                ->visible(fn (): bool => $this->record->payment_status === 'pending' && ! in_array($this->record->booking?->status, ['cancelled', 'rejected']))
                ->disabled(fn (): bool => $this->record->payment_status === 'unpaid'
                    || $this->record->isVerificationLocked()
                    || ($this->record->booking && $this->record->booking->isReviewClaimedByOther(Auth::user())))
                ->tooltip(fn (): ?string => match (true) {
                    $this->record->booking && $this->record->booking->isReviewClaimedByOther(Auth::user()) => $this->record->booking->getReviewClaimTooltip(Auth::user()),
                    $this->record->payment_status === 'unpaid' => 'Cannot reject: Payment status is Unpaid.',
                    default => $this->record->verificationTimerTooltip(),
                })
                ->form([
                    \Filament\Forms\Components\Radio::make('rejection_reason')
                        ->label('Reason for Rejection')
                        ->options(array_combine(Booking::REJECTION_REASONS, Booking::REJECTION_REASONS))
                        ->required()
                        ->live()
                        ->columns(1),
                    \Filament\Forms\Components\Textarea::make('rejection_notes')
                        ->label('Additional Notes / Specified Reason')
                        ->placeholder('Please provide specific details here...')
                        ->rows(3)
                        ->maxLength(1000)
                        ->helperText('Required when selecting "Other — please specify reason". Optional otherwise.')
                        ->required(fn (\Filament\Forms\Get $get): bool => $get('rejection_reason') === 'Other — please specify reason')
                        ->visible(fn (\Filament\Forms\Get $get): bool => filled($get('rejection_reason'))),
                ])
                ->modalHeading('Reject Payment Verification')
                ->modalDescription('Please select the reason for rejecting this payment. The client will be notified by email with a polite explanation and guidance on next steps.')
                ->modalSubmitActionLabel('Reject & Notify Client')
                ->modalWidth('lg')
                ->action(function (array $data): void {
                    $record = $this->record;
                    $reason = $data['rejection_reason'];
                    $notes  = filled($data['rejection_notes'] ?? '') ? trim($data['rejection_notes']) : null;

                    if ($reason === 'Other — please specify reason' && $notes) {
                        $reason = 'Other: ' . $notes;
                        $notes  = null;
                    }

                    $booking = $record->booking;

                    if ($booking) {
                        if ($booking->rebooking_status === 'pending') {
                            $booking->rejectRebooking($reason, $notes, Auth::user());
                        } else {
                            $booking->rejectBooking($reason, $notes, Auth::user());
                        }
                    } else {
                        $record->update(['payment_status' => 'rejected']);
                    }

                    Notification::make()
                        ->title('Payment Rejected')
                        ->body('Payment has been rejected and the client has been notified.')
                        ->danger()
                        ->send();

                    $this->redirect(TransactionResource::getUrl('index'));
                }),
            Actions\DeleteAction::make(),
        ];
    }
}

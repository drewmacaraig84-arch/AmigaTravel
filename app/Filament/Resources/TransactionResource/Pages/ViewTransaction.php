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
                ->disabled(fn (): bool => $this->record->payment_status === 'unpaid' || $this->record->isVerificationLocked())
                ->tooltip(fn (): ?string => $this->record->payment_status === 'unpaid'
                    ? 'Cannot verify: Payment status is Unpaid.'
                    : $this->record->verificationTimerTooltip())
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
            Actions\DeleteAction::make(),
        ];
    }
}

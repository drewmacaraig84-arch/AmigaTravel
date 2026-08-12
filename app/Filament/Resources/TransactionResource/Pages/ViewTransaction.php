<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use App\Mail\BookingConfirmation;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
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
                        ->directory('receipts')
                        ->disk('public')
                        ->acceptedFileTypes(['application/pdf'])
                        ->maxSize(10240),
                ])
                ->disabled(fn (): bool => $this->record->isVerificationLocked())
                ->tooltip(fn (): ?string => $this->record->verificationTimerTooltip())
                ->action(function (array $data): void {
                    if (empty($data['confirmation_url']) && empty($data['confirmation_pdf'])) {
                        throw new \Exception('Please provide either a confirmation URL or upload a PDF before verifying.');
                    }

                    $record = $this->record;

                    if (! empty($data['confirmation_pdf'])) {
                        $pdfPath = is_string($data['confirmation_pdf'])
                            ? $data['confirmation_pdf']
                            : $data['confirmation_pdf']->storeAs('receipts', 'receipt-' . $record->booking->transaction_number . '.pdf', 'public');
                        $ticketUrl = null;
                        $receiptPath = $pdfPath; // relative path — BookingConfirmation uses attachFromStorageDisk
                        $receiptDisk = 'public';
                    } else {
                        $pdfPath = null;
                        $ticketUrl = $data['confirmation_url'];
                        $receiptPath = null;
                        $receiptDisk = null;
                    }

                    $staffUserId = Auth::id();
                    $now = now();

                    $record->update([
                        'payment_status' => 'paid',
                        'confirmation_url' => $ticketUrl,
                        'confirmation_pdf' => $pdfPath,
                        'verified_by_user_id' => $staffUserId,
                        'verified_at' => $now,
                    ]);

                    $record->booking->update([
                        'status' => 'confirmed',
                        'verified_by_user_id' => $staffUserId,
                        'verified_at' => $now,
                    ]);

                    app(\App\Services\GraciaPointsService::class)->awardPointsForBooking($record->booking, Auth::user());

                    try {
                        Mail::to($record->booking->client_email)->send(
                            new BookingConfirmation($record->booking, $ticketUrl, $receiptPath, $receiptDisk)
                        );

                        Notification::make()
                            ->title('Payment verified')
                            ->body('Payment verified and confirmation email sent.')
                            ->success()
                            ->send();
                    } catch (Throwable $e) {
                        Log::error('Failed sending booking confirmation email (transaction detail verify)', [
                            'transaction_id' => $record->id ?? null,
                            'booking_id' => $record->booking->id ?? null,
                            'email' => $record->booking->client_email ?? null,
                            'error' => $e->getMessage(),
                        ]);

                        Notification::make()
                            ->title('Payment verified with warning')
                            ->body('Payment was verified, but the confirmation email failed to send.')
                            ->warning()
                            ->send();
                    }
                })
                ->requiresConfirmation()
                ->color('success')
                ->visible(fn (): bool => $this->record->payment_status === 'pending' && $this->record->booking?->status !== 'cancelled'),
            Actions\DeleteAction::make(),
        ];
    }
}

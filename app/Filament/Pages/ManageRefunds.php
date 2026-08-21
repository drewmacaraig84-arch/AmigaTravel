<?php

namespace App\Filament\Pages;

use App\Filament\Resources\BookingResource;
use App\Mail\RefundCompletedMail;
use App\Models\Booking;
use App\Models\User;
use App\Models\UserNotification;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ManageRefunds extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $slug = 'refunds';

    protected static ?string $navigationGroup = 'Bookings';

    protected static ?int $navigationSort = 35;

    protected static ?string $navigationLabel = 'Refunds';

    protected static ?string $title = 'Refunds & Disbursements';

    protected static string $view = 'filament.pages.manage-refunds';

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
                        $query->whereIn('status', [Booking::STATUS_CANCELLED, Booking::STATUS_OPERATOR_CANCELLED])
                            ->orWhere('refund_status', 'pending')
                            ->orWhere('refund_status', 'completed')
                            ->orWhere('refund_amount', '>', 0)
                            ->orWhereHas('passengers', function (Builder $pq) {
                                $pq->where('refund_amount', '>', 0)
                                   ->orWhereIn('status', [\App\Models\Passenger::STATUS_REFUND_PENDING, \App\Models\Passenger::STATUS_REFUNDED]);
                            });
                    })
                    ->where(function (Builder $query) {
                        $query->where('refund_amount', '>', 0)
                            ->orWhereHas('passengers', fn (Builder $pq) => $pq->where('refund_amount', '>', 0));
                    })
                    ->with(['transaction', 'user', 'refundProcessedByUser', 'passengers'])
            )
            ->defaultSort('updated_at', 'desc')
            ->poll('10s')
            ->recordAction('viewRefund')
            ->columns([
                Tables\Columns\TextColumn::make('transaction_number')
                    ->label('Transaction #')
                    ->searchable()
                    ->sortable()
                    ->action('viewRefund')
                    ->color('primary')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('affected_items')
                    ->label('Affected Item(s)')
                    ->state(fn (Booking $record): string => $record->getAffectedItemsLabel())
                    ->wrap()
                    ->badge()
                    ->color('warning')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('client_name')
                    ->label('Client Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('client_email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('route')
                    ->label('Route')
                    ->state(fn (Booking $record): string => $record->origin . ' → ' . $record->destination),
                Tables\Columns\TextColumn::make('departure_date')
                    ->label('Travel Date')
                    ->date('M d, Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Requested Date')
                    ->dateTime('M d, Y g:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('refund_method')
                    ->label('Method')
                    ->badge()
                    ->state(fn (Booking $record): string => $record->getParsedRefundDestination()['method'] ?? ($record->refund_destination ? 'Other' : '—'))
                    ->color(fn (Booking $record): string => match (strtolower($record->getParsedRefundDestination()['method'] ?? '')) {
                        'gcash' => 'info',
                        'maya', 'paymaya', 'online wallet' => 'success',
                        'bank account', 'bank' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('refund_account')
                    ->label('Account / Recipient')
                    ->state(function (Booking $record): string {
                        $parsed = $record->getParsedRefundDestination();
                        $parts = [];
                        if (filled($parsed['institution'])) {
                            $parts[] = $parsed['institution'];
                        }
                        if (filled($parsed['account_number'])) {
                            $parts[] = $parsed['account_number'];
                        }
                        if (filled($parsed['account_name'])) {
                            $parts[] = '(' . $parsed['account_name'] . ')';
                        }
                        return !empty($parts) ? implode(' • ', $parts) : ($record->refund_destination ?? '—');
                    })
                    ->searchable(query: fn (Builder $query, string $search) => $query->where('refund_destination', 'like', "%{$search}%"))
                    ->wrap()
                    ->copyable()
                    ->copyableState(fn (Booking $record) => $record->getParsedRefundDestination()['account_number'] ?? $record->refund_destination),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('Original Total')
                    ->money('PHP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('retained_amount')
                    ->label('Retained by Amiga')
                    ->state(fn (Booking $record): float => max(0, (float) $record->total_price - (float) $record->refund_amount))
                    ->money('PHP')
                    ->color('success')
                    ->weight('bold')
                    ->sortable(),
                Tables\Columns\TextColumn::make('refund_amount')
                    ->label('Customer Refund')
                    ->money('PHP')
                    ->color('danger')
                    ->weight('semibold')
                    ->sortable(),
                Tables\Columns\TextColumn::make('refund_status')
                    ->label('Refund Status')
                    ->badge()
                    ->formatStateUsing(function (?string $state, Booking $record) {
                        if ($state === 'completed') {
                            return 'Disbursed';
                        }
                        return 'Pending Processing';
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'completed' => 'success',
                        'rejected' => 'danger',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('refund_reference')
                    ->label('Transfer Ref No.')
                    ->searchable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('refund_processed_at')
                    ->label('Disbursed At')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('refundProcessedByUser.name')
                    ->label('Processed By')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('refund_status')
                    ->label('Processing Status')
                    ->options([
                        'pending' => 'Pending Processing',
                        'completed' => 'Disbursed',
                    ]),
            ])
            ->actions([
                Action::make('viewRefund')
                    ->label('View Details')
                    ->icon('heroicon-m-eye')
                    ->color('gray')
                    ->modalHeading(fn (Booking $record) => "Refund Details — #{$record->transaction_number}")
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->infolist([
                        Infolists\Components\Section::make('Refund Overview')
                            ->schema([
                                TextEntry::make('transaction_number')
                                    ->label('Booking Reference')
                                    ->weight('bold')
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                                TextEntry::make('refund_status')
                                    ->label('Disbursement Status')
                                    ->badge()
                                    ->formatStateUsing(fn ($state) => $state === 'completed' ? 'Disbursed' : 'Pending Processing')
                                    ->color(fn ($state) => $state === 'completed' ? 'success' : 'warning')
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                                TextEntry::make('cancellation_reason_type')
                                    ->label('Cancellation Type')
                                    ->state(fn (Booking $record) => filled($record->service_cancellation_id) ? '100% Service Disruption' : 'Customer Requested')
                                    ->badge()
                                    ->color(fn (Booking $record) => filled($record->service_cancellation_id) ? 'danger' : 'gray'),
                            ])
                            ->columns(3),

                        Infolists\Components\Section::make('Financial Calculation')
                            ->schema([
                                TextEntry::make('total_price')
                                    ->label('Original Total Fare')
                                    ->money('PHP'),
                                TextEntry::make('retained_amount')
                                    ->label('Retained by Amiga (System Revenue)')
                                    ->state(fn (Booking $record): float => max(0, (float) $record->total_price - (float) $record->refund_amount))
                                    ->money('PHP')
                                    ->color('success')
                                    ->weight('bold')
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                                TextEntry::make('refund_amount')
                                    ->label('Customer Refund Payout')
                                    ->money('PHP')
                                    ->color('danger')
                                    ->weight('semibold')
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                            ])
                            ->columns(3),

                        Infolists\Components\Section::make('Recipient & Transfer Info')
                            ->schema([
                                TextEntry::make('refund_method')
                                    ->label('Payout Method')
                                    ->badge()
                                    ->state(fn (Booking $record): string => $record->getParsedRefundDestination()['method'] ?? '—')
                                    ->color(fn (Booking $record): string => match (strtolower($record->getParsedRefundDestination()['method'] ?? '')) {
                                        'gcash' => 'info',
                                        'maya', 'paymaya', 'online wallet' => 'success',
                                        'bank account', 'bank' => 'warning',
                                        default => 'gray',
                                    }),
                                TextEntry::make('refund_institution')
                                    ->label('Bank / Institution')
                                    ->state(fn (Booking $record): string => $record->getParsedRefundDestination()['institution'] ?? '—')
                                    ->placeholder('—'),
                                TextEntry::make('refund_account_number')
                                    ->label('Account / Mobile No.')
                                    ->state(fn (Booking $record): string => $record->getParsedRefundDestination()['account_number'] ?? ($record->refund_destination ?? '—'))
                                    ->copyable()
                                    ->weight('bold')
                                    ->fontFamily(Infolists\Components\TextEntry\TextEntryFontFamily::Mono),
                                TextEntry::make('refund_account_name')
                                    ->label('Account Holder Name')
                                    ->state(fn (Booking $record): string => $record->getParsedRefundDestination()['account_name'] ?? '—')
                                    ->weight('bold')
                                    ->placeholder('—'),
                                TextEntry::make('refund_reference')
                                    ->label('Disbursement Transfer Ref No.')
                                    ->placeholder('Not yet disbursed')
                                    ->copyable()
                                    ->weight('bold')
                                    ->color('primary'),
                                TextEntry::make('refund_processed_at')
                                    ->label('Disbursed Date & Time')
                                    ->dateTime()
                                    ->placeholder('—'),
                                TextEntry::make('refundProcessedByUser.name')
                                    ->label('Processed By Staff')
                                    ->placeholder('—'),
                            ])
                            ->columns(3),

                        Infolists\Components\Section::make('Disbursement Proof Receipt')
                            ->schema([
                                ViewEntry::make('refund_proof_view')
                                    ->label('')
                                    ->view('filament.infolists.entries.refund-proof-image'),
                            ])
                            ->visible(fn (Booking $record): bool => filled($record->refund_proof)),

                        Infolists\Components\Section::make('Trip & Passenger Details')
                            ->schema([
                                TextEntry::make('affected_items_detail')
                                    ->label('Affected Passenger Item(s)')
                                    ->columnSpanFull()
                                    ->state(function (Booking $record): string {
                                        $passengers = $record->passengers
                                            ->filter(fn ($p) => (float) $p->refund_amount > 0)
                                            ->sortBy('item_number');
                                        if ($passengers->isEmpty()) {
                                            return '—';
                                        }
                                        return $passengers->map(fn ($p) =>
                                            "Item {$p->item_number} – " . ($p->name ?? 'Passenger') .
                                            " (Refund: ₱" . number_format((float) $p->refund_amount, 2) . ")")
                                            ->implode('; ');
                                    }),
                                TextEntry::make('client_name')
                                    ->label('Client Name'),
                                TextEntry::make('client_email')
                                    ->label('Client Email')
                                    ->copyable(),
                                TextEntry::make('client_phone')
                                    ->label('Client Phone')
                                    ->default('—'),
                                TextEntry::make('route')
                                    ->label('Route')
                                    ->state(fn (Booking $record): string => $record->origin . ' → ' . $record->destination),
                                TextEntry::make('departure_date')
                                    ->label('Departure Date')
                                    ->date(),
                                TextEntry::make('schedule_service')
                                    ->label('Service / Vessel')
                                    ->default('—'),
                            ])
                            ->columns(3)
                            ->collapsed(),
                    ])
                    ->extraModalFooterActions([
                        Action::make('modalDownloadAcknowledgement')
                            ->label('Download Refund PDF')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->color('gray')
                            ->url(fn (Booking $record) => route('ticket.refund-acknowledgement', ['transaction_number' => $record->transaction_number]))
                            ->openUrlInNewTab()
                            ->visible(fn (Booking $record) => $record->refund_status === 'completed'),
                    ]),

                Action::make('verifyRefund')
                    ->label('Verify & Disburse')
                    ->icon('heroicon-m-check-badge')
                    ->button()
                    ->color('success')
                    ->visible(fn (Booking $record): bool => $record->refund_status !== 'completed')
                    ->form([
                        TextInput::make('refund_reference')
                            ->label('Disbursement Reference No. (GCash / Maya / Bank Ref)')
                            ->required()
                            ->placeholder('e.g. 100234981293'),
                        FileUpload::make('refund_proof')
                            ->label('Disbursement Proof Receipt')
                            ->helperText('Upload a screenshot of the GCash receipt or bank transfer confirmation.')
                            ->directory('refunds')
                            ->disk('public')
                            ->required()
                            ->acceptedFileTypes(['image/*', 'application/pdf'])
                            ->maxSize(10240),
                        Textarea::make('refund_notes')
                            ->label('Internal Notes')
                            ->placeholder('Optional notes for internal record-keeping...'),
                    ])
                    ->action(function (Booking $record, array $data): void {
                        try {
                            $record->update([
                                'refund_status' => 'completed',
                                'refund_reference' => trim($data['refund_reference']),
                                'refund_proof' => $data['refund_proof'],
                                'refund_notes' => $data['refund_notes'] ?? null,
                                'refund_processed_at' => now(),
                                'refund_processed_by_user_id' => Auth::id(),
                            ]);

                            // Update individual passenger items
                            foreach ($record->passengers as $passenger) {
                                if ((float) $passenger->refund_amount > 0 || in_array($passenger->status, [\App\Models\Passenger::STATUS_REFUND_PENDING, \App\Models\Passenger::STATUS_CANCELLED], true)) {
                                    $passenger->update([
                                        'status' => \App\Models\Passenger::STATUS_REFUNDED,
                                        'refund_status' => 'completed',
                                        'refund_reference' => trim($data['refund_reference']),
                                        'refund_proof' => $data['refund_proof'],
                                        'refund_processed_at' => now(),
                                        'refund_processed_by_user_id' => Auth::id(),
                                    ]);
                                }
                            }

                            // Send email confirmation with PDF and proof attached
                            if (filled($record->client_email)) {
                                try {
                                    Mail::to($record->client_email)->send(new RefundCompletedMail($record));
                                } catch (Throwable $e) {
                                    Log::error("Failed to send refund email to {$record->client_email}: " . $e->getMessage());
                                }
                            }

                            // Send In-App Notification
                            if ($record->user_id) {
                                UserNotification::notify(
                                    $record->user_id,
                                    '💰 Refund Processed & Disbursed',
                                    "Your refund of ₱" . number_format((float) $record->refund_amount, 2) . " for booking #{$record->transaction_number} has been disbursed (Ref: {$record->refund_reference}).",
                                    'booking',
                                    $record->id,
                                    ['transaction_number' => $record->transaction_number]
                                );
                            }

                            Notification::make()
                                ->title('Refund Disbursed Successfully')
                                ->body("Booking #{$record->transaction_number} has been marked as disbursed. Refund Acknowledgement and proof sent to {$record->client_email}.")
                                ->success()
                                ->send();
                        } catch (Throwable $e) {
                            Log::error("Refund verification error for booking {$record->id}: " . $e->getMessage());
                            Notification::make()
                                ->title('Refund Verification Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('acknowledgement')
                    ->label('PDF')
                    ->icon('heroicon-m-document-arrow-down')
                    ->color('gray')
                    ->url(fn (Booking $record): string => route('ticket.refund-acknowledgement', ['transaction_number' => $record->transaction_number]))
                    ->openUrlInNewTab()
                    ->visible(fn (Booking $record): bool => $record->refund_status === 'completed'),

                Action::make('viewProof')
                    ->label('Proof')
                    ->icon('heroicon-m-photo')
                    ->color('info')
                    ->visible(fn (Booking $record): bool => filled($record->refund_proof))
                    ->url(fn (Booking $record): string => storage_asset_path($record->refund_proof))
                    ->openUrlInNewTab(),
            ]);
    }
}

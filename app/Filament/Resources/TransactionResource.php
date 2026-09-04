<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Mail\BookingConfirmation;
use App\Models\Booking;
use App\Models\Transaction;
use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use Throwable;

class TransactionResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Bookings';
    protected static ?int $navigationSort = 20;
    protected static ?string $navigationLabel = 'Transactions';

    protected static ?string $model = Transaction::class;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user instanceof User && $user->hasAdminPermission('transactions');
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
        return parent::getEloquentQuery();
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Payment')
                    ->schema([
                        TextEntry::make('payment_status')
                            ->badge()
                            ->formatStateUsing(function (string $state, Transaction $record) {
                                if ($state === 'cancelled' && $record->booking && $record->booking->refund_amount > 0) {
                                    return 'Refunded';
                                }
                                return ucfirst($state);
                            })
                            ->color(fn (string $state, Transaction $record): string => match (true) {
                                $state === 'paid' => 'success',
                                $state === 'pending' => 'warning',
                                $state === 'cancelled' && $record->booking && $record->booking->refund_amount > 0 => 'info',
                                $state === 'cancelled' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('payment_reference')
                            ->label('Payment Ref No. (GCash / Maya / Bank)')
                            ->default('N/A'),
                        TextEntry::make('created_at')
                            ->label('Submitted at')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label('Last updated')
                            ->dateTime(),
                        TextEntry::make('verification_timer')
                            ->label('Lock Timer')
                            ->badge()
                            ->state(fn (Transaction $record): string => $record->verificationTimerLabel())
                            ->tooltip(fn (Transaction $record): ?string => $record->verificationTimerTooltip()),
                        ViewEntry::make('proof_of_payment')
                            ->label('Proof of payment')
                            ->view('filament.infolists.entries.proof-image')
                            ->visible(fn (?Transaction $record): bool => filled($record?->proof_of_payment))
                            ->columnSpanFull(),
                        TextEntry::make('proof_of_payment')
                            ->label('Proof of payment')
                            ->default('No proof uploaded yet.')
                            ->visible(fn (?Transaction $record): bool => blank($record?->proof_of_payment))
                            ->columnSpanFull(),
                        ViewEntry::make('rebooking_proof_of_payment')
                            ->label('Rebooking proof of payment')
                            ->view('filament.infolists.entries.proof-image')
                            ->visible(fn (?Transaction $record): bool => filled($record?->rebooking_proof_of_payment))
                            ->columnSpanFull(),
                        TextEntry::make('rebooking_proof_of_payment')
                            ->label('Rebooking proof of payment')
                            ->default('No rebooking proof uploaded yet.')
                            ->visible(fn (?Transaction $record): bool => blank($record?->rebooking_proof_of_payment))
                            ->columnSpanFull(),
                        TextEntry::make('confirmation_url')
                            ->label('Confirmation URL')
                            ->visible(fn (?Transaction $record): bool => filled($record?->confirmation_url))
                            ->url(fn (?Transaction $record): ?string => $record?->confirmation_url)
                            ->default(fn (?Transaction $record) => $record?->confirmation_url)
                            ->columnSpanFull(),
                        TextEntry::make('confirmation_pdf')
                            ->label('Confirmation PDF')
                            ->visible(fn (?Transaction $record): bool => filled($record?->confirmation_pdf))
                            ->state('View PDF')
                            ->url(fn (?Transaction $record): ?string => $record?->confirmation_pdf ? Storage::disk('public')->url($record->confirmation_pdf) : null)
                            ->openUrlInNewTab()
                            ->columnSpanFull(),
                        ViewEntry::make('student_discount_proofs')
                            ->label('Student discount proof images')
                            ->view('filament.infolists.entries.student-proof-images')
                            ->visible(fn (?Transaction $record): bool => ! empty($record?->student_discount_proofs))
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Section::make('Booking')
                    ->schema([
                        TextEntry::make('booking.transaction_number')
                            ->label('Transaction number'),
                        TextEntry::make('booking.status')
                            ->label('Booking status')
                            ->badge()
                            ->formatStateUsing(function (string $state, Transaction $record) {
                                if (in_array($state, ['cancelled', 'operator_cancelled']) && $record->booking && $record->booking->refund_amount > 0) {
                                    return 'Refunded';
                                }
                                if ($state === 'operator_cancelled') {
                                    return 'Cancelled by Operator';
                                }
                                return ucfirst($state);
                            })
                            ->color(fn (string $state, Transaction $record): string => match (true) {
                                $state === 'pending' => 'warning',
                                $state === 'confirmed' => 'success',
                                in_array($state, ['cancelled', 'operator_cancelled']) && $record->booking && $record->booking->refund_amount > 0 => 'info',
                                in_array($state, ['cancelled', 'operator_cancelled', 'rejected']) => 'danger',
                                default => 'secondary',
                            }),
                        TextEntry::make('booking.client_name')
                            ->label('Client name'),
                        TextEntry::make('booking.client_email')
                            ->label('Client email'),
                        TextEntry::make('booking.origin')
                            ->label('Origin'),
                        TextEntry::make('booking.destination')
                            ->label('Destination'),
                        TextEntry::make('booking.operator_name')
                            ->label('Operator')
                            ->state(fn (Transaction $record): string => $record->booking?->getOperatorName() ?? '—'),
                        TextEntry::make('booking.departure_date')
                            ->label('Departure date')
                            ->date(),
                        TextEntry::make('booking.return_date')
                            ->label('Return date')
                            ->date()
                            ->placeholder('One-way'),
                        TextEntry::make('booking.schedule_summary')
                            ->label('Ferry schedule')
                            ->placeholder('Not recorded'),
                        TextEntry::make('booking.schedule_price')
                            ->label('Schedule price (per passenger)')
                            ->money('PHP')
                            ->placeholder('—'),
                        TextEntry::make('booking.baggage_details')
                            ->label('Extra baggage')
                            ->visible(fn (?Transaction $record): bool => (bool) $record?->booking?->has_extra_baggage)
                            ->state(fn (Transaction $record): string => "{$record->booking->extra_baggage_weight} kg — ₱" . number_format((float) $record->booking->extra_baggage_price, 2)),
                        TextEntry::make('booking.total_price')
                            ->label('Total amount')
                            ->money('PHP'),
                    ])
                    ->columns(3),

                Section::make('Price Breakdown')
                    ->schema([
                        TextEntry::make('price_breakdown_table')
                            ->label('')
                            ->html()
                            ->state(function (Transaction $record): HtmlString {
                                $booking = $record->booking;
                                if (! $booking) {
                                    return new HtmlString('<span class="text-gray-500">No booking linked.</span>');
                                }

                                $breakdown = $booking->getPriceBreakdown();
                                $total = (float) $booking->total_price;

                                // Reconcile computed sum with stored total_price
                                $computedSum = array_sum(array_column($breakdown, 'amount'));
                                $diff = round($total - $computedSum, 2);
                                if (abs($diff) >= 0.01) {
                                    $breakdown[] = $diff > 0
                                        ? ['label' => 'Service Fees & Adjustments', 'amount' => $diff, 'class' => 'text-slate-500']
                                        : ['label' => 'Discount / Adjustment', 'amount' => $diff, 'class' => 'text-green-600'];
                                }

                                $html = '<div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">';
                                $html .= '<table class="w-full text-sm text-left text-gray-700 dark:text-gray-200">';
                                $html .= '<thead class="text-xs uppercase bg-gray-50 dark:bg-gray-700/50 text-gray-500 border-b border-gray-200 dark:border-gray-700">';
                                $html .= '<tr><th class="py-2.5 px-3">Item / Description</th><th class="py-2.5 px-3 text-right">Amount</th></tr>';
                                $html .= '</thead>';
                                $html .= '<tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">';

                                foreach ($breakdown as $item) {
                                    $label = htmlspecialchars($item['label'] ?? '');
                                    $amount = (float) ($item['amount'] ?? 0);
                                    $isDiscount = $amount < 0;
                                    $rowClass = $item['class'] ?? '';
                                    if ($isDiscount && !$rowClass) {
                                        $rowClass = 'text-green-600 font-medium';
                                    }
                                    $displayAmount = ($isDiscount ? '-₱' : '₱') . number_format(abs($amount), 2);

                                    $html .= '<tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/20">';
                                    $html .= '<td class="py-2 px-3 ' . htmlspecialchars($rowClass) . '">' . $label . '</td>';
                                    $html .= '<td class="py-2 px-3 text-right font-medium ' . htmlspecialchars($rowClass) . '">' . $displayAmount . '</td>';
                                    $html .= '</tr>';
                                }

                                $html .= '</tbody>';
                                $html .= '<tfoot class="border-t-2 border-gray-300 dark:border-gray-600 font-bold">';
                                $html .= '<tr>';
                                $html .= '<td class="py-3 px-3 text-base text-gray-900 dark:text-white">Grand Total</td>';
                                $html .= '<td class="py-3 px-3 text-right text-base text-primary-600 dark:text-primary-400">₱' . number_format($total, 2) . '</td>';
                                $html .= '</tr>';
                                $html .= '</tfoot>';
                                $html .= '</table>';
                                $html .= '</div>';

                                return new HtmlString($html);
                            })
                            ->columnSpanFull(),
                    ]),

                Section::make('Passenger Items & Financial Breakdown')
                    ->schema([
                        TextEntry::make('passengers_summary')
                            ->label('')
                            ->html()
                            ->state(function (Transaction $record): HtmlString {
                                $passengers = $record->booking?->passengers?->sortBy('item_number') ?? collect();
                                if ($passengers->isEmpty()) {
                                    return new HtmlString('<span class="text-gray-500">No passenger items recorded.</span>');
                                }

                                $html  = '<div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800 space-y-4">';
                                $html .= '<div class="overflow-x-auto">';
                                $html .= '<table class="w-full text-sm text-left text-gray-700 dark:text-gray-200">';
                                $html .= '<thead class="text-xs uppercase bg-gray-50 dark:bg-gray-700/50 text-gray-500 border-b border-gray-200 dark:border-gray-700">';
                                $html .= '<tr>';
                                $html .= '<th class="py-2.5 px-3">Item #</th>';
                                $html .= '<th class="py-2.5 px-3">Passenger</th>';
                                $html .= '<th class="py-2.5 px-3">Status</th>';
                                $html .= '<th class="py-2.5 px-3 text-right">Fare & Class</th>';
                                $html .= '<th class="py-2.5 px-3 text-right">Discount</th>';
                                $html .= '<th class="py-2.5 px-3 text-right">Voucher/Pts</th>';
                                $html .= '<th class="py-2.5 px-3 text-right">Web Fee</th>';
                                $html .= '<th class="py-2.5 px-3 text-right">Tx Fee</th>';
                                $html .= '<th class="py-2.5 px-3 text-right">Total</th>';
                                $html .= '</tr>';
                                $html .= '</thead>';
                                $html .= '<tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">';

                                foreach ($passengers as $p) {
                                    $itemNum = $p->item_number ?? 1;
                                    $name = htmlspecialchars($p->name ?? 'Passenger');
                                    $type = htmlspecialchars(ucfirst($p->type ?? 'adult'));
                                    $ticket = $p->ticket_number ? '<div class="text-xs text-gray-400 font-mono">' . htmlspecialchars($p->ticket_number) . '</div>' : '';
                                    $statusLabel = htmlspecialchars($p->getStatusLabel());
                                    $statusColor = $p->getStatusColor();
                                    $badgeColorClass = match($statusColor) {
                                        'success' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
                                        'danger'  => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
                                        'warning' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
                                        'info'    => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
                                        'primary' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300',
                                        default   => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                    };

                                    $fareAndClass = $p->getEffectiveFareAndClass();
                                    $discAmt = (float) ($p->discount_amount ?? 0);
                                    $voucherPoints = (float) ($p->voucher_discount_share ?? 0) + (float) ($p->points_discount_share ?? 0);
                                    $webFee = $p->getEffectiveWebAdminFee();
                                    $txFee = $p->getEffectiveTransactionFee();
                                    $itemTotal = $p->getEffectiveItemTotal();

                                    $discCell = $discAmt > 0
                                        ? '<span class="text-emerald-600 dark:text-emerald-400 font-medium">-₱' . number_format($discAmt, 2) . '</span>' . ($p->discount ? ' <span class="text-[10px] bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 px-1 py-0.5 rounded border border-emerald-200 dark:border-emerald-800">' . htmlspecialchars($p->discount->name) . '</span>' : '')
                                        : '<span class="text-gray-400">—</span>';

                                    $voucherCell = $voucherPoints > 0
                                        ? '<span class="text-emerald-600 dark:text-emerald-400 font-medium">-₱' . number_format($voucherPoints, 2) . '</span>'
                                        : '<span class="text-gray-400">—</span>';

                                    $html .= '<tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/20">';
                                    $html .= '<td class="py-2.5 px-3 font-bold text-gray-900 dark:text-white">Item ' . $itemNum . '</td>';
                                    $html .= '<td class="py-2.5 px-3"><span class="font-medium text-gray-900 dark:text-white">' . $name . '</span> <span class="text-xs text-gray-500">(' . $type . ')</span>' . $ticket . '</td>';
                                    $html .= '<td class="py-2.5 px-3"><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold ' . $badgeColorClass . '">' . $statusLabel . '</span></td>';
                                    $html .= '<td class="py-2.5 px-3 text-right font-medium">₱' . number_format($fareAndClass, 2) . '</td>';
                                    $html .= '<td class="py-2.5 px-3 text-right">' . $discCell . '</td>';
                                    $html .= '<td class="py-2.5 px-3 text-right">' . $voucherCell . '</td>';
                                    $html .= '<td class="py-2.5 px-3 text-right text-gray-500">₱' . number_format($webFee, 2) . '</td>';
                                    $html .= '<td class="py-2.5 px-3 text-right text-gray-500">₱' . number_format($txFee, 2) . '</td>';
                                    $html .= '<td class="py-2.5 px-3 text-right font-bold text-primary-600 dark:text-primary-400">₱' . number_format($itemTotal, 2) . '</td>';
                                    $html .= '</tr>';
                                }

                                $html .= '</tbody>';
                                $html .= '</table>';
                                $html .= '</div>';
                                $html .= '</div>';

                                return new HtmlString($html);
                            })
                            ->columnSpanFull(),
                    ]),

                Section::make('Accommodations')
                    ->schema([
                        TextEntry::make('accommodations_summary')
                            ->label('')
                            ->state(function (Transaction $record): array {
                                $accommodations = $record->booking?->accommodations ?? collect();

                                if ($accommodations->isEmpty()) {
                                    return ['No accommodations selected.'];
                                }

                                return $accommodations
                                    ->map(fn ($accommodation) => "{$accommodation->name} — ₱".number_format((float) $accommodation->pivot->price, 2))
                                    ->all();
                            })
                            ->listWithLineBreaks(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->poll('10s')
            ->columns([
                TextColumn::make('booking.transaction_number')
                    ->label('Transaction')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('payment_status')
                    ->label('Payment Status')
                    ->badge()
                    ->formatStateUsing(function (string $state, Transaction $record) {
                        if ($state === 'cancelled' && $record->booking && $record->booking->refund_amount > 0) {
                            return 'Refunded';
                        }
                        return ucfirst($state);
                    })
                    ->color(fn (string $state, Transaction $record): string => match (true) {
                        $state === 'paid' => 'success',
                        $state === 'pending' => 'warning',
                        $state === 'cancelled' && $record->booking && $record->booking->refund_amount > 0 => 'info',
                        in_array($state, ['cancelled', 'rejected']) => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('payment_reference')
                    ->label('Payment Ref No.')
                    ->searchable()
                    ->placeholder('N/A'),
                TextColumn::make('booking.operator_name')
                    ->label('Operator')
                    ->state(fn (Transaction $record): string => $record->booking?->getOperatorName() ?? '—')
                    ->toggleable(),
                TextColumn::make('booking.status')
                    ->label('Booking Status')
                    ->badge()
                    ->formatStateUsing(function (?string $state, Transaction $record) {
                        if (in_array($state, ['cancelled', 'operator_cancelled']) && $record->booking && $record->booking->refund_amount > 0) {
                            return 'Refunded';
                        }
                        if ($state === 'operator_cancelled') {
                            return 'Cancelled by Operator';
                        }
                        return $state ? ucfirst($state) : null;
                    })
                    ->color(fn (?string $state, Transaction $record): string => match (true) {
                        $state === 'pending' => 'warning',
                        $state === 'confirmed' => 'success',
                        in_array($state, ['cancelled', 'operator_cancelled']) && $record->booking && $record->booking->refund_amount > 0 => 'info',
                        in_array($state, ['cancelled', 'operator_cancelled', 'rejected']) => 'danger',
                        default => 'secondary',
                    }),
                TextColumn::make('verification_timer')
                    ->label('Lock Timer')
                    ->badge()
                    ->icon(fn (Transaction $record): string => match (true) {
                        $record->payment_status !== 'pending' => 'heroicon-m-check-circle',
                        ! $record->isVerificationLocked() => 'heroicon-m-check-badge',
                        default => 'heroicon-m-clock',
                    })
                    ->color(fn (Transaction $record): string => match (true) {
                        $record->payment_status !== 'pending' => 'gray',
                        ! $record->isVerificationLocked() => 'success',
                        default => 'warning',
                    })
                    ->state(fn (Transaction $record): string => $record->verificationTimerLabel())
                    ->tooltip(fn (Transaction $record): ?string => $record->verificationTimerTooltip()),
                TextColumn::make('booking.client_name')
                    ->label('Client name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('payment_status')
                    ->label('Payment status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'cancelled' => 'Cancelled',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('verify')
                    ->label('Verify booking')
                    ->visible(fn (Transaction $record): bool => $record->payment_status === 'pending')
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
                    ->disabled(fn (Transaction $record): bool => $record->payment_status === 'unpaid' || $record->isVerificationLocked() || ($record->booking && $record->booking->isReviewClaimedByOther(Auth::user())))
                    ->tooltip(fn (Transaction $record): ?string => match (true) {
                        $record->booking && $record->booking->isReviewClaimedByOther(Auth::user()) => $record->booking->getReviewClaimTooltip(Auth::user()),
                        $record->payment_status === 'unpaid' => 'Cannot verify: Payment status is Unpaid.',
                        default => $record->verificationTimerTooltip(),
                    })
                    ->action(function (Transaction $record, array $data): void {
                        if (empty($data['confirmation_url']) && empty($data['confirmation_pdf'])) {
                            throw new \Exception('Please provide either a confirmation URL or upload a PDF before verifying.');
                        }

                        $alreadyVerifiedBy = null;
                        $shouldSendEmail = false;
                        $isRebooking = false;
                        $ticketUrl = !empty($data['confirmation_url']) ? trim($data['confirmation_url']) : null;
                        $txNumber = $record->booking?->transaction_number ?? (string) $record->id;
                        $confirmationPdfPath = Booking::resolveUploadedPdfPath($data['confirmation_pdf'] ?? null, $txNumber);
                        $receiptPath = $confirmationPdfPath;
                        $receiptDisk = $confirmationPdfPath ? 'public' : null;

                        DB::transaction(function () use (
                            $record, $ticketUrl, $confirmationPdfPath, $receiptPath, $receiptDisk,
                            &$alreadyVerifiedBy, &$shouldSendEmail, &$isRebooking
                        ) {
                            $lockedTx = Transaction::where('id', $record->id)
                                ->with(['booking', 'verifiedBy'])
                                ->lockForUpdate()
                                ->first();

                            if (! $lockedTx || $lockedTx->payment_status === 'paid' || $lockedTx->verified_by_user_id !== null) {
                                $alreadyVerifiedBy = $lockedTx?->verifiedBy?->name ?? 'another staff member';
                                return;
                            }

                            $booking = $lockedTx->booking ? Booking::where('id', $lockedTx->booking->id)->lockForUpdate()->first() : null;
                            if ($booking && ($booking->status === 'confirmed' || $booking->verified_by_user_id !== null)) {
                                $alreadyVerifiedBy = $booking->verifiedBy?->name ?? $lockedTx->verifiedBy?->name ?? 'another staff member';
                                return;
                            }

                            $staffUserId = Auth::id();
                            $now = now();

                            $lockedTx->update([
                                'payment_status' => 'paid',
                                'confirmation_url' => $ticketUrl,
                                'confirmation_pdf' => $confirmationPdfPath,
                                'verified_by_user_id' => $staffUserId,
                                'verified_at' => $now,
                            ]);

                            if ($booking) {
                                $booking->update([
                                    'verified_by_user_id' => $staffUserId,
                                    'verified_at'         => $now,
                                ]);
                                $booking->setRelation('transaction', $lockedTx);

                                if ($booking->rebooking_status === 'pending') {
                                    $isRebooking = true;
                                    // Rebooking path — handles status, dates, points, and email internally
                                    $booking->verifyRebooking($ticketUrl, $receiptPath, $receiptDisk);
                                } else {
                                    $booking->update(['status' => 'confirmed']);
                                    app(\App\Services\GraciaPointsService::class)->awardPointsForBooking($booking, Auth::user());
                                    $shouldSendEmail = true;
                                }
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

                        if ($isRebooking) {
                            Notification::make()
                                ->title('Rebooking verified')
                                ->body('Rebooking verified and confirmation email sent.')
                                ->success()
                                ->send();
                            return;
                        }

                        if ($shouldSendEmail && $record->booking) {
                            $booking = $record->booking->fresh();
                            try {
                                Mail::to($booking->client_email)->send(new BookingConfirmation($booking, $ticketUrl, $receiptPath, $receiptDisk));

                                Notification::make()
                                    ->title('Payment verified')
                                    ->body('Payment verified and confirmation email sent.')
                                    ->success()
                                    ->send();
                            } catch (Throwable $e) {
                                Log::error('Failed sending booking confirmation email (transaction verify)', [
                                    'transaction_id' => $record->id ?? null,
                                    'booking_id'     => $booking->id ?? null,
                                    'email'          => $booking->client_email ?? null,
                                    'error'          => $e->getMessage(),
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
                    ->visible(fn (Transaction $record): bool => $record->payment_status !== 'paid' && $record->booking?->status !== 'cancelled'),

                Tables\Actions\Action::make('reject')
                    ->label('Reject payment')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->visible(fn (Transaction $record): bool => $record->payment_status === 'pending' && ! in_array($record->booking?->status, ['cancelled', 'rejected']))
                    ->disabled(fn (Transaction $record): bool => $record->payment_status === 'unpaid'
                        || $record->isVerificationLocked()
                        || ($record->booking && $record->booking->isReviewClaimedByOther(Auth::user())))
                    ->tooltip(fn (Transaction $record): ?string => match (true) {
                        $record->booking && $record->booking->isReviewClaimedByOther(Auth::user()) => $record->booking->getReviewClaimTooltip(Auth::user()),
                        $record->payment_status === 'unpaid' => 'Cannot reject: Payment status is Unpaid.',
                        default => $record->verificationTimerTooltip(),
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
                    ->action(function (Transaction $record, array $data): void {
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'view' => Pages\ViewTransaction::route('/{record}'),
        ];
    }
}

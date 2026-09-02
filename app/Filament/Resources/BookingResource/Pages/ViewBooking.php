<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use App\Mail\BookingConfirmation;
use App\Models\Booking;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Throwable;

class ViewBooking extends ViewRecord
{
    protected static string $resource = BookingResource::class;

    private function resolveStorageUrl(?string $path = null): ?string
    {
        if (blank($path)) {
            return null;
        }

        $trimmed = trim($path);

        // Already a full URL
        if (filter_var($trimmed, FILTER_VALIDATE_URL)) {
            return $trimmed;
        }

        // Delegate to storage_asset_path which routes via /storage-file/ server endpoint
        return storage_asset_path($trimmed);
    }

    private function renderProofImageContent(?string $path = null): HtmlString
    {
        $url = $this->resolveStorageUrl($path);

        if (!$url) {
            return new HtmlString('<span class="text-gray-500">No proof uploaded.</span>');
        }

        return new HtmlString('<a href="' . e($url) . '" target="_blank"><img src="' . e($url) . '" class="rounded-lg border border-gray-700 max-w-full h-auto" alt="Proof of payment" /></a>');
    }

    private function renderPassengerIdLinkContent(?array $state = null): HtmlString
    {
        $url = is_array($state) ? ($state['id_image_front_url'] ?? null) : null;

        if (!$url) {
            return new HtmlString('<em>No image</em>');
        }

        return new HtmlString('<a href="' . e($url) . '" target="_blank" class="text-blue-600 underline">View Front ID</a>');
    }

    private function renderPassengerIdBackLinkContent(?array $state = null): HtmlString
    {
        $url = is_array($state) ? ($state['id_image_back_url'] ?? null) : null;

        if (!$url) {
            return new HtmlString('<em>No image</em>');
        }

        return new HtmlString('<a href="' . e($url) . '" target="_blank" class="text-blue-600 underline">View Back ID</a>');
    }

    private function renderPriceBreakdownContent(): HtmlString
    {
        $breakdown = $this->record->getPriceBreakdown();
        $total     = (float) $this->record->total_price;

        // Sum what the breakdown items give us
        $computedSum = array_sum(array_column($breakdown, 'amount'));

        // If there is a discrepancy between computed items and stored total_price,
        // insert a reconciliation row so the table always balances.
        $diff = round($total - $computedSum, 2);
        if (abs($diff) >= 0.01) {
            if ($diff > 0) {
                $breakdown[] = [
                    'label'  => 'Service Fees & Adjustments',
                    'amount' => $diff,
                    'class'  => 'text-slate-500',
                ];
            } else {
                $breakdown[] = [
                    'label'  => 'Discount / Adjustment',
                    'amount' => $diff,          // negative → shown as discount
                    'class'  => 'text-green-600',
                ];
            }
        }

        $html  = '<div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">';
        $html .= '<table class="w-full text-sm text-left text-gray-700 dark:text-gray-200">';
        $html .= '<thead class="text-xs uppercase bg-gray-50 dark:bg-gray-700/50 text-gray-500 border-b border-gray-200 dark:border-gray-700">';
        $html .= '<tr><th class="py-2.5 px-3">Item / Description</th><th class="py-2.5 px-3 text-right">Amount</th></tr>';
        $html .= '</thead>';
        $html .= '<tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">';

        foreach ($breakdown as $item) {
            $label     = htmlspecialchars($item['label'] ?? '');
            $amount    = (float) ($item['amount'] ?? 0);
            $isDiscount = $amount < 0;
            $rowClass  = $item['class'] ?? '';
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
    }

    private function renderPassengerItemsContent(): HtmlString
    {
        $allPassengers = $this->record->passengers->sortBy('item_number');
        if ($allPassengers->isEmpty()) {
            return new HtmlString('<span class="text-gray-500">No passenger items recorded.</span>');
        }

        $activePax = $this->record->getActivePassengers();
        $refundedPax = $this->record->getRefundedPassengers();
        $rebookedPax = $this->record->getRebookedHistoryPassengers();

        $renderRow = function ($p, $isArchived = false) {
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

            $fareAmt = $p->getEffectiveFareAmount();
            $classAmt = $p->getEffectiveAccommodationAmount();
            $fareDisplay = '₱' . number_format($fareAmt, 2);
            if ($classAmt > 0) {
                $fareDisplay .= '<br><span class="text-[10px] text-gray-500 font-normal">+ ₱' . number_format($classAmt, 2) . ' Class</span>';
            }
            $discAmt = (float) ($p->discount_amount ?? 0);
            $voucherPoints = (float) ($p->voucher_discount_share ?? 0) + (float) ($p->points_discount_share ?? 0);
            $webFee = $p->getEffectiveWebAdminFee();
            $txFee = $p->getEffectiveTransactionFee();
            $itemTotal = $p->getEffectiveItemTotal();

            $rowOpacity = $isArchived ? 'opacity-70 bg-gray-50/50 dark:bg-gray-800/30' : '';

            $discCell = $discAmt > 0
                ? '<span class="text-emerald-600 dark:text-emerald-400 font-medium">-₱' . number_format($discAmt, 2) . '</span>' . ($p->discount ? ' <span class="text-[10px] bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 px-1 py-0.5 rounded border border-emerald-200 dark:border-emerald-800">' . htmlspecialchars($p->discount->name) . '</span>' : '')
                : '<span class="text-gray-400">—</span>';

            $voucherCell = $voucherPoints > 0
                ? '<span class="text-emerald-600 dark:text-emerald-400 font-medium">-₱' . number_format($voucherPoints, 2) . '</span>'
                : '<span class="text-gray-400">—</span>';

            $row  = '<tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/20 ' . $rowOpacity . '">';
            $row .= '<td class="py-2.5 px-3 font-bold text-gray-900 dark:text-white">Item ' . $itemNum . '</td>';
            $row .= '<td class="py-2.5 px-3"><span class="font-medium text-gray-900 dark:text-white">' . $name . '</span> <span class="text-xs text-gray-500">(' . $type . ')</span>' . $ticket . '</td>';
            $row .= '<td class="py-2.5 px-3"><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold ' . $badgeColorClass . '">' . $statusLabel . '</span></td>';
            $row .= '<td class="py-2.5 px-3 text-right font-medium">' . $fareDisplay . '</td>';
            $row .= '<td class="py-2.5 px-3 text-right">' . $discCell . '</td>';
            $row .= '<td class="py-2.5 px-3 text-right">' . $voucherCell . '</td>';
            $row .= '<td class="py-2.5 px-3 text-right text-gray-500">₱' . number_format($webFee, 2) . '</td>';
            $row .= '<td class="py-2.5 px-3 text-right text-gray-500">₱' . number_format($txFee, 2) . '</td>';
            $row .= '<td class="py-2.5 px-3 text-right font-bold text-primary-600 dark:text-primary-400">₱' . number_format($itemTotal, 2) . '</td>';
            $row .= '</tr>';
            return $row;
        };

        $html  = '<div class="space-y-4">';

        // 1. Active Passengers Table
        $html .= '<div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800 space-y-2">';
        $html .= '<div class="flex items-center justify-between pb-2 border-b border-gray-100 dark:border-gray-700"><h4 class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">🟢 Active Boarding Manifest (' . $activePax->count() . ')</h4></div>';
        if ($activePax->isEmpty()) {
            $html .= '<p class="text-xs text-gray-500 italic py-2">All original items on this booking have been refunded or rescheduled.</p>';
        } else {
            $html .= '<div class="overflow-x-auto"><table class="w-full text-sm text-left text-gray-700 dark:text-gray-200">';
            $html .= '<thead class="text-xs uppercase bg-gray-50 dark:bg-gray-700/50 text-gray-500 border-b border-gray-200 dark:border-gray-700"><tr><th class="py-2.5 px-3">Item #</th><th class="py-2.5 px-3">Passenger</th><th class="py-2.5 px-3">Status</th><th class="py-2.5 px-3 text-right">Fare & Class</th><th class="py-2.5 px-3 text-right">Discount</th><th class="py-2.5 px-3 text-right">Voucher/Pts</th><th class="py-2.5 px-3 text-right">Web Fee</th><th class="py-2.5 px-3 text-right">Tx Fee</th><th class="py-2.5 px-3 text-right">Total</th></tr></thead>';
            $html .= '<tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">';
            foreach ($activePax as $p) {
                $html .= $renderRow($p, false);
            }
            $html .= '</tbody></table></div>';
        }
        $html .= '</div>';

        // 2. Refunded Items
        if ($refundedPax->isNotEmpty()) {
            $html .= '<div class="rounded-xl border border-amber-200 bg-amber-50/30 p-4 shadow-sm dark:border-amber-900/50 dark:bg-amber-950/20 space-y-2">';
            $html .= '<div class="flex items-center justify-between pb-2 border-b border-amber-200/50"><h4 class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">💰 Cancelled & Refunded Items (' . $refundedPax->count() . ')</h4></div>';
            $html .= '<div class="overflow-x-auto"><table class="w-full text-sm text-left text-gray-700 dark:text-gray-200">';
            $html .= '<thead class="text-xs uppercase bg-amber-100/50 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300 border-b border-amber-200"><tr><th class="py-2 px-3">Item #</th><th class="py-2 px-3">Passenger</th><th class="py-2 px-3">Status</th><th class="py-2 px-3 text-right">Refund Amount</th><th class="py-2 px-3 text-right">Fee Deduction</th><th class="py-2 px-3">Destination</th></tr></thead>';
            $html .= '<tbody class="divide-y divide-amber-100 dark:divide-amber-900/30">';
            foreach ($refundedPax as $rp) {
                $statusBadge = $rp->refund_status === 'completed'
                    ? '<span class="px-2 py-0.5 rounded text-xs font-bold bg-green-100 text-green-800">Disbursed</span>'
                    : '<span class="px-2 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-800">Pending Review</span>';
                $html .= '<tr>';
                $html .= '<td class="py-2 px-3 font-bold">Item ' . $rp->item_number . '</td>';
                $html .= '<td class="py-2 px-3">' . htmlspecialchars($rp->name ?? 'Passenger') . '</td>';
                $html .= '<td class="py-2 px-3">' . $statusBadge . '</td>';
                $html .= '<td class="py-2 px-3 text-right font-bold text-amber-700">₱' . number_format((float) $rp->refund_amount, 2) . '</td>';
                $html .= '<td class="py-2 px-3 text-right text-gray-500">₱' . number_format((float) $rp->cancellation_fee, 2) . '</td>';
                $html .= '<td class="py-2 px-3 text-xs text-gray-600">' . htmlspecialchars($rp->refund_destination ?? '—') . '</td>';
                $html .= '</tr>';
            }
            $html .= '</tbody></table></div>';
            $html .= '</div>';
        }

        // 3. Rebooked History
        if ($rebookedPax->isNotEmpty()) {
            $html .= '<div class="rounded-xl border border-purple-200 bg-purple-50/30 p-4 shadow-sm dark:border-purple-900/50 dark:bg-purple-950/20 space-y-2">';
            $html .= '<div class="flex items-center justify-between pb-2 border-b border-purple-200/50"><h4 class="text-xs font-bold uppercase tracking-wider text-purple-700 dark:text-purple-400">🔄 Rescheduled / Rebooked History (' . $rebookedPax->count() . ')</h4></div>';
            $html .= '<div class="overflow-x-auto"><table class="w-full text-sm text-left text-gray-700 dark:text-gray-200">';
            $html .= '<thead class="text-xs uppercase bg-purple-100/50 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300 border-b border-purple-200"><tr><th class="py-2 px-3">Old Item #</th><th class="py-2 px-3">Passenger</th><th class="py-2 px-3">Status</th><th class="py-2 px-3">History Note</th></tr></thead>';
            $html .= '<tbody class="divide-y divide-purple-100 dark:divide-purple-900/30">';
            foreach ($rebookedPax as $reb) {
                $html .= '<tr>';
                $html .= '<td class="py-2 px-3 font-bold">Item ' . $reb->item_number . '</td>';
                $html .= '<td class="py-2 px-3">' . htmlspecialchars($reb->name ?? 'Passenger') . '</td>';
                $html .= '<td class="py-2 px-3"><span class="px-2 py-0.5 rounded text-xs font-bold bg-purple-100 text-purple-800">Rescheduled</span></td>';
                $html .= '<td class="py-2 px-3 text-xs text-purple-700">Replaced by active itinerary replacement item</td>';
                $html .= '</tr>';
            }
            $html .= '</tbody></table></div>';
            $html .= '</div>';
        }

        $html .= '</div>';

        return new HtmlString($html);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Passenger Items & Financial Breakdown')
                    ->schema([
                        Placeholder::make('passenger_items_breakdown')
                            ->label('')
                            ->content(fn (): HtmlString => $this->renderPassengerItemsContent())
                            ->columnSpanFull(),
                    ]),
                Section::make('Price Breakdown')
                    ->schema([
                        Placeholder::make('price_breakdown')
                            ->label('')
                            ->content(fn (): HtmlString => $this->renderPriceBreakdownContent())
                            ->columnSpanFull(),
                    ]),
                Section::make('Booking details')
                    ->schema([
                        TextInput::make('transaction_number')
                            ->label('Transaction number'),
                        TextInput::make('client_name')
                            ->label('Client name'),
                        TextInput::make('client_email')
                            ->label('Client email'),
                        TextInput::make('client_phone')
                            ->label('Contact number')
                            ->placeholder('—'),
                        TextInput::make('origin')
                            ->label('Origin'),
                        TextInput::make('destination')
                            ->label('Destination'),
                        TextInput::make('operator_name')
                            ->label('Operator'),
                        TextInput::make('status')
                            ->label('Booking status'),
                        TextInput::make('schedule_service')
                            ->label('Schedule'),
                        TextInput::make('schedule_departure_time')
                            ->label('Departure time'),
                        TextInput::make('schedule_arrival_time')
                            ->label('Arrival time'),
                        DatePicker::make('departure_date')
                            ->label('Departure date'),
                        DatePicker::make('return_date')
                            ->label('Return date'),
                        TextInput::make('total_price')
                            ->label('Total price')
                            ->prefix('₱'),
                        TextInput::make('baggage_details')
                            ->label('Extra baggage')
                            ->visible(fn (): bool => (bool) $this->record->has_extra_baggage),
                        TextInput::make('transaction_payment_status')
                            ->label('Payment status'),
                        TextInput::make('payment_reference')
                            ->label('Payment Ref No. (GCash/Maya)')
                            ->readOnly()
                            ->dehydrated(false),
                        TextInput::make('verification_timer')
                            ->label('Lock timer')
                            ->readOnly()
                            ->dehydrated(false),
                        TextInput::make('proof_uploaded')
                            ->label('Proof uploaded'),
                        TextInput::make('confirmation_url')
                            ->label('Confirmation URL')
                            ->readOnly()
                            ->dehydrated(false)
                            ->placeholder('—'),
                        Placeholder::make('confirmation_pdf')
                            ->label('Confirmation PDF')
                            ->content(function (): HtmlString {
                                $pdf = $this->record->transaction?->confirmation_pdf;
                                if (! $pdf) {
                                    return new HtmlString('<span class="text-gray-500">—</span>');
                                }
                                $url = storage_asset_path($pdf);
                                return new HtmlString('<a href="' . e($url) . '" target="_blank" class="inline-flex items-center gap-1 font-semibold text-primary-600 hover:underline">📄 View Uploaded Ticket PDF</a>');
                            })
                            ->visible(fn (): bool => filled($this->record->transaction?->confirmation_pdf)),

                        Placeholder::make('proof_image')
                            ->label('Proof of payment')
                            ->content(fn (): HtmlString => $this->renderProofImageContent($this->record->transaction?->proof_of_payment ?? $this->record->transaction?->proof_url)),
                        Placeholder::make('student_discount_proofs')
                            ->label('Student discount proof images')
                            ->content(function (): HtmlString {
                                $proofs = $this->record->transaction?->student_discount_proofs ?? [];

                                if (empty($proofs)) {
                                    return new HtmlString('<span class="text-gray-500">No student discount proof uploaded.</span>');
                                }

                                $html = '<div class="space-y-4">';
                                foreach ($proofs as $proof) {
                                    if (!is_array($proof)) {
                                        continue;
                                    }

                                    $front = $proof['front'] ?? null;
                                    $back = $proof['back'] ?? null;
                                    $name = $proof['passenger_name'] ?? 'Student proof';
                                    $studentNumber = $proof['student_number'] ?? null;

                                    $html .= '<div class="rounded-lg border border-gray-200 p-3">';
                                    $html .= '<p class="font-semibold text-sm">' . e($name) . '</p>';
                                    if ($studentNumber) {
                                        $html .= '<p class="text-xs text-gray-500">Student number: ' . e($studentNumber) . '</p>';
                                    }
                                    $html .= '<div class="mt-3 grid gap-3 md:grid-cols-2">';

                                    $frontUrl = $this->resolveStorageUrl($front);
                                    if ($frontUrl) {
                                        $html .= '<div><p class="mb-1 text-xs uppercase tracking-wide text-gray-500">Front</p><a href="' . e($frontUrl) . '" target="_blank"><img src="' . e($frontUrl) . '" class="max-h-60 rounded-md border border-gray-300 object-contain" alt="Student proof front" /></a></div>';
                                    }

                                    $backUrl = $this->resolveStorageUrl($back);
                                    if ($backUrl) {
                                        $html .= '<div><p class="mb-1 text-xs uppercase tracking-wide text-gray-500">Back</p><a href="' . e($backUrl) . '" target="_blank"><img src="' . e($backUrl) . '" class="max-h-60 rounded-md border border-gray-300 object-contain" alt="Student proof back" /></a></div>';
                                    }

                                    $html .= '</div></div>';
                                }

                                return new HtmlString($html . '</div>');
                            })
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Passenger Items')
                    ->schema([
                        Placeholder::make('passenger_items_table')
                            ->label('')
                            ->columnSpanFull()
                            ->content(function (): HtmlString {
                                $passengers = $this->record->passengers->sortBy('item_number');

                                if ($passengers->isEmpty()) {
                                    return new HtmlString('<p class="text-sm text-gray-500">No passengers recorded.</p>');
                                }

                                $statusColors = [
                                    'pending'            => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300',
                                    'confirmed'          => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
                                    'cancelled'          => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
                                    'operator_cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
                                    'operator_rebooking' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300',
                                    'refund_pending'     => 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300',
                                    'refunded'           => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
                                    'rebooking_pending'  => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300',
                                    'rebooked'           => 'bg-teal-100 text-teal-800 dark:bg-teal-900/40 dark:text-teal-300',
                                ];

                                $html  = '<div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">';
                                $html .= '<table class="w-full text-sm text-left text-gray-700 dark:text-gray-200">';
                                $html .= '<thead class="text-xs uppercase bg-gray-50 dark:bg-gray-800 text-gray-500 border-b border-gray-200 dark:border-gray-700">';
                                $html .= '<tr>
                                    <th class="py-2.5 px-3">Item #</th>
                                    <th class="py-2.5 px-3">Status</th>
                                    <th class="py-2.5 px-3">Passenger</th>
                                    <th class="py-2.5 px-3">Type</th>
                                    <th class="py-2.5 px-3">Birthdate</th>
                                    <th class="py-2.5 px-3">Discount</th>
                                    <th class="py-2.5 px-3 text-right">Fare & Class</th>
                                    <th class="py-2.5 px-3 text-right">Discount Amt</th>
                                    <th class="py-2.5 px-3 text-right">Voucher</th>
                                    <th class="py-2.5 px-3 text-right">Points</th>
                                    <th class="py-2.5 px-3 text-right">Fees</th>
                                    <th class="py-2.5 px-3 text-right font-bold">Item Total</th>
                                    <th class="py-2.5 px-3">IDs</th>
                                </tr>';
                                $html .= '</thead>';
                                $html .= '<tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">';

                                foreach ($passengers as $p) {
                                    $itemNum    = $p->item_number ?? '—';
                                    $ticketNum  = $p->ticket_number ?? '—';
                                    $status     = $p->status ?? 'pending';
                                    $statusLabel = $p->getStatusLabel();
                                    $colorClass = $statusColors[$status] ?? 'bg-gray-100 text-gray-700';
                                    $name       = e($p->name ?? 'N/A');
                                    $type       = e(strtoupper($p->type ?? 'adult'));
                                    $bday       = $p->birthdate ? $p->birthdate->format('Y-m-d') : 'N/A';
                                    $discount   = e($p->discount?->name ?: 'None');
                                    $fareAmt = $p->getEffectiveFareAmount();
                                    $classAmt = $p->getEffectiveAccommodationAmount();
                                    $fareAndClassAmt = '₱' . number_format($fareAmt, 2);
                                    if ($classAmt > 0) {
                                        $fareAndClassAmt .= '<br><span class="text-[10px] text-gray-500 font-normal">+ ₱' . number_format($classAmt, 2) . ' Class</span>';
                                    }
                                    $discAmt    = $p->discount_amount > 0 ? '-₱' . number_format((float) $p->discount_amount, 2) : '—';
                                    $voucherAmt = $p->voucher_discount_share > 0 ? '-₱' . number_format((float) $p->voucher_discount_share, 2) : '—';
                                    $pointsAmt  = $p->points_discount_share > 0 ? '-₱' . number_format((float) $p->points_discount_share, 2) : '—';
                                    $feesAmt    = '₱' . number_format((float) ($p->web_admin_fee_share + $p->transaction_fee_share), 2);
                                    $itemTotal  = '₱' . number_format((float) $p->item_total, 2);

                                    // ID image links
                                    $idLinks = '';
                                    if ($p->id_image_front_url) {
                                        $idLinks .= '<a href="' . e($p->id_image_front_url) . '" target="_blank" class="text-primary-600 text-xs underline">Front</a> ';
                                    }
                                    if ($p->id_image_back_url) {
                                        $idLinks .= '<a href="' . e($p->id_image_back_url) . '" target="_blank" class="text-primary-600 text-xs underline">Back</a>';
                                    }
                                    if (! $idLinks) {
                                        $idLinks = '<em class="text-gray-400 text-xs">—</em>';
                                    }

                                    $passportBadge = '';
                                    if ($p->hasPassportInfo()) {
                                        $passportBadge = '<div class="text-[11px] text-emerald-600 dark:text-emerald-400 mt-0.5">🛂 <span class="font-mono font-bold">' . e($p->passport_number) . '</span> (' . e($p->passport_country ?? 'N/A') . ') &bull; Exp: ' . ($p->passport_expiry_date ? $p->passport_expiry_date->format('Y-m-d') : 'N/A') . '</div>';
                                    }

                                    $baggageBadge = '';
                                    if ($p->hasExtraBaggage()) {
                                        $baggageBadge = '<div class="text-[11px] text-sky-600 dark:text-sky-400 mt-0.5">🧳 <span class="font-bold">' . e($p->extra_baggage_weight) . '</span> (+₱' . number_format((float) $p->extra_baggage_price, 2) . ')</div>';
                                    }

                                    $html .= "<tr class=\"hover:bg-gray-50/50 dark:hover:bg-gray-700/20\">
                                        <td class=\"py-2.5 px-3 font-semibold text-gray-900 dark:text-white whitespace-nowrap\">
                                            <div class=\"text-xs font-bold\">#{$itemNum}</div>
                                            <div class=\"text-xs text-gray-400 font-mono\">{$ticketNum}</div>
                                        </td>
                                        <td class=\"py-2.5 px-3\"><span class=\"inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {$colorClass}\">{$statusLabel}</span></td>
                                        <td class=\"py-2.5 px-3 font-medium\">
                                            <div>{$name}</div>
                                            {$passportBadge}
                                            {$baggageBadge}
                                        </td>
                                        <td class=\"py-2.5 px-3 text-xs\">{$type}</td>
                                        <td class=\"py-2.5 px-3 text-xs\">{$bday}</td>
                                        <td class=\"py-2.5 px-3 text-xs\">{$discount}</td>
                                        <td class=\"py-2.5 px-3 text-right text-xs font-medium\">{$fareAndClassAmt}</td>
                                        <td class=\"py-2.5 px-3 text-right text-xs text-green-600\">{$discAmt}</td>
                                        <td class=\"py-2.5 px-3 text-right text-xs text-green-600\">{$voucherAmt}</td>
                                        <td class=\"py-2.5 px-3 text-right text-xs text-green-600\">{$pointsAmt}</td>
                                        <td class=\"py-2.5 px-3 text-right text-xs text-slate-500\">{$feesAmt}</td>
                                        <td class=\"py-2.5 px-3 text-right text-sm font-bold text-primary-600 dark:text-primary-400\">{$itemTotal}</td>
                                        <td class=\"py-2.5 px-3\">{$idLinks}</td>
                                    </tr>";
                                }

                                $html .= '</tbody></table></div>';

                                return new HtmlString($html);
                            }),
                    ])
                    ->visible(fn (): bool => $this->record->passengers->isNotEmpty()),
                Section::make('Cancellation & Refund Details')
                    ->schema([
                        Placeholder::make('cancellation_refund_type')
                            ->label('Refund Type')
                            ->content(fn () => filled($this->record->service_cancellation_id) ? '100% — Service Cancellation' : 'Customer Cancellation'),
                        Placeholder::make('cancellation_refund_amount')
                            ->label('Refund Amount')
                            ->content(fn () => new HtmlString('<span class="font-bold text-emerald-600 dark:text-emerald-400 text-base">₱' . number_format((float) ($this->record->refund_amount ?? ($this->record->total_price * (filled($this->record->service_cancellation_id) ? 1.0 : 0.5))), 2) . '</span>')),
                        Placeholder::make('cancellation_refund_status')
                            ->label('Disbursement Status')
                            ->content(fn () => new HtmlString('<span class="font-bold ' . ($this->record->isRefundCompleted() ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-500') . '">' . e($this->record->getRefundStatusLabel()) . '</span>')),
                        Placeholder::make('cancellation_refund_destination')
                            ->label('Destination Account Details')
                            ->content(function () {
                                $parsed = $this->record->getParsedRefundDestination();
                                if (blank($parsed['method']) && blank($parsed['account_number']) && blank($parsed['account_name'])) {
                                    return $this->record->refund_destination ?? '—';
                                }

                                $html = '<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 text-xs">';
                                $html .= '<div><span class="text-gray-500 block font-semibold">Payout Method:</span><span class="font-bold text-primary-600 dark:text-primary-400">' . e($parsed['method'] ?? '—') . (filled($parsed['institution']) ? ' (' . e($parsed['institution']) . ')' : '') . '</span></div>';
                                $html .= '<div><span class="text-gray-500 block font-semibold">Account / Mobile No:</span><span class="font-mono font-bold text-gray-900 dark:text-white">' . e($parsed['account_number'] ?? '—') . '</span></div>';
                                $html .= '<div><span class="text-gray-500 block font-semibold">Account Holder Name:</span><span class="font-bold text-gray-900 dark:text-white">' . e($parsed['account_name'] ?? '—') . '</span></div>';
                                $html .= '</div>';

                                return new HtmlString($html);
                            })
                            ->columnSpanFull(),
                        Placeholder::make('customer_refund_docs')
                            ->label('Customer Submitted Documents (Valid ID, Original Ticket & Auth Letter)')
                            ->content(function () {
                                $idUrl = $this->record->refund_id_image ? storage_asset_path($this->record->refund_id_image) : null;
                                $ticketUrl = $this->record->refund_ticket_file ? storage_asset_path($this->record->refund_ticket_file) : null;
                                $authLetterUrl = $this->record->refund_auth_letter ? storage_asset_path($this->record->refund_auth_letter) : null;

                                if (! $idUrl && ! $ticketUrl && ! $authLetterUrl) {
                                    return new HtmlString('<p class="text-xs text-gray-400 italic">No customer ID, ticket, or authorization letter was uploaded with this request.</p>');
                                }

                                $html = '<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-3.5 bg-gray-50 dark:bg-gray-800/80 rounded-xl border border-gray-200 dark:border-gray-700 text-xs">';
                                
                                if ($idUrl) {
                                    $isPdf = str_ends_with(strtolower($this->record->refund_id_image), '.pdf');
                                    $html .= '<div><span class="text-gray-600 dark:text-gray-300 block font-bold mb-1.5 uppercase text-[11px] tracking-wider">1. Valid ID:</span>';
                                    if (! $isPdf) {
                                        $html .= '<a href="' . e($idUrl) . '" target="_blank" class="inline-block border border-gray-200 dark:border-gray-600 rounded-lg p-1.5 bg-white dark:bg-gray-900 shadow-sm hover:border-primary-500 transition"><img src="' . e($idUrl) . '" class="max-h-28 max-w-[180px] object-contain rounded"/><span class="text-[11px] text-primary-600 font-bold block text-center mt-1">Open Full Size ID &rarr;</span></a>';
                                    } else {
                                        $html .= '<a href="' . e($idUrl) . '" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-red-50 text-red-700 border border-red-200 font-semibold text-xs hover:bg-red-100 transition"><svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>View Valid ID (PDF)</a>';
                                    }
                                    $html .= '</div>';
                                }

                                if ($ticketUrl) {
                                    $isTicketPdf = str_ends_with(strtolower($this->record->refund_ticket_file), '.pdf');
                                    $html .= '<div><span class="text-gray-600 dark:text-gray-300 block font-bold mb-1.5 uppercase text-[11px] tracking-wider">2. Original Ticket:</span>';
                                    if (! $isTicketPdf) {
                                        $html .= '<a href="' . e($ticketUrl) . '" target="_blank" class="inline-block border border-gray-200 dark:border-gray-600 rounded-lg p-1.5 bg-white dark:bg-gray-900 shadow-sm hover:border-primary-500 transition"><img src="' . e($ticketUrl) . '" class="max-h-28 max-w-[180px] object-contain rounded"/><span class="text-[11px] text-primary-600 font-bold block text-center mt-1">Open Original Ticket &rarr;</span></a>';
                                    } else {
                                        $html .= '<a href="' . e($ticketUrl) . '" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-blue-50 text-blue-700 border border-blue-200 font-semibold text-xs hover:bg-blue-100 transition"><svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>View Original Ticket (PDF)</a>';
                                    }
                                    $html .= '</div>';
                                }

                                if ($authLetterUrl) {
                                    $isAuthPdf = str_ends_with(strtolower($this->record->refund_auth_letter), '.pdf');
                                    $html .= '<div><span class="text-gray-600 dark:text-gray-300 block font-bold mb-1.5 uppercase text-[11px] tracking-wider">3. Auth Letter:</span>';
                                    if (! $isAuthPdf) {
                                        $html .= '<a href="' . e($authLetterUrl) . '" target="_blank" class="inline-block border border-gray-200 dark:border-gray-600 rounded-lg p-1.5 bg-white dark:bg-gray-900 shadow-sm hover:border-primary-500 transition"><img src="' . e($authLetterUrl) . '" class="max-h-28 max-w-[180px] object-contain rounded"/><span class="text-[11px] text-primary-600 font-bold block text-center mt-1">Open Auth Letter &rarr;</span></a>';
                                    } else {
                                        $html .= '<a href="' . e($authLetterUrl) . '" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-amber-50 text-amber-700 border border-amber-200 font-semibold text-xs hover:bg-amber-100 transition"><svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>View Auth Letter (PDF)</a>';
                                    }
                                    $html .= '</div>';
                                }

                                $html .= '</div>';
                                return new HtmlString($html);
                            })
                            ->columnSpanFull(),
                        Placeholder::make('cancellation_refund_reference')
                            ->label('Transfer Reference Number')
                            ->content(fn () => $this->record->refund_reference ?? '—')
                            ->visible(fn () => filled($this->record->refund_reference))
                            ->columnSpanFull(),
                        Placeholder::make('cancellation_refund_action')
                            ->label('')
                            ->columnSpanFull()
                            ->content(fn (): HtmlString => new HtmlString(
                                '<div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-gray-200 dark:border-gray-700">' .
                                '<p class="text-xs text-gray-500 dark:text-gray-400">All refund verifications and proof receipts are managed in the dedicated <strong>Refunds</strong> page.</p>' .
                                '<a href="/admin/refunds" class="inline-flex items-center gap-1.5 rounded-lg bg-primary-600 hover:bg-primary-500 px-4 py-2 text-xs font-bold text-white transition shadow-sm">Go to Refunds Page &rarr;</a>' .
                                '</div>'
                            )),
                    ])
                    ->columns(3)
                    ->visible(fn (): bool => in_array($this->record->status, ['cancelled', 'operator_cancelled']) || filled($this->record->refund_destination)),
                Section::make('Rebook ticket details')
                    ->schema([
                        TextInput::make('is_rebooked_label')
                            ->label('Ticket status'),
                        TextInput::make('rebooking_status_label')
                            ->label('Rebooking status'),
                        DatePicker::make('rebooking_departure_date')
                            ->label('Rebook departure date'),
                        DatePicker::make('rebooking_return_date')
                            ->label('Rebook return date'),
                        TextInput::make('preferred_replacement_schedule_label')
                            ->label('Rebook schedule'),
                        TextInput::make('verified_at_label')
                            ->label('Rebook verified at'),
                        Placeholder::make('rebooking_proof_image')
                            ->label('Rebooking Proof of Payment')
                            ->columnSpanFull()
                            ->content(function (): HtmlString {
                                $proofPath = $this->record->transaction?->rebooking_proof_of_payment;
                                $url = $this->resolveStorageUrl($proofPath);

                                if (! $url) {
                                    return new HtmlString('<span class="text-gray-500">No rebooking proof uploaded.</span>');
                                }

                                $ext = strtolower(pathinfo($proofPath, PATHINFO_EXTENSION));

                                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                                    return new HtmlString(
                                        '<a href="' . e($url) . '" target="_blank"><img src="' . e($url) . '" class="rounded-lg border border-gray-700 max-w-full h-auto" alt="Rebooking proof of payment" /></a>'
                                    );
                                }

                                return new HtmlString(
                                    '<a href="' . e($url) . '" target="_blank" class="text-primary-600 underline">View rebooking proof of payment</a>'
                                );
                            }),
                    ])
                    ->columns(2)
                    ->visible(fn (): bool => (bool) $this->record->is_rebooked || filled($this->record->rebooking_status) || filled($this->record->rebooking_departure_date) || filled($this->record->rebooking_return_date)),
                Section::make('Vehicle details')
                    ->schema([
                        Toggle::make('has_vehicle')
                            ->label('Has vehicle'),
                        TextInput::make('vehicle_type')
                            ->label('Vehicle type')
                            ->nullable(),
                        TextInput::make('vehicle_plate_number')
                            ->label('Plate number')
                            ->nullable(),
                        TextInput::make('vehicle_price')
                            ->label('Vehicle price')
                            ->prefix('₱')
                            ->nullable(),
                    ])
                    ->columns(2)
                    ->visible(fn (): bool => (bool) $this->record->has_vehicle && stripos((string) $this->record->schedule_service, 'airline') === false),

                Section::make('Disruption & Rebooking Details')
                    ->schema([
                        TextInput::make('disruption_status_label')
                            ->label('Disruption Status'),
                        TextInput::make('rebooking_status_label')
                            ->label('Rebooking Status'),
                        DatePicker::make('preferred_replacement_date')
                            ->label('Preferred Replacement Date'),
                        TextInput::make('preferred_replacement_schedule_label')
                            ->label('Preferred Replacement Schedule'),
                        Placeholder::make('customer_request')
                            ->label('Customer Reschedule Request')
                            ->columnSpanFull()
                            ->content(function () {
                                $notes = $this->record->disruption_notes;
                                if (!\Illuminate\Support\Str::isJson($notes)) {
                                    return new HtmlString('<span class="text-gray-500">No custom details provided.</span>');
                                }
                                
                                $data = json_decode($notes, true);
                                $html = '<div class="space-y-2 mt-2">';
                                
                                if (!empty($data['dep_schedule_id'])) {
                                    $sch = \App\Models\Schedule::with('ferryRoute')->find($data['dep_schedule_id']);
                                    $html .= "<p><strong>Departure Schedule:</strong> " . ($sch ? "{$sch->ferryRoute->origin} → {$sch->ferryRoute->destination} ({$sch->formatted_departure})" : 'Unknown') . "</p>";
                                }
                                if (!empty($data['dep_accommodation_id'])) {
                                    $accName = $this->resolveAccommodationName($data['dep_accommodation_id']);
                                    $html .= "<p><strong>Departure Accommodation:</strong> {$accName}</p>";
                                }
                                if (!empty($data['ret_schedule_id'])) {
                                    $sch = \App\Models\Schedule::with('ferryRoute')->find($data['ret_schedule_id']);
                                    $html .= "<p><strong>Return Schedule:</strong> " . ($sch ? "{$sch->ferryRoute->origin} → {$sch->ferryRoute->destination} ({$sch->formatted_departure})" : 'Unknown') . "</p>";
                                }
                                if (!empty($data['ret_accommodation_id'])) {
                                    $accName = $this->resolveAccommodationName($data['ret_accommodation_id']);
                                    $html .= "<p><strong>Return Accommodation:</strong> {$accName}</p>";
                                }
                                if (isset($data['price_diff']) && $data['price_diff'] > 0) {
                                    $html .= "<p><strong>Price Difference Paid:</strong> ₱" . number_format($data['price_diff'], 2) . "</p>";
                                }
                                if (!empty($data['proof_path'])) {
                                    $url = storage_asset_path($data['proof_path']);
                                    $html .= "<p><strong>Payment Proof:</strong> <a href=\"{$url}\" target=\"_blank\" class=\"text-primary-600 underline\">View Payment Proof</a></p>";
                                }
                                
                                $html .= '</div>';
                                return new HtmlString($html);
                            })
                            ->visible(fn (): bool => \Illuminate\Support\Str::isJson($this->record->disruption_notes)),
                        Textarea::make('disruption_notes')
                            ->label('Staff Approval Notes')
                            ->columnSpanFull()
                            ->visible(fn (): bool => !\Illuminate\Support\Str::isJson($this->record->disruption_notes)),
                    ])
                    ->columns(2)
                    ->visible(fn (): bool => filled($this->record->service_cancellation_id) || filled($this->record->rebooking_status) || filled($this->record->disruption_status)),
            ]);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $prefSchedule = $this->record->preferredReplacementSchedule;

        return [
            ...$data,
            'operator_name' => $this->record->getOperatorName() ?? '—',
            'baggage_details' => $this->record->has_extra_baggage ? "{$this->record->extra_baggage_weight} kg — ₱" . number_format((float) $this->record->extra_baggage_price, 2) : 'None',
            'transaction_payment_status' => $this->record->transaction?->payment_status,
            'payment_reference' => $this->record->transaction?->payment_reference ?? '—',
            'verification_timer' => $this->record->verificationTimerLabel(),
            'proof_uploaded' => filled($this->record->transaction?->proof_of_payment) ? 'Yes' : 'No',
            'confirmation_url' => $this->record->transaction?->confirmation_url,
            'is_rebooked_label' => $this->record->is_rebooked ? 'Rebooked ticket' : 'Not rebooked',
            'disruption_status_label' => match ($this->record->disruption_status) {
                'cancelled_by_operator_rescheduling_required' => 'Cancelled by Operator — Reschedule Required',
                'reschedule_requested' => 'Customer Reschedule Requested',
                'rescheduled_approved' => 'Rescheduled & Approved',
                'rescheduled_declined' => 'Rescheduled — Declined',
                'contact_support_required' => 'Contact Support Required',
                default => $this->record->disruption_status ? ucfirst(str_replace('_', ' ', $this->record->disruption_status)) : '—',
            },
            'rebooking_status_label' => match ($this->record->rebooking_status) {
                'rebooking_required' => 'Rebooking Required',
                'reschedule_requested' => 'Reschedule Requested',
                'verified' => 'Rebooked',
                'pending' => 'Pending',
                default => $this->record->rebooking_status ? ucfirst(str_replace('_', ' ', $this->record->rebooking_status)) : '—',
            },
            'preferred_replacement_schedule_label' => $prefSchedule
                ? "{$prefSchedule->service_name} ({$prefSchedule->formatted_departure} → {$prefSchedule->formatted_arrival})"
                : '—',
            'verified_at_label' => $this->record->is_rebooked && $this->record->verified_at ? $this->record->verified_at->format('M d, Y h:i A') : '—',
            'passengers' => $this->record->passengers->map(fn ($passenger) => [
                'name' => $passenger->name,
                'type' => $passenger->type,
                'discount' => $passenger->discount?->name ?: 'None',
                'birthdate' => $passenger->birthdate?->format('Y-m-d') ?: 'N/A',
                'id_number' => $passenger->id_number ?: 'N/A',
                'id_image_front_url' => $passenger->id_image_front_url,
                'id_image_back_url' => $passenger->id_image_back_url,
            ])->toArray(),
            'proof_url' => $this->record->transaction?->proof_url ? $this->record->transaction->proof_url : 'No proof uploaded',
        ];
    }

    public function getRelationManagers(): array
    {
        return [];
    }

    protected function getHeaderActions(): array
    {
        return [

            Actions\Action::make('confirm')
                ->label('Verify Payment & Confirm Booking')
                ->form([
                    Forms\Components\TextInput::make('confirmation_url')
                        ->label('Confirmation URL')
                        ->url()
                        ->placeholder('https://example.com/ticket/ABC123'),
                    Forms\Components\FileUpload::make('confirmation_pdf')
                        ->label('Confirmation PDF')
                        ->directory('tickets')
                        ->disk('public')
                        ->acceptedFileTypes(['application/pdf'])
                        ->maxSize(10240),
                ])
                ->disabled(fn (): bool => ! $this->record->transaction || $this->record->transaction->payment_status === 'unpaid' || $this->record->isVerificationLocked())
                ->tooltip(fn (): ?string => ! $this->record->transaction
                    ? 'No payment transaction found for this booking.'
                    : ($this->record->transaction->payment_status === 'unpaid'
                        ? 'Cannot verify: Payment status is Unpaid.'
                        : $this->record->verificationTimerTooltip()))
                ->action(function (array $data) {
                    $booking = $this->record;

                    if (empty($data['confirmation_url']) && empty($data['confirmation_pdf'])) {
                        throw new \Exception('Please provide either a confirmation URL or upload a PDF before confirming.');
                    }

                    $alreadyVerifiedBy = null;
                    $shouldSendEmail = false;
                    $isRebooking = false;
                    $ticketUrl = !empty($data['confirmation_url']) ? trim($data['confirmation_url']) : null;
                    $confirmationPdfPath = Booking::resolveUploadedPdfPath($data['confirmation_pdf'] ?? null, $booking->transaction_number);
                    $receiptPath = $confirmationPdfPath;
                    $receiptDisk = $confirmationPdfPath ? 'public' : null;

                    DB::transaction(function () use (
                        $booking, $ticketUrl, $confirmationPdfPath, $receiptPath, $receiptDisk,
                        &$alreadyVerifiedBy, &$shouldSendEmail, &$isRebooking
                    ) {
                        $lockedBooking = Booking::where('id', $booking->id)
                            ->with(['transaction', 'verifiedBy'])
                            ->lockForUpdate()
                            ->first();

                        if (! $lockedBooking || $lockedBooking->status === 'confirmed' || $lockedBooking->verified_by_user_id !== null) {
                            $alreadyVerifiedBy = $lockedBooking?->verifiedBy?->name ?? 'another staff member';
                            return;
                        }

                        $staffUserId = Auth::id();
                        $now = now();

                        $transaction = $lockedBooking->transaction ?? \App\Models\Transaction::where('booking_id', $lockedBooking->id)->lockForUpdate()->first();
                        if ($transaction) {
                            $transaction->update([
                                'payment_status'      => 'paid',
                                'confirmation_url'    => $ticketUrl,
                                'confirmation_pdf'    => $confirmationPdfPath,
                                'verified_by_user_id' => $staffUserId,
                                'verified_at'         => $now,
                            ]);
                        } else {
                            $transaction = \App\Models\Transaction::create([
                                'booking_id'          => $lockedBooking->id,
                                'payment_status'      => 'paid',
                                'confirmation_url'    => $ticketUrl,
                                'confirmation_pdf'    => $confirmationPdfPath,
                                'verified_by_user_id' => $staffUserId,
                                'verified_at'         => $now,
                            ]);
                        }
                        $lockedBooking->setRelation('transaction', $transaction);

                        $lockedBooking->update([
                            'verified_by_user_id' => $staffUserId,
                            'verified_at'         => $now,
                        ]);

                        if ($lockedBooking->rebooking_status === 'pending') {
                            $isRebooking = true;
                            // Rebooking path: delegate entirely to the service which handles
                            // schedule assignment, DB updates, and sends BookingConfirmation email.
                            try {
                                app(\App\Services\ServiceCancellationManager::class)->processAutomaticRebookingApproval(
                                    $lockedBooking,
                                    Auth::user()
                                );
                            } catch (\Exception $e) {
                                Log::error('Failed automatic rebooking during confirmation: ' . $e->getMessage());
                                throw $e;
                            }
                        } else {
                            // Normal (non-rebooking) path: mark confirmed and send email.
                            $lockedBooking->update(['status' => 'confirmed']);
                            app(\App\Services\GraciaPointsService::class)->awardPointsForBooking($lockedBooking, Auth::user());
                            $shouldSendEmail = true;
                        }
                    });

                    if ($alreadyVerifiedBy !== null) {
                        Notification::make()
                            ->title('Already Verified')
                            ->body("This booking was already verified by {$alreadyVerifiedBy}.")
                            ->warning()
                            ->send();
                        return;
                    }

                    if ($isRebooking) {
                        Notification::make()
                            ->title('Booking & Rebooking Confirmed')
                            ->body('Both original booking and rebooking have been verified and confirmed.')
                            ->success()
                            ->send();
                        return;
                    }

                    if ($shouldSendEmail) {
                        try {
                            Mail::to($booking->client_email)->send(new BookingConfirmation($booking->fresh(), $ticketUrl, $receiptPath, $receiptDisk));
                            Notification::make()
                                ->title('Booking confirmed')
                                ->body('Booking confirmed and confirmation email sent.')
                                ->success()
                                ->send();
                        } catch (Throwable $e) {
                            Log::error('Failed sending booking confirmation email', [
                                'booking_id' => $booking->id ?? null,
                                'email'      => $booking->client_email ?? null,
                                'error'      => $e->getMessage(),
                            ]);
                            Notification::make()
                                ->title('Booking confirmed with warning')
                                ->body('Booking was confirmed, but the confirmation email failed to send.')
                                ->warning()
                                ->send();
                        }
                    }
                })
                ->requiresConfirmation()
                ->color('success')
                ->visible(fn (): bool => $this->record->status === 'pending'),


        ];
    }

    /**
     * Resolve an accommodation name from a prefixed ID string.
     * Prefix 'acc_' = ScheduleAccommodation, 'tc_' = TransportClass pivot.
     */
    private function resolveAccommodationName(string $prefixedId): string
    {
        if (str_starts_with($prefixedId, 'acc_')) {
            $id = (int) substr($prefixedId, 4);
            $acc = \App\Models\ScheduleAccommodation::find($id);
            return $acc ? $acc->name : $prefixedId;
        }

        if (str_starts_with($prefixedId, 'tc_')) {
            $id = (int) substr($prefixedId, 3);
            $tc = \App\Models\TransportClass::find($id);
            return $tc ? $tc->name : $prefixedId;
        }

        return $prefixedId;
    }
}

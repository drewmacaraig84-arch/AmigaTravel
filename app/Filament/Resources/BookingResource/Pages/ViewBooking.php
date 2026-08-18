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
        $total = (float) $this->record->total_price;

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
            $class = $isDiscount ? 'text-green-600 font-medium' : '';
            $displayAmount = ($isDiscount ? '-₱' : '₱') . number_format(abs($amount), 2);

            $html .= '<tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/20">';
            $html .= '<td class="py-2 px-3">' . $label . '</td>';
            $html .= '<td class="py-2 px-3 text-right font-medium ' . $class . '">' . $displayAmount . '</td>';
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

    public function form(Form $form): Form
    {
        return $form
            ->schema([
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
                Section::make('Passenger details')
                    ->schema([
                        Repeater::make('passengers')
                            ->label('Passengers')
                            ->disableLabel()
                            ->schema([
                                TextInput::make('name')
                                    ->label('Name')
                                    ->disabled(),
                                TextInput::make('type')
                                    ->label('Type')
                                    ->disabled(),
                                TextInput::make('birthdate')
                                    ->label('Birthdate')
                                    ->disabled(),
                                TextInput::make('discount')
                                    ->label('Discount')
                                    ->disabled(),
                                TextInput::make('id_number')
                                    ->label('ID Number')
                                    ->disabled(),
                                Placeholder::make('id_image_front_view')
                                    ->label('Front ID Image')
                                    ->content(fn (?array $state = null): HtmlString => $this->renderPassengerIdLinkContent($state)),
                                Placeholder::make('id_image_back_view')
                                    ->label('Back ID Image')
                                    ->content(fn (?array $state = null): HtmlString => $this->renderPassengerIdBackLinkContent($state)),
                            ])
                            ->columns(3)
                            ->visible(fn (): bool => $this->record->passengers->isNotEmpty()),
                    ]),
                Section::make('Cancellation Details')
                    ->schema([
                        Placeholder::make('cancellation_refund_info')
                            ->label('Cancellation & Refund Info')
                            ->columnSpanFull()
                            ->content(function (): HtmlString {
                                $booking = $this->record;

                                // Determine refund type and amount
                                $isServiceCancellation = filled($booking->service_cancellation_id);
                                $refundPercent = $isServiceCancellation ? 100 : 50;
                                $refundAmount = $booking->refund_amount ?? ($booking->total_price * $refundPercent / 100);

                                $html = '<div class="space-y-4">';

                                // Refund amount row
                                $html .= '<div class="grid grid-cols-2 gap-4">';
                                $html .= '<div><p class="text-xs uppercase tracking-wide text-gray-500 mb-1">Refund Type</p>';
                                $html .= '<p class="font-medium">' . ($isServiceCancellation ? '100% — Service Cancellation (Full Refund)' : '50% — Customer-Initiated Cancellation') . '</p></div>';
                                $html .= '<div><p class="text-xs uppercase tracking-wide text-gray-500 mb-1">Refund Amount</p>';
                                $html .= '<p class="font-semibold text-green-400">₱' . number_format($refundAmount, 2) . '</p></div>';
                                $html .= '</div>';

                                // Parse refund_destination string
                                $destination = $booking->refund_destination;
                                if (! filled($destination)) {
                                    $html .= '<p class="text-gray-500 text-sm">No refund destination provided yet.</p>';
                                    $html .= '</div>';
                                    return new HtmlString($html);
                                }

                                $parts = array_map('trim', explode('|', $destination));
                                $parsed = [];
                                foreach ($parts as $part) {
                                    if (str_contains($part, ':')) {
                                        [$key, $val] = array_map('trim', explode(':', $part, 2));
                                        $parsed[$key] = $val;
                                    }
                                }

                                $html .= '<div class="grid grid-cols-2 gap-4 mt-2">';

                                if (isset($parsed['Method'])) {
                                    $html .= '<div><p class="text-xs uppercase tracking-wide text-gray-500 mb-1">Refund Method</p>';
                                    $html .= '<p class="font-medium">' . e($parsed['Method']) . '</p></div>';
                                }
                                if (isset($parsed['Institution'])) {
                                    $html .= '<div><p class="text-xs uppercase tracking-wide text-gray-500 mb-1">Bank / E-Wallet</p>';
                                    $html .= '<p class="font-medium">' . e($parsed['Institution']) . '</p></div>';
                                }
                                if (isset($parsed['Name'])) {
                                    $html .= '<div><p class="text-xs uppercase tracking-wide text-gray-500 mb-1">Account Name</p>';
                                    $html .= '<p class="font-medium">' . e($parsed['Name']) . '</p></div>';
                                }
                                if (isset($parsed['Account No'])) {
                                    $html .= '<div><p class="text-xs uppercase tracking-wide text-gray-500 mb-1">Account Number</p>';
                                    $html .= '<p class="font-medium font-mono">' . e($parsed['Account No']) . '</p></div>';
                                }

                                $html .= '</div></div>';
                                return new HtmlString($html);
                            }),
                    ])
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
                    ->visible(fn (): bool => $this->record->has_vehicle && $this->record->schedule?->ferryRoute?->mode !== 'airline'),

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
                                    $html .= "<p><strong>Payment Proof:</strong> <a href=\"{$url}\" target=\"_blank\" class=\"text-primary-600 underline\">View Receipt</a></p>";
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

    protected function getAllRelationManagers(): array
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

                    $ticketUrl = !empty($data['confirmation_url']) ? trim($data['confirmation_url']) : null;
                    $confirmationPdfPath = Booking::resolveUploadedPdfPath($data['confirmation_pdf'] ?? null, $booking->transaction_number);
                    $receiptPath = $confirmationPdfPath;
                    $receiptDisk = $confirmationPdfPath ? 'public' : null;

                    $staffUserId = auth()->id();
                    $now = now();

                    $booking->update([
                        'verified_by_user_id' => $staffUserId,
                        'verified_at'         => $now,
                    ]);

                    $transaction = $booking->transaction ?? \App\Models\Transaction::where('booking_id', $booking->id)->first();
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
                            'booking_id'          => $booking->id,
                            'payment_status'      => 'paid',
                            'confirmation_url'    => $ticketUrl,
                            'confirmation_pdf'    => $confirmationPdfPath,
                            'verified_by_user_id' => $staffUserId,
                            'verified_at'         => $now,
                        ]);
                    }
                    $booking->setRelation('transaction', $transaction);

                    if ($booking->rebooking_status === 'pending') {
                        // Rebooking path: delegate entirely to the service which handles
                        // schedule assignment, DB updates, and sends BookingConfirmation email.
                        try {
                            app(\App\Services\ServiceCancellationManager::class)->processAutomaticRebookingApproval(
                                $booking,
                                auth()->user()
                            );
                            Notification::make()
                                ->title('Booking & Rebooking Confirmed')
                                ->body('Both original booking and rebooking have been verified and confirmed.')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Log::error('Failed automatic rebooking during confirmation: ' . $e->getMessage());
                            Notification::make()
                                ->title('Booking Confirmed, but Rebooking Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                        return;
                    }

                    // Normal (non-rebooking) path: mark confirmed and send email.
                    $booking->update(['status' => 'confirmed']);
                    app(\App\Services\GraciaPointsService::class)->awardPointsForBooking($booking, auth()->user());

                    try {
                        Mail::to($booking->client_email)->send(new BookingConfirmation($booking, $ticketUrl, $receiptPath, $receiptDisk));
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

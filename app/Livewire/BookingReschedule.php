<?php

namespace App\Livewire;

use App\Models\Booking;
use App\Models\Schedule;
use App\Models\ScheduleAccommodation;
use App\Services\ServiceCancellationManager;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Throwable;

class BookingReschedule extends Component
{
    use WithFileUploads;

    public string $transaction_number = '';
    public ?Booking $booking = null;

    public ?string $feedback = null;
    public bool $submitted = false;
    public bool $supportRequested = false;

    // Cancel & Refund inline form
    public bool $showRefundForm = false;
    public string $refund_method = 'GCash';
    public string $refund_bank_name = '';
    public string $refund_account_number = '';
    public string $refund_account_name = '';
    public $refund_id_image = null;
    public $refund_ticket_file = null;
    public $refund_auth_letter = null;

    // Wizard State
    // Steps: departure_date, departure_schedule, departure_accommodation, return_date, return_schedule, return_accommodation, confirm
    public string $step = 'departure_date';

    // Departure Leg
    public ?string $dep_date = null;
    public ?int $dep_schedule_id = null;
    public ?string $dep_accommodation_id = null;
    public ?float $dep_schedule_price = null;
    public ?float $dep_accommodation_price = null;

    // Return Leg (if applicable)
    public ?string $ret_date = null;
    public ?int $ret_schedule_id = null;
    public ?string $ret_accommodation_id = null;
    public ?float $ret_schedule_price = null;
    public ?float $ret_accommodation_price = null;

    // Payment Diff
    public float $priceDiff = 0.0;
    public float $rebookSurcharge = 0.0;
    public float $rebookRevalidationFee = 0.0;
    public float $rebookRateDiff = 0.0;
    public float $totalRebookFee = 0.0;
    public $paymentProof;
    public bool $isUploading = false;

    // Passenger Items Selection
    public array $selectedPassengerItems = [];

    public function selectAllPassengers(): void
    {
        if (! $this->booking) return;
        $this->selectedPassengerItems = $this->booking->getActivePassengers()
            ->pluck('item_number')
            ->map(fn ($n) => (int) $n)
            ->values()
            ->toArray();
    }

    public function deselectAllPassengers(): void
    {
        $this->selectedPassengerItems = [];
    }

    public function togglePassengerItem(int $itemNumber): void
    {
        $targetPax = $this->booking?->passengers->firstWhere('item_number', $itemNumber);
        if ($targetPax && ! $targetPax->isActiveBookingItem()) {
            return;
        }

        if ($this->booking && $this->booking->hasSingleAdultWithNonAdults()) {
            $this->selectAllPassengers();
            $this->feedback = 'This booking has only one adult accompanying minor/child passengers. All passengers must be rebooked, cancelled, or refunded together.';
            return;
        }

        if (in_array($itemNumber, $this->selectedPassengerItems, true)) {
            $this->selectedPassengerItems = array_values(array_diff($this->selectedPassengerItems, [$itemNumber]));
        } else {
            $this->selectedPassengerItems[] = $itemNumber;
            sort($this->selectedPassengerItems);
        }
    }

    public function updatedSelectedPassengerItems(): void
    {
        if (! $this->booking) {
            return;
        }

        if ($this->booking->hasSingleAdultWithNonAdults()) {
            $activeItems = $this->booking->getActivePassengers()->pluck('item_number')->map(fn ($n) => (int) $n)->toArray();
            if (count($this->selectedPassengerItems) !== count($activeItems)) {
                $this->selectedPassengerItems = $activeItems;
                $this->feedback = 'This booking has only one adult accompanying minor/child passengers. All passengers must be rebooked, cancelled, or refunded together.';
            }
        }
    }

    public function getSelectedItemsLabelProperty(): string
    {
        if (! $this->booking) return '—';
        return $this->booking->getAffectedItemsLabel(empty($this->selectedPassengerItems) ? null : $this->selectedPassengerItems);
    }

    public function mount(string $transaction_number): void
    {
        $this->transaction_number = ltrim(trim($transaction_number), '#');
        $this->loadBooking();

        if (! $this->booking || ! $this->booking->serviceCancellation) {
            abort(403, 'Unauthorized access. This page is only for bookings affected by service disruptions.');
        }

        $this->selectAllPassengers();

        if ($this->booking && $this->booking->serviceCancellation) {
            $resumeDate = $this->booking->serviceCancellation->resume_date;
            $this->dep_date = $resumeDate ? $resumeDate->format('Y-m-d') : Carbon::tomorrow()->format('Y-m-d');
            if ($this->isRoundTrip()) {
                $this->ret_date = $this->dep_date;
            }
        }
    }

    public function loadBooking(): void
    {
        $cleanNumber = ltrim(trim($this->transaction_number), '#');

        $this->booking = Booking::with([
            'serviceCancellation',
            'passengers.discount',
            'transportClasses',
            'accommodations',
        ])
        ->where(function ($query) use ($cleanNumber) {
            $query->where('transaction_number', $cleanNumber)
                  ->orWhere('transaction_number', '#' . $cleanNumber);
        })
        ->first();
    }

    public function isRoundTrip(): bool
    {
        return $this->booking && ($this->booking->return_date || $this->booking->return_schedule_id);
    }

    // -- Wizard Navigation --

    public function setStep(string $step)
    {
        $this->step = $step;
        $this->feedback = null;
        if ($step === 'confirm') {
            $this->calculatePriceDiff();
        }
    }

    public function updatedDepDate()
    {
        $this->dep_schedule_id = null;
        $this->dep_accommodation_id = null;
        $this->dep_schedule_price = null;
        $this->dep_accommodation_price = null;
    }

    public function updatedRetDate()
    {
        $this->ret_schedule_id = null;
        $this->ret_accommodation_id = null;
        $this->ret_schedule_price = null;
        $this->ret_accommodation_price = null;
    }

    public function selectDepartureSchedule(int $scheduleId, float $price)
    {
        $this->dep_schedule_id = $scheduleId;
        $this->dep_schedule_price = $price;
        $this->dep_accommodation_id = null;
        $this->dep_accommodation_price = null;
        $this->setStep('departure_accommodation');
    }

    public function selectDepartureAccommodation(string $accId, float $price)
    {
        if ($this->booking && $this->booking->getMode() === 'airline') {
            $passengerCount = max(1, $this->booking->passengers()->count());
            $originalBasePricePerPax = $this->originalFare / $passengerCount;
            
            // The $price coming in is already marked up by 1.5 for airlines.
            // We need to divide by 1.5 to get the raw base price.
            $unmarkedPrice = $price / 1.5;
            $newBasePricePerPax = ($this->dep_schedule_price ?? 0) + $this->getSelectedAccommodationCost($accId, $unmarkedPrice, 1);
        }

        $this->dep_accommodation_id = $accId;
        $this->dep_accommodation_price = $price;
        
        if ($this->isRoundTrip()) {
            $this->setStep('return_date');
        } else {
            $this->setStep('confirm');
        }
    }

    public function selectReturnSchedule(int $scheduleId, float $price)
    {
        $this->ret_schedule_id = $scheduleId;
        $this->ret_schedule_price = $price;
        $this->ret_accommodation_id = null;
        $this->ret_accommodation_price = null;
        $this->setStep('return_accommodation');
    }

    public function selectReturnAccommodation(string $accId, float $price)
    {
        if ($this->booking && $this->booking->getMode() === 'airline') {
            $passengerCount = max(1, $this->booking->passengers()->count());
            $originalBasePricePerPax = $this->originalFare / $passengerCount;
            
            // The $price coming in is already marked up by 1.5 for airlines.
            // We need to divide by 1.5 to get the raw base price.
            $unmarkedPrice = $price / 1.5;
            $newBasePricePerPax = ($this->ret_schedule_price ?? 0) + $this->getSelectedAccommodationCost($accId, $unmarkedPrice, 1);
        }

        $this->ret_accommodation_id = $accId;
        $this->ret_accommodation_price = $price;
        $this->setStep('confirm');
    }

    // -- Data Fetching --

    public function getAvailableDepartureSchedulesProperty()
    {
        if (!$this->booking || !$this->dep_date || !$this->booking->serviceCancellation) return collect();

        $cancellationId = $this->booking->serviceCancellation->id;
        $depDate = $this->dep_date;

        return Schedule::forRouteAndDate($this->booking->origin, $this->booking->destination, $this->dep_date)
            ->whereIn('id', function ($query) use ($cancellationId, $depDate) {
                $query->select('schedule_id')
                      ->from('cancellation_replacements')
                      ->where('service_cancellation_id', $cancellationId)
                      ->whereDate('replacement_date', $depDate);
            })
            ->with(['ferryRoute', 'vehicle'])
            ->where('departure_time', '>', now())
            ->get();
    }

    public function getAvailableReturnSchedulesProperty()
    {
        if (!$this->booking || !$this->ret_date || !$this->booking->serviceCancellation) return collect();

        $cancellationId = $this->booking->serviceCancellation->id;
        $retDate = $this->ret_date;

        return Schedule::forRouteAndDate($this->booking->destination, $this->booking->origin, $this->ret_date)
            ->whereIn('id', function ($query) use ($cancellationId, $retDate) {
                $query->select('schedule_id')
                      ->from('cancellation_replacements')
                      ->where('service_cancellation_id', $cancellationId)
                      ->whereDate('replacement_date', $retDate);
            })
            ->with(['ferryRoute', 'vehicle'])
            ->where('departure_time', '>', now())
            ->get();
    }

    public function getDepartureAccommodationsProperty()
    {
        if (!$this->dep_schedule_id) return collect();
        $schedule = Schedule::with(['scheduleAccommodations', 'transportClasses'])->find($this->dep_schedule_id);
        if (!$schedule) return collect();

        $isAirline = $this->booking && $this->booking->getMode() === 'airline';
        $schedulePrice = (float)($schedule->price ?? 0);

        $items = collect();
        foreach ($schedule->scheduleAccommodations->where('is_active', true)->sortBy('sort_order') as $acc) {
            $price = $schedulePrice + (float)$acc->price;
            if ($isAirline) {
                $price = $price * 1.5;
            }
            $items->push((object)[
                'id' => 'acc_' . $acc->id,
                'name' => $acc->name,
                'description' => $acc->description,
                'price' => $price,
            ]);
        }
        foreach ($schedule->transportClasses->where('pivot.is_active', true)->sortBy('pivot.sort_order') as $tc) {
            $price = $schedulePrice + (float)$tc->pivot->additional_price;
            if ($isAirline) {
                $price = $price * 1.5;
            }
            $items->push((object)[
                'id' => 'tc_' . $tc->id,
                'name' => $tc->name,
                'description' => $tc->pivot->description ?? $tc->description,
                'price' => $price,
            ]);
        }
        return $items;
    }

    public function getReturnAccommodationsProperty()
    {
        if (!$this->ret_schedule_id) return collect();
        $schedule = Schedule::with(['scheduleAccommodations', 'transportClasses'])->find($this->ret_schedule_id);
        if (!$schedule) return collect();

        $isAirline = $this->booking && $this->booking->getMode() === 'airline';
        $schedulePrice = (float)($schedule->price ?? 0);

        $items = collect();
        foreach ($schedule->scheduleAccommodations->where('is_active', true)->sortBy('sort_order') as $acc) {
            $price = $schedulePrice + (float)$acc->price;
            if ($isAirline) {
                $price = $price * 1.5;
            }
            $items->push((object)[
                'id' => 'acc_' . $acc->id,
                'name' => $acc->name,
                'description' => $acc->description,
                'price' => $price,
            ]);
        }
        foreach ($schedule->transportClasses->where('pivot.is_active', true)->sortBy('pivot.sort_order') as $tc) {
            $price = $schedulePrice + (float)$tc->pivot->additional_price;
            if ($isAirline) {
                $price = $price * 1.5;
            }
            $items->push((object)[
                'id' => 'tc_' . $tc->id,
                'name' => $tc->name,
                'description' => $tc->pivot->description ?? $tc->description,
                'price' => $price,
            ]);
        }
        return $items;
    }

    public function calculatePriceDiff()
    {
        if (!$this->booking) return;

        try {
            $selectedItems = !empty($this->selectedPassengerItems)
                ? $this->selectedPassengerItems
                : $this->booking->passengers->whereNotIn('status', ['cancelled', 'operator_cancelled', 'refunded'])->pluck('item_number')->toArray();

            $depAccId = null;
            if ($this->dep_accommodation_id) {
                $depAccId = (int) str_replace(['acc_', 'tc_'], '', (string) $this->dep_accommodation_id);
            }
            $retAccId = null;
            if ($this->ret_accommodation_id) {
                $retAccId = (int) str_replace(['acc_', 'tc_'], '', (string) $this->ret_accommodation_id);
            }

            $calc = $this->booking->getPartialRebookingCalculation(
                $selectedItems,
                $this->dep_schedule_id,
                $depAccId,
                $this->ret_schedule_id,
                $retAccId
            );

            $this->rebookRateDiff = $calc['rate_diff'];
            $this->rebookSurcharge = $calc['surcharge'];
            $this->rebookRevalidationFee = $calc['revalidation_fee'];
            $this->totalRebookFee = $calc['total_rebooking_fee'];
            $this->priceDiff = $calc['rate_diff'];
        } catch (\Exception $e) {
            $this->feedback = "Error calculating price difference: " . $e->getMessage();
        }
    }

    public function getOriginalFareProperty(): float
    {
        if (! $this->booking) {
            return 0.0;
        }

        $selectedItems = !empty($this->selectedPassengerItems)
            ? $this->selectedPassengerItems
            : $this->booking->passengers->pluck('item_number')->toArray();

        $selectedPax = $this->booking->passengers->filter(fn ($p) => in_array((int) $p->item_number, array_map('intval', $selectedItems), true));
        return (float) $selectedPax->sum(fn ($p) => $p->getEffectiveFareAndClass()) ?: (float) $this->booking->getTicketBase();
    }

    public function getNewFareProperty(): float
    {
        if (! $this->booking) {
            return 0.0;
        }

        $selectedCount = max(1, count($this->selectedPassengerItems ?: $this->booking->passengers));
        $depPrice = (float)($this->dep_schedule_price ?? 0) + (float)($this->dep_accommodation_price ?? 0);
        $retPrice = (float)($this->ret_schedule_price ?? 0) + (float)($this->ret_accommodation_price ?? 0);

        return ($depPrice + $retPrice) * $selectedCount;
    }

    protected function getSelectedAccommodationCost(?string $accommodationId, ?float $price, int $passengerCount): float
    {
        if (! $accommodationId || $price === null) {
            return 0.0;
        }

        if (str_starts_with($accommodationId, 'tc_')) {
            return $price;
        }

        return $price * $passengerCount;
    }

    protected function getBookingTransportClassTotal(): float
    {
        if (! $this->booking) {
            return 0.0;
        }

        return $this->booking->transportClasses->sum(function ($transportClass) {
            return floatval($transportClass->pivot->price ?? 0);
        });
    }

    public function submitReschedule(): void
    {
        if (!$this->booking) return;

        if ($this->priceDiff > 0) {
            $this->validate([
                'paymentProof' => 'required|image|max:10240'
            ]);
        }

        try {
            $proofPath = null;
            if ($this->priceDiff > 0 && $this->paymentProof) {
                $extension = $this->paymentProof->extension();
                $filename = 'rebook_proof_' . $this->booking->transaction_number . '_' . uniqid() . '.' . $extension;
                $proofPath = $this->paymentProof->storeAs('proofs', $filename, 'public');
            }

            $selectedItems = !empty($this->selectedPassengerItems)
                ? $this->selectedPassengerItems
                : $this->booking->passengers->whereNotIn('status', ['cancelled', 'operator_cancelled', 'refunded'])->pluck('item_number')->toArray();

            $policy = $this->booking->validatePassengerPartyPolicy($selectedItems, 'reschedule');
            if (! $policy['valid']) {
                $this->feedback = $policy['error'];
                return;
            }

            foreach ($this->booking->passengers as $p) {
                if (in_array((int) $p->item_number, array_map('intval', $selectedItems), true)) {
                    $p->update([
                        'status'                            => \App\Models\Passenger::STATUS_OPERATOR_REBOOKING,
                        'is_rebooked'                       => true,
                        'rebooking_status'                  => 'reschedule_requested',
                        'preferred_replacement_schedule_id' => $this->dep_schedule_id,
                        'rebooking_departure_date'          => $this->dep_date,
                        'rebooking_return_date'             => $this->isRoundTrip() ? $this->ret_date : null,
                        'disruption_notes'                  => json_encode([
                            'dep_schedule_id'      => $this->dep_schedule_id,
                            'dep_accommodation_id' => $this->dep_accommodation_id,
                            'ret_schedule_id'      => $this->ret_schedule_id,
                            'ret_accommodation_id' => $this->ret_accommodation_id,
                            'price_diff'           => $this->priceDiff,
                            'proof_path'           => $proofPath,
                        ]),
                    ]);
                }
            }

            $this->booking->update([
                'status' => 'operator_rebooking',
                'preferred_replacement_schedule_id' => $this->dep_schedule_id,
                'preferred_replacement_date' => $this->dep_date,
                'rebooking_departure_date' => $this->dep_date,
                'rebooking_return_date' => $this->isRoundTrip() ? $this->ret_date : null,
                'disruption_status' => 'reschedule_requested',
                'rebooking_status' => 'reschedule_requested',
                'disruption_notes' => json_encode([
                    'dep_schedule_id' => $this->dep_schedule_id,
                    'dep_accommodation_id' => $this->dep_accommodation_id,
                    'ret_schedule_id' => $this->ret_schedule_id,
                    'ret_accommodation_id' => $this->ret_accommodation_id,
                    'price_diff' => $this->priceDiff,
                    'proof_path' => $proofPath,
                    'affected_items' => $this->booking->getAffectedItemsLabel($selectedItems),
                ])
            ]);

            if ($proofPath && $this->booking->transaction) {
                $this->booking->transaction->update([
                    'rebooking_proof_of_payment' => $proofPath,
                ]);
            }

            $this->loadBooking();
            $this->submitted = true;
            $this->feedback = 'Your replacement travel selection has been submitted and is awaiting staff approval. You can track this under your booking status.';
        } catch (\Exception $e) {
            $this->feedback = $e->getMessage();
        }
    }

    // -- Inline Refund Form --

    public function openRefundForm()
    {
        $this->showRefundForm = true;
    }

    public function closeRefundForm()
    {
        $this->showRefundForm = false;
        $this->reset(['refund_method', 'refund_bank_name', 'refund_account_number', 'refund_account_name']);
    }

    public function submitCancelAndRefund(): void
    {
        if (!$this->booking) {
            $this->feedback = 'Booking not found.';
            return;
        }

        $rules = [
            'refund_method' => 'required|string|in:GCash,Online Wallet,Bank Account',
            'refund_account_number' => 'required|string|max:50',
            'refund_account_name' => 'required|string|max:100',
            'refund_id_image' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:10240',
            'refund_ticket_file' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:10240',
            'refund_auth_letter' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:10240',
        ];

        if (in_array($this->refund_method, ['Bank Account', 'Online Wallet'], true)) {
            $rules['refund_bank_name'] = 'required|string|max:100';
        }

        $this->validate($rules);

        $idImagePath = null;
        if ($this->refund_id_image) {
            $idImagePath = $this->refund_id_image->store('refund_docs/ids', 'public');
        }

        $ticketFilePath = null;
        if ($this->refund_ticket_file) {
            $ticketFilePath = $this->refund_ticket_file->store('refund_docs/tickets', 'public');
        }

        $authLetterPath = null;
        if ($this->refund_auth_letter) {
            $authLetterPath = $this->refund_auth_letter->store('refund_docs/auth_letters', 'public');
        }

        $destinationParts = [];
        $destinationParts[] = "Method: " . $this->refund_method;
        if (in_array($this->refund_method, ['Bank Account', 'Online Wallet'], true)) {
            $destinationParts[] = "Institution: " . $this->refund_bank_name;
        }
        $destinationParts[] = "Account No: " . $this->refund_account_number;
        $destinationParts[] = "Name: " . $this->refund_account_name;
        
        $refundDestination = implode(' | ', $destinationParts);

        $selectedItems = !empty($this->selectedPassengerItems)
            ? $this->selectedPassengerItems
            : $this->booking->passengers->whereNotIn('status', ['cancelled', 'operator_cancelled', 'refunded'])->pluck('item_number')->toArray();

        $policy = $this->booking->validatePassengerPartyPolicy($selectedItems, 'cancel');
        if (! $policy['valid']) {
            $this->feedback = $policy['error'];
            return;
        }

        $selectedPassengers = $this->booking->passengers->filter(fn ($p) => in_array((int) $p->item_number, array_map('intval', $selectedItems), true));
        $netRefund = $selectedPassengers->sum(fn ($p) => $p->getRefundableBase()) ?: ($this->booking->getTicketBase() * (count($selectedItems) / max(1, $this->booking->passengers()->count())));

        try {
            foreach ($selectedPassengers as $p) {
                $pRefund = $p->getRefundableBase();
                $p->update([
                    'status'             => \App\Models\Passenger::STATUS_OPERATOR_CANCELLED,
                    'refund_status'      => 'pending',
                    'refund_amount'      => $pRefund,
                    'refund_destination' => $refundDestination,
                    'refund_id_image'    => $idImagePath ?: $p->refund_id_image,
                    'refund_ticket_file' => $ticketFilePath ?: $p->refund_ticket_file,
                    'refund_auth_letter' => $authLetterPath ?: $p->refund_auth_letter,
                ]);
            }

            $allCancelled = $this->booking->passengers->every(fn ($p) => in_array($p->status, ['cancelled', 'operator_cancelled', 'refund_pending', 'refunded'], true));

            $this->booking->update([
                'status' => $allCancelled ? 'operator_cancelled' : $this->booking->status,
                'disruption_status' => 'refund_requested',
                'refund_destination' => $refundDestination,
                'refund_id_image'    => $idImagePath ?: $this->booking->refund_id_image,
                'refund_ticket_file' => $ticketFilePath ?: $this->booking->refund_ticket_file,
                'refund_auth_letter' => $authLetterPath ?: $this->booking->refund_auth_letter,
                'refund_amount' => $this->booking->passengers->sum('refund_amount') ?: $netRefund,
            ]);

            if ($allCancelled && $this->booking->transaction) {
                $this->booking->transaction->update(['payment_status' => 'cancelled']);
            }

            $this->refund_id_image = null;
            $this->refund_ticket_file = null;
            $this->refund_auth_letter = null;

            $this->loadBooking();
            $this->closeRefundForm();
            $this->submitted = true;
            $this->feedback = 'Your cancellation has been recorded and a refund of ₱' . number_format($netRefund, 2) . ' has been requested for ' . $this->booking->getAffectedItemsLabel($selectedItems) . '. Our team will disburse it to your provided account within 24 to 48 hours.';
        } catch (\Exception $e) {
            $this->feedback = $e->getMessage();
        }
    }

    public function requestSupport(): void
    {
        if (! $this->booking) {
            return;
        }

        $this->booking->update([
            'disruption_status' => 'contact_support_required',
        ]);

        $this->loadBooking();
        $this->supportRequested = true;
        $this->feedback = 'Our support team has been notified. We will reach out to your email shortly to assist with custom travel arrangements.';
    }

    /**
     * Returns true if rescheduling should be blocked.
     * This includes only cases where no resume date has been announced yet.
     */
    public function getIsResumeBlockedProperty(): bool
    {
        $cancellation = $this->booking?->serviceCancellation;
        if (! $cancellation) {
            return false;
        }

        return ! $cancellation->resume_date;
    }

    /**
     * Get the original per-passenger accommodation price for comparison on cards.
     * dep/ret: which leg. Returns null if not applicable.
     */
    public function getOriginalDepAccommodationPrice(): ?float
    {
        if (! $this->booking) return null;
        
        $base = (float) ($this->booking->schedule_price ?? 0);
        $acc = (float) ($this->booking->schedule_accommodation_price ?? 0);
        
        if ($acc == 0 && $this->booking->transportClasses->count() > 0) {
            $tcs = $this->booking->transportClasses;
            $depTcs = $tcs->filter(fn ($tc) => ! (bool) $tc->pivot->is_return);
            $retTcs = $tcs->filter(fn ($tc) => (bool) $tc->pivot->is_return);
            // Bidirectional fallback: split by index order if one bucket is empty
            if ($tcs->count() === 2 && ($depTcs->isEmpty() || $retTcs->isEmpty())) {
                $arr = $tcs->values();
                $depTcs = collect([$arr[0]]);
            }
            $acc = (float) $depTcs->sum(fn ($tc) => $tc->pivot->price);
        }
        
        return $base + $acc;
    }

    public function getOriginalRetAccommodationPrice(): ?float
    {
        if (! $this->booking) return null;
        
        $base = (float) ($this->booking->return_schedule_price ?? 0);
        $acc = (float) ($this->booking->return_schedule_accommodation_price ?? 0);
        
        if ($acc == 0 && $this->booking->transportClasses->count() > 0) {
            $tcs = $this->booking->transportClasses;
            $depTcs = $tcs->filter(fn ($tc) => ! (bool) $tc->pivot->is_return);
            $retTcs = $tcs->filter(fn ($tc) => (bool) $tc->pivot->is_return);
            // Bidirectional fallback: split by index order if one bucket is empty
            if ($tcs->count() === 2 && ($depTcs->isEmpty() || $retTcs->isEmpty())) {
                $arr = $tcs->values();
                $retTcs = collect([$arr[1]]);
            }
            $acc = (float) $retTcs->sum(fn ($tc) => $tc->pivot->price);
        }
        
        return $base + $acc;
    }

    public function render()
    {
        return view('livewire.booking-reschedule', [
            'originalDepAccPrice' => $this->getOriginalDepAccommodationPrice(),
            'originalRetAccPrice' => $this->getOriginalRetAccommodationPrice(),
            'originalFare' => $this->originalFare,
            'newFare' => $this->newFare,
        ]);
    }
}

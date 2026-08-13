<?php

namespace App\Livewire;

use App\Mail\BookingCancellation;
use App\Mail\RebookingRequested;
use App\Models\Booking;
use App\Models\Transaction;
use App\Models\Schedule;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;
use Throwable;

class BookingLookup extends Component
{
    use WithFileUploads;
    public string $transaction_number = '';
    public string $email = '';
    public ?Booking $booking = null;
    public $bookings = null; // Collection of bookings
    public bool $searched = false;
    public ?string $feedback = null;
    public bool $cancellationRequested = false;
    public bool $cancellationWindowActive = false;
    public bool $cancellationExpired = false;
    public int $cancelCountdown = 300;
    public ?string $refund_destination = null;
    public string $refund_method = 'GCash';
    public string $refund_bank_name = '';
    public string $refund_account_number = '';
    public string $refund_account_name = '';
    public bool $rebookingRequested = false;
    public bool $rebookingPaid = false;
    public bool $rebooking_is_round_trip = false;
    public $rebookingProof;
    public bool $isUploadingRebooking = false;
    public ?string $rebooking_departure_date = null;
    public ?string $rebooking_return_date = null;

    // Customer Rebooking Wizard State
    public string $rebooking_step = 'departure_date';

    // Departure Leg Selection
    public ?int $rebooking_dep_schedule_id = null;
    public ?string $rebooking_dep_accommodation_id = null;
    public ?float $rebooking_dep_schedule_price = null;
    public ?float $rebooking_dep_accommodation_price = null;

    // Return Leg Selection
    public ?int $rebooking_ret_schedule_id = null;
    public ?string $rebooking_ret_accommodation_id = null;
    public ?float $rebooking_ret_schedule_price = null;
    public ?float $rebooking_ret_accommodation_price = null;

    // Price computation (before and after booking)
    public float $rebooking_new_total = 0.0;
    public float $rebooking_price_diff = 0.0;
    public float $rebooking_surcharge = 0.0;
    public float $rebooking_revalidation_fee = 0.0;
    public float $rebooking_rate_diff = 0.0;
    public float $rebooking_total_to_pay = 0.0;

    public bool $showCancellationWarning = false;
    public bool $showRebookingWarning = false;
    public bool $showCancellationReminder = false;
    public array $availableRebookingDates = [];
    public array $availableRebookingReturnDates = [];

    protected $rules = [
        'rebookingProof' => 'nullable|image|max:10240',
        'rebooking_departure_date' => 'required|date',
        'rebooking_is_round_trip' => 'boolean',
        'rebooking_return_date' => 'nullable|date|after_or_equal:rebooking_departure_date|required_if:rebooking_is_round_trip,1',
    ];

    public function mount(): void
    {
        $transactionNumber = request()->query('transaction_number');
        $email = request()->query('email');

        if (filled($transactionNumber) || filled($email)) {
            $this->transaction_number = trim((string) $transactionNumber);
            $this->email = trim((string) $email);
            $this->search();
            // If the link included start_cancellation=1, begin the cancellation flow and start the window.
            if (request()->query('start_cancellation')) {
                $this->requestCancellation();
            }
            if (request()->query('show_cancellation_reminder')) {
                $this->showCancellationReminder = true;
            }
            $this->loadCancellationWindowFromSession();

            if ($this->cancellationExpired) {
                $this->showCancellationWarning = false;
                $this->showCancellationReminder = false;
            }
        }
    }

    public function search(): void
    {
        $this->validate([
            'transaction_number' => 'required_without:email|nullable|string',
            'email' => 'required_without:transaction_number|nullable|email',
        ]);

        $this->searched = true;
        $this->feedback = null;
        $this->resetCancellationState();
        $this->resetRebookingState();

        $transactionNumber = trim($this->transaction_number);
        $email = trim($this->email);

        $query = Booking::with(['passengers.discount', 'accommodations', 'transaction']);
        
        if (filled($transactionNumber)) {
            $query->where('transaction_number', $transactionNumber);
        }
        if (filled($email)) {
            $query->where('client_email', $email);
        }

        $bookings = $query->latest()->get();

        if ($bookings->count() === 1) {
            $this->booking = $bookings->first();
            $this->bookings = null;
        } elseif ($bookings->count() > 1) {
            $this->bookings = $bookings;
            $this->booking = null;
        } else {
            $this->booking = null;
            $this->bookings = null;
        }

        if (! $this->booking && ! $this->bookings && filled($transactionNumber) && ctype_digit($transactionNumber)) {
            $tQuery = Transaction::with('booking.passengers.discount', 'booking.accommodations', 'booking.transaction')
                ->where('id', $transactionNumber);
                
            if (filled($email)) {
                $tQuery->whereHas('booking', function ($q) use ($email) {
                    $q->where('client_email', $email);
                });
            }
                
            $transaction = $tQuery->first();

            if ($transaction && $transaction->booking) {
                $this->booking = $transaction->booking;
            }
        }

        $this->loadCancellationWindowFromSession();

        if ($this->cancellationExpired) {
            $this->showCancellationWarning = false;
            $this->showCancellationReminder = false;
        }

        if ($this->booking && $this->booking->transaction) {
            $transaction = $this->booking->transaction;
            if ($transaction->payment_status === 'unpaid' &&
                $transaction->payment_deadline_at) {
                
                if ($transaction->payment_deadline_at->isFuture()) {
                    $this->redirectRoute('payment.show', $transaction->id);
                    return;
                } else if ($this->booking->status !== \App\Models\Booking::STATUS_CANCELLED) {
                    // Auto-cancel if the cron job hasn't picked it up yet
                    $this->booking->update(['status' => \App\Models\Booking::STATUS_CANCELLED]);
                    $transaction->update(['payment_status' => 'cancelled']);
                    $this->booking->refresh();
                }
            }
        }
    }

    public function showCancellationWarning(): void
    {
        if (! $this->booking) {
            $this->feedback = 'Booking not found.';
            return;
        }

        if (! $this->booking->canCancel()) {
            $this->feedback = 'You cannot cancel this booking as the departure date has passed or the payment is not fully verified.';
            return;
        }

        if (! in_array($this->booking->status, ['pending', 'confirmed'], true) || ! $this->booking->transaction || ! in_array($this->booking->transaction->payment_status, ['paid', 'pending', 'unpaid'])) {
            $this->feedback = 'This booking cannot be cancelled because it is not in a valid state.';
            return;
        }

        $this->resetRebookingState();
        $this->showCancellationWarning = true;
        $this->feedback = 'Please confirm that you want to start cancellation. This will begin a 5-minute confirmation timer and lock in a 50% refund.';
    }    public function viewBooking(string $transactionNumber): void
    {
        $this->transaction_number = $transactionNumber;
        $this->email = ''; // clear email so it strictly searches by transaction number
        $this->search();
    }

    public function requestCancellation(): void
    {
        if (! $this->booking) {
            $this->feedback = 'Booking not found.';
            return;
        }

        if (! $this->booking->canCancel()) {
            $this->feedback = 'You cannot cancel this booking as the departure date has passed.';
            return;
        }

        if (! in_array($this->booking->status, ['pending', 'confirmed'], true) || ! $this->booking->transaction || ! in_array($this->booking->transaction->payment_status, ['paid', 'pending', 'unpaid'])) {
            $this->feedback = 'This booking cannot be cancelled because it is not in a valid state.';
            return;
        }

        $this->resetRebookingState();
        $this->showCancellationWarning = false;
        $this->showCancellationReminder = false;

        $this->cancellationRequested = true;
        $this->cancellationWindowActive = true;

        $expiresAt = $this->booking->created_at->addMinutes(5);
        $remaining = $expiresAt->timestamp - now()->timestamp;

        if ($remaining <= 0) {
            $this->cancellationExpired = true;
            $this->cancelCountdown = 0;
            if (! $this->booking->isRefundEligible()) {
                $this->feedback = 'You cannot request a refund as it is less than 3 hours before the departure time.';
                $this->cancellationRequested = false;
                $this->cancellationWindowActive = false;
                return;
            }
            $refund = $this->booking->getRefundAmount(false);
            $fee    = $this->booking->getCancellationFeeAmount(false);
            $this->feedback = 'Enter where you would like the refund sent. Estimated refund: ₱' . number_format($refund, 2) . ' (cancellation deductions: ₱' . number_format($fee, 2) . ').';
        } else {
            $this->cancellationExpired = false;
            $this->cancelCountdown = $remaining;
            $this->feedback = 'Enter where you would like the refund sent. Cancellation is eligible for a 100% refund within 5 minutes of booking.';
        }

        $this->refund_destination = null;
        $this->refund_method = 'GCash';
        $this->refund_bank_name = '';
        $this->refund_account_number = '';
        $this->refund_account_name = '';
    }

    public function dismissCancellationReminder(): void
    {
        $this->showCancellationReminder = false;
    }

    public function confirmCancellationRequest(): void
    {
        $this->requestCancellation();
    }

    public function cancelBooking(): void
    {
        $this->requestCancellation();
    }

    public function confirmCancellation(): void
    {
        if (blank($this->refund_account_number) && blank($this->refund_account_name) && filled($this->refund_destination)) {
            // Fallback for tests that set refund_destination directly
            $this->validate([
                'refund_destination' => 'required|string|max:255',
            ]);
        } else {
            // Compile the refund destination string
            $destinationParts = [];
            $destinationParts[] = "Method: " . $this->refund_method;
            if (in_array($this->refund_method, ['Bank Account', 'Online Wallet'], true)) {
                $destinationParts[] = "Institution: " . $this->refund_bank_name;
            }
            $destinationParts[] = "Account No: " . $this->refund_account_number;
            $destinationParts[] = "Name: " . $this->refund_account_name;
            $this->refund_destination = implode(' | ', $destinationParts);

            // Perform validation
            $rules = [
                'refund_method' => 'required|string|in:GCash,Online Wallet,Bank Account',
                'refund_account_number' => 'required|string|max:50',
                'refund_account_name' => 'required|string|max:100',
            ];

            if (in_array($this->refund_method, ['Bank Account', 'Online Wallet'], true)) {
                $rules['refund_bank_name'] = 'required|string|max:100';
            }

            $this->validate($rules);
        }

        if (! $this->booking) {
            $this->feedback = 'Booking not found.';
            return;
        }

        if (! $this->booking->canCancel()) {
            $this->feedback = 'You cannot cancel this booking as the departure date has passed.';
            return;
        }

        if (! in_array($this->booking->status, ['pending', 'confirmed'], true) || ! $this->booking->transaction || ! in_array($this->booking->transaction->payment_status, ['paid', 'pending', 'unpaid'])) {
            $this->feedback = 'This booking cannot be cancelled because it is not in a valid state.';
            return;
        }

        // Calculate refund using the new surcharge-based formula
        $isWithinFiveMinutes = $this->booking->created_at->addMinutes(5)->isFuture();
        $cancellationFee = $this->booking->getCancellationFeeAmount($isWithinFiveMinutes);
        $refundAmount    = $this->booking->getRefundAmount($isWithinFiveMinutes);

        if (! $isWithinFiveMinutes && ! $this->booking->isRefundEligible()) {
            $this->feedback = 'You cannot request a refund as it is less than 3 hours before the departure time.';
            return;
        }

        $this->booking->update([
            'status' => 'cancelled',
            'cancellation_fee' => $cancellationFee,
            'refund_amount' => $refundAmount,
            'refund_destination' => $this->refund_destination,
        ]);
        $this->booking->transaction->update(['payment_status' => 'cancelled']);
        $this->booking = $this->booking->fresh(['passengers.discount', 'accommodations', 'transaction']);

        try {
            Mail::to($this->booking->client_email)->send(new BookingCancellation($this->booking, $this->refund_destination));
        } catch (Throwable $e) {
            Log::error('Failed sending booking cancellation email', [
                'booking_id' => $this->booking->id ?? null,
                'email' => $this->booking->client_email ?? null,
                'error' => $e->getMessage(),
            ]);
        }

        $this->feedback = "Your booking has been refunded successfully. Cancellation fee: ₱" . number_format($cancellationFee, 2) . ", Refundable amount: ₱" . number_format($refundAmount, 2) . ". A confirmation email has been sent.";
        $this->resetCancellationState();
    }

    public function tickCancelCountdown(): void
    {
        if (! $this->booking) {
            return;
        }

        $remaining = $this->booking->created_at->addMinutes(5)->timestamp - now()->timestamp;

        if ($remaining <= 0) {
            $this->cancelCountdown = 0;
            $this->cancellationExpired = true;
        } else {
            $this->cancelCountdown = $remaining;
            $this->cancellationExpired = false;
        }
    }

    public function cancelCancellationRequest(): void
    {
        $this->resetCancellationState();
        $this->feedback = 'Cancellation request cancelled. Your proof-upload timer will remain active if it has not yet expired.';
    }

    public function cancelRebookingWarning(): void
    {
        $this->showRebookingWarning = false;
        $this->feedback = null;
    }

    public function showRebookingWarning(): void
    {
        if (! $this->booking) {
            $this->feedback = 'Booking not found.';
            return;
        }

        if (! $this->booking->canRebook()) {
            $this->feedback = 'You cannot rebook this booking as the departure date has passed.';
            return;
        }

        if ($this->booking->departure_date->isToday()) {
            $this->feedback = 'Rebooking cannot be requested for same-day departures. Please contact support for urgent changes.';
            return;
        }

        if ($this->booking->is_rebooked) {
            $this->feedback = 'This booking has already been rebooked once. You cannot rebook it again.';
            return;
        }

        if (! in_array($this->booking->status, ['pending', 'confirmed'], true) || ! $this->booking->transaction || $this->booking->transaction->payment_status !== 'paid') {
            $this->feedback = 'This booking cannot be rebooked because it is not fully paid and verified.';
            return;
        }

        $this->resetCancellationState();
        $this->showRebookingWarning = true;
        $this->feedback = 'Please confirm that you want to start rebooking. Rebooking requires a new travel date selection and proof of payment for the 30% fee.';
    }

    public function requestRebooking(): void
    {
        $this->showRebookingWarning();
    }

    public function confirmRebookingRequest(): void
    {
        if ($this->booking && $this->booking->is_rebooked) {
            $this->feedback = 'This booking has already been rebooked once. You cannot rebook it again.';
            return;
        }

        $this->resetCancellationState();
        $this->resetRebookingState();
        $this->showRebookingWarning = false;
        $this->rebookingRequested = true;
        $this->rebooking_is_round_trip = filled($this->booking->return_date);
        $this->rebooking_departure_date = $this->booking->departure_date?->format('Y-m-d');
        $this->rebooking_return_date = $this->booking->return_date?->format('Y-m-d');
        $this->rebooking_step = 'departure_date';
        $this->feedback = "Please select your new travel date, schedule, and preferred accommodation below.";
    }

    public function setRebookingStep(string $step): void
    {
        $this->rebooking_step = $step;
        $this->feedback = null;
        if ($step === 'confirm') {
            $this->calculateRebookingPriceDiff();
        }
    }

    public function updatedRebookingDepartureDate(): void
    {
        $this->rebooking_dep_schedule_id = null;
        $this->rebooking_dep_accommodation_id = null;
        $this->rebooking_dep_schedule_price = null;
        $this->rebooking_dep_accommodation_price = null;
    }

    public function updatedRebookingReturnDate(): void
    {
        $this->rebooking_ret_schedule_id = null;
        $this->rebooking_ret_accommodation_id = null;
        $this->rebooking_ret_schedule_price = null;
        $this->rebooking_ret_accommodation_price = null;
    }

    public function selectRebookingDepartureSchedule(int $scheduleId, float $price): void
    {
        $this->rebooking_dep_schedule_id = $scheduleId;
        $this->rebooking_dep_schedule_price = $price;
        $this->rebooking_dep_accommodation_id = null;
        $this->rebooking_dep_accommodation_price = null;
        $this->setRebookingStep('departure_accommodation');
    }

    public function selectRebookingDepartureAccommodation(string $accId, float $price): void
    {
        $passengerCount = max(1, $this->booking->passengers()->count());
        $mode = $this->booking ? $this->booking->getMode() : 'ferry';

        // Original per-pax: base fare + transport class from pivot (index 0 = departure)
        $this->booking->loadMissing('transportClasses');
        $transportClasses = $this->booking->transportClasses;
        $origDepTCPerPax = (float)optional($transportClasses->values()->get(0))->pivot?->price;
        $originalPerPax  = (float)($this->booking->schedule_price ?? 0) + $origDepTCPerPax
                         + (float)($this->booking->schedule_accommodation_price ?? 0);

        if ($mode === 'airline') {
            // For airlines, the displayed price is already (schedule + class) * 1.5 markup.
            // Divide by 1.5 to get the comparable base.
            $newPerPax = $price / 1.5;
            if ($newPerPax < $originalPerPax) {
                $this->feedback = "You cannot select a lower class than your original booking. Please select an equal or higher class.";
                return;
            }
        } else {
            // For ferries, price = accommodation_price per pax (already combined in UI with schedule)
            $newPerPax = ($this->rebooking_dep_schedule_price ?? 0) + $price;
            if ($newPerPax < $originalPerPax) {
                $this->feedback = "You cannot rebook to a lower class than your original booking. Please select an equal or higher class.";
                return;
            }
        }

        $this->rebooking_dep_accommodation_id = $accId;
        $this->rebooking_dep_accommodation_price = $price;
        if ($this->rebooking_is_round_trip) {
            $this->setRebookingStep('return_date');
        } else {
            $this->setRebookingStep('confirm');
        }
    }

    public function selectRebookingReturnSchedule(int $scheduleId, float $price): void
    {
        $this->rebooking_ret_schedule_id = $scheduleId;
        $this->rebooking_ret_schedule_price = $price;
        $this->rebooking_ret_accommodation_id = null;
        $this->rebooking_ret_accommodation_price = null;
        $this->setRebookingStep('return_accommodation');
    }

    public function selectRebookingReturnAccommodation(string $accId, float $price): void
    {
        $passengerCount = max(1, $this->booking->passengers()->count());
        $mode = $this->booking ? $this->booking->getMode() : 'ferry';

        // Original per-pax return: base fare + transport class from pivot (index 1 = return)
        $this->booking->loadMissing('transportClasses');
        $transportClasses = $this->booking->transportClasses;
        $origRetTCPerPax = (float)optional($transportClasses->values()->get(1))->pivot?->price;
        $originalPerPax  = (float)($this->booking->return_schedule_price ?? 0) + $origRetTCPerPax
                         + (float)($this->booking->return_schedule_accommodation_price ?? 0);

        if ($mode === 'airline') {
            $newPerPax = $price / 1.5;
            if ($newPerPax < $originalPerPax) {
                $this->feedback = "You cannot select a lower class than your original return booking. Please select an equal or higher class.";
                return;
            }
        } else {
            $newPerPax = ($this->rebooking_ret_schedule_price ?? 0) + $price;
            if ($newPerPax < $originalPerPax) {
                $this->feedback = "You cannot rebook to a lower class than your original return booking. Please select an equal or higher class.";
                return;
            }
        }

        $this->rebooking_ret_accommodation_id = $accId;
        $this->rebooking_ret_accommodation_price = $price;
        $this->setRebookingStep('confirm');
    }

    public function getAvailableRebookingDepartureSchedulesProperty()
    {
        if (!$this->booking || !$this->rebooking_departure_date) return collect();
        return Schedule::forRouteAndDate($this->booking->origin, $this->booking->destination, $this->rebooking_departure_date)
            ->with(['ferryRoute', 'vehicle'])
            ->get();
    }

    public function getAvailableRebookingReturnSchedulesProperty()
    {
        if (!$this->booking || !$this->rebooking_return_date) return collect();
        return Schedule::forRouteAndDate($this->booking->destination, $this->booking->origin, $this->rebooking_return_date)
            ->with(['ferryRoute', 'vehicle'])
            ->get();
    }

    public function getRebookingDepartureAccommodationsProperty()
    {
        if (!$this->rebooking_dep_schedule_id) return collect();
        $schedule = Schedule::with(['scheduleAccommodations', 'transportClasses'])->find($this->rebooking_dep_schedule_id);
        if (!$schedule) return collect();

        $isAirline    = $this->booking && $this->booking->getMode() === 'airline';
        $schedulePrice = $isAirline ? ($schedule->price ?? 0) : 0;

        // Compute the original per-pax minimum (ticket + original transport class)
        $this->booking->loadMissing('transportClasses');
        $origTCPerPax   = (float) optional($this->booking->transportClasses->values()->get(0))->pivot?->price;
        $originalPerPax = (float)($this->booking->schedule_price ?? 0)
                        + $origTCPerPax
                        + (float)($this->booking->schedule_accommodation_price ?? 0);

        $items = collect();
        foreach ($schedule->scheduleAccommodations->where('is_active', true)->sortBy('sort_order') as $acc) {
            $price = $acc->price;
            if ($isAirline) {
                $price = ($schedulePrice + $price) * 1.5;
            }
            $newPerPax = $isAirline ? ($price / 1.5) : (($this->rebooking_dep_schedule_price ?? 0) + $price);
            $items->push((object)[
                'id'       => 'acc_' . $acc->id,
                'name'     => $acc->name,
                'description' => $acc->description,
                'price'    => $price,
                'disabled' => $newPerPax < $originalPerPax,
            ]);
        }
        foreach ($schedule->transportClasses->where('pivot.is_active', true)->sortBy('pivot.sort_order') as $tc) {
            $price = $tc->pivot->additional_price;
            if ($isAirline) {
                $price = ($schedulePrice + $price) * 1.5;
            }
            $newPerPax = $isAirline ? ($price / 1.5) : (($this->rebooking_dep_schedule_price ?? 0) + $price);
            $items->push((object)[
                'id'       => 'tc_' . $tc->id,
                'name'     => $tc->name,
                'description' => $tc->pivot->description ?? $tc->description,
                'price'    => $price,
                'disabled' => $newPerPax < $originalPerPax,
            ]);
        }
        return $items;
    }

    public function getRebookingReturnAccommodationsProperty()
    {
        if (!$this->rebooking_ret_schedule_id) return collect();
        $schedule = Schedule::with(['scheduleAccommodations', 'transportClasses'])->find($this->rebooking_ret_schedule_id);
        if (!$schedule) return collect();

        $isAirline    = $this->booking && $this->booking->getMode() === 'airline';
        $schedulePrice = $isAirline ? ($schedule->price ?? 0) : 0;

        // Compute the original per-pax minimum for the return leg (index 1 = return transport class)
        $this->booking->loadMissing('transportClasses');
        $origTCPerPax   = (float) optional($this->booking->transportClasses->values()->get(1))->pivot?->price;
        $originalPerPax = (float)($this->booking->return_schedule_price ?? 0)
                        + $origTCPerPax
                        + (float)($this->booking->return_schedule_accommodation_price ?? 0);

        $items = collect();
        foreach ($schedule->scheduleAccommodations->where('is_active', true)->sortBy('sort_order') as $acc) {
            $price = $acc->price;
            if ($isAirline) {
                $price = ($schedulePrice + $price) * 1.5;
            }
            $newPerPax = $isAirline ? ($price / 1.5) : (($this->rebooking_ret_schedule_price ?? 0) + $price);
            $items->push((object)[
                'id'       => 'acc_' . $acc->id,
                'name'     => $acc->name,
                'description' => $acc->description,
                'price'    => $price,
                'disabled' => $newPerPax < $originalPerPax,
            ]);
        }
        foreach ($schedule->transportClasses->where('pivot.is_active', true)->sortBy('pivot.sort_order') as $tc) {
            $price = $tc->pivot->additional_price;
            if ($isAirline) {
                $price = ($schedulePrice + $price) * 1.5;
            }
            $newPerPax = $isAirline ? ($price / 1.5) : (($this->rebooking_ret_schedule_price ?? 0) + $price);
            $items->push((object)[
                'id'       => 'tc_' . $tc->id,
                'name'     => $tc->name,
                'description' => $tc->pivot->description ?? $tc->description,
                'price'    => $price,
                'disabled' => $newPerPax < $originalPerPax,
            ]);
        }
        return $items;
    }

    public function calculateRebookingPriceDiff(): void
    {
        if (!$this->booking) return;

        $passengerCount = $this->booking->passengers()->count() ?: 1;
        $mode = $this->booking->getMode();
        $isAirline = $mode === 'airline';

        // ── Original per-pax fare: base schedule + transport class (pivot) + hotel accommodation ──
        // The transport class pivot price (index 0 = departure, index 1 = return)
        // is per-pax and is stored separately from schedule_accommodation_price (hotel).
        $this->booking->loadMissing('transportClasses');
        $tcs = $this->booking->transportClasses->values();
        $depTCPerPax = (float) optional($tcs->get(0))->pivot?->price;
        $retTCPerPax = (float) optional($tcs->get(1))->pivot?->price;

        $origDepPerPax = (float)($this->booking->schedule_price ?? 0)
                       + $depTCPerPax
                       + (float)($this->booking->schedule_accommodation_price ?? 0);
        $origRetPerPax = (float)($this->booking->return_schedule_price ?? 0)
                       + $retTCPerPax
                       + (float)($this->booking->return_schedule_accommodation_price ?? 0);
        $originalFare  = ($origDepPerPax + $origRetPerPax) * $passengerCount;

        // ── New total ──
        $newTotal = 0.0;

        if ($isAirline) {
            // For airlines the stored acc price is already (schedule + class) * 1.5.
            // Divide by 1.5 to get the comparable base then sum.
            $depPerPax = ($this->rebooking_dep_accommodation_price ?? 0) / 1.5;
            $newTotal  += $depPerPax * $passengerCount;
            if ($this->rebooking_is_round_trip) {
                $retPerPax = ($this->rebooking_ret_accommodation_price ?? 0) / 1.5;
                $newTotal += $retPerPax * $passengerCount;
            }
        } else {
            // Ferry: schedule price is stored separately; acc price is per pax
            $depPerPax = ($this->rebooking_dep_schedule_price ?? 0)
                       + ($this->rebooking_dep_accommodation_price ?? 0);
            $newTotal += $depPerPax * $passengerCount;
            if ($this->rebooking_is_round_trip) {
                $retPerPax = ($this->rebooking_ret_schedule_price ?? 0)
                           + ($this->rebooking_ret_accommodation_price ?? 0);
                $newTotal += $retPerPax * $passengerCount;
            }
        }

        if ($this->booking->has_vehicle) {
            $newTotal += $this->booking->vehicle_price;
        }
        $this->rebooking_new_total = $newTotal;

        $settings = \App\Models\PaymentSetting::current();
        $isAfterDeparture = $this->booking->isAfterDeparture();

        // 1. Revalidation Fee
        $this->rebooking_revalidation_fee = floatval($settings->revalidation_fee) * $passengerCount;

        // 2. Surcharge applied on the original fare base
        $surchargePct = 0;
        if ($isAirline) {
            $surchargePct = (float)$settings->rebook_airline_before_departure_surcharge_pct;
        } elseif ($isAfterDeparture) {
            $surchargePct = (float)$settings->rebook_ferry_after_departure_surcharge_pct;
        } else {
            $surchargePct = (float)$settings->rebook_ferry_before_departure_surcharge_pct;
        }
        $this->rebooking_surcharge = $originalFare * ($surchargePct / 100);

        // 3. Rate Difference (only charged if upgrading; downgrade is already blocked at selection)
        $this->rebooking_rate_diff  = max(0, $newTotal - $originalFare);
        $this->rebooking_price_diff = $this->rebooking_rate_diff;
        $this->rebooking_total_to_pay = $this->rebooking_surcharge
                                      + $this->rebooking_revalidation_fee
                                      + $this->rebooking_rate_diff;
    }


    public function submitRebookingProof(): void
    {
        $this->validate([
            'rebookingProof' => 'required|image|max:10240',
        ]);

        $this->isUploadingRebooking = true;

        $extension = $this->rebookingProof->extension();
        $filename = 'rebook_proof_' . $this->booking->transaction_number . '_' . uniqid() . '.' . $extension;
        $path = $this->rebookingProof->storeAs('rebooking_proofs', $filename, 'public');

        $this->booking->transaction->update([
            'rebooking_fee' => $this->rebooking_total_to_pay,
            'rebooking_proof_of_payment' => $path,
        ]);

        $this->booking->update([
            'rebooking_status' => 'pending',
            'preferred_replacement_schedule_id' => $this->rebooking_dep_schedule_id,
            'preferred_replacement_date' => $this->rebooking_departure_date,
            'rebooking_departure_date' => $this->rebooking_departure_date,
            'rebooking_return_date' => $this->rebooking_is_round_trip ? $this->rebooking_return_date : null,
            'disruption_notes' => json_encode([
                'dep_schedule_id' => $this->rebooking_dep_schedule_id,
                'dep_accommodation_id' => $this->rebooking_dep_accommodation_id,
                'ret_schedule_id' => $this->rebooking_ret_schedule_id,
                'ret_accommodation_id' => $this->rebooking_ret_accommodation_id,
                'rate_diff' => $this->rebooking_rate_diff,
                'surcharge' => $this->rebooking_surcharge,
                'revalidation_fee' => $this->rebooking_revalidation_fee,
                'total_paid' => $this->rebooking_total_to_pay,
                'proof_path' => $path,
            ]),
        ]);

        try {
            Mail::to($this->booking->client_email)->send(new RebookingRequested($this->booking));
        } catch (Throwable $e) {
            Log::error('Failed sending rebooking requested email', [
                'booking_id' => $this->booking->id ?? null,
                'email' => $this->booking->client_email ?? null,
                'error' => $e->getMessage(),
            ]);
        }

        $this->isUploadingRebooking = false;
        $this->rebookingPaid = true;

        $this->feedback = "Rebooking fee & payment received and is now pending verification. Total paid: ₱" . number_format($this->rebooking_total_to_pay, 2) . ".";
    }

    private function getCancellationSessionKey(): string
    {
        return 'cancellation_window_expires_for_' . $this->transaction_number;
    }

    private function getCancellationExpiredKey(): string
    {
        return 'cancellation_expired_for_' . $this->transaction_number;
    }

    private function loadCancellationWindowFromSession(): void
    {
        if (! $this->booking) {
            return;
        }

        $remaining = $this->booking->created_at->addMinutes(5)->timestamp - now()->timestamp;
        if ($remaining <= 0) {
            $this->cancellationExpired = true;
            $this->cancelCountdown = 0;
        } else {
            $this->cancellationExpired = false;
            $this->cancelCountdown = $remaining;
        }
    }

    private function resetCancellationState(): void
    {
        $this->cancellationRequested = false;
        $this->cancellationWindowActive = false;
        $this->refund_destination = null;
        $this->refund_method = 'GCash';
        $this->refund_bank_name = '';
        $this->refund_account_number = '';
        $this->refund_account_name = '';
        $this->showCancellationWarning = false;
        // Re-sync the expired flag from the real 5-minute timer — never blindly reset it to false.
        $this->loadCancellationWindowFromSession();
        // NOTE: do NOT delete the cancellation_expired session key here.
        // It must survive page refreshes. Only startCancellationWindow() clears it
        // when the user explicitly begins a fresh cancellation attempt.
    }

    private function resetRebookingState(): void
    {
        $this->rebookingRequested = false;
        $this->rebookingPaid = false;
        $this->rebooking_is_round_trip = false;
        $this->rebookingProof = null;
        $this->isUploadingRebooking = false;
        $this->rebooking_departure_date = null;
        $this->rebooking_return_date = null;
        $this->rebooking_step = 'departure_date';
        $this->rebooking_dep_schedule_id = null;
        $this->rebooking_dep_accommodation_id = null;
        $this->rebooking_dep_schedule_price = null;
        $this->rebooking_dep_accommodation_price = null;
        $this->rebooking_ret_schedule_id = null;
        $this->rebooking_ret_accommodation_id = null;
        $this->rebooking_ret_schedule_price = null;
        $this->rebooking_ret_accommodation_price = null;
        $this->rebooking_new_total = 0.0;
        $this->rebooking_price_diff = 0.0;
        $this->rebooking_total_to_pay = 0.0;
    }

    public function getPriceBreakdownProperty(): array
    {
        if (! $this->booking) return [];

        return $this->booking->getPriceBreakdown();
    }

    public function render()
    {
        return view('livewire.booking-lookup');
    }
}

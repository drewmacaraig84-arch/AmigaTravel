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
    public $refund_id_image = null;
    public $refund_ticket_file = null;
    public $refund_auth_letter = null;
    public bool $rebookingRequested = false;
    public bool $rebookingPaid = false;
    public bool $isUploadingRebooking = false;
    public bool $rebooking_is_round_trip = false;
    public ?string $rebooking_reference_number = null;
    public $rebookingProof;
    public ?string $rebooking_departure_date = null;
    public ?string $rebooking_return_date = null;
    public array $selectedPassengerItems = [];

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

    public float $rebooking_original_fare = 0.0;
    public float $rebooking_new_total = 0.0;
    public float $rebooking_price_diff = 0.0;
    public float $rebooking_surcharge = 0.0;
    public float $rebooking_revalidation_fee = 0.0;
    public float $rebooking_rate_diff = 0.0;
    public float $rebooking_total_to_pay = 0.0;
    public array $rebooking_passengers_breakdown = [];

    public bool $showCancellationWarning = false;
    public bool $showRebookingWarning = false;
    public bool $showCancellationReminder = false;
    public array $availableRebookingDates = [];
    public array $availableRebookingReturnDates = [];

    protected $rules = [
        'rebooking_reference_number' => 'required|string|max:120',
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

        if ($this->booking) {
            // Default select all active travelling passengers
            $this->selectedPassengerItems = $this->booking->getActivePassengers()
                ->pluck('item_number')
                ->map(fn ($n) => (int) $n)
                ->values()
                ->toArray();
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

    public function selectAllPassengers(): void
    {
        if (! $this->booking) {
            return;
        }
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
        if (! $this->booking) {
            return;
        }

        $pax = $this->booking->passengers->firstWhere('item_number', $itemNumber);
        if (! $pax || ! $pax->isActiveBookingItem()) {
            // Cannot toggle locked/inactive items
            return;
        }

        if ($this->booking->hasSingleAdultWithNonAdults()) {
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
        if (! $this->booking) {
            return '—';
        }
        return $this->booking->getAffectedItemsLabel(empty($this->selectedPassengerItems) ? null : $this->selectedPassengerItems);
    }

    public function showCancellationWarning(): void
    {
        if (! $this->booking) {
            $this->feedback = 'Booking not found.';
            return;
        }

        if (! $this->booking->canCancel()) {
            if ($this->booking->hasPromoTicket() && !($this->booking->created_at && $this->booking->created_at->addMinutes(5)->isFuture())) {
                $this->feedback = 'Promotional tickets cannot be cancelled after the 5-minute grace period.';
            } else {
                $this->feedback = 'You cannot cancel this booking as the departure date has passed or the payment is not fully verified.';
            }
            return;
        }

        if (! in_array($this->booking->status, ['pending', 'confirmed'], true) || ! $this->booking->transaction || ! in_array($this->booking->transaction->payment_status, ['paid', 'pending', 'unpaid'])) {
            $this->feedback = 'This booking cannot be cancelled because it is not in a valid state.';
            return;
        }

        $this->resetRebookingState();
        $this->showCancellationWarning = true;
        $this->feedback = 'Please confirm that you want to start cancellation. This will begin a 5-minute confirmation timer and lock in a 50% refund.';
    }

    public function viewBooking(string $transactionNumber): void
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

        if (empty($this->selectedPassengerItems)) {
            $this->selectAllPassengers();
        }

        if (! $this->booking->canCancel()) {
            if ($this->booking->hasPromoTicket() && !($this->booking->created_at && $this->booking->created_at->addMinutes(5)->isFuture())) {
                $this->feedback = 'Promotional tickets cannot be cancelled after the 5-minute grace period.';
            } else {
                $this->feedback = 'You cannot cancel this booking as the departure date has passed or the payment is not fully verified.';
            }
            return;
        }

        $selectedItems = ! empty($this->selectedPassengerItems) ? $this->selectedPassengerItems : $this->booking->passengers->pluck('item_number')->toArray();
        $policy = $this->booking->validatePassengerPartyPolicy($selectedItems, 'cancel');
        if (! $policy['valid']) {
            $this->feedback = $policy['error'];
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

        $selectedItems = ! empty($this->selectedPassengerItems) ? $this->selectedPassengerItems : $this->booking->passengers->pluck('item_number')->toArray();
        $isWithinFiveMinutes = $remaining > 0;
        $breakdown = $this->booking->getPartialRefundBreakdown($selectedItems, $isWithinFiveMinutes);

        if ($remaining <= 0) {
            $this->cancellationExpired = true;
            $this->cancelCountdown = 0;
            if (! $this->booking->isRefundEligible()) {
                $this->feedback = 'You cannot request a refund as it is less than 24 hours before the departure time.';
                $this->cancellationRequested = false;
                $this->cancellationWindowActive = false;
                return;
            }
            $refund = $breakdown['refundable_amount'];
            $fee    = $breakdown['deduction_amount'];
            $itemsLabel = $this->booking->getAffectedItemsLabel($selectedItems);
            $this->feedback = "Enter where you would like the refund sent for {$itemsLabel}. Estimated refund: ₱" . number_format($refund, 2) . " (cancellation deductions: ₱" . number_format($fee, 2) . ").";
        } else {
            $this->cancellationExpired = false;
            $this->cancelCountdown = $remaining;
            $itemsLabel = $this->booking->getAffectedItemsLabel($selectedItems);
            $this->feedback = "Enter where you would like the refund sent for {$itemsLabel}. Cancellation is eligible for a 100% refund within 5 minutes of booking.";
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
        $this->validate([
            'refund_id_image' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:10240',
            'refund_ticket_file' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:10240',
            'refund_auth_letter' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:10240',
        ]);

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
            if ($this->booking->hasPromoTicket() && !($this->booking->created_at && $this->booking->created_at->addMinutes(5)->isFuture())) {
                $this->feedback = 'Promotional tickets cannot be cancelled after the 5-minute grace period.';
            } else {
                $this->feedback = 'You cannot cancel this booking as the departure date has passed or the payment is not fully verified.';
            }
            return;
        }

        if (! in_array($this->booking->status, ['pending', 'confirmed'], true) || ! $this->booking->transaction || ! in_array($this->booking->transaction->payment_status, ['paid', 'pending', 'unpaid'])) {
            $this->feedback = 'This booking cannot be cancelled because it is not in a valid state.';
            return;
        }

        $isWithinFiveMinutes = $this->booking->created_at->addMinutes(5)->isFuture();

        if (! $isWithinFiveMinutes && ! $this->booking->isRefundEligible()) {
            $this->feedback = 'You cannot request a refund as it is less than 24 hours before the departure time.';
            return;
        }

        $eligiblePassengers = $this->booking->passengers->filter(function ($p) {
            return ! in_array($p->status, ['refund_pending', 'refunded', 'rebooking_pending', 'rebooked', 'cancelled', 'operator_cancelled'], true);
        });

        if ($eligiblePassengers->isEmpty()) {
            $this->feedback = 'No eligible passenger items can be cancelled or refunded on this booking.';
            return;
        }

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

        $rawSelected = ! empty($this->selectedPassengerItems)
            ? $this->selectedPassengerItems
            : $eligiblePassengers->pluck('item_number')->toArray();

        $selectedItems = array_values(array_intersect(
            array_map('intval', $rawSelected),
            $eligiblePassengers->pluck('item_number')->map(fn ($n) => (int) $n)->toArray()
        ));

        if (empty($selectedItems)) {
            $this->feedback = 'Selected passenger item(s) cannot be cancelled because their refund or rebooking is already pending or completed.';
            return;
        }

        $policy = $this->booking->validatePassengerPartyPolicy($selectedItems, 'cancel');
        if (! $policy['valid']) {
            $this->feedback = $policy['error'];
            return;
        }

        $partialBreakdown = $this->booking->getPartialRefundBreakdown($selectedItems, $isWithinFiveMinutes);
        $totalRefundAmount = $partialBreakdown['refundable_amount'];
        $totalCancellationFee = $partialBreakdown['deduction_amount'];

        $allPassengers = $this->booking->passengers->sortBy('item_number')->values();
        $selectedCount = count($selectedItems);
        $totalPaxCount = $allPassengers->count();
        $isFullCancellation = ($selectedCount >= $totalPaxCount);

        // Update each selected passenger item
        foreach ($allPassengers as $p) {
            if (in_array((int) $p->item_number, array_map('intval', $selectedItems), true)) {
                $pBreakdown = $p->getRefundBreakdown($isWithinFiveMinutes);
                $pRefund = $pBreakdown['refundable_amount'];
                $pFee    = $pBreakdown['deduction_amount'];

                $p->update([
                    'status'             => 'refund_pending',
                    'refund_amount'      => $pRefund,
                    'cancellation_fee'   => $pFee,
                    'refund_destination' => $this->refund_destination,
                    'refund_id_image'    => $idImagePath ?: $p->refund_id_image,
                    'refund_ticket_file' => $ticketFilePath ?: $p->refund_ticket_file,
                    'refund_auth_letter' => $authLetterPath ?: $p->refund_auth_letter,
                    'refund_status'      => 'pending',
                ]);
            }
        }

        // Refresh passengers
        $this->booking->load('passengers');
        $allCancelled = $this->booking->passengers->every(fn ($p) => in_array($p->status, ['cancelled', 'refund_pending', 'refunded', 'operator_cancelled']));

        if ($isFullCancellation || $allCancelled) {
            $this->booking->update([
                'status'             => 'cancelled',
                'cancellation_fee'   => $totalCancellationFee,
                'refund_amount'      => $this->booking->passengers->sum('refund_amount') ?: $totalRefundAmount,
                'refund_destination' => $this->refund_destination,
                'refund_id_image'    => $idImagePath ?: $this->booking->refund_id_image,
                'refund_ticket_file' => $ticketFilePath ?: $this->booking->refund_ticket_file,
                'refund_auth_letter' => $authLetterPath ?: $this->booking->refund_auth_letter,
                'refund_status'      => 'pending',
            ]);
            $this->booking->transaction?->update(['payment_status' => 'cancelled']);
        } else {
            // Partial cancellation: booking remains active, passenger items show refund_pending
            $this->booking->update([
                'refund_amount'      => $this->booking->passengers->sum('refund_amount'),
                'refund_destination' => $this->refund_destination,
                'refund_id_image'    => $idImagePath ?: $this->booking->refund_id_image,
                'refund_ticket_file' => $ticketFilePath ?: $this->booking->refund_ticket_file,
                'refund_auth_letter' => $authLetterPath ?: $this->booking->refund_auth_letter,
                'refund_status'      => 'pending',
            ]);
        }

        $this->refund_id_image = null;
        $this->refund_ticket_file = null;
        $this->refund_auth_letter = null;
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

        $itemsLabel = $this->booking->getAffectedItemsLabel($selectedItems);

        // Send User Notification & FCM push notification
        if ($this->booking->user_id) {
            \App\Models\UserNotification::notify(
                $this->booking->user_id,
                '💰 Refund Request Received',
                "Your refund request of ₱" . number_format((float) $totalRefundAmount, 2) . " for {$itemsLabel} (booking #{$this->booking->transaction_number}) is being processed. Please allow 24–48 hours for review and disbursement.",
                'booking',
                'money_off',
                ['transaction_number' => $this->booking->transaction_number, 'refund_status' => 'pending']
            );
        }

        $this->feedback = "Your cancellation and refund request for {$itemsLabel} have been submitted successfully. Please allow 24–48 hours for our finance team to review and disburse your refund of ₱" . number_format($totalRefundAmount, 2) . " to your account. A confirmation email has been sent.";
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
            $this->feedback = 'You cannot rebook this booking as it is less than 24 hours before the departure time or the departure date has passed.';
            return;
        }

        $selectedItems = ! empty($this->selectedPassengerItems)
            ? $this->selectedPassengerItems
            : $this->booking->getActivePassengers()->pluck('item_number')->toArray();

        $policy = $this->booking->validatePassengerPartyPolicy($selectedItems, 'rebook');
        if (! $policy['valid']) {
            $this->feedback = $policy['error'];
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

        $selectedItems = ! empty($this->selectedPassengerItems)
            ? $this->selectedPassengerItems
            : $this->booking->getActivePassengers()->pluck('item_number')->toArray();

        $policy = $this->booking->validatePassengerPartyPolicy($selectedItems, 'rebook');
        if (! $policy['valid']) {
            $this->feedback = $policy['error'];
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
        $this->feedback = "Please select your new travel dates, preferred schedule, and accommodation below. Kindly note that you may only choose an accommodation or transport class that is equal to or higher in value than your original booking; downgrades are not permitted.";
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

        // Original per-pax: base fare + transport class/accommodation
        $this->booking->loadMissing('transportClasses');
        $transportClasses = $this->booking->transportClasses;
        $origDepTCPerPax = (float)optional($transportClasses->values()->get(0))->pivot?->price;
        $accPrice = (float)($this->booking->schedule_accommodation_price ?? 0);
        $classPrice = ($mode === 'airline') ? $origDepTCPerPax : ($accPrice > 0 ? $accPrice : $origDepTCPerPax);
        
        $originalPerPax = (float)($this->booking->schedule_price ?? 0) + $classPrice;

        if ($mode === 'airline') {
            $newPerPax = $price;
            if ($newPerPax < $originalPerPax) {
                $this->feedback = "Reminder: To proceed with rebooking, please select an accommodation or transport class that is equal to or higher than your original booking. Downgrades are not permitted.";
                return;
            }
        } else {
            // For ferries, price = accommodation_price per pax (already combined in UI with schedule)
            $newPerPax = ($this->rebooking_dep_schedule_price ?? 0) + $price;
            if ($newPerPax < $originalPerPax) {
                $this->feedback = "Reminder: To proceed with rebooking, please select an accommodation or transport class that is equal to or higher than your original booking. Downgrades are not permitted.";
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

        // Original per-pax return: base fare + transport class/accommodation
        $this->booking->loadMissing('transportClasses');
        $transportClasses = $this->booking->transportClasses;
        $origRetTCPerPax = (float)optional($transportClasses->values()->get(1))->pivot?->price;
        $retAccPrice = (float)($this->booking->return_schedule_accommodation_price ?? 0);
        $classPrice = ($mode === 'airline') ? $origRetTCPerPax : ($retAccPrice > 0 ? $retAccPrice : $origRetTCPerPax);
        
        $originalPerPax = (float)($this->booking->return_schedule_price ?? 0) + $classPrice;

        if ($mode === 'airline') {
            $newPerPax = $price;
            if ($newPerPax < $originalPerPax) {
                $this->feedback = "Reminder: To proceed with rebooking, please select an accommodation or transport class that is equal to or higher than your original booking. Downgrades are not permitted.";
                return;
            }
        } else {
            $newPerPax = ($this->rebooking_ret_schedule_price ?? 0) + $price;
            if ($newPerPax < $originalPerPax) {
                $this->feedback = "Reminder: To proceed with rebooking, please select an accommodation or transport class that is equal to or higher than your original booking. Downgrades are not permitted.";
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
        $operator = $this->booking->getOperatorName();
        $schedules = Schedule::forRouteAndDate($this->booking->origin, $this->booking->destination, $this->rebooking_departure_date, $this->booking->getMode(), $operator)
            ->with(['ferryRoute.operatorRecord', 'vehicle', 'scheduleAccommodations', 'transportClasses'])
            ->get()
            ->filter(fn ($sch) => $this->booking->matchesOperator($sch, false));

        $isAirline = $this->booking->getMode() === 'airline';
        $this->booking->loadMissing('transportClasses');
        $origTCPerPax = (float) optional($this->booking->transportClasses->values()->get(0))->pivot?->price;
        $originalPerPax = (float)($this->booking->schedule_price ?? 0) + $origTCPerPax + (float)($this->booking->schedule_accommodation_price ?? 0);

        return $schedules->filter(function($schedule) use ($isAirline, $originalPerPax) {
            if ($schedule->scheduleAccommodations->isEmpty() && $schedule->transportClasses->isEmpty()) {
                $newPerPax = (float)($schedule->price ?? 0);
                return $newPerPax >= $originalPerPax;
            }

            foreach ($schedule->scheduleAccommodations->where('is_active', true) as $acc) {
                $price = (float)$acc->price;
                $newPerPax = $isAirline ? ((($schedule->price ?? 0) + $price) * 1.5) : (($schedule->price ?? 0) + $price);
                if ($newPerPax >= $originalPerPax) return true;
            }

            foreach ($schedule->transportClasses->where('pivot.is_active', true) as $tc) {
                $price = (float)$tc->pivot->additional_price;
                $newPerPax = $isAirline ? ((($schedule->price ?? 0) + $price) * 1.5) : (($schedule->price ?? 0) + $price);
                if ($newPerPax >= $originalPerPax) return true;
            }

            return false;
        })->values();
    }

    public function getAvailableRebookingReturnSchedulesProperty()
    {
        if (!$this->booking || !$this->rebooking_return_date) return collect();
        $operator = $this->booking->getReturnOperatorName() ?: $this->booking->getOperatorName();
        $schedules = Schedule::forRouteAndDate($this->booking->destination, $this->booking->origin, $this->rebooking_return_date, $this->booking->getMode(), $operator)
            ->with(['ferryRoute.operatorRecord', 'vehicle', 'scheduleAccommodations', 'transportClasses'])
            ->get()
            ->filter(fn ($sch) => $this->booking->matchesOperator($sch, true));

        $isAirline = $this->booking->getMode() === 'airline';
        $this->booking->loadMissing('transportClasses');
        $origTCPerPax = (float) optional($this->booking->transportClasses->values()->get(1))->pivot?->price;
        $originalPerPax = (float)($this->booking->return_schedule_price ?? 0) + $origTCPerPax + (float)($this->booking->return_schedule_accommodation_price ?? 0);

        return $schedules->filter(function($schedule) use ($isAirline, $originalPerPax) {
            if ($schedule->scheduleAccommodations->isEmpty() && $schedule->transportClasses->isEmpty()) {
                $newPerPax = (float)($schedule->price ?? 0);
                return $newPerPax >= $originalPerPax;
            }

            foreach ($schedule->scheduleAccommodations->where('is_active', true) as $acc) {
                $price = (float)$acc->price;
                $newPerPax = $isAirline ? ((($schedule->price ?? 0) + $price) * 1.5) : (($schedule->price ?? 0) + $price);
                if ($newPerPax >= $originalPerPax) return true;
            }

            foreach ($schedule->transportClasses->where('pivot.is_active', true) as $tc) {
                $price = (float)$tc->pivot->additional_price;
                $newPerPax = $isAirline ? ((($schedule->price ?? 0) + $price) * 1.5) : (($schedule->price ?? 0) + $price);
                if ($newPerPax >= $originalPerPax) return true;
            }

            return false;
        })->values();
    }

    public function getRebookingDepartureAccommodationsProperty()
    {
        if (!$this->rebooking_dep_schedule_id) return collect();
        $schedule = Schedule::with(['scheduleAccommodations', 'transportClasses'])->find($this->rebooking_dep_schedule_id);
        if (!$schedule) return collect();

        $isAirline    = $this->booking && $this->booking->getMode() === 'airline';
        $schedulePrice = (float) ($schedule->price ?? 0);

        // Compute the original per-pax minimum
        $this->booking->loadMissing('transportClasses');
        $origTCPerPax = (float)optional($this->booking->transportClasses->values()->get(0))->pivot?->price;
        $accPrice = (float)($this->booking->schedule_accommodation_price ?? 0);
        $classPrice = $isAirline ? $origTCPerPax : ($accPrice > 0 ? $accPrice : $origTCPerPax);
        
        $originalPerPax = (float)($this->booking->schedule_price ?? 0) + $classPrice;

        $items = collect();

        if (!$isAirline) {
            foreach ($schedule->scheduleAccommodations->where('is_active', true)->sortBy('sort_order') as $acc) {
                $price = (float)$acc->price;
                $newPerPax = (($this->rebooking_dep_schedule_price ?? 0) + $price);
                $items->push((object)[
                    'id'       => 'acc_' . $acc->id,
                    'name'     => $acc->name,
                    'description' => $acc->description,
                    'price'    => $price,
                    'disabled' => $newPerPax < $originalPerPax,
                ]);
            }
        }
        
        if ($isAirline || $items->isEmpty()) {
            foreach ($schedule->transportClasses->where('pivot.is_active', true)->sortBy('pivot.sort_order') as $tc) {
                $price = (float)$tc->pivot->additional_price;
                $newPerPax = $isAirline ? (($schedulePrice + $price) * 1.5) : (($this->rebooking_dep_schedule_price ?? 0) + $price);
                $displayPrice = $isAirline ? $newPerPax : $price;
                $items->push((object)[
                    'id'       => 'tc_' . $tc->id,
                    'name'     => $tc->name,
                    'description' => $tc->pivot->description ?? $tc->description,
                    'price'    => $displayPrice,
                    'disabled' => $newPerPax < $originalPerPax,
                ]);
            }
        }
        return $items;
    }

    public function getRebookingReturnAccommodationsProperty()
    {
        if (!$this->rebooking_ret_schedule_id) return collect();
        $schedule = Schedule::with(['scheduleAccommodations', 'transportClasses'])->find($this->rebooking_ret_schedule_id);
        if (!$schedule) return collect();

        $isAirline    = $this->booking && $this->booking->getMode() === 'airline';
        $schedulePrice = (float) ($schedule->price ?? 0);

        // Compute the original per-pax minimum for the return leg
        $this->booking->loadMissing('transportClasses');
        $origTCPerPax = (float)optional($this->booking->transportClasses->values()->get(1))->pivot?->price;
        $accPrice = (float)($this->booking->return_schedule_accommodation_price ?? 0);
        $classPrice = $isAirline ? $origTCPerPax : ($accPrice > 0 ? $accPrice : $origTCPerPax);
        
        $originalPerPax = (float)($this->booking->return_schedule_price ?? 0) + $classPrice;

        $items = collect();

        if (!$isAirline) {
            foreach ($schedule->scheduleAccommodations->where('is_active', true)->sortBy('sort_order') as $acc) {
                $price = (float)$acc->price;
                $newPerPax = (($this->rebooking_ret_schedule_price ?? 0) + $price);
                $items->push((object)[
                    'id'       => 'acc_' . $acc->id,
                    'name'     => $acc->name,
                    'description' => $acc->description,
                    'price'    => $price,
                    'disabled' => $newPerPax < $originalPerPax,
                ]);
            }
        }
        
        if ($isAirline || $items->isEmpty()) {
            foreach ($schedule->transportClasses->where('pivot.is_active', true)->sortBy('pivot.sort_order') as $tc) {
                $price = (float)$tc->pivot->additional_price;
                $newPerPax = $isAirline ? (($schedulePrice + $price) * 1.5) : (($this->rebooking_ret_schedule_price ?? 0) + $price);
                $displayPrice = $isAirline ? $newPerPax : $price;
                $items->push((object)[
                    'id'       => 'tc_' . $tc->id,
                    'name'     => $tc->name,
                    'description' => $tc->pivot->description ?? $tc->description,
                    'price'    => $displayPrice,
                    'disabled' => $newPerPax < $originalPerPax,
                ]);
            }
        }
        return $items;
    }

    public function calculateRebookingPriceDiff(): void
    {
        try {
            $selectedItems = !empty($this->selectedPassengerItems)
                ? $this->selectedPassengerItems
                : $this->booking->passengers->whereNotIn('status', ['cancelled', 'operator_cancelled', 'refunded'])->pluck('item_number')->toArray();

            $depAccId = null;
            if ($this->rebooking_dep_accommodation_id) {
                $depAccId = (int) str_replace(['acc_', 'tc_'], '', (string) $this->rebooking_dep_accommodation_id);
            }
            $retAccId = null;
            if ($this->rebooking_ret_accommodation_id) {
                $retAccId = (int) str_replace(['acc_', 'tc_'], '', (string) $this->rebooking_ret_accommodation_id);
            }

            $calc = $this->booking->getPartialRebookingCalculation(
                $selectedItems,
                $this->rebooking_dep_schedule_id,
                $depAccId,
                $this->rebooking_ret_schedule_id,
                $retAccId
            );

            $this->rebooking_original_fare = (float) $calc['original_fare'];
            $this->rebooking_new_total = (float) $calc['new_fare'];
            $this->rebooking_revalidation_fee = (float) $calc['revalidation_fee'];
            $this->rebooking_surcharge = (float) $calc['surcharge'];
            $this->rebooking_rate_diff = (float) $calc['rate_diff'];
            $this->rebooking_price_diff = (float) $calc['rate_diff'];
            $this->rebooking_total_to_pay = (float) $calc['total_rebooking_fee'];
            $this->rebooking_passengers_breakdown = $calc['passengers_breakdown'] ?? [];
        } catch (\Exception $e) {
            $this->feedback = "Error in calculateRebookingPriceDiff: " . $e->getMessage();
        }
    }


    public function submitRebookingProof(): void
    {
        $this->validate([
            'rebooking_reference_number' => 'required|string|max:120',
            'rebookingProof' => 'required|image|max:10240',
        ]);

        if ($this->rebooking_dep_schedule_id) {
            $depSch = Schedule::with('ferryRoute.operatorRecord')->find($this->rebooking_dep_schedule_id);
            if ($depSch && ! $this->booking->matchesOperator($depSch, false)) {
                $this->feedback = "Rebooking is only permitted with the same operator (" . ($this->booking->getOperatorName() ?? 'original operator') . ").";
                return;
            }
        }
        if ($this->rebooking_ret_schedule_id) {
            $retSch = Schedule::with('ferryRoute.operatorRecord')->find($this->rebooking_ret_schedule_id);
            if ($retSch && ! $this->booking->matchesOperator($retSch, true)) {
                $this->feedback = "Return rebooking is only permitted with the same operator (" . ($this->booking->getReturnOperatorName() ?: $this->booking->getOperatorName()) . ").";
                return;
            }
        }

        $this->isUploadingRebooking = true;

        $extension = $this->rebookingProof->extension();
        $safeReference = preg_replace('/[^A-Za-z0-9_-]/', '', $this->rebooking_reference_number ?? uniqid());
        $filename = 'rebook_proof_' . $this->booking->transaction_number . '_' . $safeReference . '.' . $extension;
        $path = $this->rebookingProof->storeAs('rebooking_proofs', $filename, 'public');

        $this->booking->transaction->update([
            'rebooking_fee' => $this->rebooking_total_to_pay,
            'rebooking_proof_of_payment' => $path,
            'payment_reference' => $this->rebooking_reference_number,
        ]);

        $selectedItems = !empty($this->selectedPassengerItems)
            ? $this->selectedPassengerItems
            : $this->booking->getActivePassengers()->pluck('item_number')->toArray();

        $policy = $this->booking->validatePassengerPartyPolicy($selectedItems, 'rebook');
        if (! $policy['valid']) {
            $this->isUploadingRebooking = false;
            $this->feedback = $policy['error'];
            return;
        }

        $newlyCreatedItems = [];
        $currentPassengers = $this->booking->passengers->values();

        foreach ($currentPassengers as $p) {
            if (in_array((int) $p->item_number, array_map('intval', $selectedItems), true) && $p->isActiveBookingItem()) {
                $oldItemNumber = $p->item_number;

                // 1. Mark original passenger item as rebooked/rescheduled (historical archive)
                $p->update([
                    'status'           => \App\Models\Passenger::STATUS_REBOOKED,
                    'is_rebooked'      => true,
                    'rebooking_status' => 'rescheduled',
                ]);

                // 2. Create the replacement passenger item (e.g. Item 3)
                $nextItemNumber = $this->booking->getNextItemNumber();
                $newPassenger = $p->replicate([
                    'ticket_pdf_path', 'verified_at', 'verified_by_user_id',
                ]);
                $newPassenger->item_number = $nextItemNumber;
                $newPassenger->ticket_number = null;
                $newPassenger->status = \App\Models\Passenger::STATUS_REBOOKING_PENDING;
                $newPassenger->is_rebooked = true;
                $newPassenger->rebooking_status = 'pending';
                $newPassenger->rebooking_departure_date = $this->rebooking_departure_date;
                $newPassenger->rebooking_return_date = $this->rebooking_is_round_trip ? $this->rebooking_return_date : null;
                $newPassenger->preferred_replacement_schedule_id = $this->rebooking_dep_schedule_id;
                $newPassenger->disruption_notes = json_encode([
                    'rebooked_from_item'   => $oldItemNumber,
                    'rebooked_from_id'     => $p->id,
                    'dep_schedule_id'      => $this->rebooking_dep_schedule_id,
                    'dep_accommodation_id' => $this->rebooking_dep_accommodation_id,
                    'ret_schedule_id'      => $this->rebooking_ret_schedule_id,
                    'ret_accommodation_id' => $this->rebooking_ret_accommodation_id,
                    'rate_diff'            => $this->rebooking_rate_diff,
                    'surcharge'            => $this->rebooking_surcharge,
                    'revalidation_fee'     => $this->rebooking_revalidation_fee,
                    'total_paid'           => $this->rebooking_total_to_pay,
                    'proof_path'           => $path,
                ]);
                $newPassenger->save();
                $newlyCreatedItems[] = $nextItemNumber;
            }
        }

        $allRebooked = $this->booking->passengers()->whereNotIn('status', ['cancelled', 'refunded'])->get()->every(fn ($p) => in_array($p->status, ['rebooking_pending', 'rebooked'], true));

        $affectedDisplayItems = !empty($newlyCreatedItems) ? $newlyCreatedItems : $selectedItems;

        $this->booking->update([
            'is_rebooked' => true,
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
                'affected_items' => $this->booking->getAffectedItemsLabel($affectedDisplayItems),
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
        $this->rebooking_reference_number = null;
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




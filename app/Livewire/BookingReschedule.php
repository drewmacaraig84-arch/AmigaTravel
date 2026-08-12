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

    public function mount(string $transaction_number): void
    {
        $this->transaction_number = ltrim(trim($transaction_number), '#');
        $this->loadBooking();

        if (! $this->booking || ! $this->booking->serviceCancellation) {
            abort(403, 'Unauthorized access. This page is only for bookings affected by service disruptions.');
        }

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
            'passengers',
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

        return Schedule::forRouteAndDate($this->booking->origin, $this->booking->destination, $this->dep_date)
            ->whereIn('id', function ($query) use ($cancellationId, $this) {
                $query->select('schedule_id')
                      ->from('service_cancellation_replacement_schedules')
                      ->where('service_cancellation_id', $cancellationId)
                      ->whereDate('replacement_date', $this->dep_date);
            })
            ->with(['ferryRoute', 'vehicle'])
            ->where('departure_time', '>', now())
            ->get();
    }

    public function getAvailableReturnSchedulesProperty()
    {
        if (!$this->booking || !$this->ret_date || !$this->booking->serviceCancellation) return collect();

        $cancellationId = $this->booking->serviceCancellation->id;

        return Schedule::forRouteAndDate($this->booking->destination, $this->booking->origin, $this->ret_date)
            ->whereIn('id', function ($query) use ($cancellationId, $this) {
                $query->select('schedule_id')
                      ->from('service_cancellation_replacement_schedules')
                      ->where('service_cancellation_id', $cancellationId)
                      ->whereDate('replacement_date', $this->ret_date);
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
        $schedulePrice = $isAirline ? ($schedule->price ?? 0) : 0;

        $items = collect();
        foreach ($schedule->scheduleAccommodations->where('is_active', true)->sortBy('sort_order') as $acc) {
            $price = $acc->price;
            if ($isAirline) {
                $price = ($schedulePrice + $price) * 1.5;
            }
            $items->push((object)[
                'id' => 'acc_' . $acc->id,
                'name' => $acc->name,
                'description' => $acc->description,
                'price' => $price,
            ]);
        }
        foreach ($schedule->transportClasses->where('pivot.is_active', true)->sortBy('pivot.sort_order') as $tc) {
            $price = $tc->pivot->additional_price;
            if ($isAirline) {
                $price = ($schedulePrice + $price) * 1.5;
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
        $schedulePrice = $isAirline ? ($schedule->price ?? 0) : 0;

        $items = collect();
        foreach ($schedule->scheduleAccommodations->where('is_active', true)->sortBy('sort_order') as $acc) {
            $price = $acc->price;
            if ($isAirline) {
                $price = ($schedulePrice + $price) * 1.5;
            }
            $items->push((object)[
                'id' => 'acc_' . $acc->id,
                'name' => $acc->name,
                'description' => $acc->description,
                'price' => $price,
            ]);
        }
        foreach ($schedule->transportClasses->where('pivot.is_active', true)->sortBy('pivot.sort_order') as $tc) {
            $price = $tc->pivot->additional_price;
            if ($isAirline) {
                $price = ($schedulePrice + $price) * 1.5;
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

        $settings = \App\Models\PaymentSetting::current();
        $mode = $this->booking->getMode();
        $isAfterDeparture = $this->booking->isAfterDeparture();
        $passengerCount = max(1, $this->booking->passengers()->count());

        $originalFare = $this->originalFare;
        $newFare = $this->newFare;

        if ($newFare < $originalFare) {
            $this->feedback = "You cannot rebook to a ticket that is cheaper than your original booking.";
            $this->rebookRateDiff = 0;
            $this->totalRebookFee = 0;
            $this->priceDiff = 0;
            return;
        }

        $this->rebookRevalidationFee = (floatval($settings->revalidation_fee) * $passengerCount);

        $surchargePct = 0;
        if ($mode === 'airline') {
            $surchargePct = (float) $settings->rebook_airline_before_departure_surcharge_pct;
        } else {
            if ($isAfterDeparture) {
                $surchargePct = (float) $settings->rebook_ferry_after_departure_surcharge_pct;
            } else {
                $surchargePct = (float) $settings->rebook_ferry_before_departure_surcharge_pct;
            }
        }
        $this->rebookSurcharge = $originalFare * ($surchargePct / 100);

        // 3. Rate Diff
        $this->rebookRateDiff = max(0, $newFare - $originalFare);

        $this->totalRebookFee = $this->rebookSurcharge + $this->rebookRevalidationFee + $this->rebookRateDiff;
        $this->priceDiff = $this->totalRebookFee;
    }

    public function getOriginalFareProperty(): float
    {
        if (! $this->booking) {
            return 0.0;
        }
        return $this->booking->getTicketBase();
    }

    public function getNewFareProperty(): float
    {
        $passengerCount = $this->booking->passengers()->count();
        if ($passengerCount === 0) {
            $passengerCount = 1;
        }

        $newFare = 0.0;
        $newFare += ($this->dep_schedule_price ?? 0) * $passengerCount;
        $newFare += $this->getSelectedAccommodationCost($this->dep_accommodation_id, $this->dep_accommodation_price, $passengerCount);

        if ($this->isRoundTrip()) {
            $newFare += ($this->ret_schedule_price ?? 0) * $passengerCount;
            $newFare += $this->getSelectedAccommodationCost($this->ret_accommodation_id, $this->ret_accommodation_price, $passengerCount);
        }

        $newFare += $this->booking->has_vehicle ? $this->booking->vehicle_price : 0;

        return $newFare;
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
            // Save proof if required
            $proofPath = null;
            if ($this->priceDiff > 0 && $this->paymentProof) {
                $extension = $this->paymentProof->extension();
                $filename = 'rebook_proof_' . $this->booking->transaction_number . '_' . uniqid() . '.' . $extension;
                $proofPath = $this->paymentProof->storeAs('proofs', $filename, 'public');
            }

            // Ideally, we'd have a method in ServiceCancellationManager to handle this custom free-pick reschedule + payment diff.
            // For now, we will update the booking record directly to reflect the custom selections, since the original method 
            // submitCustomerReschedule() expects a single seeded option.
            
            $this->booking->update([
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
                    'proof_path' => $proofPath
                ])
            ]);

            if ($proofPath && $this->booking->transaction) {
                $this->booking->transaction->update([
                    'rebooking_proof_of_payment' => $proofPath,
                ]);
            }

            $this->loadBooking();
            $this->submitted = true;
            $this->feedback = 'Your new travel dates and accommodations have been submitted successfully and are awaiting staff approval.';
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
        ];

        if (in_array($this->refund_method, ['Bank Account', 'Online Wallet'], true)) {
            $rules['refund_bank_name'] = 'required|string|max:100';
        }

        $this->validate($rules);

        $destinationParts = [];
        $destinationParts[] = "Method: " . $this->refund_method;
        if (in_array($this->refund_method, ['Bank Account', 'Online Wallet'], true)) {
            $destinationParts[] = "Institution: " . $this->refund_bank_name;
        }
        $destinationParts[] = "Account No: " . $this->refund_account_number;
        $destinationParts[] = "Name: " . $this->refund_account_name;
        
        $refundDestination = implode(' | ', $destinationParts);

        try {
            $this->booking->update([
                'status' => 'cancelled',
                'disruption_status' => 'refund_requested',
                'refund_destination' => $refundDestination,
                'refund_amount' => $this->booking->total_price, // 100% full refund
            ]);

            if ($this->booking->transaction) {
                $this->booking->transaction->update(['payment_status' => 'cancelled']);
            }

            $this->loadBooking();
            $this->closeRefundForm();
            $this->submitted = true;
            $this->feedback = 'Your booking has been cancelled and a full 100% refund has been requested. Our team will process it shortly to your provided account.';
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
        return (float) ($this->booking->schedule_accommodation_price ?? 0);
    }

    public function getOriginalRetAccommodationPrice(): ?float
    {
        if (! $this->booking) return null;
        return (float) ($this->booking->return_schedule_accommodation_price ?? 0);
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

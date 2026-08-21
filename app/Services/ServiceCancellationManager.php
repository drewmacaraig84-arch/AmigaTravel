<?php

namespace App\Services;

use App\Mail\RescheduleApprovalNotificationMail;
use App\Mail\ServiceCancellationNotificationMail;
use App\Models\AppNotification;
use App\Models\Booking;
use App\Models\Schedule;
use App\Models\ServiceCancellation;
use App\Models\ServiceCancellationReplacementSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ServiceCancellationManager
{
    /**
     * Preview affected schedules and bookings for a cancellation draft.
     */
    public function previewCancellation(array $data): array
    {
        $cancellation = new ServiceCancellation([
            'service_type' => $data['service_type'] ?? 'ferry',
            'carrier' => $data['carrier'] ?? null,
            'ferry_route_id' => $data['ferry_route_id'] ?? null,
            'vehicle_id' => $data['vehicle_id'] ?? null,
            'scope' => $data['scope'] ?? 'specific_schedule',
            'schedule_id' => $data['schedule_id'] ?? null,
            'affected_date' => $data['affected_date'] ?? null,
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
        ]);

        $affectedSchedules = $cancellation->getAffectedSchedulesQuery()->get();
        $affectedBookings = $cancellation->getAffectedBookingsQuery()->get();

        return [
            'schedules_count' => $affectedSchedules->count(),
            'bookings_count' => $affectedBookings->count(),
            'schedules' => $affectedSchedules,
            'bookings' => $affectedBookings,
        ];
    }

    /**
     * Finalize and store a service cancellation, mark bookings, seed replacements, and notify customers.
     */
    public function finalizeCancellation(array $data, User $adminUser): ServiceCancellation
    {
        return DB::transaction(function () use ($data, $adminUser) {
            $cancellation = ServiceCancellation::create([
                'cancellation_code' => ServiceCancellation::generateCode(),
                'service_type' => $data['service_type'],
                'carrier' => $data['carrier'] ?? null,
                'ferry_route_id' => $data['ferry_route_id'] ?? null,
                'vehicle_id' => $data['vehicle_id'] ?? null,
                'scope' => $data['scope'],
                'schedule_id' => $data['schedule_id'] ?? null,
                'affected_date' => $data['affected_date'] ?? null,
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'reason_category' => $data['reason_category'],
                'internal_notes' => $data['internal_notes'] ?? null,
                'customer_message' => $data['customer_message'],
                'resume_date' => $data['resume_date'],
                'status' => 'active',
                'created_by_user_id' => $adminUser->id,
            ]);

            // Update affected bookings
            $affectedBookings = $cancellation->getAffectedBookingsQuery()->get();

            foreach ($affectedBookings as $booking) {
                $booking->update([
                    'status' => 'operator_cancelled',
                    'service_cancellation_id' => $cancellation->id,
                    'disruption_status' => 'cancelled_by_operator_rescheduling_required',
                    'rebooking_status' => 'rebooking_required',
                ]);

                // Preserve paid or pending status if already paid/submitted by customer
                if ($booking->transaction && !in_array($booking->transaction->payment_status, ['paid', 'pending'])) {
                    $booking->transaction->update([
                        'payment_status' => 'cancelled',
                    ]);
                }
            }

            // Seed default replacement options on/after resume_date if provided
            if (! empty($cancellation->resume_date)) {
                $this->seedDefaultReplacements($cancellation);
            }

            // Notify customers without duplicate triggers
            $this->notifyAffectedBookers($cancellation, $affectedBookings, false);

            return $cancellation;
        });
    }

    /**
     * Declare official service resume date when travel clearance is granted, seed replacements, and notify customers.
     */
    public function declareResumeDate(ServiceCancellation $cancellation, string $resumeDate): void
    {
        DB::transaction(function () use ($cancellation, $resumeDate) {
            $cancellation->update([
                'resume_date' => $resumeDate,
                'resumed_at'  => now(),
                'status'      => 'active',
            ]);

            // Seed replacement schedules starting on/after the declared resume date
            $this->seedDefaultReplacements($cancellation);

            // Re-notify affected bookers with the official resume date and reschedule link
            $affectedBookings = $cancellation->affectedBookings;
            $this->notifyAffectedBookers($cancellation, $affectedBookings, true);
        });
    }

    /**
     * Seed initial eligible replacement schedules matching origin, destination, and operator on/after resume_date.
     */
    public function seedDefaultReplacements(ServiceCancellation $cancellation): void
    {
        if (empty($cancellation->resume_date)) {
            return;
        }

        $affectedSchedules = $cancellation->getAffectedSchedulesQuery()->get();
        $resumeDate = Carbon::parse($cancellation->resume_date);

        // Find candidate dates starting from resume_date (e.g. next 14 days)
        foreach ($affectedSchedules as $affectedSchedule) {
            if (! $affectedSchedule->ferryRoute) {
                continue;
            }

            $route = $affectedSchedule->ferryRoute;

            for ($i = 0; $i < 14; $i++) {
                $date = $resumeDate->copy()->addDays($i)->format('Y-m-d');

                // Find matching active schedules for this route on that date
                $matchingSchedules = Schedule::query()
                    ->active()
                    ->where('ferry_route_id', $route->id)
                    ->whereDate('departure_time', $date)
                    ->get();

                foreach ($matchingSchedules as $match) {
                    ServiceCancellationReplacementSchedule::firstOrCreate([
                        'service_cancellation_id' => $cancellation->id,
                        'schedule_id' => $match->id,
                        'replacement_date' => $date,
                    ]);
                }
            }
        }
    }

    /**
     * Send email, push, and internal notifications to unique bookers.
     */
    public function notifyAffectedBookers(ServiceCancellation $cancellation, Collection $bookings, bool $isResumption = false): void
    {
        $uniqueBookings = $bookings->unique('id');

        foreach ($uniqueBookings as $booking) {
            \App\Jobs\NotifyAffectedBookerJob::dispatch($booking, $cancellation, $isResumption);
        }
    }

    /**
     * Process customer submitting a preferred replacement schedule/date.
     */
    public function submitCustomerReschedule(Booking $booking, int $replacementScheduleId, string $date): bool
    {
        if ($booking->disruption_status === 'rescheduled_approved') {
            throw new \InvalidArgumentException('This booking has already been rescheduled and approved.');
        }

        $cancellation = $booking->serviceCancellation;
        if (! $cancellation) {
            throw new \InvalidArgumentException('No active disruption cancellation record associated with this booking.');
        }

        $resumeDate = Carbon::parse($cancellation->resume_date)->format('Y-m-d');
        if ($date < $resumeDate) {
            throw new \InvalidArgumentException("Replacement date must be on or after the service resume date ({$cancellation->resume_date->format('M d, Y')}).");
        }

        // Verify replacement schedule is in staff-approved list for this cancellation
        $isEligible = ServiceCancellationReplacementSchedule::query()
            ->where('service_cancellation_id', $cancellation->id)
            ->where('schedule_id', $replacementScheduleId)
            ->whereDate('replacement_date', $date)
            ->exists();

        if (! $isEligible) {
            throw new \InvalidArgumentException('The selected replacement date and schedule is not eligible for this cancellation.');
        }

        $replacementSchedule = Schedule::findOrFail($replacementScheduleId);

        $booking->update([
            'preferred_replacement_schedule_id' => $replacementSchedule->id,
            'preferred_replacement_date' => $date,
            'disruption_status' => 'reschedule_requested',
            'rebooking_status' => 'reschedule_requested',
        ]);

        return true;
    }

    /**
     * Process staff approval or decline of customer reschedule request.
     * Supports two data sources:
     *  1. preferred_replacement_schedule_id (legacy single-schedule path)
     *  2. disruption_notes JSON (wizard free-pick path from BookingReschedule)
     */
    public function processStaffApproval(Booking $booking, bool $approve, ?string $staffNote, User $staffUser): void
    {
        if ($approve) {
            // --- Detect data source ---
            $wizardData = null;
            if (! $booking->preferred_replacement_schedule_id && filled($booking->disruption_notes)) {
                $decoded = json_decode($booking->disruption_notes, true);
                if (is_array($decoded) && isset($decoded['dep_schedule_id'])) {
                    $wizardData = $decoded;
                }
            }

            if (! $wizardData && (! $booking->preferred_replacement_schedule_id || ! $booking->preferred_replacement_date)) {
                throw new \InvalidArgumentException('No preferred replacement schedule is selected for this booking.');
            }

            // ---- PATH A: Wizard free-pick (disruption_notes JSON) ----
            if ($wizardData) {
                $depSchedule = Schedule::find($wizardData['dep_schedule_id']);
                if (! $depSchedule) {
                    throw new \InvalidArgumentException('The departure schedule selected by the customer no longer exists.');
                }

                $retSchedule = isset($wizardData['ret_schedule_id']) ? Schedule::find($wizardData['ret_schedule_id']) : null;

                // Resolve accommodation details
                $depAccommodation = null;
                $depAccPrice = 0;
                if (isset($wizardData['dep_accommodation_id'])) {
                    $depAccommodation = $this->resolveAccommodation($wizardData['dep_accommodation_id']);
                    $depAccPrice = $depAccommodation?->price ?? 0;
                }

                $retAccommodation = null;
                $retAccPrice = 0;
                if (isset($wizardData['ret_accommodation_id'])) {
                    $retAccommodation = $this->resolveAccommodation($wizardData['ret_accommodation_id']);
                    $retAccPrice = $retAccommodation?->price ?? 0;
                }

                $newDepartureDate = Carbon::parse($booking->rebooking_departure_date ?? $booking->preferred_replacement_date);
                $newReturnDate = $booking->rebooking_return_date
                    ? Carbon::parse($booking->rebooking_return_date)
                    : null;

                DB::transaction(function () use (
                    $booking, $depSchedule, $retSchedule,
                    $depAccommodation, $depAccPrice,
                    $retAccommodation, $retAccPrice,
                    $newDepartureDate, $newReturnDate,
                    $staffNote, $staffUser, $wizardData
                ) {
                    $passengerCount = max(1, $booking->passengers()->count());
                    $newTotal = ($depSchedule->price + $depAccPrice) * $passengerCount;
                    if ($retSchedule) {
                        $newTotal += ($retSchedule->price + $retAccPrice) * $passengerCount;
                    }
                    if ($booking->has_vehicle) {
                        $newTotal += $booking->vehicle_price;
                    }

                    $booking->update([
                        // Departure leg
                        'schedule_id'                     => $depSchedule->id,
                        'schedule_service'                => $depSchedule->service_name,
                        'schedule_departure_time'         => $depSchedule->formatted_departure,
                        'schedule_arrival_time'           => $depSchedule->formatted_arrival,
                        'schedule_price'                  => $depSchedule->price,
                        'schedule_accommodation_id'       => $depAccommodation?->id,
                        'schedule_accommodation_name'     => $depAccommodation?->name,
                        'schedule_accommodation_price'    => $depAccPrice,
                        'departure_date'                  => $newDepartureDate,
                        // Return leg
                        'return_schedule_id'              => $retSchedule?->id,
                        'return_schedule_service'         => $retSchedule?->service_name,
                        'return_schedule_departure_time'  => $retSchedule?->formatted_departure,
                        'return_schedule_arrival_time'    => $retSchedule?->formatted_arrival,
                        'return_schedule_price'           => $retSchedule?->price,
                        'return_schedule_accommodation_id'    => $retAccommodation?->id,
                        'return_schedule_accommodation_name'  => $retAccommodation?->name,
                        'return_schedule_accommodation_price' => $retAccPrice,
                        'return_date'                     => $newReturnDate ?? $booking->return_date,
                        // Totals & status
                        'total_price'            => $newTotal,
                        'status'                 => 'confirmed',
                        'disruption_status'      => 'rescheduled_approved',
                        'disruption_notes'       => $staffNote,
                        'is_rebooked'            => true,
                        'rebooking_status'       => 'verified',
                        'verified_by_user_id'    => $staffUser->id,
                        'verified_at'            => now(),
                    ]);

                    if ($booking->transaction) {
                        $booking->transaction->update([
                            'payment_status'      => 'paid',
                            'verified_by_user_id' => $staffUser->id,
                            'verified_at'         => now(),
                        ]);
                    }

                    foreach ($booking->passengers as $p) {
                        if ($p->is_rebooked || in_array($p->status, ['operator_rebooking', 'rebooking_pending'], true) || $p->rebooking_status === 'reschedule_requested') {
                            $p->update([
                                'status'           => 'confirmed',
                                'is_rebooked'      => true,
                                'rebooking_status' => 'verified',
                            ]);
                        }
                    }
                });

            // ---- PATH B: Legacy preferred_replacement_schedule_id ----
            } else {
                $newSchedule = Schedule::findOrFail($booking->preferred_replacement_schedule_id);
                $newDepartureDate = Carbon::parse($booking->preferred_replacement_date);

                $newReturnDate = null;
                if ($booking->return_date && $booking->departure_date) {
                    $dayOffset = $booking->departure_date->diffInDays($booking->return_date);
                    $newReturnDate = $newDepartureDate->copy()->addDays($dayOffset);
                }

                DB::transaction(function () use ($booking, $newSchedule, $newDepartureDate, $newReturnDate, $staffNote, $staffUser) {
                    $booking->update([
                        'schedule_id'            => $newSchedule->id,
                        'schedule_service'       => $newSchedule->service_name,
                        'schedule_departure_time'=> $newSchedule->formatted_departure,
                        'schedule_arrival_time'  => $newSchedule->formatted_arrival,
                        'departure_date'         => $newDepartureDate,
                        'return_date'            => $newReturnDate ?? $booking->return_date,
                        'status'                 => 'confirmed',
                        'disruption_status'      => 'rescheduled_approved',
                        'disruption_notes'       => $staffNote,
                        'is_rebooked'            => true,
                        'rebooking_status'       => 'verified',
                        'verified_by_user_id'    => $staffUser->id,
                        'verified_at'            => now(),
                    ]);

                    if ($booking->transaction) {
                        $booking->transaction->update([
                            'payment_status'      => 'paid',
                            'verified_by_user_id' => $staffUser->id,
                            'verified_at'         => now(),
                        ]);
                    }

                    foreach ($booking->passengers as $p) {
                        if ($p->is_rebooked || in_array($p->status, ['operator_rebooking', 'rebooking_pending'], true) || $p->rebooking_status === 'reschedule_requested') {
                            $p->update([
                                'status'           => 'confirmed',
                                'is_rebooked'      => true,
                                'rebooking_status' => 'verified',
                            ]);
                        }
                    }
                });
            }

            // Send approval confirmation email (both paths)
            if (filled($booking->client_email)) {
                try {
                    Mail::to($booking->client_email)->send(new RescheduleApprovalNotificationMail($booking, true, $staffNote));
                } catch (\Exception $e) {
                    Log::error("Failed sending reschedule approval mail to {$booking->client_email}: " . $e->getMessage());
                }
            }

        } else {
            // ---- DECLINE ----
            $booking->update([
                'disruption_status' => 'rescheduled_declined',
                'disruption_notes'  => $staffNote,
            ]);

            if (filled($booking->client_email)) {
                try {
                    Mail::to($booking->client_email)->send(new RescheduleApprovalNotificationMail($booking, false, $staffNote));
                } catch (\Exception $e) {
                    Log::error("Failed sending reschedule decline mail to {$booking->client_email}: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Resolve an accommodation object from a prefixed ID string.
     * Prefix 'acc_' = ScheduleAccommodation, 'tc_' = TransportClass pivot.
     */
    private function resolveAccommodation(string $prefixedId): ?object
    {
        if (str_starts_with($prefixedId, 'acc_')) {
            $id = (int) substr($prefixedId, 4);
            return \App\Models\ScheduleAccommodation::find($id);
        }

        if (str_starts_with($prefixedId, 'tc_')) {
            $id = (int) substr($prefixedId, 3);
            $tc = \App\Models\TransportClass::find($id);
            if ($tc) {
                return (object) [
                    'id'    => $prefixedId,
                    'name'  => $tc->name,
                    'price' => $tc->pivot?->additional_price ?? 0,
                ];
            }
        }

        return null;
    }

    /**
     * Automatically find matching schedule on the new date and approve rebooking.
     */
    public function processAutomaticRebookingApproval(Booking $booking, User $staffUser): void
    {
        if (! $booking->rebooking_departure_date) {
            throw new \InvalidArgumentException('No rebooking departure date was specified.');
        }

        $originalSchedule = Schedule::find($booking->schedule_id);
        if (! $originalSchedule) {
            throw new \InvalidArgumentException('Original schedule not found.');
        }

        $originalDepartureTimeStr = Carbon::parse($originalSchedule->departure_time)->format('H:i:s');
        
        $newSchedule = Schedule::query()
            ->active()
            ->where('ferry_route_id', $originalSchedule->ferry_route_id)
            ->whereDate('departure_time', $booking->rebooking_departure_date)
            ->whereTime('departure_time', $originalDepartureTimeStr)
            ->first();

        if (! $newSchedule) {
            throw new \Exception("Cannot automatically rebook: No matching schedule found on " . Carbon::parse($booking->rebooking_departure_date)->format('M d, Y') . " at " . Carbon::parse($originalSchedule->departure_time)->format('g:i A') . ".");
        }

        $newDepartureDate = Carbon::parse($booking->rebooking_departure_date);

        $newReturnDate = null;
        if ($booking->return_date && $booking->departure_date) {
            $dayOffset = $booking->departure_date->diffInDays($booking->return_date);
            $newReturnDate = $newDepartureDate->copy()->addDays($dayOffset);
        } elseif ($booking->rebooking_return_date) {
            $newReturnDate = Carbon::parse($booking->rebooking_return_date);
        }

        DB::transaction(function () use ($booking, $newSchedule, $newDepartureDate, $newReturnDate, $staffUser) {
            $booking->update([
                'schedule_id' => $newSchedule->id,
                'schedule_service' => $newSchedule->service_name,
                'schedule_departure_time' => $newSchedule->formatted_departure,
                'schedule_arrival_time' => $newSchedule->formatted_arrival,
                'departure_date' => $newDepartureDate,
                'return_date' => $newReturnDate ?? $booking->return_date,
                'status' => 'confirmed',
                'is_rebooked' => true,
                'rebooking_status' => 'verified',
                'verified_by_user_id' => $staffUser->id,
                'verified_at' => now(),
            ]);

            if ($booking->transaction) {
                $booking->transaction->update([
                    'verified_by_user_id' => $staffUser->id,
                    'verified_at' => now(),
                ]);
            }
        });

        if (filled($booking->client_email)) {
            try {
                $relPdfPath = $booking->transaction?->confirmation_pdf;
                $diskForPdf = $relPdfPath ? 'public' : null;
                Mail::to($booking->client_email)->send(new \App\Mail\BookingConfirmation($booking, $booking->transaction?->confirmation_url, $relPdfPath, $diskForPdf));
            } catch (\Exception $e) {
                Log::error("Failed sending rebooking confirmation mail to {$booking->client_email}: " . $e->getMessage());
            }
        }
    }
}

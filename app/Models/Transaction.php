<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class Transaction extends Model
{
    protected $fillable = [
        'booking_id',
        'payment_status',
        'payment_deadline_at',
        'payment_reference',
        'proof_of_payment',
        'proof_submitted_at',
        'confirmation_url',
        'confirmation_pdf',
        'rebooking_fee',
        'rebooking_proof_of_payment',
        'student_discount_proofs',
        'verified_by_user_id',
        'verified_at',
    ];

    protected $casts = [
        'rebooking_fee' => 'decimal:2',
        'student_discount_proofs' => 'array',
        'verified_at' => 'datetime',
        'payment_deadline_at' => 'datetime',
        'proof_submitted_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::updated(function ($transaction) {
            if ($transaction->isDirty('payment_status') && $transaction->payment_status === 'paid') {
                if ($transaction->booking && $transaction->booking->user_id) {
                    \App\Http\Controllers\Api\ReferralController::onBookingCompleted($transaction->booking->user_id);
                }
            }
        });
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function verifiedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    public function verificationUnlockAt(): ?Carbon
    {
        $base = $this->proof_submitted_at ?? $this->created_at;
        if (! $base) {
            return null;
        }

        return $base->copy()->addMinutes(5);
    }

    public function isVerificationLocked(): bool
    {
        $unlockTime = $this->verificationUnlockAt();

        return $this->payment_status === 'pending'
            && $unlockTime !== null
            && $unlockTime->isFuture();
    }

    public function verificationTimerLabel(): string
    {
        if ($this->payment_status !== 'pending') {
            return '—';
        }

        $unlockTime = $this->verificationUnlockAt();
        if (! $unlockTime || $unlockTime->isPast()) {
            return 'Ready';
        }

        $diff = now()->diff($unlockTime);
        $minutes = ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;

        return sprintf('%dm %ds', $minutes, $diff->s);
    }

    public function verificationTimerTooltip(): ?string
    {
        if ($this->payment_status !== 'pending') {
            return null;
        }

        $unlockTime = $this->verificationUnlockAt();
        if (! $unlockTime) {
            return null;
        }

        if ($unlockTime->isPast()) {
            return '5-minute cancellation window complete. Ready for verification.';
        }

        return 'Unlocks at ' . $unlockTime->format('h:i:s A');
    }

    public function getProofUrlAttribute(): ?string
    {
        if (! $this->proof_of_payment) {
            return null;
        }

        return storage_asset_path($this->proof_of_payment);
    }

    public function deleteProof(): void
    {
        if (! $this->proof_of_payment) {
            return;
        }

        Storage::disk('public')->delete($this->proof_of_payment);

        $this->update([
            'proof_of_payment' => null,
        ]);
    }

    public function storeStudentDiscountProofs(array $frontFiles, array $backFiles, array $passengerData = []): void
    {
        $proofEntries = is_array($this->student_discount_proofs) ? $this->student_discount_proofs : [];

        foreach (array_keys($frontFiles + $backFiles) as $index) {
            $frontFile = $frontFiles[$index] ?? null;
            $backFile = $backFiles[$index] ?? null;

            if (blank($frontFile) && blank($backFile)) {
                continue;
            }

            $entry = $proofEntries[$index] ?? [];
            $entry['passenger_name'] = $entry['passenger_name'] ?? data_get($passengerData, $index . '.name') ?? null;
            $entry['student_number'] = $entry['student_number'] ?? data_get($passengerData, $index . '.student_number') ?? null;
            $entry['discount_name'] = $entry['discount_name'] ?? data_get($passengerData, $index . '.discount_name') ?? null;

            if ($frontFile instanceof UploadedFile) {
                $entry['front'] = $frontFile->storeAs(
                    'student-discount-proofs/' . $this->getKey(),
                    'front-' . $index . '-' . md5(uniqid()) . '.' . $frontFile->getClientOriginalExtension(),
                    'public'
                );
            } elseif (is_string($frontFile) && filled($frontFile)) {
                $entry['front'] = $frontFile;
            }

            if ($backFile instanceof UploadedFile) {
                $entry['back'] = $backFile->storeAs(
                    'student-discount-proofs/' . $this->getKey(),
                    'back-' . $index . '-' . md5(uniqid()) . '.' . $backFile->getClientOriginalExtension(),
                    'public'
                );
            } elseif (is_string($backFile) && filled($backFile)) {
                $entry['back'] = $backFile;
            }

            $proofEntries[$index] = $entry;
        }

        $this->update([
            'student_discount_proofs' => array_values($proofEntries),
        ]);
    }
}

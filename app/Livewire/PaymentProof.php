<?php

namespace App\Livewire;

use App\Models\Transaction;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;
use Throwable;

class PaymentProof extends Component
{
    use WithFileUploads;

    public Transaction $transaction;

    public string $reference_number = '';
    public $proof;

    public bool $showThankYou = false;
    public int $uploadProgress = 0;
    public bool $isUploading = false;

    /** Unix timestamp of when the payment window expires (for JS countdown) */
    public int $deadlineTimestamp = 0;

    /** Whether the booking is already cancelled due to timeout */
    public bool $isExpired = false;

    protected $rules = [
        'reference_number' => 'required|string',
        'proof' => 'required|image|max:10240',
    ];

    public function mount(): void
    {
        $this->showThankYou = filled($this->transaction->proof_of_payment);

        // Check if already cancelled
        $this->isExpired = $this->transaction->booking->status === 'cancelled'
            && $this->transaction->payment_status === 'cancelled';

        // Set the payment deadline if not yet set and booking not already done
        if (! $this->showThankYou && ! $this->isExpired) {
            if (! $this->transaction->payment_deadline_at) {
                $deadline = now()->addHour();
                $this->transaction->update(['payment_deadline_at' => $deadline]);
                $this->transaction->refresh();
            }
        }

        $this->deadlineTimestamp = $this->transaction->payment_deadline_at
            ? $this->transaction->payment_deadline_at->timestamp
            : 0;
    }

    public function updatedProof(): void
    {
        $this->isUploading = false;
        $this->uploadProgress = 0;
    }

    // Livewire will automatically update $uploadProgress when using file uploads!

    public function submitProof(): void
    {
        // Guard: don't allow submission if booking is cancelled/expired
        if ($this->isExpired) {
            $this->addError('reference_number', 'Your booking has been cancelled due to non-payment within the required time.');
            return;
        }

        // Re-check live deadline in case the scheduler ran between page load and submit
        $this->transaction->refresh();
        if (
            $this->transaction->payment_deadline_at
            && $this->transaction->payment_deadline_at->isPast()
            && ! filled($this->transaction->proof_of_payment)
            && $this->transaction->booking->status === 'cancelled'
        ) {
            $this->isExpired = true;
            $this->addError('reference_number', 'Your booking has been cancelled due to non-payment within the required time.');
            return;
        }

        $this->isUploading = true;
        $this->uploadProgress = 0;
        $this->validate();

        // Compress the image before storing it!
        $filePath = $this->proof->path();

        // If GD is not available on the host, skip compression and store the original file.
        if (! extension_loaded('gd')) {
            Log::warning('GD extension not available — skipping image compression and storing original proof file.', [
                'transaction_id' => $this->transaction->id ?? null,
            ]);
            $path = $this->proof->store('proofs', 'public');
        } else {
            $imageInfo = getimagesize($filePath);

            if ($imageInfo) {
                $mimeType = $imageInfo['mime'];

                // Create an image resource from the uploaded file only if the driver supports it.
                $image = null;

                if (in_array($mimeType, ['image/jpeg', 'image/jpg'], true) && function_exists('imagecreatefromjpeg')) {
                    $image = imagecreatefromjpeg($filePath);
                } elseif ($mimeType === 'image/png' && function_exists('imagecreatefrompng')) {
                    $image = imagecreatefrompng($filePath);
                } elseif ($mimeType === 'image/gif' && function_exists('imagecreatefromgif')) {
                    $image = imagecreatefromgif($filePath);
                } elseif ($mimeType === 'image/webp' && function_exists('imagecreatefromwebp')) {
                    $image = imagecreatefromwebp($filePath);
                }

                if ($image) {
                // Resize if too big (max width 1920px)
                $maxWidth = 1920;
                $originalWidth = $imageInfo[0];
                $originalHeight = $imageInfo[1];
                
                if ($originalWidth > $maxWidth) {
                    $newWidth = $maxWidth;
                    $newHeight = (int) round(($originalHeight / $originalWidth) * $newWidth);
                    $resized = imagecreatetruecolor($newWidth, $newHeight);
                    
                    // Preserve transparency for PNGs and GIFs
                    if (in_array($mimeType, ['image/png', 'image/gif'])) {
                        imagealphablending($resized, false);
                        imagesavealpha($resized, true);
                        $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
                        imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $transparent);
                    }
                    
                    imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
                    imagedestroy($image);
                    $image = $resized;
                }
                
                // Save compressed image to temp file
                $tempFile = tempnam(sys_get_temp_dir(), 'proof');
                
                switch ($mimeType) {
                    case 'image/jpeg':
                    case 'image/jpg':
                        imagejpeg($image, $tempFile, 70); // 70% quality
                        break;
                    case 'image/png':
                        imagepng($image, $tempFile, 6); // 6/9 compression
                        break;
                    case 'image/webp':
                        imagewebp($image, $tempFile, 70);
                        break;
                    case 'image/gif':
                        imagegif($image, $tempFile);
                        break;
                }
                
                imagedestroy($image);
                
                // Get file extension
                $extension = match ($mimeType) {
                    'image/jpeg', 'image/jpg' => 'jpg',
                    'image/png' => 'png',
                    'image/gif' => 'gif',
                    'image/webp' => 'webp',
                    default => $this->proof->extension(),
                };
                
                    // Generate new filename and store compressed image
                    $safeReference = preg_replace('/[^A-Za-z0-9_-]/', '', $this->reference_number);
                    $filename = $this->transaction->booking->transaction_number . '_' . $safeReference . '.' . $extension;
                    $path = \Illuminate\Support\Facades\Storage::disk('public')->putFileAs('proofs', new \Illuminate\Http\File($tempFile), $filename);

                    // Delete temp file
                    unlink($tempFile);
                } else {
                    // Fall back to original if compression failed
                    Log::warning('Image compression failed — storing original uploaded proof.', [
                        'transaction_id' => $this->transaction->id ?? null,
                        'file' => $this->proof->getClientOriginalName(),
                    ]);
                    $safeReference = preg_replace('/[^A-Za-z0-9_-]/', '', $this->reference_number);
                    $fallbackFilename = $this->transaction->booking->transaction_number . '_' . $safeReference . '.' . $this->proof->extension();
                    $path = $this->proof->storeAs('proofs', $fallbackFilename, 'public');
                }
            } else {
                // Fall back to original if not an image
                $safeReference = preg_replace('/[^A-Za-z0-9_-]/', '', $this->reference_number);
                $fallbackFilename = $this->transaction->booking->transaction_number . '_' . $safeReference . '.' . $this->proof->extension();
                $path = $this->proof->storeAs('proofs', $fallbackFilename, 'public');
            }
        }

        // Proof uploaded — update transaction with proof and set status to pending (under review).
        // Also clear the deadline so the countdown stops.
        $this->transaction->update([
            'proof_of_payment' => $path,
            'payment_status' => 'pending',
            'payment_reference' => $this->reference_number,
            'payment_deadline_at' => null, // Stop the timer
            'proof_submitted_at' => now(),
        ]);

        // Also ensure the booking status is not cancelled (it might have been caught
        // in the brief window between the deadline passing and this submit)
        if ($this->transaction->booking->status === 'cancelled') {
            $this->transaction->booking->update(['status' => 'pending']);
        }

        try {
            \Illuminate\Support\Facades\Mail::to($this->transaction->booking->client_email)
                ->queue(new \App\Mail\PaymentProofReceived($this->transaction));
        } catch (Throwable $e) {
            Log::error('Failed queueing payment proof received email', [
                'transaction_id' => $this->transaction->id ?? null,
                'booking_id' => $this->transaction->booking->id ?? null,
                'email' => $this->transaction->booking->client_email ?? null,
                'error' => $e->getMessage(),
            ]);
        }

        $this->transaction->refresh();
        session(['cancellation_window_expires_for_' . $this->transaction->booking->transaction_number => now()->addMinutes(5)->timestamp]);
        $this->isUploading = false;
        $this->showThankYou = true;
    }

    public function render()
    {
        return view('livewire.payment-proof');
    }
}

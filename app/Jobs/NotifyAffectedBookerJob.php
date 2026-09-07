<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Models\ServiceCancellation;
use App\Models\AppNotification;
use App\Mail\ServiceCancellationNotificationMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Kreait\Firebase\Contract\Messaging;

class NotifyAffectedBookerJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public array $backoff = [30, 60, 120];

    public function __construct(
        public readonly Booking $booking,
        public readonly ServiceCancellation $cancellation,
        public readonly bool $isResumption = false
    ) {}

    public function handle(): void
    {
        // Email notification
        if (filled($this->booking->client_email)) {
            try {
                Mail::to($this->booking->client_email)->send(new ServiceCancellationNotificationMail($this->booking, $this->cancellation, $this->isResumption));
            } catch (\Exception $e) {
                Log::error("Failed sending disruption cancellation email to {$this->booking->client_email}: " . $e->getMessage());
            }
        }

        // Mobile App Push Notification (FCM)
        try {
            if ($this->isResumption && ! empty($this->cancellation->resume_date)) {
                $title = "🟢 {$this->cancellation->carrier} Operations Resuming";
                $body = "Travel for Booking #{$this->booking->transaction_number} is resuming! Tap to choose your replacement travel date starting {$this->cancellation->resume_date->format('M d, Y')}.";
                $notifType = 'service_resumption';
            } elseif (! empty($this->cancellation->resume_date)) {
                $title = "🔴 {$this->cancellation->carrier} Disruption Notice";
                $body = "Booking #{$this->booking->transaction_number} was cancelled due to {$this->cancellation->reason_category}. Service resumes {$this->cancellation->resume_date->format('M d, Y')}. Tap to select a replacement date.";
                $notifType = 'service_cancellation';
            } else {
                $title = "🔴 {$this->cancellation->carrier} Disruption Notice";
                $body = "Booking #{$this->booking->transaction_number} was cancelled due to {$this->cancellation->reason_category}. Operations temporarily suspended.";
                $notifType = 'service_cancellation';
            }

            // Send user-specific FCM push to the affected user's devices
            if (filled($this->booking->client_email)) {
                $email = strtolower(trim($this->booking->client_email));
                $topics = [
                    'user_' . md5($email),
                    'user_' . preg_replace('/[^a-zA-Z0-9-_.~%+]/', '_', $email),
                ];
                if ($this->booking->user_id) {
                    $topics[] = 'user_' . $this->booking->user_id;
                }
                $topics = array_unique($topics);

                $messaging = app(Messaging::class);
                $notification = \Kreait\Firebase\Messaging\Notification::create($title, $body);
                $androidConfig = \Kreait\Firebase\Messaging\AndroidConfig::fromArray([
                    'notification' => [
                        'channel_id' => 'amiga_travel_alerts',
                    ],
                ]);

                foreach ($topics as $userTopic) {
                    try {
                        $message = \Kreait\Firebase\Messaging\CloudMessage::new()
                            ->withTopic($userTopic)
                            ->withNotification($notification)
                            ->withData(['type' => $notifType, 'target_id' => $this->booking->transaction_number])
                            ->withAndroidConfig($androidConfig);
                        $messaging->send($message);
                    } catch (\Throwable $te) {
                        Log::warning("FCM topic push failed for {$userTopic}: " . $te->getMessage());
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed creating push notification for disruption: " . $e->getMessage());
        }
    }
}

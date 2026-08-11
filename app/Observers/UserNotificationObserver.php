<?php

namespace App\Observers;

use App\Models\UserNotification;
use Kreait\Firebase\Contract\Messaging;

class UserNotificationObserver
{
    public function created(UserNotification $userNotification): void
    {
        try {
            $messaging = app(Messaging::class);

            $user = $userNotification->user;
            if (!$user) {
                return;
            }

            $userTopic = 'user_' . md5($user->email);

            $notification = \Kreait\Firebase\Messaging\Notification::create(
                $userNotification->title,
                $userNotification->body
            );

            $androidConfig = \Kreait\Firebase\Messaging\AndroidConfig::fromArray([
                'notification' => [
                    'channel_id' => 'high_importance_channel',
                ],
            ]);

            $message = \Kreait\Firebase\Messaging\CloudMessage::new()
                ->withTopic($userTopic)
                ->withNotification($notification)
                ->withData([
                    'type' => $userNotification->type ?? 'general',
                    'target_id' => $userNotification->data['target_id'] ?? $userNotification->data['transaction_number'] ?? '',
                ])
                ->withAndroidConfig($androidConfig);

            $messaging->send($message);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Firebase Push Failed for UserNotification: ' . $e->getMessage());
        }
    }
}

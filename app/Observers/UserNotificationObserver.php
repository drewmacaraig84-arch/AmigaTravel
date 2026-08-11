<?php

namespace App\Observers;

use App\Models\UserNotification;
use Kreait\Firebase\Contract\Messaging;

class UserNotificationObserver
{
    public function created(UserNotification $userNotification): void
    {
        // Firebase has been removed from the flutter app
        // Push notifications are no longer sent via FCM
    }
}

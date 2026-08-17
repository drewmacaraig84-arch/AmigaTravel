<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotification extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'body',
        'type',
        'icon',
        'is_read',
        'data',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'data'    => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Send a notification to a given user.
     */
    public static function notify(
        int $userId,
        string $title,
        string $body,
        string $type = 'general',
        string $icon = 'notifications',
        array $data = []
    ): self {
        $notification = self::create([
            'user_id' => $userId,
            'title'   => $title,
            'body'    => $body,
            'type'    => $type,
            'icon'    => $icon,
            'is_read' => false,
            'data'    => $data ?: null,
        ]);

        try {
            \App\Services\FirebasePushService::sendToUser(
                $userId,
                $title,
                $body,
                array_merge(['type' => $type, 'id' => (string) $notification->id], $data)
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to send FCM push for notification: ' . $e->getMessage());
        }

        return $notification;
    }
}

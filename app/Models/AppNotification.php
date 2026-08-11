<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kreait\Firebase\Contract\Messaging;

class AppNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'body',
        'image_path',
    ];

    protected static function booted()
    {
        static::created(function ($model) {
            try {
                $messaging = app(Messaging::class);

                $notification = \Kreait\Firebase\Messaging\Notification::create($model->title, $model->body);
                if ($model->image_path) {
                    $notification = $notification->withImageUrl(url('storage/' . $model->image_path));
                }

                $androidConfig = \Kreait\Firebase\Messaging\AndroidConfig::fromArray([
                    'notification' => [
                        'channel_id' => 'high_importance_channel',
                    ],
                ]);

                $message = \Kreait\Firebase\Messaging\CloudMessage::new()
                    ->withTopic('all_users')
                    ->withNotification($notification)
                    ->withData(['type' => 'announcement'])
                    ->withAndroidConfig($androidConfig);

                $messaging->send($message);
            } catch (\Exception $e) {
                // Log the error but don't fail the creation
                \Illuminate\Support\Facades\Log::error('FCM Error: ' . $e->getMessage());
            }
        });
    }
}

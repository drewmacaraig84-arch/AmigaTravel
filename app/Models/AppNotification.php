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
                \App\Services\FirebasePushService::sendToAll(
                    $model->title,
                    $model->body,
                    ['type' => 'announcement', 'id' => 'app_' . $model->id]
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('FCM broadcast failed for AppNotification: ' . $e->getMessage());
            }
        });
    }
}

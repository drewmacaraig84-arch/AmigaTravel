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
            // Firebase has been removed from the flutter app
            // Push notifications are no longer sent via FCM
        });
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
    ];

    protected static function booted(): void
    {
        static::saved(function () {
            \App\Support\AdminNotificationFeed::clearAllCache();
            \Illuminate\Support\Facades\Cache::put('admin_activity_version', microtime(true), now()->addHours(24));
        });

        static::deleted(function () {
            \App\Support\AdminNotificationFeed::clearAllCache();
            \Illuminate\Support\Facades\Cache::put('admin_activity_version', microtime(true), now()->addHours(24));
        });
    }
}

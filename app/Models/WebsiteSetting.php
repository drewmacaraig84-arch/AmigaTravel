<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteSetting extends Model
{
    protected $fillable = ['page', 'hero_images', 'content', 'booking_cards', 'header_data', 'footer_data', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'hero_images' => 'array',
        'content' => 'array',
        'booking_cards' => 'array',
        'header_data' => 'array',
        'footer_data' => 'array',
    ];

    const PAGES = [
        'header' => 'Header',
        'footer' => 'Footer',
        'home' => 'Home',
        'about' => 'About',
        'services' => 'Services',
        'contact_us' => 'Contact Us',
        'faqs' => 'FAQs',
        'referrals' => 'Referrals',
    ];

    const BOOKING_CARD_NAMES = [
        '2GO Ferry' => '2GO Ferry',
        'Starlite Ferry' => 'Starlite Ferry',
        'Air Asia' => 'Air Asia',
        'Cebu Pacific' => 'Cebu Pacific',
        'Philippine Airlines' => 'Philippine Airlines',
        'Travel With Us' => 'Travel With Us',
    ];

    protected static function booted()
    {
        $flushCache = function ($setting) {
            foreach (array_keys(self::PAGES) as $pageKey) {
                \Illuminate\Support\Facades\Cache::forget('website_settings:' . $pageKey);
                \Illuminate\Support\Facades\Cache::forget('website_settings:page:' . $pageKey);
            }
            \Illuminate\Support\Facades\Cache::forget('website_settings:header');
            \Illuminate\Support\Facades\Cache::forget('website_settings:footer');
            \Illuminate\Support\Facades\Cache::forget('website_settings:header_data');
            \Illuminate\Support\Facades\Cache::forget('website_settings:footer_data');
            \Illuminate\Support\Facades\Cache::forget('website_settings:page:home');
            \Illuminate\Support\Facades\Cache::forget('api:services');
            \Illuminate\Support\Facades\Cache::forget('api:promotions');
        };

        static::saved($flushCache);
        static::deleted($flushCache);
    }

    public static function getPageOptions()
    {
        return self::PAGES;
    }

    public static function getBookingCardNames()
    {
        return self::BOOKING_CARD_NAMES;
    }

    public static function getOrCreateByPage($page)
    {
        return self::firstOrCreate(['page' => $page], [
            'page' => $page,
            'is_active' => true,
        ]);
    }

    public static function isWebsiteVouchersEnabled(): bool
    {
        return \Illuminate\Support\Facades\Cache::remember('website_vouchers_enabled', 3600, function () {
            $setting = static::where('page', 'vouchers')->first();
            if (!$setting || !is_array($setting->content)) {
                return false;
            }
            return (bool) ($setting->content['enable_website_vouchers'] ?? false);
        });
    }

    public static function setWebsiteVouchersEnabled(bool $enabled): void
    {
        $setting = static::getOrCreateByPage('vouchers');
        $content = is_array($setting->content) ? $setting->content : [];
        $content['enable_website_vouchers'] = $enabled;
        $setting->content = $content;
        $setting->save();
        \Illuminate\Support\Facades\Cache::forget('website_vouchers_enabled');
    }

    public static function getAppMaintenanceSettings(): array
    {
        return \Illuminate\Support\Facades\Cache::remember('app_maintenance_settings', 60, function () {
            try {
                $setting = static::where('page', 'app_maintenance')->first();
            } catch (\Throwable) {
                $setting = null;
            }
            $content = is_array($setting?->content) ? $setting->content : [];

            $isActive = (bool) ($setting?->is_active ?? false);
            $durationValue = (int) ($content['duration_value'] ?? 45);
            $durationUnit = $content['duration_unit'] ?? 'minutes';
            $startsAt = $content['starts_at'] ?? null;
            $endsAt = $content['ends_at'] ?? null;

            $remainingSeconds = 0;
            if ($isActive && $endsAt) {
                $endCarbon = \Carbon\Carbon::parse($endsAt);
                $remainingSeconds = max(0, now()->diffInSeconds($endCarbon, false));
            }

            return [
                'is_active' => $isActive,
                'title' => $content['title'] ?? 'Scheduled System Maintenance',
                'message' => $content['message'] ?? 'We are currently performing system maintenance. Schedules, bookings, and ticket actions are temporarily paused. Thank you for your patience!',
                'duration_value' => $durationValue,
                'duration_unit' => $durationUnit,
                'estimated_duration' => $content['estimated_duration'] ?? ($durationValue . ' ' . $durationUnit),
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'remaining_seconds' => $remainingSeconds,
                'allow_browsing' => (bool) ($content['allow_browsing'] ?? true),
            ];
        });
    }

    public static function setAppMaintenanceSettings(array $data): void
    {
        $setting = static::getOrCreateByPage('app_maintenance');
        $setting->is_active = (bool) ($data['is_active'] ?? false);

        $durationValue = max(1, (int) ($data['duration_value'] ?? 45));
        $durationUnit = in_array($data['duration_unit'] ?? '', ['hours', 'minutes'], true) ? $data['duration_unit'] : 'minutes';

        $startsAt = ! empty($data['starts_at']) ? \Carbon\Carbon::parse($data['starts_at']) : now();
        $endsAt = $durationUnit === 'hours'
            ? $startsAt->copy()->addHours($durationValue)
            : $startsAt->copy()->addMinutes($durationValue);

        $content = [
            'title' => $data['title'] ?? 'Scheduled System Maintenance',
            'message' => $data['message'] ?? 'We are currently performing system maintenance. Schedules, bookings, and ticket actions are temporarily paused. Thank you for your patience!',
            'duration_value' => $durationValue,
            'duration_unit' => $durationUnit,
            'estimated_duration' => $durationValue . ' ' . ($durationValue === 1 ? rtrim($durationUnit, 's') : $durationUnit),
            'starts_at' => $startsAt->toDateTimeString(),
            'ends_at' => $endsAt->toDateTimeString(),
            'allow_browsing' => (bool) ($data['allow_browsing'] ?? true),
        ];

        $setting->content = $content;
        $setting->save();

        \Illuminate\Support\Facades\Cache::forget('app_maintenance_settings');
    }

    public static function isAppMaintenanceActive(): bool
    {
        $settings = static::getAppMaintenanceSettings();
        return (bool) ($settings['is_active'] ?? false);
    }
}

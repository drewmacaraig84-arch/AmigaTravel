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
                return true;
            }
            return (bool) ($setting->content['enable_website_vouchers'] ?? true);
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
}

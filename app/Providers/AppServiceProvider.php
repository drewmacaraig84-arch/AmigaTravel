<?php

namespace App\Providers;

use App\Console\Commands\PurgeExpiredSchedules;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Model;
use App\Models\WebsiteSetting;
use App\Observers\BookingObserver;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Mailer\Bridge\Sendgrid\Transport\SendgridTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                PurgeExpiredSchedules::class,
            ]);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! app()->isProduction());
        Schema::defaultStringLength(191);

        // Register model observers
        Booking::observe(BookingObserver::class);
        \App\Models\UserNotification::observe(\App\Observers\UserNotificationObserver::class);
        
        if (config('app.env') === 'production' || request()->server('HTTP_X_FORWARDED_PROTO') === 'https') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Cache header & footer settings — fetched on every single page load.
        // TTL: 1 hour. Cleared automatically when admin saves website settings.
        View::composer('layouts.app', function ($view) {
            $headerData = Cache::remember('website_settings:header_data', now()->addHour(), function () {
                try {
                    return WebsiteSetting::firstWhere('page', 'header')?->header_data ?? [];
                } catch (\Throwable $e) {
                    return [];
                }
            });
            $footerData = Cache::remember('website_settings:footer_data', now()->addHour(), function () {
                try {
                    return WebsiteSetting::firstWhere('page', 'footer')?->footer_data ?? [];
                } catch (\Throwable $e) {
                    return [];
                }
            });

            $view->with('headerData', $headerData);
            $view->with('footerData', $footerData);
        });

        Mail::extend('sendgrid', function (array $config) {
            return (new SendgridTransportFactory)->create(
                new Dsn(
                    'sendgrid+api',
                    'default',
                    $config['api_key'] ?? env('SENDGRID_API_KEY')
                )
            );
        });

        // Automatically bust cached settings whenever migrations are run
        // This prevents the cache from holding onto stale database schemas (e.g. after a git pull)
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Database\Events\MigrationsEnded::class, function () {
            try {
                \App\Models\PaymentSetting::bust();
                Cache::forget('website_settings:header_data');
                Cache::forget('website_settings:footer_data');
            } catch (\Throwable $e) {
                // Ignore if tables don't exist yet
            }
        });
    }
}

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
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;

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

        FilamentView::registerRenderHook(
            PanelsRenderHook::GLOBAL_SEARCH_BEFORE,
            fn (): string => Blade::render('
                <div class="fi-global-search flex items-center cursor-pointer" x-on:click="$dispatch(\'open-spotlight\')">
                    <div class="fi-global-search-field">
                        <label class="sr-only">Search</label>
                        <div class="fi-input-wrapper flex rounded-lg shadow-sm ring-1 transition duration-75 bg-white focus-within:ring-2 dark:bg-white/5 ring-gray-950/10 focus-within:ring-primary-600 dark:ring-white/20 dark:focus-within:ring-primary-500">
                            <div class="items-center gap-x-3 ps-3 flex pe-2">
                                <x-heroicon-m-magnifying-glass class="fi-input-wrapper-icon h-5 w-5 text-gray-400 dark:text-gray-500" />
                            </div>
                            <div class="min-w-0 flex-1 flex items-center py-1.5 px-3">
                                <span class="block w-full border-none text-base text-gray-400 transition duration-75 sm:text-sm sm:leading-6 bg-transparent ps-0 pe-3">Search</span>
                            </div>
                            <div class="items-center gap-x-3 pe-3 flex ps-2">
                                <kbd class="hidden sm:inline-flex items-center px-2 py-0.5 text-xs font-medium text-gray-500 bg-gray-100 rounded border border-gray-200 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400">Ctrl+K</kbd>
                            </div>
                        </div>
                    </div>
                </div>
            ')
        );

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

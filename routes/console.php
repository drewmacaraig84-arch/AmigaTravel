<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('proofs:purge')->daily();
Schedule::command('schedules:purge-expired')->hourly();
Schedule::command('payments:cancel-expired')->everyMinute();
Schedule::command('payments:send-reminders')->everyMinute();
Schedule::command('bookings:issue-sla-vouchers')->everyFiveMinutes();
Schedule::command('vouchers:notify-expiring')->dailyAt('09:00');
Schedule::command('app:cleanup-old-schedules')->daily();


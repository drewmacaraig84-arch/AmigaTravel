<?php

namespace App\Http\Middleware;

use App\Models\WebsiteSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAppMaintenanceBreak
{
    /**
     * Handle an incoming request.
     *
     * If the mobile app maintenance break is enabled in Admin settings,
     * block transactions, mutating actions, schedules, and bookings with an informative payload.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (WebsiteSetting::isAppMaintenanceActive()) {
            $settings = WebsiteSetting::getAppMaintenanceSettings();

            $title = $settings['title'] ?? 'Scheduled System Maintenance';
            $msg = $settings['message'] ?? 'We are currently performing scheduled maintenance to upgrade our system. Schedules, bookings, and ticket actions are temporarily paused. Thank you for your patience!';
            $duration = $settings['estimated_duration'] ?? '45 minutes';

            return response()->json([
                'status'             => 'maintenance',
                'error'              => 'maintenance',
                'message'            => "⚠️ {$title}: {$msg} (Est. duration: {$duration})",
                'maintenance'        => $settings,
            ], 503);
        }

        return $next($request);
    }
}

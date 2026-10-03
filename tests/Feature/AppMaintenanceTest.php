<?php

namespace Tests\Feature;

use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppMaintenanceTest extends TestCase
{
    use RefreshDatabase;
    public function test_app_version_returns_maintenance_payload(): void
    {
        $response = $this->getJson('/api/app-version');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'version',
            'force_update',
            'play_store_url',
            'app_store_url',
            'app_gallery_url',
            'maintenance' => [
                'is_active',
                'title',
                'message',
                'duration_value',
                'duration_unit',
                'estimated_duration',
                'allow_browsing',
            ],
        ]);
    }

    public function test_maintenance_middleware_blocks_schedules_and_bookings_when_active(): void
    {
        // Simulate active maintenance break
        WebsiteSetting::setAppMaintenanceSettings([
            'is_active' => true,
            'title' => 'Emergency Server Update',
            'message' => 'System maintenance is ongoing.',
            'duration_value' => 30,
            'duration_unit' => 'minutes',
            'allow_browsing' => true,
        ]);

        // 1. Browsing origins/services should still be accessible
        $originsRes = $this->getJson('/api/origins');
        $originsRes->assertStatus(200);

        // 2. /api/schedules must be blocked with 503 maintenance response
        $schedRes = $this->postJson('/api/schedules', [
            'origin' => 'Batangas',
            'destination' => 'Calapan',
            'date' => '2026-10-05',
        ]);
        $schedRes->assertStatus(503);
        $schedRes->assertJsonFragment([
            'status' => 'maintenance',
            'error' => 'maintenance',
        ]);

        // 3. /api/bookings must be blocked with 503
        $bookingRes = $this->getJson('/api/bookings?email=test@example.com');
        $bookingRes->assertStatus(503);
        $bookingRes->assertJsonFragment([
            'status' => 'maintenance',
        ]);

        // Reset maintenance
        WebsiteSetting::setAppMaintenanceSettings([
            'is_active' => false,
            'title' => 'Scheduled System Maintenance',
            'message' => 'Regular operations',
            'duration_value' => 45,
            'duration_unit' => 'minutes',
            'allow_browsing' => true,
        ]);
    }
}

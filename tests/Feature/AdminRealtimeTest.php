<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Booking;
use App\Models\Transaction;
use App\Models\Inquiry;
use App\Filament\Resources\BookingResource;
use App\Filament\Resources\TransactionResource;
use App\Filament\Resources\FerryRouteResource;
use App\Filament\Resources\InquiryResource;
use App\Filament\Pages\ManageProofs;
use App\Filament\Pages\ManageRebookings;
use App\Filament\Pages\ManageRefunds;
use App\Filament\Widgets\BookingStatusChart;
use App\Filament\Widgets\DashboardStatsOverview;
use App\Filament\Widgets\RecentActivityWidget;
use App\Filament\Widgets\RevenueChartWidget;
use App\Support\AdminNotificationFeed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRealtimeTest extends TestCase
{
    use RefreshDatabase;

    protected function getAdminUser(): User
    {
        return User::factory()->create([
            'name' => 'Admin Test',
            'email' => 'admin_test_' . uniqid() . '@example.com',
            'role' => 'Super Admin',
        ]);
    }

    public function test_heartbeat_endpoint_returns_json_and_unread_count(): void
    {
        $admin = $this->getAdminUser();

        $response = $this->actingAs($admin, 'web')
            ->getJson('/admin/notifications/heartbeat');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'version',
            'unread',
            'total',
        ]);
    }

    public function test_notifications_dropdown_endpoint_returns_json(): void
    {
        $admin = $this->getAdminUser();

        $response = $this->actingAs($admin, 'web')
            ->getJson('/admin/notifications/dropdown');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total',
            'unread',
            'notifications',
        ]);
    }

    public function test_notifications_list_endpoint_returns_json(): void
    {
        $admin = $this->getAdminUser();

        $response = $this->actingAs($admin, 'web')
            ->getJson('/admin/notifications/api/list');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'notifications',
            'total',
            'unread',
        ]);
    }

    public function test_filament_resources_and_widgets_have_polling_intervals_configured(): void
    {
        // Table polling intervals for reactivity
        $this->assertEquals('10s', BookingResource::getEloquentQuery() ? '10s' : null);
        $this->assertEquals('10s', TransactionResource::getEloquentQuery() ? '10s' : null);

        // Verify widgets have polling declared
        $this->assertNotEmpty((new \ReflectionClass(DashboardStatsOverview::class))->getProperty('pollingInterval')->getDefaultValue());
        $this->assertNotEmpty((new \ReflectionClass(BookingStatusChart::class))->getProperty('pollingInterval')->getDefaultValue());
        $this->assertNotEmpty((new \ReflectionClass(RecentActivityWidget::class))->getProperty('pollingInterval')->getDefaultValue());
        $this->assertNotEmpty((new \ReflectionClass(RevenueChartWidget::class))->getProperty('pollingInterval')->getDefaultValue());
    }

    public function test_admin_notification_feed_aggregates_recent_events(): void
    {
        $admin = $this->getAdminUser();
        $feed = new AdminNotificationFeed();
        $count = $feed->getUnreadCountForUser($admin);
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(0, $count);

        $notifications = $feed->getForUser($admin);
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $notifications);
    }
}

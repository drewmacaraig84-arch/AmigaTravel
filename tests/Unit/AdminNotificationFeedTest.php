<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\Inquiry;
use App\Support\AdminNotificationFeed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNotificationFeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_builds_ordered_admin_notifications_for_bookings_and_inquiries(): void
    {
        Booking::create([
            'transaction_number' => 'AGT-TEST-001',
            'origin' => 'Calapan',
            'destination' => 'Batangas',
            'departure_date' => '2026-07-20',
            'return_date' => '2026-07-22',
            'status' => 'pending',
            'total_price' => 1200,
            'client_email' => 'client@example.com',
            'client_name' => 'Client One',
        ]);

        Booking::create([
            'transaction_number' => 'AGT-TEST-002',
            'origin' => 'Calapan',
            'destination' => 'Batangas',
            'departure_date' => '2026-07-21',
            'return_date' => '2026-07-23',
            'status' => 'cancelled',
            'total_price' => 800,
            'client_email' => 'client@example.com',
            'client_name' => 'Client Two',
        ]);

        Booking::create([
            'transaction_number' => 'AGT-TEST-003',
            'origin' => 'Calapan',
            'destination' => 'Batangas',
            'departure_date' => '2026-07-24',
            'return_date' => '2026-07-26',
            'status' => 'pending',
            'is_rebooked' => true,
            'rebooking_status' => 'pending',
            'total_price' => 1500,
            'client_email' => 'client@example.com',
            'client_name' => 'Client Three',
        ]);

        Inquiry::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'subject' => 'Route question',
            'message' => 'Can you help with our ferry route?',
        ]);

        $notifications = (new AdminNotificationFeed())->getForUser(new \App\Models\User());

        $this->assertCount(5, $notifications);
        $this->assertEqualsCanonicalizing(
            ['new_booking', 'cancellation', 'rebooking', 'inquiry'],
            collect($notifications)->pluck('type')->unique()->values()->all()
        );
    }
}

<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageProofs;
use App\Models\Booking;
use App\Models\FerryRoute;
use App\Models\Schedule;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProofsAndReceiptsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['mail.default' => 'array']);

        \App\Models\PaymentSetting::firstOrCreate([], [
            'proof_retention_days' => 30,
            'admin_fee_long_haul' => 50,
            'admin_fee_short_haul' => 25,
            'transaction_fee_long_haul' => 20,
            'transaction_fee_short_haul' => 10,
        ]);
    }

    public function test_proofs_page_renders_with_category_tabs_and_formatted_tags()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
            'admin_permissions' => ['proofs' => true, 'bookings' => true],
        ]);

        $route = FerryRoute::create([
            'origin' => 'Calapan',
            'destination' => 'Batangas',
            'operator' => 'Starlite Ferries',
            'mode' => 'ferry',
            'is_active' => true,
        ]);

        $schedule = Schedule::create([
            'ferry_route_id' => $route->id,
            'service_name' => 'Starlite Eagle',
            'departure_time' => '08:00:00',
            'arrival_time' => '10:00:00',
            'price' => 450,
            'duration_minutes' => 120,
            'is_active' => true,
        ]);

        // 1. Confirmed booking
        $bConfirmed = Booking::create([
            'transaction_number' => 'AGT-20260826-0001',
            'client_name' => 'John Doe',
            'client_email' => 'john@example.com',
            'origin' => 'Calapan',
            'destination' => 'Batangas',
            'departure_date' => now()->addDays(5),
            'schedule_id' => $schedule->id,
            'schedule_price' => 450,
            'status' => 'confirmed',
            'total_price' => 550,
        ]);

        $tConfirmed = Transaction::create([
            'booking_id' => $bConfirmed->id,
            'payment_status' => 'paid',
            'payment_method' => 'gcash',
            'payment_reference' => 'REF123456',
            'proof_of_payment' => 'proofs/test1.jpg',
        ]);

        // 2. Rebooked booking
        $bRebooked = Booking::create([
            'transaction_number' => 'AGT-20260826-0002',
            'client_name' => 'Jane Smith',
            'client_email' => 'jane@example.com',
            'origin' => 'Calapan',
            'destination' => 'Batangas',
            'departure_date' => now()->addDays(6),
            'schedule_id' => $schedule->id,
            'schedule_price' => 450,
            'status' => 'confirmed',
            'is_rebooked' => true,
            'rebooking_status' => 'verified',
            'total_price' => 650,
        ]);

        $tRebooked = Transaction::create([
            'booking_id' => $bRebooked->id,
            'payment_status' => 'paid',
            'payment_method' => 'gcash',
            'payment_reference' => 'REF654321',
            'proof_of_payment' => 'proofs/test2.jpg',
            'rebooking_proof_of_payment' => 'rebooking_proofs/test2_rebook.jpg',
        ]);

        // 3. Refunded booking
        $bRefunded = Booking::create([
            'transaction_number' => 'AGT-20260826-0003',
            'client_name' => 'Bob Brown',
            'client_email' => 'bob@example.com',
            'origin' => 'Calapan',
            'destination' => 'Batangas',
            'departure_date' => now()->addDays(7),
            'schedule_id' => $schedule->id,
            'schedule_price' => 450,
            'status' => 'cancelled',
            'refund_amount' => 500,
            'refund_status' => 'completed',
            'refund_proof' => 'proofs/test3_refund.jpg',
            'total_price' => 550,
        ]);

        $tRefunded = Transaction::create([
            'booking_id' => $bRefunded->id,
            'payment_status' => 'paid',
            'payment_method' => 'gcash',
            'proof_of_payment' => 'proofs/test3.jpg',
        ]);

        $component = Livewire::actingAs($admin)
            ->test(ManageProofs::class)
            ->assertSuccessful()
            ->assertSee('AGT-20260826-0001')
            ->assertSee('AGT-20260826-0002 - Rebooked')
            ->assertSee('AGT-20260826-0003 - Refunded/Cancelled');

        // Test filtering tabs
        $component->call('setTypeFilter', 'confirmed')
            ->assertSet('typeFilter', 'confirmed')
            ->assertSee('AGT-20260826-0001')
            ->assertDontSee('AGT-20260826-0002 - Rebooked');

        $component->call('setTypeFilter', 'rebooked')
            ->assertSet('typeFilter', 'rebooked')
            ->assertSee('AGT-20260826-0002 - Rebooked')
            ->assertDontSee('AGT-20260826-0001');

        $component->call('setTypeFilter', 'refunded')
            ->assertSet('typeFilter', 'refunded')
            ->assertSee('AGT-20260826-0003 - Refunded/Cancelled')
            ->assertDontSee('AGT-20260826-0001');
    }

    public function test_receipt_pdf_download_endpoint_works_for_all_types()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
            'admin_permissions' => ['proofs' => true, 'bookings' => true],
        ]);

        $route = FerryRoute::create([
            'origin' => 'Calapan',
            'destination' => 'Batangas',
            'operator' => 'Starlite Ferries',
            'mode' => 'ferry',
            'is_active' => true,
        ]);

        $schedule = Schedule::create([
            'ferry_route_id' => $route->id,
            'service_name' => 'Starlite Eagle',
            'departure_time' => '08:00:00',
            'arrival_time' => '10:00:00',
            'price' => 450,
            'duration_minutes' => 120,
            'is_active' => true,
        ]);

        $booking = Booking::create([
            'transaction_number' => 'AGT-20260826-9999',
            'client_name' => 'Test Traveler',
            'client_email' => 'traveler@example.com',
            'origin' => 'Calapan',
            'destination' => 'Batangas',
            'departure_date' => now()->addDays(5),
            'schedule_id' => $schedule->id,
            'schedule_price' => 450,
            'status' => 'confirmed',
            'total_price' => 550,
        ]);

        Transaction::create([
            'booking_id' => $booking->id,
            'payment_status' => 'paid',
            'payment_method' => 'gcash',
        ]);

        // Test Confirmed receipt
        $response = $this->actingAs($admin)->get(route('admin.receipts.download', ['booking' => $booking->id, 'type' => 'confirmed']));
        $response->assertSuccessful();
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));

        // Test Rebooked receipt
        $responseRebooked = $this->actingAs($admin)->get(route('admin.receipts.download', ['booking' => $booking->id, 'type' => 'rebooked']));
        $responseRebooked->assertSuccessful();
        $this->assertEquals('application/pdf', $responseRebooked->headers->get('content-type'));

        // Test Refunded receipt
        $responseRefunded = $this->actingAs($admin)->get(route('admin.receipts.download', ['booking' => $booking->id, 'type' => 'refunded']));
        $responseRefunded->assertSuccessful();
        $this->assertEquals('application/pdf', $responseRefunded->headers->get('content-type'));
    }

    public function test_pre_retention_archive_service_and_download()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
            'admin_permissions' => ['proofs' => true, 'bookings' => true],
        ]);

        \Illuminate\Support\Facades\Storage::fake('public');
        \Illuminate\Support\Facades\Storage::disk('public')->put('proofs/test_expiring.jpg', 'fake image content');

        $booking = Booking::create([
            'transaction_number' => 'AGT-20260826-8888',
            'client_name' => 'Archive Traveler',
            'client_email' => 'arch@example.com',
            'origin' => 'Calapan',
            'destination' => 'Batangas',
            'departure_date' => now()->addDays(5),
            'status' => 'confirmed',
            'total_price' => 550,
        ]);
        $booking->timestamps = false;
        $booking->updated_at = now()->subDays(35);
        $booking->save();

        $tx = Transaction::create([
            'booking_id' => $booking->id,
            'payment_status' => 'paid',
            'payment_method' => 'gcash',
            'proof_of_payment' => 'proofs/test_expiring.jpg',
        ]);
        $tx->timestamps = false;
        $tx->updated_at = now()->subDays(35);
        $tx->save();

        $service = app(\App\Services\ProofArchivalService::class);
        $result = $service->createPreRetentionArchive(30);

        $this->assertNotNull($result);
        $this->assertFileExists($result['path']);
        $this->assertGreaterThan(0, $result['files_count']);

        // Test downloading the created archive
        $response = $this->actingAs($admin)->get(route('admin.proofs.download-archive', ['filename' => $result['filename']]));
        $response->assertSuccessful();
        $this->assertEquals('application/zip', $response->headers->get('content-type'));

        // Test deleting the archive via service
        $deleted = $service->deleteArchive($result['filename']);
        $this->assertTrue($deleted);
        $this->assertFileDoesNotExist($result['path']);
    }

    public function test_proof_archive_deletion_via_filament_page_action()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
            'admin_permissions' => ['proofs' => true, 'bookings' => true],
        ]);

        \Illuminate\Support\Facades\Storage::fake('public');
        \Illuminate\Support\Facades\Storage::disk('public')->put('proofs/test_archive_del.jpg', 'image for archive');

        $booking = Booking::create([
            'transaction_number' => 'AGT-20260826-9999',
            'client_name' => 'Delete Test User',
            'client_email' => 'del@example.com',
            'origin' => 'Calapan',
            'destination' => 'Batangas',
            'departure_date' => now()->addDays(2),
            'status' => 'confirmed',
            'total_price' => 550,
        ]);

        Transaction::create([
            'booking_id' => $booking->id,
            'payment_status' => 'paid',
            'payment_method' => 'gcash',
            'proof_of_payment' => 'proofs/test_archive_del.jpg',
        ]);

        $service = app(\App\Services\ProofArchivalService::class);
        $archive = $service->createArchive();
        $this->assertNotNull($archive);
        $this->assertFileExists($archive['path']);

        Livewire::actingAs($admin)
            ->test(ManageProofs::class)
            ->callAction('deleteArchive', ['filename' => $archive['filename']])
            ->assertHasNoErrors();

        $this->assertFileDoesNotExist($archive['path']);
    }
}

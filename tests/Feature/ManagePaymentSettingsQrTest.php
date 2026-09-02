<?php

namespace Tests\Feature;

use App\Filament\Pages\ManagePaymentSettings;
use App\Models\Booking;
use App\Models\PaymentSetting;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ManagePaymentSettingsQrTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_payment_qr_code_saves_and_persists_on_reload(): void
    {
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('admin'));

        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);

        $file = UploadedFile::fake()->image('my_gcash_qr.png', 400, 400);

        Livewire::actingAs($admin)
            ->test(ManagePaymentSettings::class)
            ->set('data.qr_code_path', [$file])
            ->call('save')
            ->assertHasNoFormErrors();

        // 1. Check that PaymentSetting in DB has qr_code_path set
        PaymentSetting::bust();
        $setting = PaymentSetting::current();
        $this->assertNotNull($setting->qr_code_path, 'PaymentSetting qr_code_path should not be null after save');
        $this->assertStringContainsString('payment-qr/', $setting->qr_code_path);

        // 2. Check reload/mount preserves the path in form state
        $reloaded = Livewire::actingAs($admin)->test(ManagePaymentSettings::class);
        $this->assertNotEmpty($reloaded->get('data.qr_code_path'));

        // 3. Calling save again on the reloaded page must NOT wipe the qr code
        $reloaded->call('save')->assertHasNoFormErrors();

        PaymentSetting::bust();
        $settingAfterSecondSave = PaymentSetting::current();
        $this->assertNotNull($settingAfterSecondSave->qr_code_path, 'qr_code_path must NOT be wiped on second save');
        $this->assertEquals($setting->qr_code_path, $settingAfterSecondSave->qr_code_path);

        // 4. Verify connection to client payment page (/payment/{transaction})
        $booking = Booking::create([
            'transaction_number' => 'AGT-TEST-1234',
            'origin' => 'Batangas',
            'destination' => 'Calapan',
            'departure_date' => '2026-09-10',
            'total_price' => 1250.00,
            'client_name' => 'John Doe',
            'client_email' => 'john@example.com',
            'client_phone' => '09171234567',
        ]);
        $transaction = Transaction::create([
            'booking_id' => $booking->id,
            'payment_status' => 'pending',
            'amount' => 1250.00,
        ]);

        $response = $this->get(route('payment.show', $transaction));
        $response->assertOk();
        $expectedUrl = storage_asset_path($setting->qr_code_path);
        $response->assertSee($expectedUrl, false);

        // 5. Verify /storage-file/ endpoint connection serves the file
        Storage::disk('public')->put($setting->qr_code_path, 'fake-image-content');
        $fileResponse = $this->get('/storage-file/' . $setting->qr_code_path);
        $fileResponse->assertOk();
    }

    public function test_removing_qr_code_sets_it_to_null_and_persists(): void
    {
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('admin'));

        $admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);

        PaymentSetting::current()->update(['qr_code_path' => 'payment-qr/existing.png']);
        PaymentSetting::bust();

        Livewire::actingAs($admin)
            ->test(ManagePaymentSettings::class)
            ->set('data.qr_code_path', [])
            ->call('save')
            ->assertHasNoFormErrors();

        PaymentSetting::bust();
        $this->assertNull(PaymentSetting::current()->qr_code_path);
    }
}

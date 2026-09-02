<?php

namespace Tests\Unit;

use App\Models\Voucher;
use App\Services\VoucherBulkImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VoucherBulkImportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_import_multiple_vouchers_from_csv(): void
    {
        Storage::fake('local');

        $csvContent = "Code,Name,Notes\n"
            . "PROMO10,Promo 10 Percent,First batch\n"
            . "PROMO20,Promo 20 Percent,Second batch\n"
            . "PROMO30,Promo 30 Percent,Third batch\n";

        $tempPath = 'temp-voucher-imports/test_vouchers.csv';
        Storage::disk('local')->put($tempPath, $csvContent);
        $fullPath = Storage::disk('local')->path($tempPath);

        $service = new VoucherBulkImportService();
        $baseConfig = [
            'discount_type' => 'percentage',
            'discount_value' => 15.00,
            'max_discount' => 300.00,
            'min_booking_amount' => 500.00,
            'eligible_scope' => 'booking_total',
            'is_active' => true,
            'total_usage_limit' => 50,
            'one_use_per_customer' => true,
        ];

        $result = $service->import($fullPath, $baseConfig);

        $this->assertEquals(3, $result['created']);
        $this->assertEquals(0, $result['skipped']);
        $this->assertCount(3, $result['created_codes']);

        $this->assertDatabaseHas('vouchers', [
            'code' => 'PROMO10',
            'name' => 'Promo 10 Percent',
            'discount_type' => 'percentage',
            'discount_value' => 15.00,
            'max_discount' => 300.00,
            'total_usage_limit' => 50,
        ]);

        $this->assertDatabaseHas('vouchers', [
            'code' => 'PROMO20',
            'name' => 'Promo 20 Percent',
        ]);

        $this->assertDatabaseHas('vouchers', [
            'code' => 'PROMO30',
            'name' => 'Promo 30 Percent',
        ]);
    }

    public function test_skips_duplicate_codes_during_bulk_import(): void
    {
        Storage::fake('local');

        Voucher::create([
            'name' => 'Existing Promo',
            'code' => 'EXISTING_CODE',
            'discount_type' => 'fixed',
            'discount_value' => 50,
            'eligible_scope' => 'booking_total',
        ]);

        $csvContent = "Code,Name\n"
            . "EXISTING_CODE,Duplicate Should Skip\n"
            . "NEW_CODE_01,New Voucher One\n";

        $tempPath = 'temp-voucher-imports/test_duplicates.csv';
        Storage::disk('local')->put($tempPath, $csvContent);
        $fullPath = Storage::disk('local')->path($tempPath);

        $service = new VoucherBulkImportService();
        $result = $service->import($fullPath, [
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'eligible_scope' => 'booking_total',
        ]);

        $this->assertEquals(1, $result['created']);
        $this->assertEquals(1, $result['skipped']);
        $this->assertDatabaseCount('vouchers', 2);
    }
}

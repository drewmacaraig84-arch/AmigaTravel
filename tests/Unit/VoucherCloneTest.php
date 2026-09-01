<?php

namespace Tests\Unit;

use App\Models\Operator;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoucherCloneTest extends TestCase
{
    use RefreshDatabase;

    public function test_voucher_can_be_cloned_with_new_code_and_name(): void
    {
        $operator = Operator::create([
            'name' => 'Starlite',
            'mode' => 'ferry',
            'is_active' => true,
        ]);

        $original = Voucher::create([
            'name' => 'Summer Holiday Promo',
            'code' => 'SUMMER2026',
            'description' => '20% off all ferry routes for summer',
            'discount_type' => 'percentage',
            'discount_value' => 20.00,
            'max_discount' => 500.00,
            'min_booking_amount' => 1000.00,
            'eligible_scope' => 'booking_total',
            'eligible_operator_id' => $operator->id,
            'eligible_origin' => 'Batangas',
            'eligible_destination' => 'Calapan',
            'is_active' => true,
            'is_hidden' => false,
            'total_usage_limit' => 100,
            'one_use_per_customer' => true,
            'start_at' => now()->subDay(),
            'end_at' => now()->addDays(30),
        ]);

        // Simulate Clone Action logic
        $clone = $original->replicate([
            'created_at',
            'updated_at',
        ]);
        $clone->name = 'Autumn Holiday Promo';
        $clone->code = 'AUTUMN2026';
        $clone->save();

        $this->assertDatabaseCount('vouchers', 2);

        $savedClone = Voucher::where('code', 'AUTUMN2026')->first();
        $this->assertNotNull($savedClone);
        $this->assertEquals('Autumn Holiday Promo', $savedClone->name);
        $this->assertEquals('AUTUMN2026', $savedClone->code);

        // Verify all other properties were copied exactly
        $this->assertEquals($original->discount_type, $savedClone->discount_type);
        $this->assertEquals(20.00, (float) $savedClone->discount_value);
        $this->assertEquals(500.00, (float) $savedClone->max_discount);
        $this->assertEquals(1000.00, (float) $savedClone->min_booking_amount);
        $this->assertEquals($original->eligible_scope, $savedClone->eligible_scope);
        $this->assertEquals($original->eligible_operator_id, $savedClone->eligible_operator_id);
        $this->assertEquals($original->eligible_origin, $savedClone->eligible_origin);
        $this->assertEquals($original->eligible_destination, $savedClone->eligible_destination);
        $this->assertEquals($original->is_active, $savedClone->is_active);
        $this->assertEquals($original->is_hidden, $savedClone->is_hidden);
        $this->assertEquals(100, $savedClone->total_usage_limit);
        $this->assertTrue($savedClone->one_use_per_customer);
        $this->assertEquals($original->description, $savedClone->description);
    }
}

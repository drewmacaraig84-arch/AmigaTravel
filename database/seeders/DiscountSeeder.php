<?php

namespace Database\Seeders;

use App\Models\Discount;
use Illuminate\Database\Seeder;

class DiscountSeeder extends Seeder
{
    /**
     * Seed the four discount categories requested by the client.
     * Percentages are placeholders — adjust them in the admin panel
     * (Discounts) to match actual policy.
     */
    public function run(): void
    {
        $discounts = [
            ['name' => 'Student', 'percentage' => 20],
            ['name' => 'Senior Citizen', 'percentage' => 20],
            ['name' => 'PWD', 'percentage' => 20],
            ['name' => 'Infant', 'percentage' => 100],
        ];

        foreach ($discounts as $discount) {
            Discount::updateOrCreate(['name' => $discount['name']], $discount);
        }
    }
}

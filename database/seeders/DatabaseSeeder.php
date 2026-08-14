<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a seeded admin account so it is restored after migrate:refresh --seed.
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('admin'),
                'is_admin' => true,
                'is_staff' => false,
            ],
        );

        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'is_staff' => true,
            ],
        );

        User::updateOrCreate(
            ['email' => 'arieskingnieto@gmail.com'],
            [
                'name' => 'Aries King',
                'password' => bcrypt('password'),
                'is_admin' => false,
                'is_staff' => false,
            ],
        );

        $this->call([
            DiscountSeeder::class,
            VehicleBrandModelSeeder::class,
            VehicleRateSeeder::class,
            VehicleSeeder::class,
            WebsiteSettingSeeder::class,
            GraciaEarningRuleSeeder::class,
            AirlineBaggageRuleSeeder::class,
            OperatorSeeder::class,
            // Routes, schedules and related transport classes
            RouteScheduleSeeder::class,
            TransportClassSeeder::class,
            // Tour hotels (imports from travel_packages_summary_MERGED.csv.txt)
            TourHotelsSeeder::class,
        ]);
    }
}

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
                'role' => 'Admin',
                'is_admin' => true,
                'is_staff' => true,
            ],
        );

        User::updateOrCreate(
            ['email' => 'agnoarrizaann27@gmail.com'],
            [
                'name' => 'Admin Arriza',
                'password' => bcrypt('Aili$h_04'),
                'role' => 'Admin',
                'is_admin' => true,
                'is_staff' => true,
            ],
        );

        User::updateOrCreate(
            ['email' => 'M.antaran23@yahoo'],
            [
                'name' => 'Admin Gracia',
                'password' => bcrypt('Hellospidey203'),
                'role' => 'Admin',
                'is_admin' => true,
                'is_staff' => true,
            ],
        );

        User::updateOrCreate(
            ['email' => 'arieskingnieto@gmail.com'],
            [
                'name' => 'Aries King',
                'password' => bcrypt('password'),
                'role' => 'Super Admin',
                'is_admin' => true,
                'is_staff' => true,
            ],
        );

        User::updateOrCreate(
            ['email' => 'macaraigdrew99@gmail.com'],
            [
                'name' => 'Drew',
                'password' => bcrypt('1115'),
                'role' => 'Super Admin',
                'is_admin' => true,
                'is_staff' => true,
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
            RouteScheduleSeeder::class
        ]);
    }
}

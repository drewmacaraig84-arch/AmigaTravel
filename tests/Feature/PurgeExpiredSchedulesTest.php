<?php

namespace Tests\Feature;

use App\Models\FerryRoute;
use App\Models\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurgeExpiredSchedulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_deletes_schedules_that_are_older_than_one_day(): void
    {
        $route = FerryRoute::create([
            'origin' => 'Test Origin',
            'destination' => 'Test Destination',
            'mode' => 'ferry',
            'is_active' => true,
        ]);

        $expired = Schedule::create([
            'ferry_route_id' => $route->id,
            'service_name' => 'Test Service',
            'vehicle_name' => 'Test Vehicle',
            'departure_time' => now()->subHours(30),
            'arrival_time' => now()->subDay(),
            'price' => 100,
            'is_active' => true,
        ]);

        $active = Schedule::create([
            'ferry_route_id' => $route->id,
            'service_name' => 'Recent Service',
            'vehicle_name' => 'Recent Vehicle',
            'departure_time' => now()->subHours(2),
            'arrival_time' => now()->addHours(2),
            'price' => 100,
            'is_active' => true,
        ]);

        $this->artisan('schedules:purge-expired')->assertExitCode(0);

        $this->assertDatabaseHas('schedules', ['id' => $expired->id, 'is_active' => false]);
        $this->assertDatabaseHas('schedules', ['id' => $active->id, 'is_active' => true]);
    }
}

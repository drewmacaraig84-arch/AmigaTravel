<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\FerryRoute;
use App\Models\Schedule;
use App\Models\Vehicle;

class SchedulesPageTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    public function test_schedules_page_loads_successfully_without_lazy_loading_violation(): void
    {
        $vehicle = Vehicle::create([
            'vehicle_id' => 'VH-001',
            'name' => 'SuperFerry 1',
            'operator' => '2GO',
            'type' => 'ferry',
            'capacity' => 500,
        ]);

        $route = FerryRoute::create([
            'origin' => 'Batangas',
            'destination' => 'Caticlan',
            'mode' => 'ferry',
            'operator' => '2GO',
            'vehicle_id' => $vehicle->id,
            'is_active' => true,
        ]);

        $schedule = Schedule::create([
            'ferry_route_id' => $route->id,
            'service_name' => 'Morning Express',
            'departure_time' => now()->addDays(2),
            'arrival_time' => now()->addDays(2)->addHours(4),
            'duration_minutes' => 240,
            'price' => 500.00,
            'is_active' => true,
        ]);

        $response = $this->get('/schedules');

        $response->assertStatus(200);
        $response->assertSee('Active Routes');
        $response->assertSee('Schedule and Routes');

        // Cleanup
        $schedule->delete();
        $route->delete();
        $vehicle->delete();
    }
}

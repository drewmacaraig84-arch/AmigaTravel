<?php

namespace Tests\Feature;

use App\Models\FerryRoute;
use App\Models\Operator;
use App\Models\Schedule;
use App\Models\ScheduleAccommodation;
use App\Models\TransportClass;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchedulesPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_schedules_page_loads_successfully_without_lazy_loading_violation(): void
    {
        $operator = Operator::create([
            'name' => '2GO',
            'mode' => 'ferry',
            'is_active' => true,
        ]);

        $vehicle = Vehicle::create([
            'vehicle_id' => 'VH-001',
            'name' => 'SuperFerry 1',
            'operator' => '2GO',
            'operator_id' => $operator->id,
            'type' => 'ferry',
            'capacity' => 500,
        ]);

        $route = FerryRoute::create([
            'origin' => 'Batangas',
            'destination' => 'Caticlan',
            'mode' => 'ferry',
            'operator' => '2GO',
            'operator_id' => $operator->id,
            'vehicle_id' => $vehicle->id,
            'is_active' => true,
        ]);

        $dep = Carbon::today()->addDays(2)->setTime(8, 30);
        $arr = (clone $dep)->addHours(4)->addMinutes(15);

        $schedule = Schedule::create([
            'ferry_route_id' => $route->id,
            'service_name' => 'Morning Express',
            'departure_time' => $dep,
            'arrival_time' => $arr,
            'duration_minutes' => 255,
            'price' => 500.00,
            'is_active' => true,
        ]);

        $tc = TransportClass::create([
            'name' => 'Tourist Bed Bunk',
            'code' => 'tourist-bed-bunk',
            'operator' => '2GO',
            'operator_id' => $operator->id,
            'mode' => 'ferry',
            'price' => 650.00,
            'is_active' => true,
        ]);

        $schedule->transportClasses()->attach($tc->id, [
            'additional_price' => 650.00,
            'tickets_available' => 50,
            'is_active' => true,
        ]);

        ScheduleAccommodation::create([
            'schedule_id' => $schedule->id,
            'name' => 'Tourist Bed Bunk',
            'price' => 650.00,
            'tickets_available' => 50,
            'is_active' => true,
        ]);

        // Test Schedule model accessors
        $this->assertEquals($dep->format('F j, Y g:i a'), $schedule->formatted_departure);
        $this->assertEquals($arr->format('F j, Y g:i a'), $schedule->formatted_arrival);
        $this->assertEquals('4h 15m', $schedule->duration_label);
        $this->assertStringContainsString('Tourist Bed Bunk', $schedule->accommodation_label);

        // Test Web Page
        $response = $this->get('/schedules');
        $response->assertStatus(200);
        $response->assertSee('Active Routes');
        $response->assertSee('Schedule and Routes');
        $response->assertSee('Batangas');
        $response->assertSee('Caticlan');
        $response->assertSee('Morning Express');
        $response->assertSee('8:30 AM');
        $response->assertSee('12:45 PM');
    }

    public function test_schedules_web_page_handles_custom_query_dates(): void
    {
        $route = FerryRoute::create([
            'origin' => 'Cebu',
            'destination' => 'Surigao',
            'mode' => 'ferry',
            'operator' => 'Starlite',
            'is_active' => true,
        ]);

        $dep = Carbon::today()->addDays(5)->setTime(10, 0);
        $arr = (clone $dep)->addHours(8);

        $schedule = Schedule::create([
            'ferry_route_id' => $route->id,
            'service_name' => 'Starlite Saturn',
            'departure_time' => $dep,
            'arrival_time' => $arr,
            'duration_minutes' => 480,
            'price' => 1550.00,
            'is_active' => true,
        ]);

        $tc = TransportClass::create([
            'name' => 'Tourist Bed Bunk',
            'code' => 'tourist-bed-bunk',
            'operator' => 'Starlite',
            'mode' => 'ferry',
            'price' => 1550.00,
            'is_active' => true,
        ]);

        $schedule->transportClasses()->attach($tc->id, [
            'additional_price' => 1550.00,
            'tickets_available' => 50,
            'is_active' => true,
        ]);

        $startDate = Carbon::today()->addDays(4)->format('Y-m-d');
        $endDate = Carbon::today()->addDays(6)->format('Y-m-d');

        $response = $this->get("/schedules?start_date={$startDate}&end_date={$endDate}");
        $response->assertStatus(200);
        $response->assertSee('Cebu');
        $response->assertSee('Surigao');
        $response->assertSee('Starlite Saturn');
    }

    public function test_api_schedules_search_for_mobile_app(): void
    {
        $route = FerryRoute::create([
            'origin' => 'Batangas',
            'destination' => 'Calapan',
            'mode' => 'ferry',
            'operator' => 'Starlite',
            'is_active' => true,
        ]);

        $dep = Carbon::today()->addDays(1)->setTime(14, 0);
        $arr = (clone $dep)->addHours(2);

        $schedule = Schedule::create([
            'ferry_route_id' => $route->id,
            'service_name' => 'Starlite Archer',
            'departure_time' => $dep,
            'arrival_time' => $arr,
            'duration_minutes' => 120,
            'price' => 680.00,
            'is_active' => true,
        ]);

        $tc = TransportClass::create([
            'name' => 'Economy Bed Bunk',
            'code' => 'economy-bed-bunk',
            'operator' => 'Starlite',
            'mode' => 'ferry',
            'price' => 680.00,
            'is_active' => true,
        ]);

        $schedule->transportClasses()->attach($tc->id, [
            'additional_price' => 680.00,
            'tickets_available' => 40,
            'is_active' => true,
        ]);

        $searchDate = Carbon::today()->addDays(1)->format('Y-m-d');

        $response = $this->postJson('/api/schedules', [
            'origin' => 'Batangas',
            'destination' => 'Calapan',
            'date' => $searchDate,
            'mode' => 'ferry',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
        ]);
        $response->assertJsonStructure([
            'status',
            'schedules' => [
                '*' => [
                    'id',
                    'departure',
                    'arrival',
                    'duration',
                    'price',
                    'service',
                    'mode',
                    'accommodations',
                    'transport_classes',
                ],
            ],
        ]);
    }
}

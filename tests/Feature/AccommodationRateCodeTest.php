<?php

namespace Tests\Feature;

use App\Models\FerryRoute;
use App\Models\Schedule;
use App\Models\ScheduleAccommodation;
use App\Models\Booking;
use App\Services\ScheduleCsvImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AccommodationRateCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_creates_multiple_rates_for_same_accommodation(): void
    {
        Cache::flush();

        // Create temporary CSV file content
        $csvPath = tempnam(sys_get_temp_dir(), 'sched_');
        $csvContent = "Mode,Operator,Vehicle Tail No.,Origin,Destination,Departure Date,Departure Time,Arrival Time,Return Date,Transport Class,Rate,Rate Code\n"
            . "ferry,Starlite,MV Starlite Eagle,Calapan,Batangas,25/08/2026,08:00:00,10:00:00,,Tourist Class,500.00,PROMO\n"
            . "ferry,Starlite,MV Starlite Eagle,Calapan,Batangas,25/08/2026,08:00:00,10:00:00,,Tourist Class,800.00,REGULAR\n";

        file_put_contents($csvPath, $csvContent);

        $importService = app(ScheduleCsvImportService::class);
        $result = $importService->import($csvPath);

        @unlink($csvPath);

        $this->assertEquals(2, $result['imported']);
        $this->assertEquals(0, $result['skipped']);
        $this->assertEmpty($result['errors']);

        // Assert schedule accommodations in database
        $schedule = Schedule::first();
        $this->assertNotNull($schedule);

        $accommodations = $schedule->scheduleAccommodations;
        $this->assertCount(2, $accommodations);

        $promoAcc = $accommodations->firstWhere('rate_code', 'PROMO');
        $regularAcc = $accommodations->firstWhere('rate_code', 'REGULAR');

        $this->assertNotNull($promoAcc);
        $this->assertNotNull($regularAcc);

        $this->assertEquals(500.00, (float) $promoAcc->price);
        $this->assertEquals(800.00, (float) $regularAcc->price);
        $this->assertEquals('Tourist Bed Bunk', $promoAcc->name);
        $this->assertEquals('Tourist Bed Bunk', $regularAcc->name);
    }

    public function test_booking_saves_rate_codes(): void
    {
        Cache::flush();

        $route = FerryRoute::create([
            'origin' => 'Calapan',
            'destination' => 'Batangas',
            'mode' => 'ferry',
            'operator' => 'Starlite',
            'is_active' => true,
        ]);

        $schedule = Schedule::create([
            'ferry_route_id' => $route->id,
            'vehicle_name' => 'MV Starlite Eagle',
            'departure_time' => '2026-08-25 08:00:00',
            'arrival_time' => '2026-08-25 10:00:00',
            'price' => 500,
            'is_active' => true,
        ]);

        $accommodation = ScheduleAccommodation::create([
            'schedule_id' => $schedule->id,
            'name' => 'Tourist Class',
            'rate_code' => 'PROMO',
            'price' => 500.00,
            'tickets_available' => 50,
            'is_active' => true,
        ]);

        $data = [
            'origin' => 'Calapan',
            'destination' => 'Batangas',
            'departure_date' => '2026-08-25',
            'return_date' => null,
            'schedule_id' => $schedule->id,
            'selected_schedule_accommodation_id' => $accommodation->id,
            'trip_type' => 'one_way',
            'client_name' => 'John Doe',
            'client_email' => 'john@example.com',
            'passengers' => [
                [
                    'name' => 'Jane Doe',
                    'type' => 'adult',
                ]
            ],
        ];

        $action = app(\App\Actions\Bookings\CreateBookingAction::class);
        $booking = $action->execute($data);

        $this->assertNotNull($booking);
        $this->assertEquals('PROMO', $booking->schedule_accommodation_rate_code);
    }

    public function test_api_returns_rate_code(): void
    {
        Cache::flush();

        $route = FerryRoute::create([
            'origin' => 'Calapan',
            'destination' => 'Batangas',
            'mode' => 'ferry',
            'operator' => 'Starlite',
            'is_active' => true,
        ]);

        $schedule = Schedule::create([
            'ferry_route_id' => $route->id,
            'vehicle_name' => 'MV Starlite Eagle',
            'departure_time' => '2026-08-25 08:00:00',
            'arrival_time' => '2026-08-25 10:00:00',
            'price' => 500,
            'is_active' => true,
        ]);

        ScheduleAccommodation::create([
            'schedule_id' => $schedule->id,
            'name' => 'Tourist Class',
            'rate_code' => 'PROMO',
            'price' => 500.00,
            'tickets_available' => 50,
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/schedules', [
            'origin' => 'Calapan',
            'destination' => 'Batangas',
            'date' => '2026-08-25',
        ]);

        $response->assertOk();
        $schedules = $response->json('schedules');
        $this->assertNotEmpty($schedules);
        $this->assertEquals('PROMO', $schedules[0]['accommodations'][0]['rate_code']);
    }
}

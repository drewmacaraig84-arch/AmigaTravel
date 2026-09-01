<?php

namespace Tests\Unit;

use App\Services\LocationCodeResolver;
use App\Services\StarliteScheduleIngestionService;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StarliteScheduleIngestionTest extends TestCase
{
    use RefreshDatabase;

    private StarliteScheduleIngestionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StarliteScheduleIngestionService(new LocationCodeResolver());
    }

    public function test_parses_route_cells_with_vessel_types(): void
    {
        $ropax = $this->service->parseRouteCell('BATANGAS-CALAPAN (ROPAX)');
        $this->assertNotNull($ropax);
        $this->assertEquals('Batangas', $ropax['origin']);
        $this->assertEquals('Calapan', $ropax['destination']);
        $this->assertEquals('ROPAX', $ropax['type']);

        $fastcraft = $this->service->parseRouteCell('BATANGAS-CALAPAN  (FASTCRAFT)');
        $this->assertNotNull($fastcraft);
        $this->assertEquals('FASTCRAFT', $fastcraft['type']);

        $lct = $this->service->parseRouteCell('BATANGAS-CALAPAN (LCT)');
        $this->assertNotNull($lct);
        $this->assertEquals('LCT', $lct['type']);

        $odiongan = $this->service->parseRouteCell('ODIONGAN TO ROXAS MINDORO');
        $this->assertNotNull($odiongan);
        $this->assertEquals('Odiongan', $odiongan['origin']);
        $this->assertEquals('Roxas Mindoro', $odiongan['destination']);
    }

    public function test_parses_various_departure_time_formats(): void
    {
        // Odd numbers rule
        $oddTimes = $this->service->parseDepartureTimes('EVERY ODD NUMBERS');
        $this->assertCount(12, $oddTimes);
        $this->assertContains('01:00', $oddTimes);
        $this->assertContains('23:00', $oddTimes);

        // Comma separated list
        $times = $this->service->parseDepartureTimes('12:30AM,5:30AM,2:30PM,10:30PM');
        $this->assertEquals(['00:30', '05:30', '14:30', '22:30'], $times);

        // Formats with AM/PM and AND
        $cebuTimes = $this->service->parseDepartureTimes('9:00AM AND 9:00PM');
        $this->assertEquals(['09:00', '21:00'], $cebuTimes);

        // Mixed notation like 12NN
        $roxasTimes = $this->service->parseDepartureTimes('1AM, 5AM,9AM, 12NN, 4PM,8PM');
        $this->assertEquals(['01:00', '05:00', '09:00', '12:00', '16:00', '20:00'], $roxasTimes);
    }

    public function test_parses_duration_minutes(): void
    {
        $this->assertEquals(180, $this->service->parseDurationMinutes('3 HOURS'));
        $this->assertEquals(90, $this->service->parseDurationMinutes('1 & 30MINS'));
        $this->assertEquals(600, $this->service->parseDurationMinutes('10 HOURS'));
        $this->assertEquals(1080, $this->service->parseDurationMinutes('18 HOURS'));
    }

    public function test_parses_days_of_week(): void
    {
        $this->assertEquals('all', $this->service->parseDaysOfWeek('DAILY'));
        $this->assertEquals('all', $this->service->parseDaysOfWeek('MON-SUN'));

        $wedFriSun = $this->service->parseDaysOfWeek('WED, FRI, SUN');
        $this->assertIsArray($wedFriSun);
        $this->assertContains(Carbon::WEDNESDAY, $wedFriSun);
        $this->assertContains(Carbon::FRIDAY, $wedFriSun);
        $this->assertContains(Carbon::SUNDAY, $wedFriSun);
    }

    public function test_lookups_starlite_fare_matrix_matching_pdf_rates(): void
    {
        $calapan = $this->service->lookupFareMatrix('Batangas', 'Calapan');
        $this->assertEquals(680, $calapan['base_price']);
        $this->assertEquals(1440, $calapan['vehicle_rates']['Motorcycle']);

        $caticlan = $this->service->lookupFareMatrix('Batangas', 'Caticlan');
        $this->assertEquals(2170, $caticlan['base_price']);
        $this->assertEquals(7020, $caticlan['vehicle_rates']['Motorcycle']);

        $surigao = $this->service->lookupFareMatrix('Cebu', 'Surigao');
        $this->assertEquals(1550, $surigao['base_price']);
    }

    public function test_ingest_strictly_creates_starlite_data_only(): void
    {
        $filePath = base_path('starlite_schedules/VESSEL ROUTE.xlsx');
        if (! file_exists($filePath)) {
            $this->markTestSkipped('starlite_schedules/VESSEL ROUTE.xlsx not present.');
        }

        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(7); // 7-day test horizon

        $result = $this->service->ingest($filePath, $startDate, $endDate);

        $this->assertTrue($result['success']);
        $this->assertGreaterThan(0, $result['routes_count']);
        $this->assertGreaterThan(0, $result['schedules_count']);
        $this->assertGreaterThan(0, $result['vessels_count']);

        // Assert all created routes belong to Starlite ONLY
        $nonStarliteRoutes = \App\Models\FerryRoute::where('operator', '!=', 'Starlite')->count();
        $this->assertEquals(0, $nonStarliteRoutes);

        // Assert vehicle rates are synced
        $this->assertDatabaseHas('vehicle_rates', ['name' => 'Motorcycle', 'price' => 1440.00]);
        $this->assertDatabaseHas('vehicle_rates', ['name' => 'Below 3 meters', 'price' => 2160.00]);
    }
}

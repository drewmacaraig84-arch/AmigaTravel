<?php

namespace Tests\Unit;

use App\Models\FerryRoute;
use App\Models\Operator;
use App\Models\Schedule;
use App\Models\Vehicle;
use App\Services\LocationCodeResolver;
use App\Services\ScheduleCsvImportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleCsvImportServiceTest extends TestCase
{
    use RefreshDatabase;

    private ScheduleCsvImportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ScheduleCsvImportService(new LocationCodeResolver());
    }

    public function test_imports_standard_csv_and_auto_provisions_new_operator(): void
    {
        $csvContent = "Operator,Mode,Vehicle,Origin,Destination,Departure Date,Departure Time,Arrival Time,Accommodation,Price,Tickets Available\n";
        $csvContent .= "SuperCat,ferry,SuperCat 38,Batangas,Calapan," . Carbon::today()->addDays(3)->format('d/m/Y') . ",09:00,10:15,Tourist Class,550,150\n";

        $tempFile = tempnam(sys_get_temp_dir(), 'test_sched_') . '.csv';
        file_put_contents($tempFile, $csvContent);

        $result = $this->service->import($tempFile);

        $this->assertEquals(1, $result['imported']);
        $this->assertEmpty($result['errors']);

        // Verify SuperCat operator was auto-created
        $this->assertDatabaseHas('operators', [
            'name' => 'SuperCat',
            'mode' => 'ferry',
        ]);

        // Verify Vehicle and FerryRoute are strictly assigned to SuperCat
        $this->assertDatabaseHas('vehicles', [
            'name' => 'SuperCat 38',
            'operator' => 'SuperCat',
        ]);

        $this->assertDatabaseHas('ferry_routes', [
            'origin' => 'Batangas',
            'destination' => 'Calapan',
            'operator' => 'SuperCat',
        ]);

        @unlink($tempFile);
    }

    public function test_imports_airline_schedule_with_iata_resolution(): void
    {
        $csvContent = "Operator,Mode,Vehicle,Origin,Destination,Departure Date,Departure Time,Arrival Time,Accommodation,Price,Tickets Available\n";
        $csvContent .= "Philippine Airlines,airline,PR 2811,MNL,DVO," . Carbon::today()->addDays(5)->format('d/m/Y') . ",06:30,08:20,Economy,3800,120\n";

        $tempFile = tempnam(sys_get_temp_dir(), 'test_airline_') . '.csv';
        file_put_contents($tempFile, $csvContent);

        $result = $this->service->import($tempFile);

        $this->assertEquals(1, $result['imported']);

        // Verify IATA codes were resolved to Manila and Davao
        $this->assertDatabaseHas('ferry_routes', [
            'origin' => 'Manila',
            'destination' => 'Davao',
            'mode' => 'airline',
            'operator' => 'Philippine Airlines',
        ]);

        @unlink($tempFile);
    }

    public function test_imports_schedule_with_explicit_arrival_date(): void
    {
        $depDate = '03/10/2026';
        $arrDate = '04/10/2026';
        $csvContent = "Operator,Mode,Vehicle,Origin,Destination,Departure Date,Departure Time,Arrival Date,Arrival Time,Accommodation,Price,Tickets Available\n";
        $csvContent .= "2GO,ferry,MV 2GO Maligaya,Manila,Bacolod,{$depDate},07:00 PM,{$arrDate},11:00 PM,Tourist Class,1997,50\n";

        $tempFile = tempnam(sys_get_temp_dir(), 'test_arrdate_') . '.csv';
        file_put_contents($tempFile, $csvContent);

        $result = $this->service->import($tempFile);

        $this->assertEquals(1, $result['imported']);
        $this->assertEmpty($result['errors']);

        $schedule = Schedule::first();
        $this->assertNotNull($schedule);
        $this->assertEquals('2026-10-03 19:00:00', $schedule->departure_time->toDateTimeString());
        $this->assertEquals('2026-10-04 23:00:00', $schedule->arrival_time->toDateTimeString());

        @unlink($tempFile);
    }

    public function test_imports_schedule_with_embedded_eta_date_and_etd_prefix(): void
    {
        $csvContent = "Operator,Mode,Vehicle Tail No,Origin,Destination,Departure Date,Departure Time,Arrival Time,Transport Class,Rate,Tickets Available\n";
        $csvContent .= "2GO,ferry,MV 2GO Maligaya,Manila,Bacolod,10/3/2026,ETD: 7 PM,ETA: OCT 04 @ 11 PM,Tourist Class,1997,50\n";

        $tempFile = tempnam(sys_get_temp_dir(), 'test_smarteta_') . '.csv';
        file_put_contents($tempFile, $csvContent);

        $result = $this->service->import($tempFile);

        $this->assertEquals(1, $result['imported']);
        $this->assertEmpty($result['errors']);

        $schedule = Schedule::first();
        $this->assertNotNull($schedule);
        $this->assertEquals('2026-10-03 19:00:00', $schedule->departure_time->toDateTimeString());
        $this->assertEquals('2026-10-04 23:00:00', $schedule->arrival_time->toDateTimeString());

        @unlink($tempFile);
    }

    public function test_failsafe_advances_arrival_date_if_arrival_time_is_earlier_than_departure(): void
    {
        // Ferry departs at 9:30 PM on Oct 22 and arrives at 10:30 AM (overnight)
        // Even if CSV accidentally specified 22/10/2026 as arrival date, it should auto-advance to 23/10/2026
        $csvContent = "Operator,Mode,Vehicle,Origin,Destination,Departure Date,Departure Time,Arrival Date,Arrival Time,Accommodation,Price,Tickets Available\n";
        $csvContent .= "2GO,ferry,MV St. Michael the Archangel,Manila,Bacolod,22/10/2026,09:30 PM,22/10/2026,10:30 AM,Tourist Class,1997,50\n";

        $tempFile = tempnam(sys_get_temp_dir(), 'test_failsafe_') . '.csv';
        file_put_contents($tempFile, $csvContent);

        $result = $this->service->import($tempFile);

        $this->assertEquals(1, $result['imported']);
        $this->assertEmpty($result['errors']);

        $schedule = Schedule::first();
        $this->assertNotNull($schedule);
        $this->assertEquals('2026-10-22 21:30:00', $schedule->departure_time->toDateTimeString());
        $this->assertEquals('2026-10-23 10:30:00', $schedule->arrival_time->toDateTimeString());

        @unlink($tempFile);
    }

    public function test_imports_formatted_2go_schedules(): void
    {
        $file = base_path('2go_schedules/2GO_Manila_Butuan_Formatted.csv');
        $this->assertFileExists($file);

        $result = $this->service->import($file);
        $this->assertEquals(13, $result['imported']);
        $this->assertEmpty($result['errors']);
    }
}

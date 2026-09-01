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
}

<?php

namespace Tests\Unit;

use App\Models\FerryRoute;
use App\Models\Schedule;
use Tests\TestCase;

class ScheduleSeatingProfileTest extends TestCase
{
    public function test_airline_seating_profile_resolves_aliases_and_aircraft_variants(): void
    {
        $schedule = new Schedule();
        // getAirlineSeatingProfile() resolves aircraft type from service_name, not vehicle_name
        $schedule->service_name = 'A321';
        $schedule->setRelation('ferryRoute', new FerryRoute(['operator' => 'PAL']));
        // Provide an empty in-memory relation to prevent a DB query in the unit test environment
        $schedule->setRelation('transportClasses', collect());

        $profile = $schedule->getAirlineSeatingProfile();

        $this->assertNotNull($profile);
        $this->assertSame(236, $profile['capacity']);
        $this->assertSame(['premium-economy', 'economy'], $profile['class_order']);
    }

    public function test_airline_seating_profiles_have_capacity_values_that_match_their_seat_counts(): void
    {
        $operators = config('airline_seating.operators', []);

        foreach ($operators as $operatorConfig) {
            foreach ($operatorConfig['aircraft'] ?? [] as $aircraftConfig) {
                $this->assertNotEmpty($aircraftConfig['class_order'] ?? []);

                $seatTotal = array_sum($aircraftConfig['seat_counts'] ?? []);

                $this->assertSame((int) $aircraftConfig['capacity'], $seatTotal);
            }
        }
    }

    public function test_airline_seating_profile_falls_back_when_vehicle_name_is_missing_for_two_class_configuration(): void
    {
        $schedule = new Schedule();
        $schedule->vehicle_name = null;
        $schedule->setRelation('ferryRoute', new FerryRoute(['operator' => 'Philippine Airlines']));

        $schedule->setRelation('transportClasses', collect([
            new \App\Models\TransportClass(['code' => 'economy', 'name' => 'Economy Class']),
            new \App\Models\TransportClass(['code' => 'premium-economy', 'name' => 'Premium Economy / Comfort Class']),
        ]));

        $profile = $schedule->getAirlineSeatingProfile();

        $this->assertNotNull($profile);
        $this->assertSame(['premium-economy', 'economy'], $profile['class_order']);
        $this->assertSame(180, $profile['capacity']);
    }

    public function test_airline_seating_profile_falls_back_to_business_class_aircraft_when_business_is_present(): void
    {
        $schedule = new Schedule();
        $schedule->vehicle_name = null;
        $schedule->setRelation('ferryRoute', new FerryRoute(['operator' => 'Philippine Airlines']));

        $schedule->setRelation('transportClasses', collect([
            new \App\Models\TransportClass(['code' => 'economy', 'name' => 'Economy Class']),
            new \App\Models\TransportClass(['code' => 'premium-economy', 'name' => 'Premium Economy / Comfort Class']),
            new \App\Models\TransportClass(['code' => 'business', 'name' => 'Business Class']),
        ]));

        $profile = $schedule->getAirlineSeatingProfile();

        $this->assertNotNull($profile);
        $this->assertSame(['business', 'premium-economy', 'economy'], $profile['class_order']);
        $this->assertSame(363, $profile['capacity']);
    }
}

<?php

namespace Database\Seeders;

use App\Models\FerryRoute;
use App\Models\Schedule;
use App\Models\ScheduleAccommodation;
use App\Models\TransportClass;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RouteScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $routesData = [
                // 1. Calapan <-> Batangas (Starlite)
                [
                    'origin' => 'Calapan',
                    'destination' => 'Batangas',
                    'mode' => 'ferry',
                    'operator' => 'Starlite',
                    'trip_type' => 'local',
                    'schedules' => [
                        ['service_name' => 'Starlite Eagle', 'vehicle_name' => 'MV Starlite Eagle', 'plate_no' => 'STE-101', 'dep_time' => '08:00:00', 'duration' => 120, 'price' => 450.00],
                        ['service_name' => 'Starlite Pioneer', 'vehicle_name' => 'MV Starlite Pioneer', 'plate_no' => 'STP-102', 'dep_time' => '14:00:00', 'duration' => 120, 'price' => 450.00],
                        ['service_name' => 'Starlite Saturn', 'vehicle_name' => 'MV Starlite Saturn', 'plate_no' => 'STS-103', 'dep_time' => '20:00:00', 'duration' => 120, 'price' => 450.00],
                    ],
                    'accommodations' => [
                        ['name' => 'Reclining Seats', 'description' => 'Comfortable air-conditioned reclining seats.', 'price' => 450.00, 'has_bed' => false, 'sort_order' => 1],
                        ['name' => 'Economy Bed', 'description' => 'Air-conditioned bunk bed accommodation.', 'price' => 650.00, 'has_bed' => true, 'sort_order' => 2],
                        ['name' => 'Tourist Bed', 'description' => 'Spacious tourist class bed accommodation.', 'price' => 850.00, 'has_bed' => true, 'sort_order' => 3],
                        ['name' => 'VIP Cabin', 'description' => 'Private cabin with exclusive amenities and restroom.', 'price' => 1800.00, 'has_bed' => true, 'sort_order' => 4],
                    ],
                ],
                [
                    'origin' => 'Batangas',
                    'destination' => 'Calapan',
                    'mode' => 'ferry',
                    'operator' => 'Starlite',
                    'trip_type' => 'local',
                    'schedules' => [
                        ['service_name' => 'Starlite Eagle', 'vehicle_name' => 'MV Starlite Eagle', 'plate_no' => 'STE-101', 'dep_time' => '08:00:00', 'duration' => 120, 'price' => 450.00],
                        ['service_name' => 'Starlite Pioneer', 'vehicle_name' => 'MV Starlite Pioneer', 'plate_no' => 'STP-102', 'dep_time' => '14:00:00', 'duration' => 120, 'price' => 450.00],
                        ['service_name' => 'Starlite Saturn', 'vehicle_name' => 'MV Starlite Saturn', 'plate_no' => 'STS-103', 'dep_time' => '20:00:00', 'duration' => 120, 'price' => 450.00],
                    ],
                    'accommodations' => [
                        ['name' => 'Reclining Seats', 'description' => 'Comfortable air-conditioned reclining seats.', 'price' => 450.00, 'has_bed' => false, 'sort_order' => 1],
                        ['name' => 'Economy Bed', 'description' => 'Air-conditioned bunk bed accommodation.', 'price' => 650.00, 'has_bed' => true, 'sort_order' => 2],
                        ['name' => 'Tourist Bed', 'description' => 'Spacious tourist class bed accommodation.', 'price' => 850.00, 'has_bed' => true, 'sort_order' => 3],
                        ['name' => 'VIP Cabin', 'description' => 'Private cabin with exclusive amenities and restroom.', 'price' => 1800.00, 'has_bed' => true, 'sort_order' => 4],
                    ],
                ],

                // 2. Batangas <-> Caticlan (2GO)
                [
                    'origin' => 'Batangas',
                    'destination' => 'Caticlan',
                    'mode' => 'ferry',
                    'operator' => '2GO',
                    'trip_type' => 'local',
                    'schedules' => [
                        ['service_name' => 'MV 2GO Maligaya', 'vehicle_name' => 'MV 2GO Maligaya', 'plate_no' => '2GO-201', 'dep_time' => '09:00:00', 'duration' => 540, 'price' => 1200.00],
                        ['service_name' => 'MV 2GO Masagana', 'vehicle_name' => 'MV 2GO Masagana', 'plate_no' => '2GO-202', 'dep_time' => '21:00:00', 'duration' => 540, 'price' => 1200.00],
                    ],
                    'accommodations' => [
                        ['name' => 'Super Value Class', 'description' => 'Budget-friendly open-air bunk beds.', 'price' => 1200.00, 'has_bed' => true, 'sort_order' => 1],
                        ['name' => 'Tourist Class', 'description' => 'Air-conditioned shared cabin bunk beds.', 'price' => 1500.00, 'has_bed' => true, 'sort_order' => 2],
                        ['name' => 'Cabin Class', 'description' => 'Shared 4-berth or 6-berth cabin with privacy.', 'price' => 2200.00, 'has_bed' => true, 'sort_order' => 3],
                        ['name' => 'State Room', 'description' => 'Private luxury suite with private bathroom and TV.', 'price' => 3500.00, 'has_bed' => true, 'sort_order' => 4],
                    ],
                ],
                [
                    'origin' => 'Caticlan',
                    'destination' => 'Batangas',
                    'mode' => 'ferry',
                    'operator' => '2GO',
                    'trip_type' => 'local',
                    'schedules' => [
                        ['service_name' => 'MV 2GO Maligaya', 'vehicle_name' => 'MV 2GO Maligaya', 'plate_no' => '2GO-201', 'dep_time' => '09:00:00', 'duration' => 540, 'price' => 1200.00],
                        ['service_name' => 'MV 2GO Masagana', 'vehicle_name' => 'MV 2GO Masagana', 'plate_no' => '2GO-202', 'dep_time' => '21:00:00', 'duration' => 540, 'price' => 1200.00],
                    ],
                    'accommodations' => [
                        ['name' => 'Super Value Class', 'description' => 'Budget-friendly open-air bunk beds.', 'price' => 1200.00, 'has_bed' => true, 'sort_order' => 1],
                        ['name' => 'Tourist Class', 'description' => 'Air-conditioned shared cabin bunk beds.', 'price' => 1500.00, 'has_bed' => true, 'sort_order' => 2],
                        ['name' => 'Cabin Class', 'description' => 'Shared 4-berth or 6-berth cabin with privacy.', 'price' => 2200.00, 'has_bed' => true, 'sort_order' => 3],
                        ['name' => 'State Room', 'description' => 'Private luxury suite with private bathroom and TV.', 'price' => 3500.00, 'has_bed' => true, 'sort_order' => 4],
                    ],
                ],

                // 3. Manila <-> Cebu (2GO)
                [
                    'origin' => 'Manila',
                    'destination' => 'Cebu',
                    'mode' => 'ferry',
                    'operator' => '2GO',
                    'trip_type' => 'local',
                    'schedules' => [
                        ['service_name' => 'MV St. Michael the Archangel', 'vehicle_name' => 'MV St. Michael the Archangel', 'plate_no' => 'SMA-301', 'dep_time' => '10:00:00', 'duration' => 1320, 'price' => 1800.00],
                        ['service_name' => 'MV St. Francis Xavier', 'vehicle_name' => 'MV St. Francis Xavier', 'plate_no' => 'SFX-302', 'dep_time' => '18:00:00', 'duration' => 1320, 'price' => 1800.00],
                    ],
                    'accommodations' => [
                        ['name' => 'Super Value Class', 'description' => 'Budget-friendly open-air bunk beds.', 'price' => 1800.00, 'has_bed' => true, 'sort_order' => 1],
                        ['name' => 'Tourist Class', 'description' => 'Air-conditioned shared cabin bunk beds.', 'price' => 2300.00, 'has_bed' => true, 'sort_order' => 2],
                        ['name' => 'Cabin Class', 'description' => 'Shared 4-berth cabin with comfort amenities.', 'price' => 3500.00, 'has_bed' => true, 'sort_order' => 3],
                        ['name' => 'State Room', 'description' => 'Private state room with en-suite bath and lounge.', 'price' => 5000.00, 'has_bed' => true, 'sort_order' => 4],
                    ],
                ],
                [
                    'origin' => 'Cebu',
                    'destination' => 'Manila',
                    'mode' => 'ferry',
                    'operator' => '2GO',
                    'trip_type' => 'local',
                    'schedules' => [
                        ['service_name' => 'MV St. Michael the Archangel', 'vehicle_name' => 'MV St. Michael the Archangel', 'plate_no' => 'SMA-301', 'dep_time' => '10:00:00', 'duration' => 1320, 'price' => 1800.00],
                        ['service_name' => 'MV St. Francis Xavier', 'vehicle_name' => 'MV St. Francis Xavier', 'plate_no' => 'SFX-302', 'dep_time' => '18:00:00', 'duration' => 1320, 'price' => 1800.00],
                    ],
                    'accommodations' => [
                        ['name' => 'Super Value Class', 'description' => 'Budget-friendly open-air bunk beds.', 'price' => 1800.00, 'has_bed' => true, 'sort_order' => 1],
                        ['name' => 'Tourist Class', 'description' => 'Air-conditioned shared cabin bunk beds.', 'price' => 2300.00, 'has_bed' => true, 'sort_order' => 2],
                        ['name' => 'Cabin Class', 'description' => 'Shared 4-berth cabin with comfort amenities.', 'price' => 3500.00, 'has_bed' => true, 'sort_order' => 3],
                        ['name' => 'State Room', 'description' => 'Private state room with en-suite bath and lounge.', 'price' => 5000.00, 'has_bed' => true, 'sort_order' => 4],
                    ],
                ],

                // 4. Roxas <-> Caticlan (Starlite)
                [
                    'origin' => 'Roxas',
                    'destination' => 'Caticlan',
                    'mode' => 'ferry',
                    'operator' => 'Starlite',
                    'trip_type' => 'local',
                    'schedules' => [
                        ['service_name' => 'Starlite Archer', 'vehicle_name' => 'MV Starlite Archer', 'plate_no' => 'STA-401', 'dep_time' => '06:00:00', 'duration' => 240, 'price' => 800.00],
                        ['service_name' => 'Starlite Venus', 'vehicle_name' => 'MV Starlite Venus', 'plate_no' => 'STV-402', 'dep_time' => '14:00:00', 'duration' => 240, 'price' => 800.00],
                    ],
                    'accommodations' => [
                        ['name' => 'Reclining Seats', 'description' => 'Air-conditioned comfortable reclining seats.', 'price' => 800.00, 'has_bed' => false, 'sort_order' => 1],
                        ['name' => 'Economy Bed', 'description' => 'Standard bunk bed accommodation.', 'price' => 1050.00, 'has_bed' => true, 'sort_order' => 2],
                        ['name' => 'Tourist Bed', 'description' => 'Premium tourist bed with curtains and charging port.', 'price' => 1350.00, 'has_bed' => true, 'sort_order' => 3],
                    ],
                ],
                [
                    'origin' => 'Caticlan',
                    'destination' => 'Roxas',
                    'mode' => 'ferry',
                    'operator' => 'Starlite',
                    'trip_type' => 'local',
                    'schedules' => [
                        ['service_name' => 'Starlite Archer', 'vehicle_name' => 'MV Starlite Archer', 'plate_no' => 'STA-401', 'dep_time' => '06:00:00', 'duration' => 240, 'price' => 800.00],
                        ['service_name' => 'Starlite Venus', 'vehicle_name' => 'MV Starlite Venus', 'plate_no' => 'STV-402', 'dep_time' => '14:00:00', 'duration' => 240, 'price' => 800.00],
                    ],
                    'accommodations' => [
                        ['name' => 'Reclining Seats', 'description' => 'Air-conditioned comfortable reclining seats.', 'price' => 800.00, 'has_bed' => false, 'sort_order' => 1],
                        ['name' => 'Economy Bed', 'description' => 'Standard bunk bed accommodation.', 'price' => 1050.00, 'has_bed' => true, 'sort_order' => 2],
                        ['name' => 'Tourist Bed', 'description' => 'Premium tourist bed with curtains and charging port.', 'price' => 1350.00, 'has_bed' => true, 'sort_order' => 3],
                    ],
                ],

                // 5. Manila <-> Cebu (Cebu Pacific)
                [
                    'origin' => 'Manila',
                    'destination' => 'Cebu',
                    'mode' => 'airline',
                    'operator' => 'Cebu Pacific',
                    'trip_type' => 'local',
                    'schedules' => [
                        ['service_name' => '5J 562', 'vehicle_name' => 'Airbus A320', 'plate_no' => 'RP-C3201', 'dep_time' => '07:30:00', 'duration' => 80, 'price' => 2500.00],
                        ['service_name' => '5J 564', 'vehicle_name' => 'Airbus A321', 'plate_no' => 'RP-C3202', 'dep_time' => '15:00:00', 'duration' => 80, 'price' => 2800.00],
                    ],
                    'accommodations' => [
                        ['name' => 'Standard', 'description' => 'Regular economy seat across main cabin.', 'price' => 2500.00, 'has_bed' => false, 'sort_order' => 1],
                        ['name' => 'Standard Plus', 'description' => 'Extra legroom seat near cabin front.', 'price' => 3300.00, 'has_bed' => false, 'sort_order' => 2],
                        ['name' => 'Premium', 'description' => 'Priority boarding and exit row extra legroom.', 'price' => 4000.00, 'has_bed' => false, 'sort_order' => 3],
                    ],
                ],
                [
                    'origin' => 'Cebu',
                    'destination' => 'Manila',
                    'mode' => 'airline',
                    'operator' => 'Cebu Pacific',
                    'trip_type' => 'local',
                    'schedules' => [
                        ['service_name' => '5J 563', 'vehicle_name' => 'Airbus A320', 'plate_no' => 'RP-C3201', 'dep_time' => '09:30:00', 'duration' => 80, 'price' => 2500.00],
                        ['service_name' => '5J 565', 'vehicle_name' => 'Airbus A321', 'plate_no' => 'RP-C3202', 'dep_time' => '17:00:00', 'duration' => 80, 'price' => 2800.00],
                    ],
                    'accommodations' => [
                        ['name' => 'Standard', 'description' => 'Regular economy seat across main cabin.', 'price' => 2500.00, 'has_bed' => false, 'sort_order' => 1],
                        ['name' => 'Standard Plus', 'description' => 'Extra legroom seat near cabin front.', 'price' => 3300.00, 'has_bed' => false, 'sort_order' => 2],
                        ['name' => 'Premium', 'description' => 'Priority boarding and exit row extra legroom.', 'price' => 4000.00, 'has_bed' => false, 'sort_order' => 3],
                    ],
                ],

                // 6. Manila <-> Davao (Philippine Airlines)
                [
                    'origin' => 'Manila',
                    'destination' => 'Davao',
                    'mode' => 'airline',
                    'operator' => 'Philippine Airlines',
                    'trip_type' => 'local',
                    'schedules' => [
                        ['service_name' => 'PR 1813', 'vehicle_name' => 'Airbus A320', 'plate_no' => 'RP-P3201', 'dep_time' => '06:00:00', 'duration' => 110, 'price' => 3500.00],
                        ['service_name' => 'PR 1815', 'vehicle_name' => 'Airbus A321', 'plate_no' => 'RP-P3202', 'dep_time' => '17:00:00', 'duration' => 110, 'price' => 3800.00],
                    ],
                    'accommodations' => [
                        ['name' => 'Economy Class', 'description' => 'Standard seating with complimentary snacks.', 'price' => 3500.00, 'has_bed' => false, 'sort_order' => 1],
                        ['name' => 'Premium Economy', 'description' => 'Extra legroom and priority baggage handling.', 'price' => 5000.00, 'has_bed' => false, 'sort_order' => 2],
                        ['name' => 'Business Class', 'description' => 'Luxury seating with gourmet dining and lounge access.', 'price' => 9000.00, 'has_bed' => false, 'sort_order' => 3],
                    ],
                ],
                [
                    'origin' => 'Davao',
                    'destination' => 'Manila',
                    'mode' => 'airline',
                    'operator' => 'Philippine Airlines',
                    'trip_type' => 'local',
                    'schedules' => [
                        ['service_name' => 'PR 1814', 'vehicle_name' => 'Airbus A320', 'plate_no' => 'RP-P3201', 'dep_time' => '08:30:00', 'duration' => 110, 'price' => 3500.00],
                        ['service_name' => 'PR 1816', 'vehicle_name' => 'Airbus A321', 'plate_no' => 'RP-P3202', 'dep_time' => '19:30:00', 'duration' => 110, 'price' => 3800.00],
                    ],
                    'accommodations' => [
                        ['name' => 'Economy Class', 'description' => 'Standard seating with complimentary snacks.', 'price' => 3500.00, 'has_bed' => false, 'sort_order' => 1],
                        ['name' => 'Premium Economy', 'description' => 'Extra legroom and priority baggage handling.', 'price' => 5000.00, 'has_bed' => false, 'sort_order' => 2],
                        ['name' => 'Business Class', 'description' => 'Luxury seating with gourmet dining and lounge access.', 'price' => 9000.00, 'has_bed' => false, 'sort_order' => 3],
                    ],
                ],

                // 7. Manila <-> Boracay (Caticlan) (AirAsia)
                [
                    'origin' => 'Manila',
                    'destination' => 'Boracay (Caticlan)',
                    'mode' => 'airline',
                    'operator' => 'AirAsia',
                    'trip_type' => 'local',
                    'schedules' => [
                        ['service_name' => 'Z2 221', 'vehicle_name' => 'Airbus A320', 'plate_no' => 'RP-A2211', 'dep_time' => '08:00:00', 'duration' => 65, 'price' => 2400.00],
                        ['service_name' => 'Z2 225', 'vehicle_name' => 'Airbus A320', 'plate_no' => 'RP-A2252', 'dep_time' => '14:30:00', 'duration' => 65, 'price' => 2600.00],
                    ],
                    'accommodations' => [
                        ['name' => 'Standard Seat', 'description' => 'Standard seating with 7kg cabin baggage included.', 'price' => 2400.00, 'has_bed' => false, 'sort_order' => 1],
                        ['name' => 'Hot Seat', 'description' => 'Extra legroom in rows 1-5 and exit rows + priority boarding.', 'price' => 3200.00, 'has_bed' => false, 'sort_order' => 2],
                    ],
                ],
                [
                    'origin' => 'Boracay (Caticlan)',
                    'destination' => 'Manila',
                    'mode' => 'airline',
                    'operator' => 'AirAsia',
                    'trip_type' => 'local',
                    'schedules' => [
                        ['service_name' => 'Z2 222', 'vehicle_name' => 'Airbus A320', 'plate_no' => 'RP-A2211', 'dep_time' => '10:00:00', 'duration' => 65, 'price' => 2400.00],
                        ['service_name' => 'Z2 226', 'vehicle_name' => 'Airbus A320', 'plate_no' => 'RP-A2252', 'dep_time' => '16:30:00', 'duration' => 65, 'price' => 2600.00],
                    ],
                    'accommodations' => [
                        ['name' => 'Standard Seat', 'description' => 'Standard seating with 7kg cabin baggage included.', 'price' => 2400.00, 'has_bed' => false, 'sort_order' => 1],
                        ['name' => 'Hot Seat', 'description' => 'Extra legroom in rows 1-5 and exit rows + priority boarding.', 'price' => 3200.00, 'has_bed' => false, 'sort_order' => 2],
                    ],
                ],

                // 8. International: Manila <-> Tokyo (Narita) (Philippine Airlines)
                [
                    'origin' => 'Manila',
                    'destination' => 'Tokyo (Narita)',
                    'mode' => 'airline',
                    'operator' => 'Philippine Airlines',
                    'trip_type' => 'international',
                    'schedules' => [
                        ['service_name' => 'PR 428', 'vehicle_name' => 'Airbus A321', 'plate_no' => 'RP-P4281', 'dep_time' => '06:55:00', 'duration' => 270, 'price' => 14500.00],
                        ['service_name' => 'PR 432', 'vehicle_name' => 'Airbus A330', 'plate_no' => 'RP-P4322', 'dep_time' => '14:20:00', 'duration' => 270, 'price' => 15800.00],
                    ],
                    'accommodations' => [
                        ['name' => 'Economy Class', 'description' => 'Standard international seating with meal service & baggage.', 'price' => 14500.00, 'has_bed' => false, 'sort_order' => 1],
                        ['name' => 'Premium Economy', 'description' => 'Extra legroom, priority check-in, and premium meals.', 'price' => 22000.00, 'has_bed' => false, 'sort_order' => 2],
                        ['name' => 'Business Class', 'description' => 'Lie-flat luxury seats, gourmet dining, and lounge access.', 'price' => 42000.00, 'has_bed' => false, 'sort_order' => 3],
                    ],
                ],
                [
                    'origin' => 'Tokyo (Narita)',
                    'destination' => 'Manila',
                    'mode' => 'airline',
                    'operator' => 'Philippine Airlines',
                    'trip_type' => 'international',
                    'schedules' => [
                        ['service_name' => 'PR 427', 'vehicle_name' => 'Airbus A321', 'plate_no' => 'RP-P4281', 'dep_time' => '13:30:00', 'duration' => 290, 'price' => 14500.00],
                        ['service_name' => 'PR 431', 'vehicle_name' => 'Airbus A330', 'plate_no' => 'RP-P4322', 'dep_time' => '20:15:00', 'duration' => 290, 'price' => 15800.00],
                    ],
                    'accommodations' => [
                        ['name' => 'Economy Class', 'description' => 'Standard international seating with meal service & baggage.', 'price' => 14500.00, 'has_bed' => false, 'sort_order' => 1],
                        ['name' => 'Premium Economy', 'description' => 'Extra legroom, priority check-in, and premium meals.', 'price' => 22000.00, 'has_bed' => false, 'sort_order' => 2],
                        ['name' => 'Business Class', 'description' => 'Lie-flat luxury seats, gourmet dining, and lounge access.', 'price' => 42000.00, 'has_bed' => false, 'sort_order' => 3],
                    ],
                ],

                // 9. International: Manila <-> Singapore (Cebu Pacific)
                [
                    'origin' => 'Manila',
                    'destination' => 'Singapore',
                    'mode' => 'airline',
                    'operator' => 'Cebu Pacific',
                    'trip_type' => 'international',
                    'schedules' => [
                        ['service_name' => '5J 813', 'vehicle_name' => 'Airbus A321', 'plate_no' => 'RP-C8131', 'dep_time' => '05:35:00', 'duration' => 230, 'price' => 5800.00],
                        ['service_name' => '5J 805', 'vehicle_name' => 'Airbus A330neo', 'plate_no' => 'RP-C8052', 'dep_time' => '13:30:00', 'duration' => 230, 'price' => 6500.00],
                    ],
                    'accommodations' => [
                        ['name' => 'Standard', 'description' => 'Regular economy seat across main cabin.', 'price' => 5800.00, 'has_bed' => false, 'sort_order' => 1],
                        ['name' => 'Standard Plus', 'description' => 'Extra legroom seat near front cabin.', 'price' => 7200.00, 'has_bed' => false, 'sort_order' => 2],
                        ['name' => 'Premium', 'description' => 'Priority boarding and exit row extra legroom.', 'price' => 8800.00, 'has_bed' => false, 'sort_order' => 3],
                    ],
                ],
                [
                    'origin' => 'Singapore',
                    'destination' => 'Manila',
                    'mode' => 'airline',
                    'operator' => 'Cebu Pacific',
                    'trip_type' => 'international',
                    'schedules' => [
                        ['service_name' => '5J 814', 'vehicle_name' => 'Airbus A321', 'plate_no' => 'RP-C8131', 'dep_time' => '10:45:00', 'duration' => 235, 'price' => 5800.00],
                        ['service_name' => '5J 806', 'vehicle_name' => 'Airbus A330neo', 'plate_no' => 'RP-C8052', 'dep_time' => '18:50:00', 'duration' => 235, 'price' => 6500.00],
                    ],
                    'accommodations' => [
                        ['name' => 'Standard', 'description' => 'Regular economy seat across main cabin.', 'price' => 5800.00, 'has_bed' => false, 'sort_order' => 1],
                        ['name' => 'Standard Plus', 'description' => 'Extra legroom seat near front cabin.', 'price' => 7200.00, 'has_bed' => false, 'sort_order' => 2],
                        ['name' => 'Premium', 'description' => 'Priority boarding and exit row extra legroom.', 'price' => 8800.00, 'has_bed' => false, 'sort_order' => 3],
                    ],
                ],

                // 10. International: Manila <-> Seoul (Incheon) (AirAsia)
                [
                    'origin' => 'Manila',
                    'destination' => 'Seoul (Incheon)',
                    'mode' => 'airline',
                    'operator' => 'AirAsia',
                    'trip_type' => 'international',
                    'schedules' => [
                        ['service_name' => 'Z2 884', 'vehicle_name' => 'Airbus A320', 'plate_no' => 'RP-A8841', 'dep_time' => '07:15:00', 'duration' => 245, 'price' => 6800.00],
                        ['service_name' => 'Z2 888', 'vehicle_name' => 'Airbus A321neo', 'plate_no' => 'RP-A8882', 'dep_time' => '17:40:00', 'duration' => 245, 'price' => 7500.00],
                    ],
                    'accommodations' => [
                        ['name' => 'Economy Class', 'description' => 'Standard international budget seat.', 'price' => 6800.00, 'has_bed' => false, 'sort_order' => 1],
                        ['name' => 'Hot Seats', 'description' => 'Forward and exit-row seating with priority boarding.', 'price' => 8500.00, 'has_bed' => false, 'sort_order' => 2],
                        ['name' => 'Premium Flatbed', 'description' => 'Fully reclining flatbed seat with complimentary baggage and meal.', 'price' => 18500.00, 'has_bed' => false, 'sort_order' => 3],
                    ],
                ],
                [
                    'origin' => 'Seoul (Incheon)',
                    'destination' => 'Manila',
                    'mode' => 'airline',
                    'operator' => 'AirAsia',
                    'trip_type' => 'international',
                    'schedules' => [
                        ['service_name' => 'Z2 885', 'vehicle_name' => 'Airbus A320', 'plate_no' => 'RP-A8841', 'dep_time' => '13:05:00', 'duration' => 250, 'price' => 6800.00],
                        ['service_name' => 'Z2 889', 'vehicle_name' => 'Airbus A321neo', 'plate_no' => 'RP-A8882', 'dep_time' => '23:25:00', 'duration' => 250, 'price' => 7500.00],
                    ],
                    'accommodations' => [
                        ['name' => 'Economy Class', 'description' => 'Standard international budget seat.', 'price' => 6800.00, 'has_bed' => false, 'sort_order' => 1],
                        ['name' => 'Hot Seats', 'description' => 'Forward and exit-row seating with priority boarding.', 'price' => 8500.00, 'has_bed' => false, 'sort_order' => 2],
                        ['name' => 'Premium Flatbed', 'description' => 'Fully reclining flatbed seat with complimentary baggage and meal.', 'price' => 18500.00, 'has_bed' => false, 'sort_order' => 3],
                    ],
                ],

                // 11. International: Manila <-> Hong Kong (Philippine Airlines)
                [
                    'origin' => 'Manila',
                    'destination' => 'Hong Kong',
                    'mode' => 'airline',
                    'operator' => 'Philippine Airlines',
                    'trip_type' => 'international',
                    'schedules' => [
                        ['service_name' => 'PR 300', 'vehicle_name' => 'Airbus A321', 'plate_no' => 'RP-P3001', 'dep_time' => '07:55:00', 'duration' => 135, 'price' => 7800.00],
                        ['service_name' => 'PR 310', 'vehicle_name' => 'Airbus A330', 'plate_no' => 'RP-P3102', 'dep_time' => '19:00:00', 'duration' => 135, 'price' => 8400.00],
                    ],
                    'accommodations' => [
                        ['name' => 'Economy Class', 'description' => 'Standard international seating with meal service & baggage.', 'price' => 7800.00, 'has_bed' => false, 'sort_order' => 1],
                        ['name' => 'Premium Economy', 'description' => 'Extra legroom, priority check-in, and premium meals.', 'price' => 11500.00, 'has_bed' => false, 'sort_order' => 2],
                        ['name' => 'Business Class', 'description' => 'Luxury recliner seats, gourmet dining, and lounge access.', 'price' => 21000.00, 'has_bed' => false, 'sort_order' => 3],
                    ],
                ],
                [
                    'origin' => 'Hong Kong',
                    'destination' => 'Manila',
                    'mode' => 'airline',
                    'operator' => 'Philippine Airlines',
                    'trip_type' => 'international',
                    'schedules' => [
                        ['service_name' => 'PR 301', 'vehicle_name' => 'Airbus A321', 'plate_no' => 'RP-P3001', 'dep_time' => '11:15:00', 'duration' => 135, 'price' => 7800.00],
                        ['service_name' => 'PR 311', 'vehicle_name' => 'Airbus A330', 'plate_no' => 'RP-P3102', 'dep_time' => '22:20:00', 'duration' => 135, 'price' => 8400.00],
                    ],
                    'accommodations' => [
                        ['name' => 'Economy Class', 'description' => 'Standard international seating with meal service & baggage.', 'price' => 7800.00, 'has_bed' => false, 'sort_order' => 1],
                        ['name' => 'Premium Economy', 'description' => 'Extra legroom, priority check-in, and premium meals.', 'price' => 11500.00, 'has_bed' => false, 'sort_order' => 2],
                        ['name' => 'Business Class', 'description' => 'Luxury recliner seats, gourmet dining, and lounge access.', 'price' => 21000.00, 'has_bed' => false, 'sort_order' => 3],
                    ],
                ],
            ];

            // Generate schedules for dates from today until 14 days ahead (2 weeks)
            $startDate = Carbon::today();
            $endDate = Carbon::today()->addDays(14);

            $allAccRecords = [];
            $allPivotRecords = [];
            $now = Carbon::now();

            $operators = \App\Models\Operator::all();
            $resolveOperator = function (?string $name) use ($operators): ?\App\Models\Operator {
                if (empty($name)) return null;
                $exact = $operators->firstWhere('name', $name);
                if ($exact) return $exact;
                $lower = strtolower(trim($name));
                if (str_contains($lower, 'starlite')) return $operators->first(fn($o) => stripos($o->name, 'Starlite') !== false);
                if (str_contains($lower, 'pal') || str_contains($lower, 'philippine')) return $operators->first(fn($o) => stripos($o->name, 'Philippine') !== false);
                if (str_contains($lower, 'cebu') || str_contains($lower, 'cebpac')) return $operators->first(fn($o) => stripos($o->name, 'Cebu') !== false);
                if (str_contains($lower, 'airasia')) return $operators->first(fn($o) => stripos($o->name, 'AirAsia') !== false);
                if (str_contains($lower, '2go')) return $operators->first(fn($o) => stripos($o->name, '2GO') !== false);
                return null;
            };

            foreach ($routesData as $rData) {
                $operator = $resolveOperator($rData['operator']);
                $operatorName = $operator ? $operator->name : $rData['operator'];
                $operatorId = $operator?->id;

                // 1. Prepare TransportClass records for this operator & accommodations
                $transportClasses = [];
                foreach ($rData['accommodations'] as $accData) {
                    $code = str($accData['name'])->slug()->value();
                    $tc = TransportClass::updateOrCreate(
                        [
                            'operator' => $operatorName,
                            'code' => $code,
                        ],
                        [
                            'operator_id' => $operatorId,
                            'name' => $accData['name'],
                            'description' => $accData['description'] ?? null,
                            'price' => $accData['price'] ?? 0,
                            'is_active' => true,
                            'sort_order' => $accData['sort_order'] ?? 1,
                        ]
                    );
                    $transportClasses[$accData['name']] = $tc;
                }

                // 2. Create distinct route for each vehicle and generate its schedules
                foreach ($rData['schedules'] as $sData) {
                    $vehicleName = $sData['vehicle_name'] ?? $sData['service_name'];

                    // Match vehicle for this operator
                    $vehicle = Vehicle::query()
                        ->where(function($q) use ($operatorId, $operatorName, $rData) {
                            if ($operatorId) $q->where('operator_id', $operatorId);
                            $q->orWhere('operator', $operatorName)->orWhere('operator', $rData['operator']);
                        })
                        ->where(function($q) use ($sData, $vehicleName) {
                            $q->where('name', $vehicleName)
                              ->orWhere('name', 'like', "%{$vehicleName}%")
                              ->orWhere('vehicle_id', $sData['plate_no'] ?? '');
                        })
                        ->first();

                    if (! $vehicle) {
                        $vehicle = Vehicle::where('name', 'like', "%{$vehicleName}%")->first();
                    }

                    if (! $vehicle) {
                        $vehicle = Vehicle::create([
                            'vehicle_id' => $sData['plate_no'] ?? \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(7)),
                            'name' => $vehicleName,
                            'type' => ($rData['mode'] === 'airline' ? 'airline' : 'ferry'),
                            'operator' => $operatorName,
                            'operator_id' => $operatorId,
                            'is_active' => true,
                        ]);
                    } elseif ($operatorId && $vehicle->operator_id !== $operatorId) {
                        $vehicle->update(['operator_id' => $operatorId, 'operator' => $operatorName]);
                    }

                    // Create or update FerryRoute per distinct (origin, destination, mode, vehicle)
                    $route = FerryRoute::firstOrCreate(
                        [
                            'origin' => $rData['origin'],
                            'destination' => $rData['destination'],
                            'mode' => $rData['mode'],
                            'vehicle_id' => $vehicle->id,
                        ],
                        [
                            'operator' => $operatorName,
                            'operator_id' => $operatorId,
                            'trip_type' => $rData['trip_type'],
                            'is_active' => true,
                        ]
                    );

                    if ($route->operator_id !== $operatorId || $route->operator !== $operatorName) {
                        $route->update(['operator_id' => $operatorId, 'operator' => $operatorName]);
                    }

                    // 3. Create daily schedules across the date range
                    for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                        $departureTime = Carbon::parse($date->format('Y-m-d') . ' ' . $sData['dep_time']);
                        $arrivalTime = $departureTime->copy()->addMinutes($sData['duration']);

                        $schedule = Schedule::updateOrCreate(
                            [
                                'ferry_route_id' => $route->id,
                                'service_name' => $sData['service_name'],
                                'departure_time' => $departureTime,
                            ],
                            [
                                'vehicle_name' => $sData['vehicle_name'],
                                'plate_no' => $sData['plate_no'],
                                'arrival_time' => $arrivalTime,
                                'duration_minutes' => $sData['duration'],
                                'price' => $sData['price'],
                                'availability_label' => 'Available',
                                'seat_rows' => 15,
                                'seat_columns' => ['A', 'B', 'C', 'D', 'E', 'F'],
                                'is_active' => true,
                            ]
                        );

                        // Collect accommodation records for bulk insertion
                        foreach ($rData['accommodations'] as $accData) {
                            $allAccRecords[] = [
                                'schedule_id' => $schedule->id,
                                'name' => $accData['name'],
                                'description' => $accData['description'] ?? null,
                                'price' => $accData['price'] ?? 0,
                                'tickets_available' => 50,
                                'has_bed' => $accData['has_bed'] ?? false,
                                'is_active' => true,
                                'sort_order' => $accData['sort_order'] ?? 1,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];

                            if (isset($transportClasses[$accData['name']])) {
                                $tc = $transportClasses[$accData['name']];
                                $allPivotRecords[] = [
                                    'schedule_id' => $schedule->id,
                                    'transport_class_id' => $tc->id,
                                    'additional_price' => $accData['price'] ?? 0,
                                    'tickets_available' => 50,
                                    'description' => $accData['description'] ?? null,
                                    'has_bed' => $accData['has_bed'] ?? false,
                                    'is_active' => true,
                                    'created_at' => $now,
                                    'updated_at' => $now,
                                ];
                            }
                        }
                    }
                }
            }

            // Bulk insert all accommodations & pivot entries in chunks for maximum performance
            foreach (array_chunk($allAccRecords, 500) as $chunk) {
                DB::table('schedule_accommodations')->insert($chunk);
            }

            foreach (array_chunk($allPivotRecords, 500) as $chunk) {
                DB::table('schedule_transport_class')->insert($chunk);
            }
        });
    }
}

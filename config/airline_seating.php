<?php

return [
    'operators' => [
        'Cebu Pacific' => [
            'classes' => [
                'premium' => [
                    'name' => 'Premium',
                    'description' => 'Extra legroom seats near emergency exits and lavatories.',
                    'price' => 1500,
                    'sort_order' => 1,
                    'columns' => ['A', 'B', 'C', 'D', 'E', 'F'],
                ],
                'standard-plus' => [
                    'name' => 'Standard Plus',
                    'description' => 'Extra legroom seats without priority boarding.',
                    'price' => 800,
                    'sort_order' => 2,
                    'columns' => ['A', 'B', 'C', 'D', 'E', 'F'],
                ],
                'standard' => [
                    'name' => 'Standard',
                    'description' => 'Regular economy seats across the main cabin.',
                    'price' => 0,
                    'sort_order' => 3,
                    'columns' => ['A', 'B', 'C', 'D', 'E', 'F'],
                ],
            ],
            'aircraft' => [
                'Airbus A320' => [
                    'capacity' => 180,
                    'class_order' => ['premium', 'standard-plus', 'standard'],
                    'seat_counts' => ['premium' => 12, 'standard-plus' => 24, 'standard' => 144],
                ],
                'Airbus A321' => [
                    'capacity' => 236,
                    'class_order' => ['premium', 'standard-plus', 'standard'],
                    'seat_counts' => ['premium' => 16, 'standard-plus' => 32, 'standard' => 188],
                ],
                'Airbus A330neo' => [
                    'capacity' => 459,
                    'class_order' => ['premium', 'standard-plus', 'standard'],
                    'seat_counts' => ['premium' => 24, 'standard-plus' => 48, 'standard' => 387],
                ],
                'ATR 72-600' => [
                    'capacity' => 78,
                    'class_order' => ['premium', 'standard-plus', 'standard'],
                    'seat_counts' => ['premium' => 6, 'standard-plus' => 12, 'standard' => 60],
                ],
            ],
        ],
        'Philippines AirAsia' => [
            'classes' => [
                'premium-flatbed' => [
                    'name' => 'Premium Flatbed',
                    'description' => 'Fully reclining flatbed seats with long-haul premium inclusions.',
                    'price' => 8000,
                    'sort_order' => 1,
                    'columns' => ['A', 'B', 'C', 'D'],
                ],
                'hot-seat' => [
                    'name' => 'Hot Seats',
                    'description' => 'Forward and exit-row seats with extra legroom and priority boarding.',
                    'price' => 1500,
                    'sort_order' => 2,
                    'columns' => ['A', 'B', 'C', 'D', 'E', 'F'],
                ],
                'economy' => [
                    'name' => 'Economy Class',
                    'description' => 'Standard budget-friendly seats for short and medium-haul flights.',
                    'price' => 0,
                    'sort_order' => 3,
                    'columns' => ['A', 'B', 'C', 'D', 'E', 'F'],
                ],
            ],
            'aircraft' => [
                'Airbus A320' => [
                    'capacity' => 180,
                    'class_order' => ['hot-seat', 'economy'],
                    'seat_counts' => ['hot-seat' => 24, 'economy' => 156],
                ],
                'Airbus A321neo' => [
                    'capacity' => 236,
                    'class_order' => ['hot-seat', 'economy'],
                    'seat_counts' => ['hot-seat' => 32, 'economy' => 204],
                ],
                'Airbus A330' => [
                    'capacity' => 377,
                    'class_order' => ['premium-flatbed', 'hot-seat', 'economy'],
                    'seat_counts' => ['premium-flatbed' => 12, 'hot-seat' => 24, 'economy' => 341],
                ],
            ],
        ],
        'Philippine Airline' => [
            'classes' => [
                'business' => [
                    'name' => 'Business Class',
                    'description' => 'Lie-flat or premium recliner seating with elevated dining and lounge-style comfort.',
                    'price' => 10000,
                    'sort_order' => 1,
                    'columns' => ['A', 'B', 'C', 'D'],
                ],
                'premium-economy' => [
                    'name' => 'Premium Economy / Comfort Class',
                    'description' => 'Wider seats, extra legroom, more recline, and priority handling.',
                    'price' => 3000,
                    'sort_order' => 2,
                    'columns' => ['A', 'B', 'C', 'D', 'E', 'F'],
                ],
                'economy' => [
                    'name' => 'Economy Class',
                    'description' => 'Standard seating with entertainment or charging availability depending on aircraft.',
                    'price' => 0,
                    'sort_order' => 3,
                    'columns' => ['A', 'B', 'C', 'D', 'E', 'F'],
                ],
            ],
            'aircraft' => [
                'Airbus A320' => [
                    'capacity' => 180,
                    'class_order' => ['premium-economy', 'economy'],
                    'seat_counts' => ['premium-economy' => 12, 'economy' => 168],
                ],
                'Airbus A321' => [
                    'capacity' => 236,
                    'class_order' => ['premium-economy', 'economy'],
                    'seat_counts' => ['premium-economy' => 12, 'economy' => 224],
                ],
                'Airbus A330-300' => [
                    'capacity' => 363,
                    'class_order' => ['business', 'premium-economy', 'economy'],
                    'seat_counts' => ['business' => 18, 'premium-economy' => 36, 'economy' => 309],
                ],
                'Airbus A350' => [
                    'capacity' => 380,
                    'class_order' => ['business', 'premium-economy', 'economy'],
                    'seat_counts' => ['business' => 28, 'premium-economy' => 24, 'economy' => 328],
                ],
                'Boeing 777-300ER' => [
                    'capacity' => 393,
                    'class_order' => ['business', 'premium-economy', 'economy'],
                    'seat_counts' => ['business' => 42, 'premium-economy' => 24, 'economy' => 327],
                ],
                'De Havilland Dash 8-Q400' => [
                    'capacity' => 78, // <-- confirm actual seat count
                    'class_order' => ['premium-economy', 'economy'],
                    'seat_counts' => ['premium-economy' => 8, 'economy' => 70], // <-- confirm actual split
                ],
            ],
        ],
    ],
];

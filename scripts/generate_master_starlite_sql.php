<?php

/**
 * MASTER STARLITE SYNC GENERATOR
 * - Full Timetable from July 2026
 * - Excludes: Roxas Mindoro <-> Odiongan AND all LCT trips
 * - Exact Rates from April 13, 2026 Tariff PDF
 * - Safe bulk INSERT IGNORE to prevent duplicate errors and preserve existing records
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Str;

$startDate = Carbon::parse('2026-09-21');
$endDate = Carbon::parse('2026-12-31');
$now = Carbon::now()->format('Y-m-d H:i:s');

// Accommodations matrix by canonical pair
$fareMatrix = [
    'Batangas|Calapan' => [
        'base_price' => 680.00,
        'accommodations' => [
            ['name' => 'Reclining Seat', 'desc' => 'Air-conditioned reclining passenger seats.', 'price' => 680.00, 'has_bed' => 0, 'sort' => 1],
            ['name' => 'Economy Bed Bunk', 'desc' => 'Open-air bunk bed accommodation.', 'price' => 680.00, 'has_bed' => 1, 'sort' => 2],
            ['name' => 'Tourist Bed Bunk', 'desc' => 'Air-conditioned bunk bed accommodation.', 'price' => 680.00, 'has_bed' => 1, 'sort' => 3],
        ],
    ],
    'Batangas|Caticlan' => [
        'base_price' => 2170.00,
        'accommodations' => [
            ['name' => 'Reclining Seat', 'desc' => 'Comfortable reclining seat.', 'price' => 2170.00, 'has_bed' => 0, 'sort' => 1],
            ['name' => 'Economy Bed Bunk', 'desc' => 'Air-conditioned lower/upper bunk.', 'price' => 2270.00, 'has_bed' => 1, 'sort' => 2],
            ['name' => 'Tourist Bed Bunk', 'desc' => 'Spacious tourist class bed.', 'price' => 2790.00, 'has_bed' => 1, 'sort' => 3],
            ['name' => 'Cabin', 'desc' => 'Shared 4-berth cabin with privacy.', 'price' => 3720.00, 'has_bed' => 1, 'sort' => 4],
            ['name' => 'VIP Room (2-3 pax)', 'desc' => 'Exclusive VIP room with en-suite bath.', 'price' => 8300.00, 'has_bed' => 1, 'sort' => 5],
            ['name' => 'VIP Room (5 pax)', 'desc' => 'VIP stateroom for up to 5 persons.', 'price' => 14400.00, 'has_bed' => 1, 'sort' => 6],
        ],
    ],
    'Roxas Mindoro|Caticlan' => [
        'base_price' => 1340.00,
        'accommodations' => [
            ['name' => 'Reclining Seat', 'desc' => 'Comfortable reclining seat.', 'price' => 1340.00, 'has_bed' => 0, 'sort' => 1],
            ['name' => 'Economy Bed Bunk', 'desc' => 'Air-conditioned bunk bed.', 'price' => 1340.00, 'has_bed' => 1, 'sort' => 2],
            ['name' => 'Tourist Bed Bunk', 'desc' => 'Spacious tourist class bed.', 'price' => 1550.00, 'has_bed' => 1, 'sort' => 3],
            ['name' => 'Cabin', 'desc' => 'Shared 4-berth cabin.', 'price' => 1750.00, 'has_bed' => 1, 'sort' => 4],
            ['name' => 'VIP Room (2-3 pax)', 'desc' => 'Private VIP room with bath.', 'price' => 4400.00, 'has_bed' => 1, 'sort' => 5],
        ],
    ],
    'Batangas|Roxas City, Capiz' => [
        'base_price' => 2580.00,
        'accommodations' => [
            ['name' => 'Reclining Seat', 'desc' => 'Comfortable reclining seat.', 'price' => 2580.00, 'has_bed' => 0, 'sort' => 1],
            ['name' => 'Economy Bed Bunk', 'desc' => 'Air-conditioned bunk bed.', 'price' => 2580.00, 'has_bed' => 1, 'sort' => 2],
            ['name' => 'Tourist Bed Bunk', 'desc' => 'Spacious tourist class bed.', 'price' => 3200.00, 'has_bed' => 1, 'sort' => 3],
            ['name' => 'Cabin', 'desc' => 'Shared cabin.', 'price' => 3820.00, 'has_bed' => 1, 'sort' => 4],
            ['name' => 'VIP Room (2-3 pax)', 'desc' => 'Exclusive private VIP room.', 'price' => 11500.00, 'has_bed' => 1, 'sort' => 5],
        ],
    ],
    'Batangas|Romblon' => [
        'base_price' => 1240.00,
        'accommodations' => [
            ['name' => 'Reclining Seat', 'desc' => 'Comfortable reclining seat.', 'price' => 1240.00, 'has_bed' => 0, 'sort' => 1],
            ['name' => 'Economy Bed Bunk', 'desc' => 'Air-conditioned bunk bed.', 'price' => 1240.00, 'has_bed' => 1, 'sort' => 2],
            ['name' => 'Tourist Bed Bunk', 'desc' => 'Spacious tourist class bed.', 'price' => 1860.00, 'has_bed' => 1, 'sort' => 3],
            ['name' => 'Cabin', 'desc' => 'Shared cabin.', 'price' => 2790.00, 'has_bed' => 1, 'sort' => 4],
            ['name' => 'VIP Room (2-3 pax)', 'desc' => 'Private VIP room with bath.', 'price' => 8300.00, 'has_bed' => 1, 'sort' => 5],
        ],
    ],
    'Batangas|Sibuyan (Magdiwang)' => [
        'base_price' => 1240.00,
        'accommodations' => [
            ['name' => 'Reclining Seat', 'desc' => 'Comfortable reclining seat.', 'price' => 1240.00, 'has_bed' => 0, 'sort' => 1],
            ['name' => 'Economy Bed Bunk', 'desc' => 'Air-conditioned bunk bed.', 'price' => 1240.00, 'has_bed' => 1, 'sort' => 2],
            ['name' => 'Tourist Bed Bunk', 'desc' => 'Spacious tourist class bed.', 'price' => 1860.00, 'has_bed' => 1, 'sort' => 3],
            ['name' => 'Cabin', 'desc' => 'Shared cabin.', 'price' => 3200.00, 'has_bed' => 1, 'sort' => 4],
            ['name' => 'VIP Room (2-3 pax)', 'desc' => 'Private VIP room with bath.', 'price' => 9600.00, 'has_bed' => 1, 'sort' => 5],
        ],
    ],
    'Batangas|Cajidiocan' => [
        'base_price' => 1550.00,
        'accommodations' => [
            ['name' => 'Reclining Seat', 'desc' => 'Comfortable reclining seat.', 'price' => 1550.00, 'has_bed' => 0, 'sort' => 1],
            ['name' => 'Economy Bed Bunk', 'desc' => 'Air-conditioned bunk bed.', 'price' => 1550.00, 'has_bed' => 1, 'sort' => 2],
            ['name' => 'Tourist Bed Bunk', 'desc' => 'Spacious tourist class bed.', 'price' => 2170.00, 'has_bed' => 1, 'sort' => 3],
            ['name' => 'Cabin', 'desc' => 'Shared cabin.', 'price' => 3410.00, 'has_bed' => 1, 'sort' => 4],
            ['name' => 'VIP Room (2-3 pax)', 'desc' => 'Private VIP room with bath.', 'price' => 9900.00, 'has_bed' => 1, 'sort' => 5],
        ],
    ],
    'Batangas|Odiongan' => [
        'base_price' => 1380.00,
        'accommodations' => [
            ['name' => 'Reclining Seat', 'desc' => 'Comfortable reclining seat.', 'price' => 1380.00, 'has_bed' => 0, 'sort' => 1],
            ['name' => 'Economy Bed Bunk', 'desc' => 'Air-conditioned bunk bed.', 'price' => 1380.00, 'has_bed' => 1, 'sort' => 2],
            ['name' => 'Tourist Bed Bunk', 'desc' => 'Spacious tourist class bed.', 'price' => 2070.00, 'has_bed' => 1, 'sort' => 3],
            ['name' => 'Cabin', 'desc' => 'Shared cabin.', 'price' => 3105.00, 'has_bed' => 1, 'sort' => 4],
            ['name' => 'VIP Room (2-3 pax)', 'desc' => 'Private VIP room with bath.', 'price' => 9315.00, 'has_bed' => 1, 'sort' => 5],
        ],
    ],
    'Odiongan|Caticlan' => [
        'base_price' => 863.00,
        'accommodations' => [
            ['name' => 'Reclining Seat', 'desc' => 'Comfortable reclining seat.', 'price' => 863.00, 'has_bed' => 0, 'sort' => 1],
            ['name' => 'Economy Bed Bunk', 'desc' => 'Air-conditioned bunk bed.', 'price' => 863.00, 'has_bed' => 1, 'sort' => 2],
            ['name' => 'Tourist Bed Bunk', 'desc' => 'Spacious tourist class bed.', 'price' => 1035.00, 'has_bed' => 1, 'sort' => 3],
            ['name' => 'Cabin', 'desc' => 'Shared cabin.', 'price' => 1725.00, 'has_bed' => 1, 'sort' => 4],
            ['name' => 'VIP Room (2-3 pax)', 'desc' => 'Private VIP room with bath.', 'price' => 4945.00, 'has_bed' => 1, 'sort' => 5],
        ],
    ],
    'Romblon|Sibuyan (Magdiwang)' => [
        'base_price' => 445.00,
        'accommodations' => [
            ['name' => 'Reclining Seat', 'desc' => 'Comfortable reclining seat.', 'price' => 445.00, 'has_bed' => 0, 'sort' => 1],
            ['name' => 'Economy Bed Bunk', 'desc' => 'Air-conditioned bunk bed.', 'price' => 445.00, 'has_bed' => 1, 'sort' => 2],
            ['name' => 'Tourist Bed Bunk', 'desc' => 'Tourist class bed.', 'price' => 445.00, 'has_bed' => 1, 'sort' => 3],
            ['name' => 'Cabin', 'desc' => 'Shared cabin.', 'price' => 620.00, 'has_bed' => 1, 'sort' => 4],
        ],
    ],
    'Sibuyan (Magdiwang)|Roxas City, Capiz' => [
        'base_price' => 1035.00,
        'accommodations' => [
            ['name' => 'Reclining Seat', 'desc' => 'Comfortable reclining seat.', 'price' => 1035.00, 'has_bed' => 0, 'sort' => 1],
            ['name' => 'Economy Bed Bunk', 'desc' => 'Air-conditioned bunk bed.', 'price' => 1035.00, 'has_bed' => 1, 'sort' => 2],
            ['name' => 'Tourist Bed Bunk', 'desc' => 'Tourist class bed.', 'price' => 1135.00, 'has_bed' => 1, 'sort' => 3],
            ['name' => 'Cabin', 'desc' => 'Shared cabin.', 'price' => 1550.00, 'has_bed' => 1, 'sort' => 4],
            ['name' => 'VIP Room (2-3 pax)', 'desc' => 'Private VIP room.', 'price' => 4400.00, 'has_bed' => 1, 'sort' => 5],
        ],
    ],
    'Romblon|Roxas City, Capiz' => [
        'base_price' => 1550.00,
        'accommodations' => [
            ['name' => 'Reclining Seat', 'desc' => 'Comfortable reclining seat.', 'price' => 1550.00, 'has_bed' => 0, 'sort' => 1],
            ['name' => 'Economy Bed Bunk', 'desc' => 'Air-conditioned bunk bed.', 'price' => 1550.00, 'has_bed' => 1, 'sort' => 2],
            ['name' => 'Tourist Bed Bunk', 'desc' => 'Tourist class bed.', 'price' => 1750.00, 'has_bed' => 1, 'sort' => 3],
            ['name' => 'Cabin', 'desc' => 'Shared cabin.', 'price' => 2170.00, 'has_bed' => 1, 'sort' => 4],
            ['name' => 'VIP Room (2-3 pax)', 'desc' => 'Private VIP room.', 'price' => 6500.00, 'has_bed' => 1, 'sort' => 5],
        ],
    ],
    'Romblon|Cajidiocan' => [
        'base_price' => 445.00,
        'accommodations' => [
            ['name' => 'Reclining Seat', 'desc' => 'Comfortable reclining seat.', 'price' => 445.00, 'has_bed' => 0, 'sort' => 1],
            ['name' => 'Economy Bed Bunk', 'desc' => 'Air-conditioned bunk bed.', 'price' => 445.00, 'has_bed' => 1, 'sort' => 2],
            ['name' => 'Tourist Bed Bunk', 'desc' => 'Tourist class bed.', 'price' => 600.00, 'has_bed' => 1, 'sort' => 3],
        ],
    ],
    'Cajidiocan|Roxas City, Capiz' => [
        'base_price' => 1035.00,
        'accommodations' => [
            ['name' => 'Reclining Seat', 'desc' => 'Comfortable reclining seat.', 'price' => 1035.00, 'has_bed' => 0, 'sort' => 1],
            ['name' => 'Economy Bed Bunk', 'desc' => 'Air-conditioned bunk bed.', 'price' => 1035.00, 'has_bed' => 1, 'sort' => 2],
            ['name' => 'Tourist Bed Bunk', 'desc' => 'Tourist class bed.', 'price' => 1135.00, 'has_bed' => 1, 'sort' => 3],
            ['name' => 'Cabin', 'desc' => 'Shared cabin.', 'price' => 1550.00, 'has_bed' => 1, 'sort' => 4],
        ],
    ],
    'Cebu|Surigao' => [
        'base_price' => 1550.00,
        'accommodations' => [
            ['name' => 'Reclining Seat', 'desc' => 'Comfortable reclining seat.', 'price' => 1550.00, 'has_bed' => 0, 'sort' => 1],
            ['name' => 'Economy Bed Bunk', 'desc' => 'Air-conditioned bunk bed.', 'price' => 1650.00, 'has_bed' => 1, 'sort' => 2],
            ['name' => 'Tourist Bed Bunk', 'desc' => 'Tourist class bed.', 'price' => 1960.00, 'has_bed' => 1, 'sort' => 3],
            ['name' => 'Cabin', 'desc' => 'Shared cabin.', 'price' => 2380.00, 'has_bed' => 1, 'sort' => 4],
            ['name' => 'VIP Room (2-3 pax)', 'desc' => 'Private VIP room.', 'price' => 7700.00, 'has_bed' => 1, 'sort' => 5],
        ],
    ],
    'Cebu|Dapitan' => [
        'base_price' => 1130.00,
        'accommodations' => [
            ['name' => 'Reclining Seat', 'desc' => 'Comfortable reclining seat.', 'price' => 1130.00, 'has_bed' => 0, 'sort' => 1],
            ['name' => 'Economy Bed Bunk', 'desc' => 'Air-conditioned bunk bed.', 'price' => 1440.00, 'has_bed' => 1, 'sort' => 2],
            ['name' => 'Tourist Bed Bunk', 'desc' => 'Tourist class bed.', 'price' => 1860.00, 'has_bed' => 1, 'sort' => 3],
            ['name' => 'Cabin', 'desc' => 'Shared cabin.', 'price' => 2270.00, 'has_bed' => 1, 'sort' => 4],
            ['name' => 'VIP Room (2-3 pax)', 'desc' => 'Private VIP room.', 'price' => 7700.00, 'has_bed' => 1, 'sort' => 5],
        ],
    ],
    'Roxas Mindoro|Buruanga' => [
        'base_price' => 1200.00,
        'accommodations' => [
            ['name' => 'Reclining Seat', 'desc' => 'Comfortable reclining seat.', 'price' => 1200.00, 'has_bed' => 0, 'sort' => 1],
            ['name' => 'Economy Bed Bunk', 'desc' => 'Air-conditioned bunk bed.', 'price' => 1200.00, 'has_bed' => 1, 'sort' => 2],
            ['name' => 'Tourist Bed Bunk', 'desc' => 'Tourist class bed.', 'price' => 1400.00, 'has_bed' => 1, 'sort' => 3],
        ],
    ],
];

// Helper to lookup fare matrix
$getFareConfig = function($origin, $dest) use ($fareMatrix) {
    $keys = ["{$origin}|{$dest}", "{$dest}|{$origin}"];
    foreach ($keys as $k) {
        if (isset($fareMatrix[$k])) return $fareMatrix[$k];
    }
    return ['base_price' => 680.00, 'accommodations' => []];
};

// All Operational Rules from July 2026 Timetable
$rules = [
    // 1. Batangas <-> Calapan (ROPAX - 12x/day)
    ['origin' => 'Batangas', 'destination' => 'Calapan', 'vessel' => 'MV Starlite Annapolis', 'plate' => 'STA-101', 'dep_times' => ['01:00:00','03:00:00','05:00:00','07:00:00','09:00:00','11:00:00','13:00:00','15:00:00','17:00:00','19:00:00','21:00:00','23:00:00'], 'duration' => 180, 'active_days' => 'all'],
    ['origin' => 'Calapan', 'destination' => 'Batangas', 'vessel' => 'MV Starlite Jupiter', 'plate' => 'STJ-102', 'dep_times' => ['01:00:00','03:00:00','05:00:00','07:00:00','09:00:00','11:00:00','13:00:00','15:00:00','17:00:00','19:00:00','21:00:00','23:00:00'], 'duration' => 180, 'active_days' => 'all'],

    // 2. Batangas <-> Calapan (Fastcraft - 3x/day)
    ['origin' => 'Batangas', 'destination' => 'Calapan', 'vessel' => 'MV Starlite Archer', 'plate' => 'STA-FC1', 'dep_times' => ['08:30:00','12:30:00','16:30:00'], 'duration' => 90, 'active_days' => 'all'],
    ['origin' => 'Calapan', 'destination' => 'Batangas', 'vessel' => 'MV Starlite Archer', 'plate' => 'STA-FC1', 'dep_times' => ['05:20:00','10:30:00','14:30:00'], 'duration' => 90, 'active_days' => 'all'],

    // 3. Batangas <-> Caticlan (4x/day)
    ['origin' => 'Batangas', 'destination' => 'Caticlan', 'vessel' => 'MV Starlite Pioneer', 'plate' => 'STP-201', 'dep_times' => ['07:30:00','13:00:00','16:00:00','19:30:00'], 'duration' => 600, 'active_days' => 'all'],
    ['origin' => 'Caticlan', 'destination' => 'Batangas', 'vessel' => 'MV Starlite Reliance', 'plate' => 'STR-202', 'dep_times' => ['01:00:00','07:30:00','17:00:00','19:30:00'], 'duration' => 600, 'active_days' => 'all'],

    // 4. Roxas Mindoro <-> Caticlan (6x/day)
    ['origin' => 'Roxas Mindoro', 'destination' => 'Caticlan', 'vessel' => 'MV Starlite Resilience', 'plate' => 'STR-301', 'dep_times' => ['02:00:00','06:00:00','11:00:00','14:00:00','18:00:00','23:00:00'], 'duration' => 240, 'active_days' => 'all'],
    ['origin' => 'Caticlan', 'destination' => 'Roxas Mindoro', 'vessel' => 'MV Starlite Resilience', 'plate' => 'STR-301', 'dep_times' => ['01:00:00','05:00:00','09:00:00','12:00:00','16:00:00','20:00:00'], 'duration' => 240, 'active_days' => 'all'],

    // 5. Batangas <-> Roxas City, Capiz (Daily ROPAX only, LCT excluded)
    ['origin' => 'Batangas', 'destination' => 'Roxas City, Capiz', 'vessel' => 'MV Starlite Stella Maris', 'plate' => 'SSM-401', 'dep_times' => ['15:00:00'], 'duration' => 1080, 'active_days' => 'all'],
    ['origin' => 'Roxas City, Capiz', 'destination' => 'Batangas', 'vessel' => 'MV Starlite Stella Maris', 'plate' => 'SSM-401', 'dep_times' => ['13:00:00'], 'duration' => 1080, 'active_days' => 'all'],

    // 6. Batangas <-> Sibuyan (Magdiwang) (Tue, Thu, Sat, Sun)
    ['origin' => 'Batangas', 'destination' => 'Sibuyan (Magdiwang)', 'vessel' => 'MV Starlite Saturn', 'plate' => 'STS-501', 'dep_times' => ['13:00:00'], 'duration' => 720, 'active_days' => [2, 4, 6, 0]],
    ['origin' => 'Sibuyan (Magdiwang)', 'destination' => 'Batangas', 'vessel' => 'MV Starlite Saturn', 'plate' => 'STS-501', 'dep_times' => ['19:00:00'], 'duration' => 720, 'active_days' => [2, 4, 6, 0]],

    // 7. Batangas <-> Romblon (Daily)
    ['origin' => 'Batangas', 'destination' => 'Romblon', 'vessel' => 'MV Starlite Venus', 'plate' => 'STV-601', 'dep_times' => ['13:00:00'], 'duration' => 540, 'active_days' => 'all'],
    ['origin' => 'Romblon', 'destination' => 'Batangas', 'vessel' => 'MV Starlite Venus', 'plate' => 'STV-601', 'dep_times' => ['22:00:00'], 'duration' => 540, 'active_days' => 'all'],

    // 8. Romblon <-> Sibuyan (Magdiwang) (4x/week)
    ['origin' => 'Romblon', 'destination' => 'Sibuyan (Magdiwang)', 'vessel' => 'MV Starlite Gratitude', 'plate' => 'STG-701', 'dep_times' => ['02:00:00'], 'duration' => 120, 'active_days' => [1, 3, 5, 0]],
    ['origin' => 'Sibuyan (Magdiwang)', 'destination' => 'Romblon', 'vessel' => 'MV Starlite Gratitude', 'plate' => 'STG-701', 'dep_times' => ['19:00:00'], 'duration' => 120, 'active_days' => [2, 4, 6, 0]],

    // 9. Sibuyan (Magdiwang) <-> Roxas City, Capiz (4x/week)
    ['origin' => 'Sibuyan (Magdiwang)', 'destination' => 'Roxas City, Capiz', 'vessel' => 'MV Starlite Poseidon 43', 'plate' => 'STP-801', 'dep_times' => ['05:00:00'], 'duration' => 300, 'active_days' => [1, 3, 5, 0]],
    ['origin' => 'Roxas City, Capiz', 'destination' => 'Sibuyan (Magdiwang)', 'vessel' => 'MV Starlite Poseidon 53', 'plate' => 'STP-802', 'dep_times' => ['13:00:00'], 'duration' => 300, 'active_days' => [2, 4, 6, 0]],

    // 10. Batangas <-> Cajidiocan (Mon, Wed, Fri)
    ['origin' => 'Batangas', 'destination' => 'Cajidiocan', 'vessel' => 'MV Starlite Prometheus 54', 'plate' => 'STP-901', 'dep_times' => ['13:00:00'], 'duration' => 720, 'active_days' => [1, 3, 5]],
    ['origin' => 'Cajidiocan', 'destination' => 'Batangas', 'vessel' => 'MV Starlite Prometheus 55', 'plate' => 'STP-902', 'dep_times' => ['18:00:00'], 'duration' => 720, 'active_days' => [1, 3, 5]],

    // 11. Romblon <-> Cajidiocan (3x/week)
    ['origin' => 'Romblon', 'destination' => 'Cajidiocan', 'vessel' => 'MV Starlite Prometheus 56', 'plate' => 'STP-903', 'dep_times' => ['02:00:00'], 'duration' => 120, 'active_days' => [2, 4, 6]],
    ['origin' => 'Cajidiocan', 'destination' => 'Romblon', 'vessel' => 'MV Starlite Prometheus 57', 'plate' => 'STP-904', 'dep_times' => ['18:00:00'], 'duration' => 120, 'active_days' => [1, 3, 5]],

    // 12. Cajidiocan <-> Roxas City, Capiz (3x/week)
    ['origin' => 'Cajidiocan', 'destination' => 'Roxas City, Capiz', 'vessel' => 'MV Starlite Poseidon 37', 'plate' => 'STP-905', 'dep_times' => ['06:00:00'], 'duration' => 300, 'active_days' => [2, 4, 6]],
    ['origin' => 'Roxas City, Capiz', 'destination' => 'Cajidiocan', 'vessel' => 'MV Starlite Poseidon 37', 'plate' => 'STP-905', 'dep_times' => ['13:00:00'], 'duration' => 300, 'active_days' => [1, 3, 5]],

    // 13. Romblon <-> Roxas City, Capiz (Daily)
    ['origin' => 'Romblon', 'destination' => 'Roxas City, Capiz', 'vessel' => 'MV Starlite Venus', 'plate' => 'STV-601', 'dep_times' => ['02:00:00'], 'duration' => 480, 'active_days' => 'all'],
    ['origin' => 'Roxas City, Capiz', 'destination' => 'Romblon', 'vessel' => 'MV Starlite Venus', 'plate' => 'STV-601', 'dep_times' => ['13:00:00'], 'duration' => 480, 'active_days' => 'all'],

    // 14. Cebu <-> Surigao (5x/week, 2x/day)
    ['origin' => 'Cebu', 'destination' => 'Surigao', 'vessel' => 'MV Starlite Saturn', 'plate' => 'STS-501', 'dep_times' => ['09:00:00','21:00:00'], 'duration' => 600, 'active_days' => [1, 2, 3, 4, 0]],
    ['origin' => 'Surigao', 'destination' => 'Cebu', 'vessel' => 'MV Starlite Saturn', 'plate' => 'STS-501', 'dep_times' => ['09:00:00','21:00:00'], 'duration' => 600, 'active_days' => [2, 3, 4, 5, 1]],

    // 15. Cebu <-> Dapitan (2x/week, 1x/day)
    ['origin' => 'Cebu', 'destination' => 'Dapitan', 'vessel' => 'MV Starlite Salve Regina', 'plate' => 'SSR-1001', 'dep_times' => ['21:00:00'], 'duration' => 600, 'active_days' => [5, 6]], // Fri, Sat
    ['origin' => 'Dapitan', 'destination' => 'Cebu', 'vessel' => 'MV Starlite Salve Regina', 'plate' => 'SSR-1001', 'dep_times' => ['09:00:00'], 'duration' => 600, 'active_days' => [6, 1]], // Sat, Mon

    // 16. Batangas <-> Odiongan (3x/week)
    ['origin' => 'Batangas', 'destination' => 'Odiongan', 'vessel' => 'MV Starlite Eagle', 'plate' => 'STE-1101', 'dep_times' => ['15:00:00'], 'duration' => 600, 'active_days' => [2, 4, 6]],
    ['origin' => 'Odiongan', 'destination' => 'Batangas', 'vessel' => 'MV Starlite Eagle', 'plate' => 'STE-1101', 'dep_times' => ['02:00:00'], 'duration' => 600, 'active_days' => [3, 5, 0]],

    // 17. Odiongan <-> Caticlan (3x/week)
    ['origin' => 'Odiongan', 'destination' => 'Caticlan', 'vessel' => 'MV Starlite Eagle', 'plate' => 'STE-1101', 'dep_times' => ['15:00:00'], 'duration' => 240, 'active_days' => [3, 5, 0]],
    ['origin' => 'Caticlan', 'destination' => 'Odiongan', 'vessel' => 'MV Starlite Eagle', 'plate' => 'STE-1101', 'dep_times' => ['10:00:00'], 'duration' => 240, 'active_days' => [3, 5, 0]],

    // 18. Roxas Mindoro <-> Buruanga (Daily 4x/day)
    ['origin' => 'Roxas Mindoro', 'destination' => 'Buruanga', 'vessel' => 'MV Starlite Pacific', 'plate' => 'STP-1201', 'dep_times' => ['03:00:00','09:00:00','15:00:00','21:00:00'], 'duration' => 420, 'active_days' => 'all'],
    ['origin' => 'Buruanga', 'destination' => 'Roxas Mindoro', 'vessel' => 'MV Starlite Pacific', 'plate' => 'STP-1201', 'dep_times' => ['03:00:00','09:00:00','15:00:00','21:00:00'], 'duration' => 420, 'active_days' => 'all'],
];

$sqlFile = __DIR__ . '/master_starlite_sync.sql';
$f = fopen($sqlFile, 'w');

fwrite($f, "-- MASTER STARLITE SCHEDULE SYNC\n");
fwrite($f, "-- Generated: {$now}\n");
fwrite($f, "-- Includes: All 18 route pairs (36 directions), full frequencies, official April 13, 2026 rates\n");
fwrite($f, "-- Excluded: Roxas Mindoro <-> Odiongan AND all LCT trips\n\n");
fwrite($f, "SET autocommit = 0;\n");
fwrite($f, "SET foreign_key_checks = 0;\n\n");

// Step 1: Ensure Starlite operator
fwrite($f, "-- Ensure Starlite operator exists\n");
fwrite($f, "INSERT IGNORE INTO operators (name, mode, logo_path, is_active, created_at, updated_at)\n");
fwrite($f, "VALUES ('Starlite', 'ferry', 'operators/Starlite_Logo.png', 1, '{$now}', '{$now}');\n");
fwrite($f, "SET @op_id = (SELECT id FROM operators WHERE name = 'Starlite' LIMIT 1);\n\n");

// Step 2: Ensure all vehicles exist
fwrite($f, "-- Ensure vehicles exist\n");
$doneVehicles = [];
foreach ($rules as $r) {
    if (isset($doneVehicles[$r['plate']])) continue;
    $doneVehicles[$r['plate']] = true;
    $vName = addslashes($r['vessel']);
    $vPlate = addslashes($r['plate']);
    fwrite($f, "INSERT IGNORE INTO vehicles (vehicle_id, name, type, operator, operator_id, is_active, created_at, updated_at)\n");
    fwrite($f, "VALUES ('{$vPlate}', '{$vName}', 'ferry', 'Starlite', @op_id, 1, '{$now}', '{$now}');\n");
}
fwrite($f, "\n");

// Step 3: Ensure ferry routes exist
fwrite($f, "-- Ensure ferry routes exist\n");
$doneRoutes = [];
foreach ($rules as $r) {
    $pairKey = $r['origin'] . '|' . $r['destination'];
    if (isset($doneRoutes[$pairKey])) continue;
    $doneRoutes[$pairKey] = true;
    $orig = addslashes($r['origin']);
    $dest = addslashes($r['destination']);
    $plate = addslashes($r['plate']);
    fwrite($f, "INSERT INTO ferry_routes (origin, destination, mode, vehicle_id, operator, operator_id, trip_type, is_active, created_at, updated_at)\n");
    fwrite($f, "SELECT '{$orig}', '{$dest}', 'ferry', (SELECT id FROM vehicles WHERE vehicle_id = '{$plate}' LIMIT 1), 'Starlite', @op_id, 'local', 1, '{$now}', '{$now}'\n");
    fwrite($f, "FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM ferry_routes WHERE origin = '{$orig}' AND destination = '{$dest}' AND (operator = 'Starlite' OR operator_id = @op_id));\n");
}
fwrite($f, "\n");

// Step 4: Ensure all transport classes exist
fwrite($f, "-- Ensure transport classes exist with official rates\n");
$doneTc = [];
foreach ($fareMatrix as $pair => $config) {
    foreach ($config['accommodations'] as $acc) {
        $code = Str::slug($acc['name']);
        if (isset($doneTc[$code])) continue;
        $doneTc[$code] = true;
        $name = addslashes($acc['name']);
        $desc = addslashes($acc['desc']);
        fwrite($f, "INSERT IGNORE INTO transport_classes (operator, operator_id, code, name, description, price, is_active, sort_order, created_at, updated_at)\n");
        fwrite($f, "VALUES ('Starlite', @op_id, '{$code}', '{$name}', '{$desc}', {$acc['price']}, 1, {$acc['sort']}, '{$now}', '{$now}');\n");
    }
}
fwrite($f, "\n");

// Step 5: Generate schedule INSERT IGNOREs
fwrite($f, "-- =============================================\n");
fwrite($f, "-- SCHEDULES: Bulk INSERT IGNORE (skip existing)\n");
fwrite($f, "-- =============================================\n\n");

$totalGenerated = 0;
$batch = [];
$batchSize = 250;
$seatCols = json_encode(['A', 'B', 'C', 'D', 'E', 'F']);

foreach ($rules as $r) {
    $orig = addslashes($r['origin']);
    $dest = addslashes($r['destination']);
    $vName = addslashes($r['vessel']);
    $plate = addslashes($r['plate']);
    $dur = $r['duration'];
    $fare = $getFareConfig($r['origin'], $r['destination']);
    $basePrice = $fare['base_price'];

    $period = CarbonPeriod::create($startDate, $endDate);
    foreach ($period as $dt) {
        if ($r['active_days'] !== 'all' && !in_array($dt->dayOfWeek, $r['active_days'], true)) {
            continue;
        }

        foreach ($r['dep_times'] as $timeStr) {
            $depTime = $dt->format('Y-m-d') . ' ' . $timeStr;
            $arrTime = Carbon::parse($depTime)->addMinutes($dur)->format('Y-m-d H:i:s');

            $batch[] = "((SELECT id FROM ferry_routes WHERE origin = '{$orig}' AND destination = '{$dest}' AND (operator = 'Starlite' OR operator_id = @op_id) LIMIT 1), "
                . "'{$vName}', '{$vName}', '{$plate}', '{$depTime}', '{$arrTime}', {$dur}, {$basePrice}, "
                . "'Available', 15, '{$seatCols}', 1, '{$now}', '{$now}')";

            $totalGenerated++;

            if (count($batch) >= $batchSize) {
                fwrite($f, "INSERT IGNORE INTO schedules (ferry_route_id, service_name, vehicle_name, plate_no, departure_time, arrival_time, duration_minutes, price, availability_label, seat_rows, seat_columns, is_active, created_at, updated_at)\nVALUES\n");
                fwrite($f, implode(",\n", $batch) . ";\n\n");
                $batch = [];
            }
        }
    }
}

if (!empty($batch)) {
    fwrite($f, "INSERT IGNORE INTO schedules (ferry_route_id, service_name, vehicle_name, plate_no, departure_time, arrival_time, duration_minutes, price, availability_label, seat_rows, seat_columns, is_active, created_at, updated_at)\nVALUES\n");
    fwrite($f, implode(",\n", $batch) . ";\n\n");
}

fwrite($f, "COMMIT;\n\n");

// Step 6: Fix prices on existing schedules (e.g. update Roxas Mindoro-Caticlan from 750 to 1340)
fwrite($f, "-- =============================================\n");
fwrite($f, "-- PRICE RECONCILIATION: Update schedule base prices to official tariff\n");
fwrite($f, "-- =============================================\n\n");
foreach ($fareMatrix as $pair => $cfg) {
    [$o, $d] = explode('|', $pair);
    $orig = addslashes($o);
    $dest = addslashes($d);
    $price = $cfg['base_price'];
    fwrite($f, "UPDATE schedules s JOIN ferry_routes fr ON s.ferry_route_id = fr.id\n");
    fwrite($f, "SET s.price = {$price}\n");
    fwrite($f, "WHERE ((fr.origin = '{$orig}' AND fr.destination = '{$dest}') OR (fr.origin = '{$dest}' AND fr.destination = '{$orig}'))\n");
    fwrite($f, "  AND (fr.operator = 'Starlite' OR fr.operator_id = @op_id)\n");
    fwrite($f, "  AND s.is_active = 1;\n");
}
fwrite($f, "\nCOMMIT;\n\n");

// Step 7: Insert missing accommodations for all Starlite schedules
fwrite($f, "-- =============================================\n");
fwrite($f, "-- ACCOMMODATIONS: Insert missing accommodations & pivots\n");
fwrite($f, "-- =============================================\n\n");

foreach ($fareMatrix as $pair => $cfg) {
    [$o, $d] = explode('|', $pair);
    $orig = addslashes($o);
    $dest = addslashes($d);

    foreach ($cfg['accommodations'] as $acc) {
        $accName = addslashes($acc['name']);
        $accDesc = addslashes($acc['desc']);
        $tcCode = Str::slug($acc['name']);
        $accPrice = $acc['price'];
        $hasBed = $acc['has_bed'];
        $sort = $acc['sort'];

        // Insert into schedule_accommodations
        fwrite($f, "INSERT INTO schedule_accommodations (schedule_id, name, description, price, tickets_available, has_bed, is_active, sort_order, created_at, updated_at)\n");
        fwrite($f, "SELECT s.id, '{$accName}', '{$accDesc}', {$accPrice}, 50, {$hasBed}, 1, {$sort}, '{$now}', '{$now}'\n");
        fwrite($f, "FROM schedules s JOIN ferry_routes fr ON s.ferry_route_id = fr.id\n");
        fwrite($f, "WHERE ((fr.origin = '{$orig}' AND fr.destination = '{$dest}') OR (fr.origin = '{$dest}' AND fr.destination = '{$orig}'))\n");
        fwrite($f, "  AND (fr.operator = 'Starlite' OR fr.operator_id = @op_id)\n");
        fwrite($f, "  AND s.is_active = 1\n");
        fwrite($f, "  AND NOT EXISTS (SELECT 1 FROM schedule_accommodations sa WHERE sa.schedule_id = s.id AND sa.name = '{$accName}');\n\n");

        // Insert into schedule_transport_class pivot
        fwrite($f, "INSERT INTO schedule_transport_class (schedule_id, transport_class_id, additional_price, tickets_available, description, has_bed, is_active, created_at, updated_at)\n");
        fwrite($f, "SELECT s.id, (SELECT id FROM transport_classes WHERE operator = 'Starlite' AND code = '{$tcCode}' LIMIT 1), {$accPrice}, 50, '{$accDesc}', {$hasBed}, 1, '{$now}', '{$now}'\n");
        fwrite($f, "FROM schedules s JOIN ferry_routes fr ON s.ferry_route_id = fr.id\n");
        fwrite($f, "WHERE ((fr.origin = '{$orig}' AND fr.destination = '{$dest}') OR (fr.origin = '{$dest}' AND fr.destination = '{$orig}'))\n");
        fwrite($f, "  AND (fr.operator = 'Starlite' OR fr.operator_id = @op_id)\n");
        fwrite($f, "  AND s.is_active = 1\n");
        fwrite($f, "  AND NOT EXISTS (SELECT 1 FROM schedule_transport_class stc WHERE stc.schedule_id = s.id AND stc.transport_class_id = (SELECT id FROM transport_classes WHERE operator = 'Starlite' AND code = '{$tcCode}' LIMIT 1));\n\n");
    }
}

fwrite($f, "COMMIT;\n");
fwrite($f, "SET foreign_key_checks = 1;\n\n");

// Final Summary
fwrite($f, "-- FINAL SUMMARY\n");
fwrite($f, "SELECT 'TOTAL ACTIVE SCHEDULES' as label, COUNT(*) as cnt FROM schedules WHERE is_active = 1\n");
fwrite($f, "UNION ALL\n");
fwrite($f, "SELECT CONCAT(fr.origin, ' -> ', fr.destination), COUNT(*)\n");
fwrite($f, "FROM schedules s JOIN ferry_routes fr ON s.ferry_route_id = fr.id\n");
fwrite($f, "WHERE fr.operator = 'Starlite' AND s.is_active = 1\n");
fwrite($f, "GROUP BY fr.origin, fr.destination;\n");

fclose($f);

echo "=== MASTER SQL GENERATED ===" . PHP_EOL;
echo "File: {$sqlFile}" . PHP_EOL;
echo "Total schedule rows generated: {$totalGenerated}" . PHP_EOL;
echo "File size: " . round(filesize($sqlFile) / 1024, 1) . " KB" . PHP_EOL;

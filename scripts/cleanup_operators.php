<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Philippine AirAsia -> AirAsia
DB::table('ferry_routes')->where('operator', 'Philippine AirAsia')->update(['operator' => 'AirAsia']);
DB::table('vehicles')->where('operator', 'Philippine AirAsia')->update(['operator' => 'AirAsia']);

// Philippines Airlines(PAL) -> Philippine Airline
DB::table('ferry_routes')->where('operator', 'Philippines Airlines(PAL)')->update(['operator' => 'Philippine Airline']);
DB::table('vehicles')->where('operator', 'Philippines Airlines(PAL)')->update(['operator' => 'Philippine Airline']);

// Also flush cache just in case
\Illuminate\Support\Facades\Cache::flush();

echo "Operators unified successfully.\n";

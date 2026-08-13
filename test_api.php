<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$start = microtime(true);
$bookings = \App\Models\Booking::where('client_email', 'macaraigdrew99@gmail.com')
    ->with(['passengers.discount', 'accommodations', 'transaction', 'schedule', 'returnSchedule', 'transportClasses'])
    ->limit(50)
    ->get();

$arr = $bookings->map(function ($b) { return $b->toArray(); });
echo "Time: " . (microtime(true) - $start) . "s\n";
echo "Size: " . strlen(json_encode($arr)) . "\n";
echo "Count: " . count($arr) . "\n";

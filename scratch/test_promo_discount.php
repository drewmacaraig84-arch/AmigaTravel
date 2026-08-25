<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Schedule;
use App\Models\PromotionalTicket;
use App\Models\Discount;
use App\Models\Passenger;
use App\Models\Booking;
use App\Actions\Bookings\CreateBookingAction;

echo "=== STARTING PROMOTIONAL TICKET + PASSENGER DISCOUNT TEST ===\n\n";

$schedule = Schedule::first();
if (!$schedule) {
    echo "No schedule found to test with.\n";
    exit(1);
}

$discount = Discount::where('name', 'like', '%Senior%')
    ->orWhere('name', 'like', '%Student%')
    ->orWhere('name', 'like', '%PWD%')
    ->first();

if (!$discount) {
    echo "No discount found to test with.\n";
    exit(1);
}

echo "Found Schedule ID: {$schedule->id} (Base price: {$schedule->price})\n";
echo "Found Discount ID: {$discount->id} ({$discount->name}, {$discount->percentage}%)\n";

// Ensure a promotional ticket exists on schedule
$promoTicket = PromotionalTicket::firstOrCreate([
    'schedule_id' => $schedule->id,
], [
    'promo_price' => 500.00,
    'quantity_available' => 10,
    'quantity_sold' => 0,
    'is_active' => true,
]);

echo "Found/Created Promo Ticket ID: {$promoTicket->id} (Promo price: {$promoTicket->promo_price})\n\n";

$data = [
    'trip_type' => 'one_way',
    'origin' => $schedule->ferryRoute?->origin ?? 'Manila',
    'destination' => $schedule->ferryRoute?->destination ?? 'Cebu',
    'departure_date' => $schedule->departure_time ? \Carbon\Carbon::parse($schedule->departure_time)->format('Y-m-d') : date('Y-m-d'),
    'selected_schedule_id' => $schedule->id,
    'promotional_ticket_id' => $promoTicket->id,
    'client_name' => 'Promo Discount Test User',
    'client_email' => 'promotest@amigatravel.com',
    'client_phone' => '09123456789',
    'payment_method' => 'gcash',
    'passengers' => [
        [
            'type' => 'adult',
            'name' => 'Senior Passenger',
            'birthdate' => '1955-05-15',
            'discount_id' => $discount->id,
            'id_number' => 'OSCA-12345',
        ]
    ]
];

try {
    $action = app(CreateBookingAction::class);
    $result = $action->execute($data);
    $booking = $result['booking'];

    echo "Booking created successfully: {$booking->transaction_number}\n";
    $pax = $booking->passengers->first();

    echo "Passenger discount_id: " . var_export($pax->discount_id, true) . "\n";
    echo "Passenger fare_amount: {$pax->fare_amount}\n";
    echo "Passenger discount_amount: {$pax->discount_amount}\n";
    echo "Passenger item_total: {$pax->item_total}\n";

    if ($pax->discount_id == $discount->id && $pax->discount_amount > 0) {
        echo "\n[PASS] Passenger successfully received {$discount->name} ({$discount->percentage}%) discount on promo ticket!\n";
    } else {
        echo "\n[FAIL] Discount was not properly applied.\n";
        exit(1);
    }
} catch (\Throwable $e) {
    echo "\n[EXCEPTION] " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

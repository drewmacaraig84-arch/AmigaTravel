<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Booking;
use App\Models\Transaction;
use App\Models\User;
use App\Models\FerryRoute;
use App\Models\Schedule;
use App\Models\GraciaPointLedger;
use App\Mail\BookingConfirmation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

echo "\n=================================================================\n";
echo "  CONCURRENT BOOKING VERIFICATION TEST SIMULATION\n";
echo "=================================================================\n\n";

// 1. Prepare Mails interception
Mail::fake();

// 2. Setup Staff Accounts
$maria = User::firstOrCreate(
    ['email' => 'maria.staff@example.com'],
    [
        'name' => 'Maria Santos',
        'password' => bcrypt('password'),
        'role' => 'admin',
        'is_admin' => true,
    ]
);

$john = User::firstOrCreate(
    ['email' => 'john.staff@example.com'],
    [
        'name' => 'John Doe',
        'password' => bcrypt('password'),
        'role' => 'admin',
        'is_admin' => true,
    ]
);

echo "👤 Staff Member A: {$maria->name} (ID: {$maria->id})\n";
echo "👤 Staff Member B: {$john->name} (ID: {$john->id})\n\n";

// 3. Create a Test Pending Booking
$route = FerryRoute::firstOrCreate(
    ['origin' => 'Batangas', 'destination' => 'Calapan'],
    ['operator' => 'FastCat', 'mode' => 'ferry', 'is_active' => true]
);

$schedule = Schedule::firstOrCreate(
    ['ferry_route_id' => $route->id, 'service_name' => 'FastCat M1'],
    ['departure_time' => '10:00:00', 'arrival_time' => '12:00:00', 'price' => 500, 'duration_minutes' => 120, 'is_active' => true]
);

$testTxNumber = 'TEST-SIM-' . time();
$booking = Booking::create([
    'transaction_number' => $testTxNumber,
    'client_name' => 'Carlos Reyes',
    'client_email' => 'carlos.customer@example.com',
    'client_phone' => '09123456789',
    'origin' => 'Batangas',
    'destination' => 'Calapan',
    'mode_of_transport' => 'ferry',
    'booking_type' => 'one_way',
    'status' => 'pending',
    'total_price' => 500,
    'schedule_id' => $schedule->id,
    'departure_date' => now()->addDays(5)->format('Y-m-d'),
]);

$transaction = Transaction::create([
    'booking_id' => $booking->id,
    'payment_status' => 'pending',
    'payment_method' => 'gcash',
    'amount' => 500,
]);
$booking->setRelation('transaction', $transaction);

echo "📋 Created Test Booking #{$booking->transaction_number} (Status: {$booking->status}, Price: ₱{$booking->total_price})\n";
echo "-----------------------------------------------------------------\n\n";

// Helper function to simulate what the Filament verify action executes for a given user
function simulateFilamentVerify(Booking $record, array $data, User $staffUser, &$notificationsOut): array
{
    Auth::login($staffUser);
    
    $alreadyVerifiedBy = null;
    $shouldSendEmail = false;
    $isRebooking = false;
    $ticketUrl = !empty($data['confirmation_url']) ? trim($data['confirmation_url']) : null;
    $confirmationPdfPath = Booking::resolveUploadedPdfPath($data['confirmation_pdf'] ?? null, $record->transaction_number);
    $receiptPath = $confirmationPdfPath;
    $receiptDisk = $confirmationPdfPath ? 'public' : null;

    DB::transaction(function () use (
        $record, $ticketUrl, $confirmationPdfPath, $receiptPath, $receiptDisk,
        &$alreadyVerifiedBy, &$shouldSendEmail, &$isRebooking
    ) {
        $lockedBooking = Booking::where('id', $record->id)
            ->with(['transaction', 'verifiedBy'])
            ->lockForUpdate()
            ->first();

        if (! $lockedBooking || $lockedBooking->status === 'confirmed' || $lockedBooking->verified_by_user_id !== null) {
            $alreadyVerifiedBy = $lockedBooking?->verifiedBy?->name ?? 'another staff member';
            return;
        }

        $staffUserId = Auth::id();
        $now = now();

        $transaction = $lockedBooking->transaction ?? Transaction::where('booking_id', $lockedBooking->id)->lockForUpdate()->first();
        if ($transaction) {
            $transaction->update([
                'payment_status' => 'paid',
                'confirmation_url' => $ticketUrl,
                'confirmation_pdf' => $confirmationPdfPath,
                'verified_by_user_id' => $staffUserId,
                'verified_at' => $now,
            ]);
        } else {
            $transaction = Transaction::create([
                'booking_id' => $lockedBooking->id,
                'payment_status' => 'paid',
                'confirmation_url' => $ticketUrl,
                'confirmation_pdf' => $confirmationPdfPath,
                'verified_by_user_id' => $staffUserId,
                'verified_at' => $now,
            ]);
        }
        $lockedBooking->setRelation('transaction', $transaction);

        $lockedBooking->update([
            'verified_by_user_id' => $staffUserId,
            'verified_at' => $now,
        ]);

        if ($lockedBooking->rebooking_status === 'pending') {
            $isRebooking = true;
            $lockedBooking->verifyRebooking($ticketUrl, $receiptPath, $receiptDisk);
        } else {
            $lockedBooking->update(['status' => 'confirmed']);
            app(\App\Services\GraciaPointsService::class)->awardPointsForBooking($lockedBooking, auth()->user());
            $shouldSendEmail = true;
        }
    });

    if ($alreadyVerifiedBy !== null) {
        $msg = "This booking was already verified by {$alreadyVerifiedBy}.";
        $notificationsOut[] = [
            'type' => 'WARNING',
            'title' => 'Already Verified',
            'body' => $msg
        ];
        return [
            'status' => 'BLOCKED',
            'error_title' => 'Already Verified',
            'error_message' => $msg
        ];
    }

    if ($isRebooking) {
        $notificationsOut[] = [
            'type' => 'SUCCESS',
            'title' => 'Rebooking verified',
            'body' => 'Rebooking verified and confirmation email sent.'
        ];
        return ['status' => 'SUCCESS_REBOOKING'];
    }

    if ($shouldSendEmail) {
        Mail::to($record->client_email)->send(
            new BookingConfirmation($record->fresh(), $ticketUrl, $receiptPath, $receiptDisk)
        );
        $notificationsOut[] = [
            'type' => 'SUCCESS',
            'title' => 'Booking verified',
            'body' => 'Booking verified and confirmation email sent.'
        ];
        return ['status' => 'SUCCESS'];
    }

    return ['status' => 'UNKNOWN'];
}

// 4. Run Staff 1 (Maria) Verification
echo "🚀 [STEP 1] Maria Santos clicks 'Verify booking'...\n";
$mariaNotifications = [];
$mariaResult = simulateFilamentVerify(
    $booking,
    ['confirmation_url' => 'https://amigatravel.ph/tickets/MARIA-TICKET-001'],
    $maria,
    $mariaNotifications
);

echo "   Result for Maria: " . json_encode($mariaResult) . "\n";
foreach ($mariaNotifications as $notif) {
    echo "   💬 Notification displayed to Maria: [{$notif['type']}] {$notif['title']} - {$notif['body']}\n";
}
echo "\n";

// 5. Run Staff 2 (John) Verification on the SAME booking
echo "🚀 [STEP 2] John Doe clicks 'Verify booking' simultaneously / immediately after...\n";
$johnNotifications = [];
$johnResult = simulateFilamentVerify(
    $booking,
    ['confirmation_url' => 'https://amigatravel.ph/tickets/JOHN-TICKET-999'],
    $john,
    $johnNotifications
);

echo "   Result for John: " . json_encode($johnResult) . "\n";
foreach ($johnNotifications as $notif) {
    echo "   ⚠️ Warning / Error Message displayed to John: [{$notif['type']}] {$notif['title']} - {$notif['body']}\n";
}
echo "\n";

// 6. Inspect Final Database State
echo "=================================================================\n";
echo "  FINAL DATABASE STATE INSPECTION\n";
echo "=================================================================\n";

$finalBooking = $booking->fresh(['verifiedBy', 'transaction']);
echo "📌 Booking Status: {$finalBooking->status}\n";
echo "📌 Verified By: {$finalBooking->verifiedBy?->name} (User ID: {$finalBooking->verified_by_user_id})\n";
echo "📌 Ticket URL in Transaction: {$finalBooking->transaction->confirmation_url}\n";

// Check Mails Sent
$emailCount = Mail::sent(BookingConfirmation::class)->count();
echo "📧 Total Customer Emails Sent: {$emailCount} (Expected: 1)\n";

// Check Gracia Points Ledger
$ledgerCount = GraciaPointLedger::where('booking_id', $booking->id)->count();
echo "🎁 Loyalty Points Entries: {$ledgerCount} (Expected: <= 1)\n";

echo "\n-----------------------------------------------------------------\n";
if ($finalBooking->verified_by_user_id === $maria->id 
    && $finalBooking->transaction->confirmation_url === 'https://amigatravel.ph/tickets/MARIA-TICKET-001'
    && $johnResult['status'] === 'BLOCKED'
    && $emailCount === 1
) {
    echo "✅ TEST PASSED: Concurrency lock successfully protected the system!\n";
    echo "   - John was blocked and shown: '{$johnResult['error_message']}'\n";
    echo "   - Maria retained full performance credit.\n";
    echo "   - Customer received exactly 1 email with Maria's ticket.\n";
} else {
    echo "❌ TEST FAILED: Race condition detected.\n";
}
echo "=================================================================\n\n";

// Cleanup test records
$transaction->delete();
$booking->delete();

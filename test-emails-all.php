<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

config([
    'mail.default' => 'mailgun',
    'mail.from.address' => 'noreply@amigagracia.com',
    'mail.from.name' => 'Amiga Gracia Travel Service',
    'services.mailgun.domain' => 'mg.amigagracia.com',
    'services.mailgun.secret' => 'dd0d6edad2a1963baac850c999fc6f91-11c539c0-10a2c92c',
    'services.mailgun.endpoint' => 'api.mailgun.net',
]);

$email = 'drewmacaraig84@gmail.com';

try {
    $booking = \App\Models\Booking::latest()->first() ?? new \App\Models\Booking([
        'reference_number' => 'TEST-BK-001',
        'client_name' => 'Drew Macaraig',
        'client_email' => $email,
    ]);
    
    // Set email just in case the real booking has a different one
    $booking->client_email = $email;

    $transaction = $booking->transaction ?? new \App\Models\Transaction([
        'reference_number' => 'TXN-001',
        'amount' => 5000,
        'status' => 'paid'
    ]);
    $transaction->booking = $booking;

    $cancellation = new \App\Models\ServiceCancellation([
        'reason' => 'Weather disturbance (Test)',
        'status' => 'cancelled'
    ]);

    // 1. Booking Confirmation
    \Illuminate\Support\Facades\Mail::to($email)->send(
        new \App\Mail\BookingConfirmation($booking, 'https://example.com/ticket', 'dummy-receipt.pdf')
    );
    echo "Sent: Booking Confirmation\n";

    // 2. Booking Cancellation
    \Illuminate\Support\Facades\Mail::to($email)->send(
        new \App\Mail\BookingCancellation($booking, 'Gcash (09123456789)')
    );
    echo "Sent: Booking Cancellation\n";

    // 3. Rebooking Requested
    \Illuminate\Support\Facades\Mail::to($email)->send(
        new \App\Mail\RebookingRequested($booking)
    );
    echo "Sent: Rebooking Requested\n";

    // 4. Rebooking Verification
    \Illuminate\Support\Facades\Mail::to($email)->send(
        new \App\Mail\RebookingVerification($booking, 'https://example.com/ticket-new', 'dummy-receipt-new.pdf')
    );
    echo "Sent: Rebooking Verification\n";

    // 5. Service Cancellation Notification (Operator)
    \Illuminate\Support\Facades\Mail::to($email)->send(
        new \App\Mail\ServiceCancellationNotificationMail($booking, $cancellation, false)
    );
    echo "Sent: Service Cancellation (Cancelled)\n";

    // 6. Reschedule Approval
    \Illuminate\Support\Facades\Mail::to($email)->send(
        new \App\Mail\RescheduleApprovalNotificationMail($booking, true, 'Looks good, approved!')
    );
    echo "Sent: Reschedule Approval\n";

    // 7. Payment Proof Received
    \Illuminate\Support\Facades\Mail::to($email)->send(
        new \App\Mail\PaymentProofReceived($transaction)
    );
    echo "Sent: Payment Proof Received\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}

echo "All test emails sent!\n";

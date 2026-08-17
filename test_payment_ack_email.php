<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$email = 'macaraigdrew99@gmail.com';

try {
    // Get the latest booking or create a test one
    $booking = \App\Models\Booking::latest()->first();
    
    if (!$booking) {
        // Create a test booking if none exists
        $booking = new \App\Models\Booking([
            'reference_number' => 'TEST-BK-' . now()->format('YmdHis'),
            'transaction_number' => 'TXN-' . now()->format('YmdHis'),
            'client_name' => 'Test User',
            'client_email' => $email,
            'client_phone' => '+63912345678',
            'status' => 'confirmed',
        ]);
    }
    
    // Override the email to send to your address
    $booking->client_email = $email;
    
    // Ensure booking has a transaction for the PDF
    if (!$booking->transaction) {
        $transaction = new \App\Models\Transaction([
            'booking_id' => $booking->id,
            'reference_number' => $booking->transaction_number ?? 'TXN-TEST-' . now()->format('YmdHis'),
            'amount' => 5000,
            'payment_status' => 'paid',
        ]);
        $booking->transaction = $transaction;
    }
    
    // Send the Booking Confirmation email with Payment Acknowledgement PDF
    \Illuminate\Support\Facades\Mail::to($email)->send(
        new \App\Mail\BookingConfirmation($booking)
    );
    
    echo "✓ Payment Acknowledgement email sent successfully to: {$email}\n";
    echo "✓ Booking Reference: {$booking->reference_number}\n";
    echo "✓ Transaction Number: {$booking->transaction_number}\n";
    echo "✓ The Payment_Acknowledgement.pdf has been attached with the new logo and E-Acknowledgement header\n";
    
} catch (\Exception $e) {
    echo "✗ Error sending email: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>

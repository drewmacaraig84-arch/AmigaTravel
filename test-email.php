<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();



\Illuminate\Support\Facades\Mail::raw('This is a test email to verify that the Amiga Gracia Travel Service email integration is working perfectly! Your setup is successful.', function ($msg) {
    $msg->to('drewmacaraig84@gmail.com')->subject('Amiga Gracia Test Email');
});

echo "Email successfully sent to drewmacaraig84@gmail.com!\n";

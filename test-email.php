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

\Illuminate\Support\Facades\Mail::raw('This is a test email to verify that the Amiga Gracia Travel Service email integration is working perfectly! Your setup is successful.', function ($msg) {
    $msg->to('drewmacaraig84@gmail.com')->subject('Amiga Gracia Test Email');
});

echo "Email successfully sent to drewmacaraig84@gmail.com!\n";

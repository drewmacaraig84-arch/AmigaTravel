<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo json_encode(DB::table("schedule_transport_class")->where("is_promo", 1)->get(), JSON_PRETTY_PRINT);

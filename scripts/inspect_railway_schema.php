<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

Config::set('database.connections.railway', [
    'driver'    => 'mysql',
    'host'      => 'kodama.proxy.rlwy.net',
    'port'      => 34553,
    'database'  => 'railway',
    'username'  => 'root',
    'password'  => 'CvPVaydCTsLSQigiGlbOaEYYhcqiYVsk',
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix'    => '',
    'strict'    => true,
    'engine'    => null,
]);

$db = DB::connection('railway');

echo "schedules columns:\n";
print_r($db->getSchemaBuilder()->getColumnListing('schedules'));

echo "schedule_transport_class columns:\n";
print_r($db->getSchemaBuilder()->getColumnListing('schedule_transport_class'));

echo "Sample schedule 328 transport classes:\n";
print_r($db->table('schedule_transport_class')->where('schedule_id', 328)->get());
echo "Sample schedule 328 accommodations:\n";
print_r($db->table('schedule_accommodations')->where('schedule_id', 328)->get());

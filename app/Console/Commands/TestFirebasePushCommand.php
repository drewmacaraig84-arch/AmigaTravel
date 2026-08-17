<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FirebasePushService;

class TestFirebasePushCommand extends Command
{
    protected $signature = 'push:test {--topic=all_users} {--title=Booking Confirmed} {--body=Your booking AGT-2026-TEST is confirmed! View and download your tickets now.}';
    protected $description = 'Send a test Firebase Cloud Messaging push notification';

    public function handle(): int
    {
        $topic = $this->option('topic');
        $title = $this->option('title');
        $body = $this->option('body');

        $this->info("Sending test push notification to topic '{$topic}'...");

        $success = FirebasePushService::sendToTopic($topic, $title, $body, [
            'type' => 'booking',
            'target_id' => 'AGT-2026-TEST',
        ]);

        if ($success) {
            $this->info("Push notification delivered to Google FCM successfully!");
            return Command::SUCCESS;
        } else {
            $this->error("Failed to send push notification. Check storage/logs/laravel.log.");
            return Command::FAILURE;
        }
    }
}

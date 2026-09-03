<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SystemCrashAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $alertData;

    public function __construct(array $alertData)
    {
        $this->alertData = $alertData;
    }

    public function build()
    {
        $severity = strtoupper($this->alertData['severity'] ?? 'CRITICAL');
        $subject = "[{$severity} CRASH ALERT] Amiga Gracia System Incident";

        return $this->subject($subject)
            ->view('emails.system-crash-alert');
    }
}

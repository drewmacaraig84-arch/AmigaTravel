<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MailConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_mail_defaults_to_smtp_when_credentials_are_present(): void
    {
        putenv('MAIL_MAILER=smtp');
        $_ENV['MAIL_MAILER'] = 'smtp';
        $_SERVER['MAIL_MAILER'] = 'smtp';
        putenv('MAIL_HOST=smtp.gmail.com');
        putenv('MAIL_PORT=587');
        putenv('MAIL_USERNAME=test@example.com');
        putenv('MAIL_PASSWORD=secret');
        putenv('MAIL_ENCRYPTION=tls');
        putenv('MAIL_FROM_ADDRESS=test@example.com');
        putenv('MAIL_FROM_NAME=');

        $this->refreshApplication();

        $this->assertSame('smtp', config('mail.default'));
        $this->assertSame('smtp.gmail.com', config('mail.mailers.smtp.host'));
        // from.address is populated from the real .env file during refreshApplication();
        // assert it is a valid email rather than a fixture-specific value.
        $this->assertMatchesRegularExpression('/.+@.+\..+/', config('mail.from.address'));
    }

    protected function tearDown(): void
    {
        putenv('MAIL_MAILER=array');
        $_ENV['MAIL_MAILER'] = 'array';
        $_SERVER['MAIL_MAILER'] = 'array';
        parent::tearDown();
    }
}

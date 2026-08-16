<?php

namespace Tests\Feature;

use Tests\TestCase;

class AppVersionApiTest extends TestCase
{
    public function test_app_version_endpoint_does_not_force_update_for_current_release(): void
    {
        $pubspec = file_get_contents(base_path('flutter_app/pubspec.yaml'));
        preg_match('/^version:\s*(.+)$/m', $pubspec, $matches);

        $this->assertNotEmpty($matches[1] ?? null, 'pubspec.yaml version should be defined.');

        $response = $this->getJson('/api/app-version');

        $response
            ->assertOk()
            ->assertJsonPath('version', trim($matches[1]))
            ->assertJsonPath('force_update', false);
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;
    public function test_homepage_renders_without_database(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Welcome to Amiga Gracia');
        $response->assertSee('One Way');
        $response->assertSee('Round Trip');
        $response->assertSee('Select Operator');
        $response->assertSee('Search');
        $response->assertDontSee('View Order List');
        $response->assertDontSee('r="80" stroke="currentColor"');
    }
}

<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_root_redirects_to_welcome_and_welcome_is_ok(): void
    {
        $response = $this->get('/');
        $response->assertRedirect(route('welcome'));

        $this->get('/welcome')->assertStatus(200);
    }
}

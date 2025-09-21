<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_accessible(): void
    {
        $this->get('/login')->assertStatus(200);
    }

    public function test_user_can_login_and_access_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@example.test',
            'password_hash' => Hash::make('secret123'),
        ]);

        $this->withoutMiddleware([VerifyCsrfToken::class]);

        $response = $this->post('/login', [
            'email' => 'admin@example.test',
            'password' => 'secret123',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticated();

        $this->get('/dashboard')->assertStatus(200);
    }
}
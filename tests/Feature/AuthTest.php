<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_login_with_correct_credentials()
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt($password = 'password123'),
        ]);

        // Attempt to login
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => $password,
        ]);

        // Assert the user is authenticated
        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/home'); // Adjust the redirect path as needed
    }

    /** @test */
    public function a_user_cannot_login_with_incorrect_credentials()
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Attempt to login with incorrect password
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        // Assert the user is not authenticated
        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }
}

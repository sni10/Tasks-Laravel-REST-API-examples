<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AuthTest extends TestCase
{
    public function testRegistration()
    {
        $response = $this->json('POST', '/api/v1/register', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['access_token', 'token_type']);
    }

    public function testLogin()
    {
        User::create([
            'name' => 'Login User',
            'email' => 'loginuser@example.com',
            'password' => bcrypt('password123')
        ]);

        $response = $this->json('POST', '/api/v1/login', [
            'email' => 'loginuser@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['access_token', 'token_type']);
    }

    public function testLogout()
    {
        $user = User::create([
            'name' => 'Logout User',
            'email' => 'logoutuser@example.com',
            'password' => bcrypt('password123')
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->post('/api/v1/logout');

        $response->assertStatus(200);
    }
}

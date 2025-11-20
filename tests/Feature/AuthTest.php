<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AuthTest extends TestCase
{
    public function testRegistration()
    {
        $response = $this->json('POST', '/api/v1/register', [
            'name' => 'Test User copy',
            'email' => 'test@copy.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['access_token', 'token_type']);
    }

    public function testLogin()
    {
        $this->testRegistration();

        $response = $this->json('POST', '/api/v1/login', [
            'email' => 'test@copy.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['access_token', 'token_type']);
    }

    public function testLogout()
    {
        $this->testLogin();

        $user = User::firstOrCreate([
            'email' => 'test@copy.com'
        ], [
            'name' => 'Test User copy',
            'password' => bcrypt('password123')
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->post('/api/v1/logout');

        $response->assertStatus(200);
    }
}

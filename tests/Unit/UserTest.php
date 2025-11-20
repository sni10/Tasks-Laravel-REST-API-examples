<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function testUserCreation()
    {
        $user = new User([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    public function test_user_can_register_via_api(): void
    {
        $email = 'dev_' . uniqid() . '@example.com';

        $response = $this->postJson('/api/auth/register', [
            'name'     => 'Tourism Developer',
            'email'    => $email,
            'password' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'user' => ['id', 'name', 'email'],
                ],
            ]);

        $this->assertDatabaseHas('users', ['email' => $email]);
    }

    public function test_user_can_login_via_api(): void
    {
        $email = 'user_' . uniqid() . '@example.com';
        $user = User::create([
            'name' => 'Login Test User',
            'email' => $email,
            'password_hash' => Hash::make('password123'),
            'role' => 'User',
            'status' => 'Active',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email'    => $email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'user',
                ],
            ]);
    }
}

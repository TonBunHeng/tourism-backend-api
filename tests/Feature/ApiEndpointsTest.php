<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiEndpointsTest extends TestCase
{
    public function test_api_health_check()
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);
    }

    public function test_user_login()
    {
        $email = 'login_test_' . uniqid() . '@example.com';
        $user = User::create([
            'name' => 'Test Login User',
            'email' => $email,
            'password_hash' => Hash::make('password123'),
            'role' => 'User',
            'status' => 'Active',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => ['token', 'user' => ['id', 'name', 'email', 'role']],
                 ]);
    }

    public function test_get_places_list()
    {
        $response = $this->getJson('/api/places');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         '*' => ['id', 'name', 'category_id', 'address', 'rating', 'status']
                     ],
                 ]);
    }

    public function test_get_dashboard_stats()
    {
        $response = $this->getJson('/api/dashboard/stats');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'stats' => ['total_places', 'total_provinces', 'total_categories', 'total_events'],
                     ],
                 ]);
    }
}

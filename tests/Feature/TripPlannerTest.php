<?php

namespace Tests\Feature;

use App\Models\Place;
use App\Models\Province;
use App\Models\Category;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TripPlannerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Alice Tourist',
            'email' => 'alice@example.com',
            'password_hash' => Hash::make('password123'),
            'role' => 'User',
            'status' => 'Active',
        ]);

        $this->otherUser = User::create([
            'name' => 'Bob Traveler',
            'email' => 'bob@example.com',
            'password_hash' => Hash::make('password123'),
            'role' => 'User',
            'status' => 'Active',
        ]);
    }

    public function test_user_can_create_and_view_trip(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/travel/trips', [
            'title' => 'Siem Reap 3-Day Adventure',
            'destination' => 'Siem Reap',
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addDays(8)->format('Y-m-d'),
            'budget' => 250.00,
            'travelers' => 2,
            'status' => 'planning',
            'notes' => 'Looking forward to sunrise at Angkor Wat',
            'itineraries' => [
                [
                    'day_number' => 1,
                    'time_slot' => '05:30 AM',
                    'activity' => 'Sunrise at Angkor Wat',
                    'estimated_cost' => 37,
                ],
                [
                    'day_number' => 1,
                    'time_slot' => '09:00 AM',
                    'activity' => 'Explore Bayon Temple',
                    'estimated_cost' => 0,
                ],
            ]
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'title' => 'Siem Reap 3-Day Adventure',
                    'travelers' => 2,
                ]
            ]);

        $tripId = $response->json('data.id');

        // Check index
        $indexResponse = $this->actingAs($this->user, 'sanctum')->getJson('/api/travel/trips');
        $indexResponse->assertStatus(200)
            ->assertJsonStructure(['data', 'pagination']);

        // Check show
        $showResponse = $this->actingAs($this->user, 'sanctum')->getJson("/api/travel/trips/{$tripId}");
        $showResponse->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $tripId,
                    'title' => 'Siem Reap 3-Day Adventure',
                ]
            ]);
    }

    public function test_user_cannot_modify_another_users_private_trip(): void
    {
        $trip = Trip::create([
            'user_id' => $this->user->id,
            'title' => 'Alice Private Trip',
            'is_public' => false,
        ]);

        $response = $this->actingAs($this->otherUser, 'sanctum')->putJson("/api/travel/trips/{$trip->id}", [
            'title' => 'Hacked Title',
        ]);

        $response->assertStatus(403);
    }
}

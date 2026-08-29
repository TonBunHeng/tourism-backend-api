<?php

namespace Tests\Feature;

use App\Models\AiConversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AiAssistantTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Sara Traveler',
            'email' => 'sara@example.com',
            'password_hash' => Hash::make('password123'),
            'role' => 'User',
            'status' => 'Active',
        ]);
    }

    public function test_ai_chat_persists_conversation_and_messages(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/travel/ai/chat', [
            'message' => 'What is the best time to visit Angkor Wat for sunrise?',
            'province' => 'Siem Reap',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('ai_conversations', [
            'user_id' => $this->user->id,
            'province' => 'Siem Reap',
        ]);

        $conversation = AiConversation::where('user_id', $this->user->id)->first();
        $this->assertNotNull($conversation);
        $this->assertGreaterThanOrEqual(2, $conversation->messages()->count());
    }

    public function test_weather_and_currency_endpoints_respond_cleanly(): void
    {
        $weatherResponse = $this->getJson('/api/travel/weather?province=Siem%20Reap');
        $weatherResponse->assertStatus(200)
            ->assertJson(['success' => true]);

        $currencyResponse = $this->getJson('/api/travel/currency');
        $currencyResponse->assertStatus(200)
            ->assertJson(['success' => true]);

        $convertResponse = $this->postJson('/api/travel/currency/convert', [
            'amount' => 10,
            'from_currency' => 'USD',
            'to_currency' => 'KHR',
        ]);
        $convertResponse->assertStatus(200)
            ->assertJson(['success' => true]);
    }
}

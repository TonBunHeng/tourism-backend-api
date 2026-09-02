<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class TravelSocialAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_login_with_api_id_token_verification()
    {
        Http::fake([
            'https://oauth2.googleapis.com/tokeninfo*' => Http::response([
                'sub' => 'google_123456789',
                'email' => 'realuser@gmail.com',
                'name' => 'Real Google User',
                'picture' => 'https://lh3.googleusercontent.com/avatar.jpg',
            ], 200),
        ]);

        $response = $this->postJson('/api/travel/auth/google', [
            'id_token' => 'mock_google_id_token',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.email', 'realuser@gmail.com')
            ->assertJsonPath('data.user.name', 'Real Google User')
            ->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'name', 'email', 'avatar', 'role'],
                    'token',
                    'token_type',
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'realuser@gmail.com',
            'provider' => 'google',
            'provider_id' => 'google_123456789',
        ]);
    }

    public function test_facebook_login_with_api_access_token_verification()
    {
        Http::fake([
            'https://graph.facebook.com/me*' => Http::response([
                'id' => 'fb_987654321',
                'email' => 'realuser@facebook.com',
                'name' => 'Real FB User',
                'picture' => [
                    'data' => [
                        'url' => 'https://platform-lookaside.fbsbx.com/avatar.jpg'
                    ]
                ],
            ], 200),
        ]);

        $response = $this->postJson('/api/travel/auth/facebook', [
            'access_token' => 'mock_fb_access_token',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.email', 'realuser@facebook.com')
            ->assertJsonPath('data.user.name', 'Real FB User');

        $this->assertDatabaseHas('users', [
            'email' => 'realuser@facebook.com',
            'provider' => 'facebook',
            'provider_id' => 'fb_987654321',
        ]);
    }

    public function test_socialite_google_redirect()
    {
        $response = $this->get('/api/travel/auth/google/redirect');
        $response->assertRedirect();
        $this->assertStringContainsString('accounts.google.com', $response->getTargetUrl());
    }

    public function test_socialite_facebook_redirect()
    {
        $response = $this->get('/api/travel/auth/facebook/redirect');
        $response->assertRedirect();
        $this->assertStringContainsString('facebook.com', $response->getTargetUrl());
    }

    public function test_socialite_google_callback()
    {
        $abstractUser = Mockery::mock('Laravel\Socialite\Two\User');
        $abstractUser->shouldReceive('getId')->andReturn('google_socialite_123');
        $abstractUser->shouldReceive('getEmail')->andReturn('socialite_google@gmail.com');
        $abstractUser->shouldReceive('getName')->andReturn('Socialite Google User');
        $abstractUser->shouldReceive('getAvatar')->andReturn('https://lh3.googleusercontent.com/avatar.jpg');

        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('stateless')->andReturnSelf();
        $provider->shouldReceive('user')->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get('/api/travel/auth/google/callback');

        $response->assertRedirect();
        $this->assertStringContainsString('/auth/callback?token=', $response->getTargetUrl());

        $this->assertDatabaseHas('users', [
            'email' => 'socialite_google@gmail.com',
            'provider' => 'google',
            'provider_id' => 'google_socialite_123',
        ]);
    }

    public function test_socialite_facebook_callback()
    {
        $abstractUser = Mockery::mock('Laravel\Socialite\Two\User');
        $abstractUser->shouldReceive('getId')->andReturn('fb_socialite_123');
        $abstractUser->shouldReceive('getEmail')->andReturn('socialite_fb@facebook.com');
        $abstractUser->shouldReceive('getName')->andReturn('Socialite FB User');
        $abstractUser->shouldReceive('getAvatar')->andReturn('https://platform-lookaside.fbsbx.com/avatar.jpg');

        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('stateless')->andReturnSelf();
        $provider->shouldReceive('user')->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('facebook')->andReturn($provider);

        $response = $this->get('/api/travel/auth/facebook/callback');

        $response->assertRedirect();
        $this->assertStringContainsString('/auth/callback?token=', $response->getTargetUrl());

        $this->assertDatabaseHas('users', [
            'email' => 'socialite_fb@facebook.com',
            'provider' => 'facebook',
            'provider_id' => 'fb_socialite_123',
        ]);
    }
}

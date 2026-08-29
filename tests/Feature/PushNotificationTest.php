<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\PushSubscription;
use App\Models\User;
use App\Models\UserNotificationSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PushNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Tourist Test User',
            'email' => 'tourist@example.com',
            'password_hash' => Hash::make('password123'),
            'role' => 'User',
            'status' => 'Active',
        ]);

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@angkorverses.com',
            'password_hash' => Hash::make('password123'),
            'role' => 'Admin',
            'status' => 'Active',
        ]);
    }

    public function test_user_can_subscribe_to_push_notifications(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/travel/notifications/subscribe', [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-sub-12345',
            'keys' => [
                'p256dh' => 'BNcRdreALRF8M+c5R8eT5...sample-key',
                'auth' => 'sampleAuthSecret123',
            ],
            'content_encoding' => 'aesgcm',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('push_subscriptions', [
            'user_id' => $this->user->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-sub-12345',
        ]);
    }

    public function test_user_can_unsubscribe_from_push(): void
    {
        PushSubscription::create([
            'user_id' => $this->user->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-sub-to-remove',
            'public_key' => 'key123',
            'auth_token' => 'auth123',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->deleteJson('/api/travel/notifications/subscribe', [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-sub-to-remove',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('push_subscriptions', [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-sub-to-remove',
        ]);
    }

    public function test_user_can_fetch_and_update_notification_settings(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/travel/notifications/settings');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'push_enabled' => true,
                    'events_enabled' => true,
                    'messages_enabled' => true,
                    'system_enabled' => true,
                ],
            ]);

        $updateResponse = $this->actingAs($this->user, 'sanctum')->putJson('/api/travel/notifications/settings', [
            'push_enabled' => false,
            'events_enabled' => true,
            'messages_enabled' => false,
        ]);

        $updateResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'push_enabled' => false,
                    'events_enabled' => true,
                    'messages_enabled' => false,
                ],
            ]);

        $this->assertDatabaseHas('user_notification_settings', [
            'user_id' => $this->user->id,
            'push_enabled' => false,
            'messages_enabled' => false,
        ]);
    }

    public function test_user_can_fetch_notifications_and_unread_count(): void
    {
        Notification::create([
            'user_id' => $this->user->id,
            'type' => 'event',
            'category' => 'Events',
            'title' => 'Water Festival Announced',
            'description' => 'Join the annual celebration in Phnom Penh.',
            'read' => false,
        ]);

        Notification::create([
            'user_id' => $this->user->id,
            'type' => 'message',
            'category' => 'Messages',
            'title' => 'Support reply',
            'description' => 'Our guide replied to your inquiry.',
            'read' => false,
        ]);

        $countResponse = $this->actingAs($this->user, 'sanctum')->getJson('/api/travel/notifications/unread-count');
        $countResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'unread_count' => 2,
            ]);

        $listResponse = $this->actingAs($this->user, 'sanctum')->getJson('/api/travel/notifications');
        $listResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'meta' => [
                    'unread_count' => 2,
                ],
            ]);
    }

    public function test_user_can_mark_notification_as_read(): void
    {
        $notif = Notification::create([
            'user_id' => $this->user->id,
            'type' => 'system',
            'category' => 'System',
            'title' => 'System update',
            'read' => false,
        ]);

        $readResponse = $this->actingAs($this->user, 'sanctum')->patchJson("/api/travel/notifications/{$notif->id}/read");
        $readResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('notifications', [
            'id' => $notif->id,
            'read' => true,
        ]);
    }

    public function test_user_can_mark_all_read(): void
    {
        Notification::create([
            'user_id' => $this->user->id,
            'title' => 'Notif 1',
            'read' => false,
        ]);
        Notification::create([
            'user_id' => $this->user->id,
            'title' => 'Notif 2',
            'read' => false,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->patchJson('/api/travel/notifications/read-all');
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'meta' => [
                    'unread_count' => 0,
                ],
            ]);

        $this->assertEquals(0, Notification::where('user_id', $this->user->id)->where('read', false)->count());
    }

    public function test_public_vapid_key_endpoint(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/travel/notifications/vapid-key');
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'data' => ['public_key'],
            ]);
    }

    public function test_admin_can_subscribe_and_manage_notifications_via_admin_api(): void
    {
        // Admin subscribe push
        $subResponse = $this->actingAs($this->admin, 'sanctum')->postJson('/api/notifications/subscribe', [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/admin-sub-99999',
            'keys' => [
                'p256dh' => 'admin-public-key',
                'auth' => 'admin-auth-token',
            ],
            'content_encoding' => 'aesgcm',
        ]);

        $subResponse->assertStatus(200)->assertJson(['success' => true]);

        $this->assertDatabaseHas('push_subscriptions', [
            'user_id' => $this->admin->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/admin-sub-99999',
        ]);

        // Admin notification settings
        $settingsResponse = $this->actingAs($this->admin, 'sanctum')->getJson('/api/notifications/settings');
        $settingsResponse->assertStatus(200)->assertJson(['success' => true]);

        $updateSettings = $this->actingAs($this->admin, 'sanctum')->putJson('/api/notifications/settings', [
            'push_enabled' => true,
            'system_enabled' => true,
            'messages_enabled' => true,
        ]);
        $updateSettings->assertStatus(200)->assertJson(['success' => true]);

        // Admin notifications list & unread count
        Notification::create([
            'user_id' => null, // system-wide announcement for admins
            'type' => 'system',
            'category' => 'System',
            'title' => 'New User Registration Spurt',
            'description' => '50 new tourists registered in the last hour.',
            'read' => false,
        ]);

        $adminCount = $this->actingAs($this->admin, 'sanctum')->getJson('/api/notifications/unread-count');
        $adminCount->assertStatus(200)->assertJson(['success' => true, 'unread_count' => 1]);

        $adminList = $this->actingAs($this->admin, 'sanctum')->getJson('/api/notifications');
        $adminList->assertStatus(200)->assertJson(['success' => true]);

        // Admin mark all read
        $markAll = $this->actingAs($this->admin, 'sanctum')->patchJson('/api/notifications/read-all');
        $markAll->assertStatus(200)->assertJson(['success' => true]);

        // Admin unsubscribe push
        $unsub = $this->actingAs($this->admin, 'sanctum')->deleteJson('/api/notifications/subscribe', [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/admin-sub-99999',
        ]);
        $unsub->assertStatus(200)->assertJson(['success' => true]);
    }
}

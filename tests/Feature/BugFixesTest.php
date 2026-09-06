<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Place;
use App\Models\Province;
use App\Models\Review;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BugFixesTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $admin1;
    protected User $admin2;
    protected User $guideEditor;
    protected User $tourist;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin_test@example.com',
            'password_hash' => Hash::make('secret123'),
            'role' => User::ROLE_SUPER_ADMIN,
            'status' => 'Active',
        ]);

        $this->admin1 = User::create([
            'name' => 'Admin One',
            'email' => 'admin1_test@example.com',
            'password_hash' => Hash::make('secret123'),
            'role' => User::ROLE_ADMIN,
            'status' => 'Active',
        ]);

        $this->admin2 = User::create([
            'name' => 'Admin Two',
            'email' => 'admin2_test@example.com',
            'password_hash' => Hash::make('secret123'),
            'role' => User::ROLE_ADMIN,
            'status' => 'Active',
        ]);

        $this->guideEditor = User::create([
            'name' => 'Guide Editor',
            'email' => 'guide_test@example.com',
            'password_hash' => Hash::make('secret123'),
            'role' => User::ROLE_GUIDE_EDITOR,
            'status' => 'Active',
        ]);

        $this->tourist = User::create([
            'name' => 'Tourist User',
            'email' => 'tourist_test@example.com',
            'password_hash' => Hash::make('secret123'),
            'role' => User::ROLE_USER,
            'status' => 'Active',
        ]);
    }

    public function test_admin_can_reply_to_reviews_via_route(): void
    {
        Sanctum::actingAs($this->admin1, ['*']);

        $category = Category::create(['name' => 'Heritage', 'slug' => 'heritage']);
        $province = Province::create(['name' => 'Siem Reap']);

        $place = Place::create([
            'name' => 'Angkor Wat',
            'category_id' => $category->id,
            'province_id' => $province->id,
            'address' => 'Siem Reap, Cambodia',
            'status' => 'Active',
        ]);

        $review = Review::create([
            'user_id' => $this->tourist->id,
            'place_id' => $place->id,
            'rating' => 5,
            'comment' => 'Spectacular sunrise view!',
            'status' => 'Approved',
        ]);

        $response = $this->postJson("/api/reviews/{$review->id}/replies", [
            'comment' => 'Thank you for visiting Angkor Wat!',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Reply added successfully.',
            ]);

        $this->assertDatabaseHas('review_replies', [
            'review_id' => $review->id,
            'user_id' => $this->admin1->id,
            'comment' => 'Thank you for visiting Angkor Wat!',
        ]);
    }

    public function test_admin_cannot_delete_or_modify_other_admin_accounts(): void
    {
        Sanctum::actingAs($this->admin1, ['*']);

        // Admin 1 cannot delete Admin 2
        $resDelete = $this->deleteJson("/api/users/{$this->admin2->id}");
        $resDelete->assertStatus(403);

        // Admin 1 cannot update Admin 2 status
        $resStatus = $this->putJson("/api/users/{$this->admin2->id}/status", [
            'status' => 'Suspended',
        ]);
        $resStatus->assertStatus(403);

        // Super Admin CAN delete Admin 2
        Sanctum::actingAs($this->superAdmin, ['*']);
        $resSuperDelete = $this->deleteJson("/api/users/{$this->admin2->id}");
        $resSuperDelete->assertStatus(200);
        $this->assertDatabaseMissing('users', ['id' => $this->admin2->id]);
    }

    public function test_public_registration_forces_user_role(): void
    {
        $response = $this->postJson('/api/travel/auth/register', [
            'name' => 'Exploit User',
            'email' => 'exploit@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'guide_editor',
        ]);

        // Should either fail validation or register with standard user role
        if ($response->status() === 201) {
            $this->assertDatabaseHas('users', [
                'email' => 'exploit@example.com',
                'role' => 'user',
            ]);
        } else {
            $response->assertStatus(422);
        }
    }

    public function test_partial_trip_update_with_end_date(): void
    {
        Sanctum::actingAs($this->tourist, ['*']);

        $trip = Trip::create([
            'user_id' => $this->tourist->id,
            'title' => 'Siem Reap Explorer',
            'destination' => 'Siem Reap',
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-05',
            'status' => 'planning',
        ]);

        $response = $this->putJson("/api/travel/trips/{$trip->id}", [
            'end_date' => '2026-10-07',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Trip updated successfully.',
            ]);

        $trip->refresh();
        $this->assertEquals('2026-10-07', $trip->end_date->format('Y-m-d'));
    }

    public function test_guide_editor_cannot_access_settings_or_security_alerts(): void
    {
        Sanctum::actingAs($this->guideEditor, ['*']);

        $this->getJson('/api/settings')->assertStatus(403);
        $this->putJson('/api/settings', ['settings' => []])->assertStatus(403);
        $this->getJson('/api/security-alerts')->assertStatus(403);
    }

    public function test_all_roles_can_add_and_remove_favorites_without_error(): void
    {
        $category = Category::create(['name' => 'Temples', 'slug' => 'temples']);
        $province = Province::create(['name' => 'Siem Reap', 'slug' => 'siem-reap']);
        $place = Place::create([
            'name' => 'Bayon Temple',
            'category_id' => $category->id,
            'province_id' => $province->id,
            'address' => 'Angkor Thom, Siem Reap',
            'status' => 'Active',
        ]);

        $businessOwner = User::create([
            'name' => 'Business Owner',
            'email' => 'biz_fav@test.com',
            'password_hash' => Hash::make('secret123'),
            'role' => User::ROLE_BUSINESS_OWNER,
            'status' => 'Active',
        ]);

        $users = [$this->tourist, $businessOwner, $this->guideEditor, $this->admin1, $this->superAdmin];

        foreach ($users as $user) {
            Sanctum::actingAs($user, ['*']);

            // Add to favorites
            $response = $this->postJson('/api/travel/favorites', [
                'place_id' => $place->id,
                'visited' => false,
            ]);

            $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                    'message' => 'Destination saved to favorites successfully.',
                ]);

            $this->assertDatabaseHas('favorites', [
                'user_id' => $user->id,
                'place_id' => $place->id,
            ]);

            // Remove from favorites
            $deleteResponse = $this->deleteJson("/api/travel/favorites/{$place->id}");
            $deleteResponse->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Removed from favorites successfully.',
                ]);
        }
    }

    public function test_admin_roles_cannot_login_via_travel_api(): void
    {
        $adminRoles = [$this->superAdmin, $this->admin1, $this->guideEditor];

        foreach ($adminRoles as $staff) {
            $response = $this->postJson('/api/travel/auth/login', [
                'email' => $staff->email,
                'password' => 'secret123',
            ]);

            $response->assertStatus(403)
                ->assertJson([
                    'success' => false,
                    'message' => 'Access restricted. Administrative accounts (Super Admin, Admin, Tourism Content Editor) must sign in via the Admin Portal.',
                ]);
        }

        // Tourist can log in
        $touristResponse = $this->postJson('/api/travel/auth/login', [
            'email' => $this->tourist->email,
            'password' => 'secret123',
        ]);
        $touristResponse->assertStatus(200);
    }

    public function test_deletion_requests_restricted_to_super_admin_only(): void
    {
        // 1. Tourism Content Editor cannot access deletion-requests or analytics
        Sanctum::actingAs($this->guideEditor, ['*']);
        $this->getJson('/api/deletion-requests')->assertStatus(403);
        $this->getJson('/api/deletion-requests/analytics')->assertStatus(403);

        // 2. Admin cannot access deletion-requests or analytics
        Sanctum::actingAs($this->admin1, ['*']);
        $this->getJson('/api/deletion-requests')->assertStatus(403);
        $this->getJson('/api/deletion-requests/analytics')->assertStatus(403);

        // 3. Super Admin CAN access deletion-requests and analytics
        Sanctum::actingAs($this->superAdmin, ['*']);
        $resIndex = $this->getJson('/api/deletion-requests');
        $resIndex->assertStatus(200);

        $resAnalytics = $this->getJson('/api/deletion-requests/analytics');
        $resAnalytics->assertStatus(200);

        // 4. Create a deletion request from tourist
        $deletionReq = \App\Models\DeletionRequest::create([
            'user_id' => $this->tourist->id,
            'request_type' => 'account',
            'reason' => 'Need account deletion',
            'status' => 'pending',
            'urgency' => 'medium',
        ]);

        // 5. Admin cannot update deletion request status
        Sanctum::actingAs($this->admin1, ['*']);
        $this->putJson("/api/deletion-requests/{$deletionReq->id}/status", [
            'status' => 'approved',
        ])->assertStatus(403);

        // 6. Super Admin can update deletion request status
        Sanctum::actingAs($this->superAdmin, ['*']);
        $this->putJson("/api/deletion-requests/{$deletionReq->id}/status", [
            'status' => 'approved',
        ])->assertStatus(200);

        // 7. Admin and Tourism Content Editor CAN submit deletion requests
        Sanctum::actingAs($this->admin1, ['*']);
        $resAdminSubmit = $this->postJson('/api/deletion-requests', [
            'request_type' => 'item',
            'reason' => 'Admin requesting event removal',
            'items' => [
                [
                    'item_type' => 'event',
                    'item_id' => 999,
                    'item_name' => 'Water Festival',
                ]
            ]
        ]);
        $resAdminSubmit->assertStatus(201);

        Sanctum::actingAs($this->guideEditor, ['*']);
        $resEditorSubmit = $this->postJson('/api/deletion-requests', [
            'request_type' => 'item',
            'reason' => 'Tourism Content Editor requesting place removal',
            'items' => [
                [
                    'item_type' => 'place',
                    'item_id' => 888,
                    'item_name' => 'Angkor Temple',
                ]
            ]
        ]);
        $resEditorSubmit->assertStatus(201);

        // 8. Direct deletions are restricted to Super Admin only
        $event = \App\Models\Event::create([
            'title' => 'Direct Delete Test Event',
            'category' => 'Cultural',
            'location' => 'Phnom Penh',
            'start_date' => '2026-10-01',
            'status' => 'Upcoming',
        ]);

        // Editor cannot directly delete event
        Sanctum::actingAs($this->guideEditor, ['*']);
        $this->deleteJson("/api/events/{$event->id}")->assertStatus(403);

        // Admin cannot directly delete event
        Sanctum::actingAs($this->admin1, ['*']);
        $this->deleteJson("/api/events/{$event->id}")->assertStatus(403);

        // Super Admin CAN directly delete event
        Sanctum::actingAs($this->superAdmin, ['*']);
        $this->deleteJson("/api/events/{$event->id}")->assertStatus(200);
        $this->assertDatabaseMissing('events', ['id' => $event->id]);

        // 9. Tourist (User) CAN submit deletion request (even if request_type is omitted)
        $freshTourist = User::create([
            'name' => 'VIT Vong',
            'email' => 'vit.vong@example.com',
            'password_hash' => Hash::make('secret123'),
            'role' => User::ROLE_USER,
            'status' => 'Active',
        ]);

        Sanctum::actingAs($freshTourist, ['*']);
        $resTouristDel = $this->postJson('/api/travel/deletion-requests', [
            'email' => $freshTourist->email,
            'reason' => 'Delete Account VIT Vong',
        ]);
        $resTouristDel->assertStatus(201);
        $this->assertDatabaseHas('deletion_requests', [
            'user_id' => $freshTourist->id,
            'request_type' => 'account',
            'reason' => 'Delete Account VIT Vong',
            'status' => 'pending',
        ]);

        // 10. Business Owner CAN submit deletion request
        $businessOwner = \App\Models\User::where('role', \App\Models\User::ROLE_BUSINESS_OWNER)->first() ?? \App\Models\User::create([
            'name' => 'Sokha Chanthou',
            'email' => 'owner_del_test@example.com',
            'password_hash' => \Illuminate\Support\Facades\Hash::make('secret123'),
            'role' => \App\Models\User::ROLE_BUSINESS_OWNER,
            'status' => 'Active',
        ]);

        Sanctum::actingAs($businessOwner, ['*']);
        $resOwnerDel = $this->postJson('/api/travel/deletion-requests', [
            'email' => $businessOwner->email,
            'reason' => 'Business owner closing account',
            'request_type' => 'account',
        ]);
        $resOwnerDel->assertStatus(201);
        $this->assertDatabaseHas('deletion_requests', [
            'user_id' => $businessOwner->id,
            'request_type' => 'account',
            'status' => 'pending',
        ]);
    }

    public function test_super_admin_has_all_achievements_unlocked(): void
    {
        // Check and award achievements for Super Admin
        \App\Services\AchievementManager::checkAndAward($this->superAdmin);

        $superAdminAchievements = \App\Models\UserAchievement::where('user_id', $this->superAdmin->id)->get();
        $this->assertNotEmpty($superAdminAchievements);
        foreach ($superAdminAchievements as $ach) {
            $this->assertTrue((bool)$ach->unlocked, "Achievement {$ach->achievement_name} should be unlocked for Super Admin");
        }

        // Test API response
        Sanctum::actingAs($this->superAdmin, ['*']);
        $response = $this->getJson('/api/achievements');
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertNotEmpty($data);
        foreach ($data as $badge) {
            $this->assertTrue((bool)$badge['unlocked'], "Badge {$badge['achievement_name']} should be unlocked in API");
        }
    }
}

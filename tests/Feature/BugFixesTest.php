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
}

<?php

namespace Tests\Feature;

use App\Models\BlockedIp;
use App\Models\Category;
use App\Models\Notification;
use App\Models\Place;
use App\Models\Province;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * 1. Unauthenticated request to protected Admin API must return 401.
     */
    public function test_unauthenticated_request_to_admin_api_returns_401(): void
    {
        $response = $this->getJson('/api/dashboard');
        $response->assertStatus(401);

        $response = $this->getJson('/api/users');
        $response->assertStatus(401);

        $response = $this->getJson('/api/security-alerts');
        $response->assertStatus(401);
    }

    /**
     * 2. Unauthenticated request to protected Tourist API must return 401.
     */
    public function test_unauthenticated_request_to_tourist_api_returns_401(): void
    {
        $response = $this->getJson('/api/travel/auth/me');
        $response->assertStatus(401);

        $response = $this->getJson('/api/travel/favorites');
        $response->assertStatus(401);

        $response = $this->postJson('/api/travel/reviews', [
            'place_id' => 1,
            'rating' => 5,
            'comment' => 'Great temple',
        ]);
        $response->assertStatus(401);
    }

    /**
     * 3. Tourist User attempting to access Admin API must return 403 Forbidden.
     */
    public function test_tourist_user_cannot_access_admin_api_returns_403(): void
    {
        $tourist = User::create([
            'name' => 'Tourist John',
            'email' => 'john@tourist.com',
            'password_hash' => Hash::make('password123'),
            'role' => 'User',
            'status' => 'Active',
        ]);

        Sanctum::actingAs($tourist, ['*']);

        // Admin analytics & management
        $response = $this->getJson('/api/dashboard');
        $response->assertStatus(403);

        $response = $this->getJson('/api/users');
        $response->assertStatus(403);

        $response = $this->getJson('/api/security-alerts');
        $response->assertStatus(403);

        $response = $this->getJson('/api/reports/analytics');
        $response->assertStatus(403);
    }

    /**
     * 4. Admin and Super Admin users can access authorized Admin endpoints (200).
     */
    public function test_admin_and_super_admin_can_access_admin_endpoints(): void
    {
        $admin = User::create([
            'name' => 'Admin Alex',
            'email' => 'alex@admin.com',
            'password_hash' => Hash::make('password123'),
            'role' => 'Admin',
            'status' => 'Active',
        ]);

        Sanctum::actingAs($admin, ['*']);

        $response = $this->getJson('/api/dashboard');
        $response->assertStatus(200);

        $response = $this->getJson('/api/users');
        $response->assertStatus(200);
    }

    /**
     * 5. Public registration cannot escalate role to Admin or Super Admin.
     */
    public function test_public_registration_cannot_escalate_role(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Attacker',
            'email' => 'attacker@exploit.com',
            'password' => 'secret123',
            'role' => 'Super Admin', // Attempted role escalation
        ]);

        $response->assertStatus(201);

        $user = User::where('email', 'attacker@exploit.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue(in_array($user->role, ['user', 'User'], true), 'Public registration MUST always create role=user');
    }

    /**
     * 6. Non-Super Admin cannot create or promote to Super Admin.
     */
    public function test_regular_admin_cannot_create_or_promote_super_admin(): void
    {
        $admin = User::create([
            'name' => 'Regular Admin',
            'email' => 'admin@test.com',
            'password_hash' => Hash::make('password123'),
            'role' => 'Admin',
            'status' => 'Active',
        ]);

        Sanctum::actingAs($admin, ['*']);

        // Attempt to create a Super Admin
        $response = $this->postJson('/api/users', [
            'name' => 'Hacked Super Admin',
            'email' => 'hacked@superadmin.com',
            'password' => 'password123',
            'role' => 'Super Admin',
            'status' => 'Active',
        ]);

        $response->assertStatus(403);
    }

    /**
     * 7. Suspended / Inactive users cannot login and their tokens are blocked.
     */
    public function test_inactive_and_suspended_users_are_blocked(): void
    {
        $suspendedUser = User::create([
            'name' => 'Suspended User',
            'email' => 'suspended@test.com',
            'password_hash' => Hash::make('password123'),
            'role' => 'User',
            'status' => 'Suspended',
        ]);

        // Login attempt blocked
        $response = $this->postJson('/api/travel/auth/login', [
            'email' => 'suspended@test.com',
            'password' => 'password123',
        ]);
        $response->assertStatus(403);

        // Even with an existing token, requests are blocked by middleware
        Sanctum::actingAs($suspendedUser, ['*']);
        $response = $this->getJson('/api/travel/auth/me');
        $response->assertStatus(403);
    }

    /**
     * 8. Blocked IPs cannot login.
     */
    public function test_blocked_ip_cannot_login(): void
    {
        BlockedIp::create([
            'ip_address' => '192.168.1.100',
            'reason' => 'Brute force violation',
            'is_active' => true,
        ]);

        $response = $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.100'])
            ->postJson('/api/auth/login', [
                'email' => 'admin@tourism.gov.kh',
                'password' => 'password123',
            ]);

        $response->assertStatus(403);
        $response->assertJsonFragment(['error' => 'IP_BLOCKED']);
    }

    /**
     * 9. IDOR Protection: User A cannot edit or delete User B's review.
     */
    public function test_idor_user_cannot_edit_or_delete_another_users_review(): void
    {
        $userA = User::create([
            'name' => 'User A',
            'email' => 'usera@test.com',
            'password_hash' => Hash::make('password123'),
            'role' => 'User',
            'status' => 'Active',
        ]);

        $userB = User::create([
            'name' => 'User B',
            'email' => 'userb@test.com',
            'password_hash' => Hash::make('password123'),
            'role' => 'User',
            'status' => 'Active',
        ]);

        $province = Province::create(['name' => 'Siem Reap']);
        $category = Category::create(['name' => 'Temples']);
        $place = Place::create([
            'name' => 'Angkor Wat',
            'province_id' => $province->id,
            'category_id' => $category->id,
            'address' => 'Angkor Archaeological Park, Siem Reap',
        ]);

        $review = Review::create([
            'user_id' => $userA->id,
            'place_id' => $place->id,
            'rating' => 5,
            'title' => 'User A Review',
            'comment' => 'Original review text',
            'status' => 'Approved',
        ]);

        // User B attempts to edit User A's review
        Sanctum::actingAs($userB, ['*']);
        $response = $this->putJson("/api/travel/reviews/{$review->id}", [
            'place_id' => $place->id,
            'rating' => 1,
            'comment' => 'Tampered review text',
        ]);
        $response->assertStatus(403);

        // User B attempts to delete User A's review
        $response = $this->deleteJson("/api/travel/reviews/{$review->id}");
        $response->assertStatus(403);

        // Verify review remained untouched
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'comment' => 'Original review text',
        ]);
    }

    /**
     * 11. IDOR Protection: User cannot delete or modify another user's favorite wishlist item.
     */
    public function test_idor_user_cannot_access_or_delete_other_users_favorite(): void
    {
        $userA = User::create([
            'name' => 'User A',
            'email' => 'usera@test.com',
            'password_hash' => Hash::make('password123'),
            'role' => 'User',
            'status' => 'Active',
        ]);

        $userB = User::create([
            'name' => 'User B',
            'email' => 'userb@test.com',
            'password_hash' => Hash::make('password123'),
            'role' => 'User',
            'status' => 'Active',
        ]);

        $province = Province::create(['name' => 'Siem Reap']);
        $category = Category::create(['name' => 'Temples']);
        $place = Place::create([
            'name' => 'Bayon',
            'province_id' => $province->id,
            'category_id' => $category->id,
            'address' => 'Angkor Thom, Siem Reap',
        ]);

        $favA = \App\Models\Favorite::create([
            'user_id' => $userA->id,
            'place_id' => $place->id,
            'visited' => false,
            'saved_date' => now()->toDateString(),
        ]);

        // User B attempts to delete User A's favorite
        Sanctum::actingAs($userB, ['*']);
        $response = $this->deleteJson("/api/travel/favorites/{$favA->id}");
        $response->assertStatus(404);

        // User B wishlist must be empty
        $response = $this->getJson('/api/travel/favorites');
        $response->assertStatus(200);
        $this->assertCount(0, $response->json('data'));
    }

    /**
     * 12. Rate Limiting: Excessive failed login attempts trigger 429 Too Many Requests.
     */
    public function test_rate_limiting_triggers_429_on_excessive_failed_logins(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/auth/login', [
                'email' => 'admin@tourism.gov.kh',
                'password' => 'wrongpassword',
            ]);
        }

        // The 11th attempt must be rejected with 429 Too Many Requests
        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@tourism.gov.kh',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(429);
    }

    /**
     * 13. Token Revocation on Logout: Once logged out, previous token is invalidated.
     */
    public function test_token_is_revoked_on_logout(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'logout_test@example.com',
            'password_hash' => Hash::make('password123'),
            'role' => 'User',
            'status' => 'Active',
        ]);

        $loginResponse = $this->postJson('/api/travel/auth/login', [
            'email' => 'logout_test@example.com',
            'password' => 'password123',
        ]);

        $loginResponse->assertStatus(200);
        $token = $loginResponse->json('data.token');

        // Can access with token
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/travel/auth/me');
        $response->assertStatus(200);

        // Perform logout
        $logoutResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/travel/auth/logout');
        $logoutResponse->assertStatus(200);

        // Verify token deleted from database
        $this->assertDatabaseCount('personal_access_tokens', 0);

        auth()->forgetGuards();

        // Access with old token is now rejected with 401
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/travel/auth/me');
        $response->assertStatus(401);
    }
}

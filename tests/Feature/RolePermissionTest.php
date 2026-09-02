<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Province;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $admin;
    protected User $guideEditor;
    protected User $businessOwner;
    protected User $tourist;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::create([
            'name' => 'Super Admin User',
            'email' => 'superadmin@test.com',
            'password_hash' => Hash::make('password123'),
            'role' => User::ROLE_SUPER_ADMIN,
            'status' => 'Active',
        ]);

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password_hash' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
            'status' => 'Active',
        ]);

        $this->guideEditor = User::create([
            'name' => 'Guide Editor User',
            'email' => 'guide@test.com',
            'password_hash' => Hash::make('password123'),
            'role' => User::ROLE_GUIDE_EDITOR,
            'status' => 'Active',
        ]);

        $this->businessOwner = User::create([
            'name' => 'Business Owner User',
            'email' => 'business@test.com',
            'password_hash' => Hash::make('password123'),
            'role' => User::ROLE_BUSINESS_OWNER,
            'status' => 'Active',
        ]);

        $this->tourist = User::create([
            'name' => 'Tourist User',
            'email' => 'tourist@test.com',
            'password_hash' => Hash::make('password123'),
            'role' => User::ROLE_USER,
            'status' => 'Active',
        ]);
    }

    /**
     * 1. Super Admin has unrestricted access to all Admin endpoints.
     */
    public function test_super_admin_has_full_access(): void
    {
        Sanctum::actingAs($this->superAdmin, ['*']);

        $this->getJson('/api/dashboard')->assertStatus(200);
        $this->getJson('/api/users')->assertStatus(200);
        $this->getJson('/api/tracking')->assertStatus(200);
        $this->getJson('/api/security-alerts')->assertStatus(200);
        $this->getJson('/api/settings')->assertStatus(200);

        // Super Admin can create another Super Admin
        $res = $this->postJson('/api/users', [
            'name' => 'New Super Admin',
            'email' => 'newsuper@test.com',
            'password' => 'password123',
            'role' => 'super_admin',
            'status' => 'Active',
        ]);
        $res->assertStatus(201);
    }

    /**
     * 2. Admin cannot create or modify Super Admin accounts.
     */
    public function test_admin_cannot_escalate_or_modify_super_admin(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        // Admin can access general dashboard and users
        $this->getJson('/api/dashboard')->assertStatus(200);
        $this->getJson('/api/users')->assertStatus(200);

        // Admin CANNOT create a Super Admin
        $res = $this->postJson('/api/users', [
            'name' => 'Illegal Super Admin',
            'email' => 'illegal_super@test.com',
            'password' => 'password123',
            'role' => 'super_admin',
            'status' => 'Active',
        ]);
        $res->assertStatus(403);

        // Admin CANNOT modify a Super Admin
        $res = $this->putJson("/api/users/{$this->superAdmin->id}", [
            'name' => 'Hacked Name',
        ]);
        $res->assertStatus(403);

        // Admin CANNOT delete a Super Admin
        $res = $this->deleteJson("/api/users/{$this->superAdmin->id}");
        $res->assertStatus(403);
    }

    /**
     * 3. Guide / Editor can manage content (Places, Events, Media) but cannot manage users or settings.
     */
    public function test_guide_editor_permissions(): void
    {
        Sanctum::actingAs($this->guideEditor, ['*']);

        $cat = Category::create(['name' => 'Temples', 'slug' => 'temples']);
        $prov = Province::create(['name' => 'Siem Reap']);

        // Guide can create places
        $res = $this->postJson('/api/places', [
            'name' => 'Banteay Srei',
            'category_id' => $cat->id,
            'province_id' => $prov->id,
            'address' => 'Banteay Srei District, Siem Reap',
            'description' => 'Citadel of Women temple.',
        ]);
        $res->assertStatus(201);

        // Guide CANNOT delete users
        $this->deleteJson("/api/users/{$this->tourist->id}")->assertStatus(403);

        // Guide CANNOT view system tracking
        $this->getJson('/api/tracking')->assertStatus(403);
    }

    /**
     * 4. Business Owner can access /api/business/* but NOT /api/dashboard or /api/users.
     */
    public function test_business_owner_boundaries(): void
    {
        Sanctum::actingAs($this->businessOwner, ['*']);

        // Business owner can access business profile and business endpoints
        $this->getJson('/api/business/profile')->assertStatus(200);
        $this->getJson('/api/business/businesses')->assertStatus(200);

        // Business owner CANNOT access admin dashboard
        $this->getJson('/api/dashboard')->assertStatus(403);

        // Business owner CANNOT access admin user management
        $this->getJson('/api/users')->assertStatus(403);

        // Business owner CANNOT access system tracking
        $this->getJson('/api/tracking')->assertStatus(403);
    }

    /**
     * 5. Tourist User cannot access /api/admin/* or /api/business/*.
     */
    public function test_tourist_boundaries(): void
    {
        Sanctum::actingAs($this->tourist, ['*']);

        // Tourist can access travel me
        $this->getJson('/api/travel/auth/me')->assertStatus(200);

        // Tourist CANNOT access business management
        $this->getJson('/api/business/profile')->assertStatus(403);
        $this->getJson('/api/business/businesses')->assertStatus(403);

        // Tourist CANNOT access admin dashboard
        $this->getJson('/api/dashboard')->assertStatus(403);
        $this->getJson('/api/users')->assertStatus(403);
    }
}

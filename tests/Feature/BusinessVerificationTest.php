<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BusinessVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $owner;
    protected User $tourist;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Boss',
            'email' => 'admin@test.com',
            'password_hash' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
            'status' => 'Active',
        ]);

        $this->owner = User::create([
            'name' => 'Owner Sam',
            'email' => 'sam@test.com',
            'password_hash' => Hash::make('password123'),
            'role' => User::ROLE_BUSINESS_OWNER,
            'status' => 'Active',
        ]);

        $this->tourist = User::create([
            'name' => 'Tourist Tina',
            'email' => 'tina@test.com',
            'password_hash' => Hash::make('password123'),
            'role' => User::ROLE_USER,
            'status' => 'Active',
        ]);
    }

    /**
     * 1. New business created by owner defaults to verification_status='pending'.
     */
    public function test_new_business_defaults_to_pending(): void
    {
        Sanctum::actingAs($this->owner, ['*']);

        $res = $this->postJson('/api/business/businesses', [
            'name' => 'Sam Khmer Noodle House',
            'description' => 'Traditional noodles.',
        ]);

        $res->assertStatus(201);
        $res->assertJsonPath('data.verification_status', 'pending');
        $res->assertJsonPath('data.status', 'active');
    }

    /**
     * 2. Admin can approve pending business.
     */
    public function test_admin_can_approve_business(): void
    {
        $business = Business::create([
            'owner_id' => $this->owner->id,
            'name' => 'Sam Khmer Noodle House',
            'slug' => 'sam-khmer-noodle-house',
            'status' => 'active',
            'verification_status' => 'pending',
        ]);

        Sanctum::actingAs($this->admin, ['*']);

        $res = $this->postJson("/api/admin/businesses/{$business->id}/approve");

        $res->assertStatus(200);
        $res->assertJsonPath('data.verification_status', 'approved');

        $fresh = $business->fresh();
        $this->assertEquals('approved', $fresh->verification_status);
        $this->assertNotNull($fresh->verified_at);
        $this->assertEquals($this->admin->id, $fresh->verified_by);
    }

    /**
     * 3. Admin can reject business with a reason.
     */
    public function test_admin_can_reject_business_with_reason(): void
    {
        $business = Business::create([
            'owner_id' => $this->owner->id,
            'name' => 'Sam Unlicensed Resort',
            'slug' => 'sam-unlicensed-resort',
            'status' => 'active',
            'verification_status' => 'pending',
        ]);

        Sanctum::actingAs($this->admin, ['*']);

        $res = $this->postJson("/api/admin/businesses/{$business->id}/reject", [
            'rejection_reason' => 'Missing commercial tourism permit.',
        ]);

        $res->assertStatus(200);
        $res->assertJsonPath('data.verification_status', 'rejected');

        $fresh = $business->fresh();
        $this->assertEquals('rejected', $fresh->verification_status);
        $this->assertEquals('Missing commercial tourism permit.', $fresh->rejection_reason);
    }

    /**
     * 4. Admin can suspend and reactivate business.
     */
    public function test_admin_can_suspend_and_activate_business(): void
    {
        $business = Business::create([
            'owner_id' => $this->owner->id,
            'name' => 'Sam Night Club',
            'slug' => 'sam-night-club',
            'status' => 'active',
            'verification_status' => 'approved',
        ]);

        Sanctum::actingAs($this->admin, ['*']);

        // Suspend
        $res = $this->postJson("/api/admin/businesses/{$business->id}/suspend", [
            'reason' => 'Safety investigation.',
        ]);
        $res->assertStatus(200);
        $this->assertEquals('suspended', $business->fresh()->status);

        // Activate
        $res = $this->postJson("/api/admin/businesses/{$business->id}/activate");
        $res->assertStatus(200);
        $this->assertEquals('active', $business->fresh()->status);
    }

    /**
     * 5. Business owner CANNOT self-approve (HTTP 403).
     */
    public function test_owner_cannot_self_approve(): void
    {
        $business = Business::create([
            'owner_id' => $this->owner->id,
            'name' => 'Sam Secret Business',
            'slug' => 'sam-secret-business',
            'status' => 'active',
            'verification_status' => 'pending',
        ]);

        Sanctum::actingAs($this->owner, ['*']);

        $res = $this->postJson("/api/admin/businesses/{$business->id}/approve");
        $res->assertStatus(403);
        $this->assertEquals('pending', $business->fresh()->verification_status);
    }

    /**
     * 6. Public / Tourist can ONLY see approved and active businesses.
     */
    public function test_tourist_can_only_see_approved_and_active_businesses(): void
    {
        $approvedActive = Business::create([
            'owner_id' => $this->owner->id,
            'name' => 'Approved Active Place',
            'slug' => 'approved-active-place',
            'status' => 'active',
            'verification_status' => 'approved',
        ]);

        $pending = Business::create([
            'owner_id' => $this->owner->id,
            'name' => 'Pending Place',
            'slug' => 'pending-place',
            'status' => 'active',
            'verification_status' => 'pending',
        ]);

        $suspended = Business::create([
            'owner_id' => $this->owner->id,
            'name' => 'Suspended Place',
            'slug' => 'suspended-place',
            'status' => 'suspended',
            'verification_status' => 'approved',
        ]);

        // Public index
        $res = $this->getJson('/api/travel/businesses');
        $res->assertStatus(200);

        $names = collect($res->json('data.businesses'))->pluck('name');
        $this->assertTrue($names->contains('Approved Active Place'));
        $this->assertFalse($names->contains('Pending Place'));
        $this->assertFalse($names->contains('Suspended Place'));

        // Public show on pending business fails (403)
        $this->getJson("/api/travel/businesses/{$pending->id}")->assertStatus(403);
    }
}

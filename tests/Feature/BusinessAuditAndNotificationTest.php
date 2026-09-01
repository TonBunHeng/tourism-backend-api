<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Business;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BusinessAuditAndNotificationTest extends TestCase
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
            'name' => 'Owner Maria',
            'email' => 'maria@test.com',
            'password_hash' => Hash::make('password123'),
            'role' => User::ROLE_BUSINESS_OWNER,
            'status' => 'Active',
        ]);

        $this->tourist = User::create([
            'name' => 'Tourist Tom',
            'email' => 'tom@test.com',
            'password_hash' => Hash::make('password123'),
            'role' => User::ROLE_USER,
            'status' => 'Active',
        ]);
    }

    /**
     * 1. Audit log is created when owner creates a business.
     */
    public function test_audit_log_created_on_business_lifecycle(): void
    {
        Sanctum::actingAs($this->owner, ['*']);

        $res = $this->postJson('/api/business/businesses', [
            'name' => 'Maria Bakery & Tea Room',
            'description' => 'Fresh pastries and teas.',
        ]);

        $res->assertStatus(201);
        $businessId = $res->json('data.id');

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'business.created',
            'entity_type' => 'Business',
            'entity_id' => $businessId,
            'user_id' => $this->owner->id,
        ]);

        // Update
        $this->putJson("/api/business/businesses/{$businessId}", [
            'description' => 'Updated pastries menu.',
        ])->assertStatus(200);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'business.updated',
            'entity_type' => 'Business',
            'entity_id' => $businessId,
        ]);
    }

    /**
     * 2. Audit log and Notification are dispatched when Admin approves business.
     */
    public function test_audit_log_and_notification_on_admin_approval(): void
    {
        $business = Business::create([
            'owner_id' => $this->owner->id,
            'name' => 'Maria Royal Villa',
            'slug' => 'maria-royal-villa',
            'status' => 'active',
            'verification_status' => 'pending',
        ]);

        Sanctum::actingAs($this->admin, ['*']);

        $res = $this->postJson("/api/admin/businesses/{$business->id}/approve");
        $res->assertStatus(200);

        // Verify audit log
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'business.approved',
            'entity_type' => 'Business',
            'entity_id' => $business->id,
            'user_id' => $this->admin->id,
        ]);

        // Verify notification sent to business owner
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->owner->id,
            'type' => 'business_approved',
            'category' => 'Business',
        ]);
    }

    /**
     * 3. Tourist submits review and business owner receives notification.
     */
    public function test_tourist_submits_review_and_owner_notified(): void
    {
        $business = Business::create([
            'owner_id' => $this->owner->id,
            'name' => 'Maria Royal Villa',
            'slug' => 'maria-royal-villa-2',
            'status' => 'active',
            'verification_status' => 'approved',
        ]);

        Sanctum::actingAs($this->tourist, ['*']);

        $res = $this->postJson("/api/travel/businesses/{$business->id}/reviews", [
            'rating' => 5,
            'title' => 'Spectacular stay!',
            'comment' => 'Had an amazing time here, beautiful rooms.',
        ]);

        $res->assertStatus(201);

        // Verify review in database
        $this->assertDatabaseHas('reviews', [
            'business_id' => $business->id,
            'user_id' => $this->tourist->id,
            'rating' => 5,
        ]);

        // Verify notification received by owner
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->owner->id,
            'type' => 'business_review',
            'category' => 'Review',
        ]);

        // Verify business rating recalculated
        $this->assertEquals(5.0, (float)$business->fresh()->rating);
        $this->assertEquals(1, $business->fresh()->review_count);
    }
}

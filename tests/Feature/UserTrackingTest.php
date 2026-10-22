<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $admin;
    protected User $guideEditor;
    protected User $tourist;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::create([
            'role' => User::ROLE_SUPER_ADMIN,
            'name' => 'Ton Bunheng',
            'email' => 'admin@tourism.gov.kh',
            'password_hash' => Hash::make('password123'),
            'status' => 'Active',
        ]);

        $this->admin = User::create([
            'role' => User::ROLE_ADMIN,
            'name' => 'Kosal Visal',
            'email' => 'staff.admin@tourism.gov.kh',
            'password_hash' => Hash::make('password123'),
            'status' => 'Active',
        ]);

        $this->guideEditor = User::create([
            'role' => User::ROLE_GUIDE_EDITOR,
            'name' => 'Sophal Sopheaktra',
            'email' => 'sopheaktra@tourism.gov.kh',
            'password_hash' => Hash::make('password123'),
            'status' => 'Active',
        ]);

        $this->tourist = User::create([
            'role' => User::ROLE_USER,
            'name' => 'VIT Vong',
            'email' => 'vit.vong@example.com',
            'password_hash' => Hash::make('password123'),
            'status' => 'Active',
        ]);
    }

    public function test_admin_and_super_admin_can_view_tracking_logs_and_stats(): void
    {
        AuditLog::create([
            'user_id' => $this->guideEditor->id,
            'user_name' => $this->guideEditor->name,
            'user_role' => 'guide_editor',
            'action' => 'place_created',
            'entity_type' => 'Place',
            'entity_id' => 10,
            'description' => 'Created temple place',
        ]);

        // Admin can view logs and stats
        Sanctum::actingAs($this->admin, ['*']);
        $res = $this->getJson('/api/user-tracking');
        $res->assertStatus(200)
            ->assertJsonStructure(['success', 'data', 'pagination', 'meta']);

        $resStats = $this->getJson('/api/user-tracking/stats');
        $resStats->assertStatus(200)
            ->assertJsonStructure(['success', 'data' => ['total_actions', 'roles_breakdown']]);

        // Super Admin can view logs and stats
        Sanctum::actingAs($this->superAdmin, ['*']);
        $this->getJson('/api/user-tracking')->assertStatus(200);
        $this->getJson('/api/user-tracking/stats')->assertStatus(200);
    }

    public function test_non_admin_roles_cannot_access_user_tracking(): void
    {
        // Guide editor forbidden
        Sanctum::actingAs($this->guideEditor, ['*']);
        $this->getJson('/api/user-tracking')->assertStatus(403);
        $this->getJson('/api/user-tracking/stats')->assertStatus(403);

        // Tourist forbidden
        Sanctum::actingAs($this->tourist, ['*']);
        $this->getJson('/api/user-tracking')->assertStatus(403);
        $this->getJson('/api/user-tracking/stats')->assertStatus(403);
    }

    public function test_super_admin_has_full_crud_on_user_tracking(): void
    {
        Sanctum::actingAs($this->superAdmin, ['*']);

        // 1. Create a tracking audit entry
        $createRes = $this->postJson('/api/user-tracking', [
            'action' => 'manual_audit_check',
            'entity_type' => 'System',
            'entity_id' => 1,
            'description' => 'Super Admin performed security protocol audit',
            'user_id' => $this->superAdmin->id,
        ]);
        $createRes->assertStatus(201);
        $logId = $createRes->json('data.id');

        // 2. Read single log
        $showRes = $this->getJson("/api/user-tracking/{$logId}");
        $showRes->assertStatus(200)
            ->assertJsonPath('data.action', 'manual_audit_check');

        // 3. Update log description
        $updateRes = $this->putJson("/api/user-tracking/{$logId}", [
            'description' => 'Updated audit check description with resolution notes',
        ]);
        $updateRes->assertStatus(200)
            ->assertJsonPath('data.description', 'Updated audit check description with resolution notes');

        // 4. Delete log
        $delRes = $this->deleteJson("/api/user-tracking/{$logId}");
        $delRes->assertStatus(200);
        $this->assertDatabaseMissing('audit_logs', ['id' => $logId]);

        // 5. Bulk clear
        AuditLog::create([
            'action' => 'temp_log',
            'description' => 'Temporary test log',
        ]);
        $clearRes = $this->deleteJson('/api/user-tracking');
        $clearRes->assertStatus(200);
    }

    public function test_admin_cannot_perform_crud_mutations_on_user_tracking(): void
    {
        $log = AuditLog::create([
            'user_id' => $this->guideEditor->id,
            'user_name' => $this->guideEditor->name,
            'user_role' => 'guide_editor',
            'action' => 'place_created',
            'description' => 'Test log',
        ]);

        Sanctum::actingAs($this->admin, ['*']);

        // Admin CANNOT create manual log
        $this->postJson('/api/user-tracking', [
            'action' => 'illegal_log',
            'description' => 'Should fail',
        ])->assertStatus(403);

        // Admin CANNOT update log
        $this->putJson("/api/user-tracking/{$log->id}", [
            'description' => 'Hacked description',
        ])->assertStatus(403);

        // Admin CANNOT delete log
        $this->deleteJson("/api/user-tracking/{$log->id}")->assertStatus(403);

        // Admin CANNOT clear logs
        $this->deleteJson('/api/user-tracking')->assertStatus(403);
    }

    public function test_tracking_filters_by_role_and_action(): void
    {
        AuditLog::create([
            'user_name' => 'Kosal Visal',
            'user_role' => 'admin',
            'action' => 'place_approved',
            'description' => 'Admin approved place',
        ]);

        AuditLog::create([
            'user_name' => 'Sophal Sopheaktra',
            'user_role' => 'guide_editor',
            'action' => 'place_created',
            'description' => 'Content editor created place',
        ]);

        AuditLog::create([
            'user_name' => 'VIT Vong',
            'user_role' => 'user',
            'action' => 'review_created',
            'description' => 'Tourist posted review',
        ]);

        Sanctum::actingAs($this->superAdmin, ['*']);

        // Filter by guide_editor role
        $resEditor = $this->getJson('/api/user-tracking?role=guide_editor');
        $resEditor->assertStatus(200);
        $this->assertCount(1, $resEditor->json('data'));
        $this->assertEquals('guide_editor', $resEditor->json('data.0.user_role'));

        // Filter by admin role
        $resAdmin = $this->getJson('/api/user-tracking?role=admin');
        $resAdmin->assertStatus(200);
        $this->assertCount(1, $resAdmin->json('data'));
        $this->assertEquals('admin', $resAdmin->json('data.0.user_role'));

        // Filter by user role
        $resUser = $this->getJson('/api/user-tracking?role=user');
        $resUser->assertStatus(200);
        $this->assertCount(1, $resUser->json('data'));
        $this->assertEquals('user', $resUser->json('data.0.user_role'));
    }
}

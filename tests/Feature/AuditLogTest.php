<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@angkorverses.com',
            'password_hash' => Hash::make('password123'),
            'role' => 'Admin',
            'status' => 'Active',
        ]);

        $this->regularUser = User::create([
            'name' => 'Tourist User',
            'email' => 'tourist@example.com',
            'password_hash' => Hash::make('password123'),
            'role' => 'User',
            'status' => 'Active',
        ]);
    }

    public function test_admin_can_access_audit_logs(): void
    {
        AuditLogger::log('place_created', 'Place', 1, 'Created Angkor Wat');

        $response = $this->actingAs($this->admin, 'sanctum')->getJson('/api/audit-logs');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure(['data', 'pagination', 'meta']);
    }

    public function test_regular_user_cannot_access_audit_logs(): void
    {
        $response = $this->actingAs($this->regularUser, 'sanctum')->getJson('/api/audit-logs');

        $response->assertStatus(403);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessImage;
use App\Models\BusinessService;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BusinessOwnershipTest extends TestCase
{
    use RefreshDatabase;

    protected User $ownerA;
    protected User $ownerB;
    protected Business $businessA;
    protected Business $businessB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ownerA = User::create([
            'name' => 'Owner Alice',
            'email' => 'alice@test.com',
            'password_hash' => Hash::make('password123'),
            'role' => User::ROLE_BUSINESS_OWNER,
            'status' => 'Active',
        ]);

        $this->ownerB = User::create([
            'name' => 'Owner Bob',
            'email' => 'bob@test.com',
            'password_hash' => Hash::make('password123'),
            'role' => User::ROLE_BUSINESS_OWNER,
            'status' => 'Active',
        ]);

        $this->businessA = Business::create([
            'owner_id' => $this->ownerA->id,
            'name' => 'Alice Cafe',
            'slug' => 'alice-cafe',
            'status' => 'active',
            'verification_status' => 'approved',
        ]);

        $this->businessB = Business::create([
            'owner_id' => $this->ownerB->id,
            'name' => 'Bob Bistro',
            'slug' => 'bob-bistro',
            'status' => 'active',
            'verification_status' => 'approved',
        ]);
    }

    /**
     * 1. Owner can create their own business.
     */
    public function test_owner_can_create_business(): void
    {
        Sanctum::actingAs($this->ownerA, ['*']);

        $res = $this->postJson('/api/business/businesses', [
            'name' => 'Alice Second Boutique',
            'description' => 'A cozy resort.',
            'phone' => '+855 12 333 444',
            'price_range' => '$$',
        ]);

        $res->assertStatus(201);
        $res->assertJsonPath('data.name', 'Alice Second Boutique');
        $res->assertJsonPath('data.owner_id', $this->ownerA->id);
        $res->assertJsonPath('data.verification_status', 'pending');
    }

    /**
     * 2. Owner can update their own business.
     */
    public function test_owner_can_update_own_business(): void
    {
        Sanctum::actingAs($this->ownerA, ['*']);

        $res = $this->putJson("/api/business/businesses/{$this->businessA->id}", [
            'name' => 'Alice Modern Cafe',
            'description' => 'Updated description.',
        ]);

        $res->assertStatus(200);
        $res->assertJsonPath('data.name', 'Alice Modern Cafe');
    }

    /**
     * 3. IDOR: Owner A CANNOT update Owner B's business (HTTP 403).
     */
    public function test_owner_cannot_update_another_owner_business(): void
    {
        Sanctum::actingAs($this->ownerA, ['*']);

        $res = $this->putJson("/api/business/businesses/{$this->businessB->id}", [
            'name' => 'Hacked Bob Bistro',
        ]);

        $res->assertStatus(403);
        $this->assertEquals('Bob Bistro', $this->businessB->fresh()->name);
    }

    /**
     * 4. IDOR: Owner A CANNOT delete Owner B's business (HTTP 403).
     */
    public function test_owner_cannot_delete_another_owner_business(): void
    {
        Sanctum::actingAs($this->ownerA, ['*']);

        $res = $this->deleteJson("/api/business/businesses/{$this->businessB->id}");

        $res->assertStatus(403);
        $this->assertDatabaseHas('businesses', ['id' => $this->businessB->id]);
    }

    /**
     * 5. IDOR: Owner A CANNOT modify images on Owner B's business.
     */
    public function test_owner_cannot_modify_another_owner_images(): void
    {
        $imageB = BusinessImage::create([
            'business_id' => $this->businessB->id,
            'image_url' => 'https://example.com/b.jpg',
            'caption' => 'Bob Cover',
        ]);

        Sanctum::actingAs($this->ownerA, ['*']);

        // Cannot upload image to Bob's business
        $res = $this->postJson("/api/business/businesses/{$this->businessB->id}/images", [
            'image_url' => 'https://example.com/malicious.jpg',
        ]);
        $res->assertStatus(403);

        // Cannot delete Bob's image
        $res = $this->deleteJson("/api/business/businesses/{$this->businessB->id}/images/{$imageB->id}");
        $res->assertStatus(403);
        $this->assertDatabaseHas('business_images', ['id' => $imageB->id]);
    }

    /**
     * 6. IDOR: Owner A CANNOT modify services on Owner B's business.
     */
    public function test_owner_cannot_modify_another_owner_services(): void
    {
        $serviceB = BusinessService::create([
            'business_id' => $this->businessB->id,
            'name' => 'Bob Signature Dish',
            'price' => 20.00,
        ]);

        Sanctum::actingAs($this->ownerA, ['*']);

        // Cannot add service to Bob's business
        $res = $this->postJson("/api/business/businesses/{$this->businessB->id}/services", [
            'name' => 'Fake Service',
            'price' => 5.00,
        ]);
        $res->assertStatus(403);

        // Cannot update Bob's service
        $res = $this->putJson("/api/business/businesses/{$this->businessB->id}/services/{$serviceB->id}", [
            'name' => 'Hacked Service',
        ]);
        $res->assertStatus(403);

        // Cannot delete Bob's service
        $res = $this->deleteJson("/api/business/businesses/{$this->businessB->id}/services/{$serviceB->id}");
        $res->assertStatus(403);
        $this->assertDatabaseHas('business_services', ['id' => $serviceB->id]);
    }

    /**
     * 7. IDOR: Owner A CANNOT access Owner B's private business statistics.
     */
    public function test_owner_cannot_view_another_owner_statistics(): void
    {
        Sanctum::actingAs($this->ownerA, ['*']);

        $res = $this->getJson("/api/business/businesses/{$this->businessB->id}/statistics");
        $res->assertStatus(403);

        // Can view own statistics
        $resOwn = $this->getJson("/api/business/businesses/{$this->businessA->id}/statistics");
        $resOwn->assertStatus(200);
        $resOwn->assertJsonPath('data.name', 'Alice Cafe');
    }

    /**
     * 8. IDOR: Owner A CANNOT reply to reviews on Owner B's business.
     */
    public function test_owner_cannot_reply_to_another_owner_reviews(): void
    {
        $tourist = User::create([
            'name' => 'Tourist Tim',
            'email' => 'tim@test.com',
            'password_hash' => Hash::make('password123'),
            'role' => User::ROLE_USER,
            'status' => 'Active',
        ]);

        $reviewB = Review::create([
            'user_id' => $tourist->id,
            'business_id' => $this->businessB->id,
            'rating' => 5,
            'comment' => 'Great food at Bobs!',
            'status' => 'Approved',
        ]);

        Sanctum::actingAs($this->ownerA, ['*']);

        $res = $this->postJson("/api/business/businesses/{$this->businessB->id}/reviews/{$reviewB->id}/reply", [
            'reply' => 'Thanks from fake owner!',
        ]);

        $res->assertStatus(403);
    }
}

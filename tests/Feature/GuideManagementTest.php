<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Province;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GuideManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $guide;
    protected Category $category;
    protected Province $province;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guide = User::create([
            'name' => 'Guide Master',
            'email' => 'guide_master@test.com',
            'password_hash' => bcrypt('password'),
            'role' => User::ROLE_GUIDE_EDITOR,
            'status' => 'Active',
        ]);

        $this->category = Category::create(['name' => 'Historical Sites', 'slug' => 'historical-sites']);
        $this->province = Province::create(['name' => 'Siem Reap']);
    }

    public function test_guide_can_access_dashboard_and_manage_places_and_events(): void
    {
        Sanctum::actingAs($this->guide, ['*']);

        // Dashboard
        $res = $this->getJson('/api/guide/dashboard');
        $res->assertStatus(200)
            ->assertJsonPath('success', true);

        // Create Place
        $res = $this->postJson('/api/guide/places', [
            'name' => 'Ta Prohm Temple',
            'category_id' => $this->category->id,
            'province_id' => $this->province->id,
            'address' => 'Angkor Archaeological Park',
            'description' => 'Famous jungle temple with overgrown tree roots.',
        ]);
        $res->assertStatus(201);
        $placeId = $res->json('data.id');

        // Create Event
        $res = $this->postJson('/api/guide/events', [
            'title' => 'Angkor Equinox Sunrise Gathering',
            'category' => 'Culture',
            'location' => 'Angkor Wat Central Tower',
            'province_id' => $this->province->id,
            'place_id' => $placeId,
            'description' => 'Watch the sunrise directly above central tower.',
        ]);
        $res->assertStatus(201);
    }
}

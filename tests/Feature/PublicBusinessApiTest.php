<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessHour;
use App\Models\BusinessImage;
use App\Models\BusinessPromotion;
use App\Models\BusinessService;
use App\Models\Category;
use App\Models\Event;
use App\Models\Province;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicBusinessApiTest extends TestCase
{
    use RefreshDatabase;

    protected Business $business;

    protected function setUp(): void
    {
        parent::setUp();

        $owner = User::create([
            'name' => 'Owner User',
            'email' => 'owner@test.com',
            'password_hash' => bcrypt('password'),
            'role' => 'business_owner',
            'status' => 'Active',
        ]);

        $category = Category::create(['name' => 'Hotels', 'slug' => 'hotels']);
        $province = Province::create(['name' => 'Siem Reap']);

        $this->business = Business::create([
            'owner_id' => $owner->id,
            'name' => 'Angkor Boutique Hotel',
            'slug' => 'angkor-boutique-hotel',
            'description' => 'Luxury stay in Siem Reap',
            'category_id' => $category->id,
            'province_id' => $province->id,
            'address' => 'Charles de Gaulle Blvd',
            'price_range' => '$$$',
            'status' => 'active',
            'verification_status' => 'approved',
            'rating' => 4.8,
            'review_count' => 1,
        ]);

        BusinessService::create([
            'business_id' => $this->business->id,
            'name' => 'Deluxe Suite',
            'price' => 120.00,
            'is_available' => true,
        ]);

        BusinessHour::create([
            'business_id' => $this->business->id,
            'day_of_week' => 'monday',
            'open_time' => '08:00',
            'close_time' => '22:00',
            'is_closed' => false,
        ]);

        BusinessImage::create([
            'business_id' => $this->business->id,
            'image_url' => 'https://example.com/cover.jpg',
            'is_cover' => true,
        ]);

        BusinessPromotion::create([
            'business_id' => $this->business->id,
            'title' => '20% Off Weekend',
            'discount_percentage' => 20,
            'status' => 'active',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(7),
        ]);

        Event::create([
            'business_id' => $this->business->id,
            'province_id' => $province->id,
            'title' => 'Gala Dinner',
            'category' => 'Culture',
            'location' => 'Angkor Boutique Hotel Ballroom',
            'start_date' => now()->addDays(2)->toDateString(),
            'organizer' => 'Angkor Boutique Hotel',
        ]);
    }

    public function test_can_fetch_public_businesses_list(): void
    {
        $res = $this->getJson('/api/travel/businesses');
        $res->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_can_fetch_public_business_sub_resources(): void
    {
        $id = $this->business->id;

        $this->getJson("/api/travel/businesses/{$id}/services")
            ->assertStatus(200);

        $this->getJson("/api/travel/businesses/{$id}/hours")
            ->assertStatus(200);

        $this->getJson("/api/travel/businesses/{$id}/gallery")
            ->assertStatus(200);

        $this->getJson("/api/travel/businesses/{$id}/promotions")
            ->assertStatus(200);

        $this->getJson("/api/travel/businesses/{$id}/events")
            ->assertStatus(200);

        $this->getJson("/api/travel/businesses/{$id}/reviews")
            ->assertStatus(200);
    }
}

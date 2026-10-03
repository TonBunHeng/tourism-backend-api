<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Favorite;
use App\Models\Place;
use App\Models\Province;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RatingsAndFavoritesAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Category $categoryTemple;
    protected Category $categoryNature;
    protected Province $province;
    protected Place $place1;
    protected Place $place2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password_hash' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
            'status' => 'Active',
        ]);

        $this->categoryTemple = Category::create([
            'name' => 'Temple',
            'icon' => 'Landmark',
            'description' => 'Ancient Temples',
        ]);

        $this->categoryNature = Category::create([
            'name' => 'Nature',
            'icon' => 'TreePine',
            'description' => 'Natural Parks',
        ]);

        $this->province = Province::create([
            'name' => 'Siem Reap',
            'code' => 'SR',
            'capital' => 'Siem Reap City',
            'description' => 'Angkor heritage',
        ]);

        $this->place1 = Place::create([
            'name' => 'Angkor Wat',
            'category_id' => $this->categoryTemple->id,
            'province_id' => $this->province->id,
            'address' => 'Angkor Archaeological Park',
            'rating' => 5.0,
            'status' => 'Active',
        ]);

        $this->place2 = Place::create([
            'name' => 'Kulen Mountain',
            'category_id' => $this->categoryNature->id,
            'province_id' => $this->province->id,
            'address' => 'Phnom Kulen',
            'rating' => 4.0,
            'status' => 'Active',
        ]);

        // Create reviews
        Review::create([
            'user_id' => $this->admin->id,
            'place_id' => $this->place1->id,
            'rating' => 5,
            'title' => 'Majestic temple',
            'comment' => 'Truly breathtaking ancient architecture.',
            'status' => 'Approved',
            'is_verified' => true,
        ]);

        Review::create([
            'user_id' => $this->admin->id,
            'place_id' => $this->place2->id,
            'rating' => 4,
            'title' => 'Nice waterfalls',
            'comment' => 'Great nature hike and waterfalls.',
            'status' => 'Approved',
            'is_verified' => false,
        ]);

        // Create favorites
        Favorite::create([
            'user_id' => $this->admin->id,
            'place_id' => $this->place1->id,
            'visited' => true,
            'saved_date' => now()->toDateString(),
        ]);

        Favorite::create([
            'user_id' => $this->admin->id,
            'place_id' => $this->place2->id,
            'visited' => false,
            'saved_date' => now()->toDateString(),
        ]);
    }

    public function test_reviews_analytics_endpoint_returns_expected_structure(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/reviews/analytics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'overview' => [
                        'total_ratings',
                        'avg_rating',
                        'positive_count',
                        'critical_count',
                        'positive_sentiment_pct',
                        'verification_pct',
                    ],
                    'monthly_trends',
                    'rating_distribution',
                    'category_distribution',
                    'categories',
                    'recent_reviews',
                ],
            ]);

        $data = $response->json('data');
        $this->assertEquals(2, $data['overview']['total_ratings']);
        $this->assertEquals(4.5, $data['overview']['avg_rating']);
        $this->assertEquals(2, $data['overview']['positive_count']);
        $this->assertEquals(0, $data['overview']['critical_count']);
        $this->assertCount(12, $data['monthly_trends']);
        $this->assertCount(5, $data['rating_distribution']);
    }

    public function test_reviews_analytics_category_and_rating_filtering(): void
    {
        Sanctum::actingAs($this->admin);

        // Filter by Temple category
        $response = $this->getJson('/api/reviews/analytics?category=Temple');
        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('data.overview.total_ratings'));
        $this->assertEquals(5.0, $response->json('data.overview.avg_rating'));

        // Filter by 4 star rating
        $responseRating = $this->getJson('/api/reviews/analytics?rating=4');
        $responseRating->assertStatus(200);
        $this->assertEquals(1, $responseRating->json('data.overview.total_ratings'));
        $this->assertEquals(4.0, $responseRating->json('data.overview.avg_rating'));
    }

    public function test_favorites_analytics_endpoint_returns_expected_structure(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/favorites/analytics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'overview' => [
                        'total_favorites',
                        'visited_count',
                        'wishlist_count',
                        'conversion_rate',
                        'unique_travelers',
                        'avg_rating',
                    ],
                    'monthly_trends',
                    'category_distribution',
                    'status_breakdown',
                    'top_favorites',
                    'categories',
                    'recent_favorites',
                ],
            ]);

        $data = $response->json('data');
        $this->assertEquals(2, $data['overview']['total_favorites']);
        $this->assertEquals(1, $data['overview']['visited_count']);
        $this->assertEquals(1, $data['overview']['wishlist_count']);
        $this->assertEquals(50.0, $data['overview']['conversion_rate']);
        $this->assertCount(12, $data['monthly_trends']);

        // Verify keys in monthly trends for Recharts
        $this->assertArrayHasKey('newSaves', $data['monthly_trends'][0]);
        $this->assertArrayHasKey('cumulative', $data['monthly_trends'][0]);
    }

    public function test_favorites_analytics_status_and_category_filtering(): void
    {
        Sanctum::actingAs($this->admin);

        // Filter by VISITED status
        $responseVisited = $this->getJson('/api/favorites/analytics?visit_status=VISITED');
        $responseVisited->assertStatus(200);
        $this->assertEquals(1, $responseVisited->json('data.overview.total_favorites'));
        $this->assertEquals(1, $responseVisited->json('data.overview.visited_count'));
        $this->assertEquals(0, $responseVisited->json('data.overview.wishlist_count'));

        // Filter by PLANNED status
        $responsePlanned = $this->getJson('/api/favorites/analytics?visit_status=PLANNED');
        $responsePlanned->assertStatus(200);
        $this->assertEquals(1, $responsePlanned->json('data.overview.total_favorites'));
        $this->assertEquals(0, $responsePlanned->json('data.overview.visited_count'));
        $this->assertEquals(1, $responsePlanned->json('data.overview.wishlist_count'));

        // Filter by Nature category
        $responseCat = $this->getJson('/api/favorites/analytics?category=Nature');
        $responseCat->assertStatus(200);
        $this->assertEquals(1, $responseCat->json('data.overview.total_favorites'));
    }
}

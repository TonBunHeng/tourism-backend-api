<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Business;
use App\Models\BusinessService;
use App\Models\Review;
use App\Models\ReviewReply;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BusinessReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $tourist;
    protected User $ownerA;
    protected User $ownerB;
    protected User $admin;
    protected Business $businessA1;
    protected Business $businessA2;
    protected Business $businessB;
    protected BusinessService $serviceA1;
    protected BusinessService $serviceA2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tourist = User::create([
            'name' => 'Alice Tourist',
            'email' => 'alice.tourist@example.com',
            'phone' => '+855 12 111 222',
            'password_hash' => Hash::make('password123'),
            'role' => User::ROLE_USER,
            'status' => 'Active',
        ]);

        $this->ownerA = User::create([
            'name' => 'Sokha Chanthou',
            'email' => 'owner.sokha@example.com',
            'password_hash' => Hash::make('password123'),
            'role' => User::ROLE_BUSINESS_OWNER,
            'status' => 'Active',
        ]);

        $this->ownerB = User::create([
            'name' => 'Dara Vuthy',
            'email' => 'owner.dara@example.com',
            'password_hash' => Hash::make('password123'),
            'role' => User::ROLE_BUSINESS_OWNER,
            'status' => 'Active',
        ]);

        $this->admin = User::create([
            'name' => 'Admin Boss',
            'email' => 'admin@angkorverses.com',
            'password_hash' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
            'status' => 'Active',
        ]);

        $this->businessA1 = Business::create([
            'owner_id' => $this->ownerA->id,
            'name' => 'Sokha Angkor Villa & Restaurant',
            'slug' => 'sokha-angkor-villa-restaurant',
            'status' => 'active',
            'verification_status' => 'approved',
            'price_range' => '$$$',
            'rating' => 4.8,
            'review_count' => 2,
        ]);

        $this->businessA2 = Business::create([
            'owner_id' => $this->ownerA->id,
            'name' => 'Sokha Riverside Coffee',
            'slug' => 'sokha-riverside-coffee',
            'status' => 'active',
            'verification_status' => 'approved',
            'price_range' => '$',
            'rating' => 4.5,
            'review_count' => 0,
        ]);

        $this->businessB = Business::create([
            'owner_id' => $this->ownerB->id,
            'name' => 'Dara Eco Boutique Resort',
            'slug' => 'dara-eco-boutique-resort',
            'status' => 'active',
            'verification_status' => 'approved',
            'price_range' => '$$$$',
            'rating' => 4.9,
            'review_count' => 0,
        ]);

        $this->serviceA1 = BusinessService::create([
            'business_id' => $this->businessA1->id,
            'name' => 'Khmer Traditional Set Dinner for Two',
            'price' => 50.00,
            'currency' => 'USD',
            'duration_minutes' => 90,
            'is_available' => true,
        ]);

        $this->serviceA2 = BusinessService::create([
            'business_id' => $this->businessA1->id,
            'name' => 'Herbal Spa Retreat 60min',
            'price' => 40.00,
            'currency' => 'USD',
            'duration_minutes' => 60,
            'is_available' => true,
        ]);
    }

    /**
     * 1. Unauthenticated requests are rejected.
     */
    public function test_guest_cannot_access_business_reports(): void
    {
        $res = $this->getJson('/api/business/reports');
        $res->assertStatus(401);
    }

    /**
     * 2. Regular tourist user cannot access business reports.
     */
    public function test_tourist_user_cannot_access_business_reports(): void
    {
        Sanctum::actingAs($this->tourist, ['*']);

        $res = $this->getJson('/api/business/reports');
        $res->assertStatus(403);
    }

    /**
     * 3. Business owner with no businesses gets clean empty report structure without errors.
     */
    public function test_business_owner_with_no_businesses_receives_empty_report_structure(): void
    {
        $newOwner = User::create([
            'name' => 'Fresh Owner',
            'email' => 'fresh@example.com',
            'password_hash' => Hash::make('password123'),
            'role' => User::ROLE_BUSINESS_OWNER,
            'status' => 'Active',
        ]);

        Sanctum::actingAs($newOwner, ['*']);

        $res = $this->getJson('/api/business/reports');
        $res->assertStatus(200);
        $res->assertJsonPath('success', true);
        $res->assertJsonPath('data.summary.total_bookings', 0);
        $res->assertJsonPath('data.summary.total_revenue', 0);
        $res->assertJsonPath('data.businesses', []);
    }

    /**
     * 4. Business owner receives comprehensive report of their businesses.
     */
    public function test_business_owner_can_retrieve_comprehensive_reports(): void
    {
        // Create sample bookings
        Booking::create([
            'user_id' => $this->tourist->id,
            'business_id' => $this->businessA1->id,
            'service_id' => $this->serviceA1->id,
            'customer_name' => 'Alice Tourist',
            'customer_email' => 'alice.tourist@example.com',
            'customer_phone' => '+855 12 111 222',
            'booking_date' => now()->format('Y-m-d'),
            'booking_time' => '19:00',
            'number_of_guests' => 2,
            'total_price' => 100.00,
            'status' => Booking::STATUS_COMPLETED,
            'payment_status' => Booking::PAYMENT_PAID,
            'completed_at' => now(),
        ]);

        Booking::create([
            'user_id' => $this->tourist->id,
            'business_id' => $this->businessA1->id,
            'service_id' => $this->serviceA2->id,
            'customer_name' => 'Alice Tourist',
            'customer_email' => 'alice.tourist@example.com',
            'customer_phone' => '+855 12 111 222',
            'booking_date' => now()->format('Y-m-d'),
            'booking_time' => '14:00',
            'number_of_guests' => 1,
            'total_price' => 40.00,
            'status' => Booking::STATUS_CONFIRMED,
            'payment_status' => Booking::PAYMENT_PAID,
            'confirmed_at' => now(),
        ]);

        Booking::create([
            'user_id' => $this->tourist->id,
            'business_id' => $this->businessA1->id,
            'customer_name' => 'Bob Walk-in',
            'customer_email' => 'bob.walkin@example.com',
            'booking_date' => now()->format('Y-m-d'),
            'booking_time' => '12:00',
            'number_of_guests' => 3,
            'total_price' => 60.00,
            'status' => Booking::STATUS_PENDING,
            'payment_status' => Booking::PAYMENT_UNPAID,
        ]);

        Booking::create([
            'user_id' => $this->tourist->id,
            'business_id' => $this->businessA2->id,
            'customer_name' => 'Charlie Coffee',
            'customer_email' => 'charlie@example.com',
            'booking_date' => now()->format('Y-m-d'),
            'booking_time' => '10:00',
            'number_of_guests' => 2,
            'total_price' => 15.00,
            'status' => Booking::STATUS_CANCELLED,
            'payment_status' => Booking::PAYMENT_UNPAID,
        ]);

        // Review
        $review = Review::create([
            'user_id' => $this->tourist->id,
            'business_id' => $this->businessA1->id,
            'rating' => 5,
            'title' => 'Splendid hospitality!',
            'comment' => 'The dinner was delicious and the atmosphere stunning.',
            'status' => 'Approved',
        ]);

        ReviewReply::create([
            'review_id' => $review->id,
            'user_id' => $this->ownerA->id,
            'reply' => 'Thank you Alice, looking forward to your next visit!',
        ]);

        Sanctum::actingAs($this->ownerA, ['*']);

        $res = $this->getJson('/api/business/reports?timeframe=this_year');
        $res->assertStatus(200);
        $res->assertJsonPath('success', true);

        // Summary Assertions
        $res->assertJsonPath('data.summary.total_bookings', 4);
        $res->assertJsonPath('data.summary.completed_bookings', 1);
        $res->assertJsonPath('data.summary.confirmed_bookings', 1);
        $res->assertJsonPath('data.summary.pending_bookings', 1);
        $res->assertJsonPath('data.summary.cancelled_bookings', 1);
        $res->assertJsonPath('data.summary.total_revenue', 140); // 100 completed + 40 confirmed
        $res->assertJsonPath('data.summary.completed_revenue', 100);
        $res->assertJsonPath('data.summary.pending_revenue', 60);
        $res->assertJsonPath('data.summary.total_guests', 8); // 2 + 1 + 3 + 2
        $this->assertEquals(25.0, $res->json('data.summary.cancellation_rate'));

        // Ratings & Quality
        $res->assertJsonPath('data.ratings_breakdown.total_reviews', 1);
        $res->assertJsonPath('data.ratings_breakdown.approved_reviews', 1);
        $res->assertJsonPath('data.ratings_breakdown.replied_reviews', 1);
        $res->assertJsonPath('data.ratings_breakdown.response_rate', 100);

        // Top Services Check
        $this->assertNotEmpty($res->json('data.top_services'));
        $res->assertJsonPath('data.top_services.0.service_name', 'Khmer Traditional Set Dinner for Two');
        $res->assertJsonPath('data.top_services.0.revenue', 100);

        // Customer Insights Check
        $res->assertJsonPath('data.customer_insights.total_unique_customers', 3); // Alice, Bob, Charlie
        $res->assertJsonPath('data.customer_insights.repeat_customers', 1); // Alice booked twice
        $this->assertEquals(33.3, $res->json('data.customer_insights.repeat_customer_rate'));
    }

    /**
     * 5. Business owner can filter report by specific owned business.
     */
    public function test_business_owner_can_filter_report_by_specific_business(): void
    {
        Booking::create([
            'user_id' => $this->tourist->id,
            'business_id' => $this->businessA1->id,
            'customer_name' => 'Alice Villa',
            'customer_email' => 'alice@villa.com',
            'booking_date' => now()->format('Y-m-d'),
            'total_price' => 200.00,
            'status' => Booking::STATUS_COMPLETED,
        ]);

        Booking::create([
            'user_id' => $this->tourist->id,
            'business_id' => $this->businessA2->id,
            'customer_name' => 'Bob Coffee',
            'customer_email' => 'bob@coffee.com',
            'booking_date' => now()->format('Y-m-d'),
            'total_price' => 30.00,
            'status' => Booking::STATUS_COMPLETED,
        ]);

        Sanctum::actingAs($this->ownerA, ['*']);

        // Test via query parameter
        $resFilter = $this->getJson("/api/business/reports?business_id={$this->businessA2->id}");
        $resFilter->assertStatus(200);
        $resFilter->assertJsonPath('data.summary.total_bookings', 1);
        $resFilter->assertJsonPath('data.summary.total_revenue', 30);

        // Test via direct route /api/business/businesses/{id}/reports
        $resDirect = $this->getJson("/api/business/businesses/{$this->businessA2->id}/reports");
        $resDirect->assertStatus(200);
        $resDirect->assertJsonPath('data.summary.total_bookings', 1);
        $resDirect->assertJsonPath('data.summary.total_revenue', 30);
    }

    /**
     * 6. Business owner cannot view another owner's business report.
     */
    public function test_business_owner_cannot_access_another_owners_business_report(): void
    {
        Sanctum::actingAs($this->ownerA, ['*']);

        // Owner A tries to access Owner B's business
        $res = $this->getJson("/api/business/reports?business_id={$this->businessB->id}");
        $res->assertStatus(403);
        $res->assertJsonPath('success', false);

        $resDirect = $this->getJson("/api/business/businesses/{$this->businessB->id}/reports");
        $resDirect->assertStatus(403);
    }

    /**
     * 7. Business owner can filter report by custom date range.
     */
    public function test_business_owner_can_filter_report_by_custom_date_range(): void
    {
        // Booking last month
        Booking::create([
            'user_id' => $this->tourist->id,
            'business_id' => $this->businessA1->id,
            'customer_name' => 'Past Customer',
            'customer_email' => 'past@example.com',
            'booking_date' => now()->subMonths(2)->format('Y-m-d'),
            'total_price' => 80.00,
            'status' => Booking::STATUS_COMPLETED,
        ]);

        // Booking yesterday
        Booking::create([
            'user_id' => $this->tourist->id,
            'business_id' => $this->businessA1->id,
            'customer_name' => 'Recent Customer',
            'customer_email' => 'recent@example.com',
            'booking_date' => now()->subDay()->format('Y-m-d'),
            'total_price' => 150.00,
            'status' => Booking::STATUS_COMPLETED,
        ]);

        Sanctum::actingAs($this->ownerA, ['*']);

        $from = now()->subDays(3)->format('Y-m-d');
        $to = now()->format('Y-m-d');

        $res = $this->getJson("/api/business/reports?date_from={$from}&date_to={$to}");
        $res->assertStatus(200);
        $res->assertJsonPath('data.summary.total_bookings', 1);
        $res->assertJsonPath('data.summary.total_revenue', 150);
        $res->assertJsonPath('data.timeframe.interval', 'day');
    }

    /**
     * 8. Business owner can export report in JSON format.
     */
    public function test_business_owner_can_export_report_as_json(): void
    {
        Booking::create([
            'user_id' => $this->tourist->id,
            'business_id' => $this->businessA1->id,
            'customer_name' => 'Alice Tourist',
            'customer_email' => 'alice@test.com',
            'booking_date' => now()->format('Y-m-d'),
            'total_price' => 120.00,
            'status' => Booking::STATUS_COMPLETED,
            'payment_status' => Booking::PAYMENT_PAID,
        ]);

        Sanctum::actingAs($this->ownerA, ['*']);

        $res = $this->getJson('/api/business/reports/export?format=json');
        $res->assertStatus(200);
        $res->assertJsonPath('success', true);
        $res->assertJsonPath('data.total_records', 1);
        $res->assertJsonPath('data.total_revenue', 120);
        $res->assertJsonPath('data.records.0.customer_name', 'Alice Tourist');
    }

    /**
     * 9. Business owner can export report in CSV format (Streaming download).
     */
    public function test_business_owner_can_export_report_as_csv(): void
    {
        Booking::create([
            'user_id' => $this->tourist->id,
            'business_id' => $this->businessA1->id,
            'customer_name' => 'Alice CSV Export',
            'customer_email' => 'alice.csv@test.com',
            'customer_phone' => '+855 12 888 999',
            'booking_date' => now()->format('Y-m-d'),
            'total_price' => 75.00,
            'status' => Booking::STATUS_COMPLETED,
            'payment_status' => Booking::PAYMENT_PAID,
        ]);

        Sanctum::actingAs($this->ownerA, ['*']);

        $res = $this->get('/api/business/reports/export?format=csv');
        $res->assertStatus(200);
        $this->assertStringContainsString('text/csv', $res->headers->get('content-type'));
        $this->assertStringContainsString('attachment; filename=', $res->headers->get('content-disposition'));

        $content = $res->streamedContent();
        $this->assertStringContainsString('Booking Reference', $content);
        $this->assertStringContainsString('Alice CSV Export', $content);
        $this->assertStringContainsString('75.00', $content);

        // Single business export route
        $resSingle = $this->get("/api/business/businesses/{$this->businessA1->id}/reports/export?format=csv");
        $resSingle->assertStatus(200);
        $this->assertStringContainsString('text/csv', $resSingle->headers->get('content-type'));
    }

    /**
     * 10. Admin can inspect any business owner's report.
     */
    public function test_admin_can_access_business_reports_for_any_business(): void
    {
        Booking::create([
            'user_id' => $this->tourist->id,
            'business_id' => $this->businessB->id,
            'customer_name' => 'Dara Guest',
            'customer_email' => 'dara.guest@example.com',
            'booking_date' => now()->format('Y-m-d'),
            'total_price' => 300.00,
            'status' => Booking::STATUS_COMPLETED,
        ]);

        Sanctum::actingAs($this->admin, ['*']);

        $res = $this->getJson("/api/business/reports?business_id={$this->businessB->id}");
        $res->assertStatus(200);
        $res->assertJsonPath('data.summary.total_bookings', 1);
        $res->assertJsonPath('data.summary.total_revenue', 300);
    }
}

<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Business;
use App\Models\BusinessService;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BusinessBookingTest extends TestCase
{
    use RefreshDatabase;

    protected User $tourist;
    protected User $touristOther;
    protected User $ownerA;
    protected User $ownerB;
    protected User $admin;
    protected Business $businessA;
    protected Business $businessB;
    protected BusinessService $serviceA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tourist = User::create([
            'name' => 'John Traveler',
            'email' => 'john.traveler@example.com',
            'phone' => '+855 12 111 222',
            'password_hash' => Hash::make('password123'),
            'role' => User::ROLE_USER,
            'status' => 'Active',
        ]);

        $this->touristOther = User::create([
            'name' => 'Jane Explorer',
            'email' => 'jane.explorer@example.com',
            'phone' => '+855 12 333 444',
            'password_hash' => Hash::make('password123'),
            'role' => User::ROLE_USER,
            'status' => 'Active',
        ]);

        $this->ownerA = User::create([
            'name' => 'Owner Alice',
            'email' => 'alice@business.com',
            'password_hash' => Hash::make('password123'),
            'role' => User::ROLE_BUSINESS_OWNER,
            'status' => 'Active',
        ]);

        $this->ownerB = User::create([
            'name' => 'Owner Bob',
            'email' => 'bob@business.com',
            'password_hash' => Hash::make('password123'),
            'role' => User::ROLE_BUSINESS_OWNER,
            'status' => 'Active',
        ]);

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@tourism.gov',
            'password_hash' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
            'status' => 'Active',
        ]);

        $this->businessA = Business::create([
            'owner_id' => $this->ownerA->id,
            'name' => 'Siem Reap Heritage Hotel & Spa',
            'slug' => 'siem-reap-heritage-hotel',
            'status' => 'active',
            'verification_status' => 'approved',
            'address' => 'Wat Bo Road, Siem Reap',
            'phone' => '+855 63 963 888',
            'email' => 'contact@heritagehotel.com',
        ]);

        $this->businessB = Business::create([
            'owner_id' => $this->ownerB->id,
            'name' => 'Bob Sunset Boat Tours',
            'slug' => 'bob-sunset-boat-tours',
            'status' => 'active',
            'verification_status' => 'approved',
            'address' => 'Riverside, Phnom Penh',
        ]);

        $this->serviceA = BusinessService::create([
            'business_id' => $this->businessA->id,
            'name' => 'Deluxe Suite with Breakfast',
            'description' => 'King bed with pool view and organic breakfast.',
            'price' => 85.00,
            'currency' => 'USD',
            'duration_minutes' => 1440,
            'is_available' => true,
        ]);
    }

    /**
     * 1. Tourist can create a booking for an approved business.
     */
    public function test_tourist_can_create_booking_for_business(): void
    {
        Sanctum::actingAs($this->tourist, ['*']);

        $res = $this->postJson('/api/travel/bookings', [
            'business_id' => $this->businessA->id,
            'service_id' => $this->serviceA->id,
            'booking_date' => now()->addDays(5)->format('Y-m-d'),
            'booking_time' => '14:00',
            'number_of_guests' => 2,
            'special_requests' => 'Quiet room on upper floor please.',
        ]);

        $res->assertStatus(201);
        $res->assertJsonPath('success', true);
        $res->assertJsonPath('data.customer_name', 'John Traveler');
        $res->assertJsonPath('data.customer_email', 'john.traveler@example.com');
        $res->assertJsonPath('data.business_id', $this->businessA->id);
        $res->assertJsonPath('data.service_id', $this->serviceA->id);
        $res->assertJsonPath('data.number_of_guests', 2);
        $res->assertJsonPath('data.total_price', 170);
        $res->assertJsonPath('data.status', 'pending');
        $res->assertJsonPath('data.payment_status', 'unpaid');

        // Check reference generated
        $bookingId = $res->json('data.id');
        $this->assertNotNull($res->json('data.booking_reference'));
        $this->assertDatabaseHas('bookings', [
            'id' => $bookingId,
            'user_id' => $this->tourist->id,
            'business_id' => $this->businessA->id,
            'status' => 'pending',
        ]);

        // Notifications created for owner and tourist
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->ownerA->id,
            'type' => 'booking_received',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->tourist->id,
            'type' => 'booking_created',
        ]);
    }

    /**
     * 2. Tourist cannot book unapproved business.
     */
    public function test_tourist_cannot_book_unapproved_business(): void
    {
        $pendingBusiness = Business::create([
            'owner_id' => $this->ownerA->id,
            'name' => 'Pending Eco Resort',
            'slug' => 'pending-eco-resort',
            'status' => 'active',
            'verification_status' => 'pending',
        ]);

        Sanctum::actingAs($this->tourist, ['*']);

        $res = $this->postJson('/api/travel/bookings', [
            'business_id' => $pendingBusiness->id,
            'booking_date' => now()->addDays(2)->format('Y-m-d'),
        ]);

        $res->assertStatus(400);
        $res->assertJsonPath('success', false);
    }

    /**
     * 3. Business owner cannot book their own business.
     */
    public function test_owner_cannot_book_own_business(): void
    {
        Sanctum::actingAs($this->ownerA, ['*']);

        $res = $this->postJson('/api/travel/bookings', [
            'business_id' => $this->businessA->id,
            'booking_date' => now()->addDays(3)->format('Y-m-d'),
        ]);

        $res->assertStatus(422);
        $res->assertJsonPath('message', 'You cannot book your own business.');
    }

    /**
     * 4. Tourist can view their own bookings list and detail.
     */
    public function test_tourist_can_view_own_bookings(): void
    {
        $booking = Booking::create([
            'user_id' => $this->tourist->id,
            'business_id' => $this->businessA->id,
            'customer_name' => $this->tourist->name,
            'customer_email' => $this->tourist->email,
            'booking_date' => now()->addDays(7)->format('Y-m-d'),
            'number_of_guests' => 1,
            'total_price' => 85.00,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($this->tourist, ['*']);

        $resList = $this->getJson('/api/travel/bookings');
        $resList->assertStatus(200);
        $resList->assertJsonPath('success', true);
        $resList->assertJsonCount(1, 'data.bookings');

        $resShow = $this->getJson("/api/travel/bookings/{$booking->id}");
        $resShow->assertStatus(200);
        $resShow->assertJsonPath('data.id', $booking->id);
        $resShow->assertJsonPath('data.business.name', $this->businessA->name);
    }

    /**
     * 5. Tourist cannot view another user's booking.
     */
    public function test_tourist_cannot_view_others_booking(): void
    {
        $booking = Booking::create([
            'user_id' => $this->tourist->id,
            'business_id' => $this->businessA->id,
            'customer_name' => $this->tourist->name,
            'customer_email' => $this->tourist->email,
            'booking_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => 'pending',
        ]);

        Sanctum::actingAs($this->touristOther, ['*']);

        $res = $this->getJson("/api/travel/bookings/{$booking->id}");
        $res->assertStatus(403);
    }

    /**
     * 6. Tourist can cancel their pending booking.
     */
    public function test_tourist_can_cancel_pending_booking(): void
    {
        $booking = Booking::create([
            'user_id' => $this->tourist->id,
            'business_id' => $this->businessA->id,
            'customer_name' => $this->tourist->name,
            'customer_email' => $this->tourist->email,
            'booking_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => 'pending',
        ]);

        Sanctum::actingAs($this->tourist, ['*']);

        $res = $this->postJson("/api/travel/bookings/{$booking->id}/cancel", [
            'cancellation_reason' => 'Change of flight schedule.',
        ]);

        $res->assertStatus(200);
        $res->assertJsonPath('data.status', 'cancelled');
        $res->assertJsonPath('data.cancellation_reason', 'Change of flight schedule.');

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'cancelled',
        ]);

        // Owner notified of cancellation
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->ownerA->id,
            'type' => 'booking_cancelled',
        ]);
    }

    /**
     * 7. Business owner can see bookings for their business only (tenant isolation).
     */
    public function test_business_owner_can_see_only_their_bookings(): void
    {
        // Booking for Owner A
        Booking::create([
            'user_id' => $this->tourist->id,
            'business_id' => $this->businessA->id,
            'customer_name' => 'John',
            'customer_email' => 'john@test.com',
            'booking_date' => now()->addDays(4)->format('Y-m-d'),
            'status' => 'pending',
        ]);

        // Booking for Owner B
        Booking::create([
            'user_id' => $this->touristOther->id,
            'business_id' => $this->businessB->id,
            'customer_name' => 'Jane',
            'customer_email' => 'jane@test.com',
            'booking_date' => now()->addDays(5)->format('Y-m-d'),
            'status' => 'pending',
        ]);

        Sanctum::actingAs($this->ownerA, ['*']);

        $res = $this->getJson('/api/business/bookings');
        $res->assertStatus(200);
        $res->assertJsonCount(1, 'data.bookings');
        $res->assertJsonPath('data.bookings.0.business_id', $this->businessA->id);

        // Owner A cannot view Owner B's business bookings endpoint
        $resForbidden = $this->getJson("/api/business/businesses/{$this->businessB->id}/bookings");
        $resForbidden->assertStatus(403);
    }

    /**
     * 8. Business owner can confirm a booking.
     */
    public function test_business_owner_can_confirm_booking(): void
    {
        $booking = Booking::create([
            'user_id' => $this->tourist->id,
            'business_id' => $this->businessA->id,
            'customer_name' => $this->tourist->name,
            'customer_email' => $this->tourist->email,
            'booking_date' => now()->addDays(3)->format('Y-m-d'),
            'status' => 'pending',
        ]);

        Sanctum::actingAs($this->ownerA, ['*']);

        $res = $this->postJson("/api/business/bookings/{$booking->id}/confirm");
        $res->assertStatus(200);
        $res->assertJsonPath('data.status', 'confirmed');
        $this->assertNotNull($res->json('data.confirmed_at'));

        // Tourist receives confirmation notification
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->tourist->id,
            'type' => 'booking_confirmed',
        ]);
    }

    /**
     * 9. Business owner can reject a booking.
     */
    public function test_business_owner_can_reject_booking(): void
    {
        $booking = Booking::create([
            'user_id' => $this->tourist->id,
            'business_id' => $this->businessA->id,
            'customer_name' => $this->tourist->name,
            'customer_email' => $this->tourist->email,
            'booking_date' => now()->addDays(3)->format('Y-m-d'),
            'status' => 'pending',
        ]);

        Sanctum::actingAs($this->ownerA, ['*']);

        $res = $this->postJson("/api/business/bookings/{$booking->id}/reject", [
            'rejection_reason' => 'Fully booked on this day.',
        ]);

        $res->assertStatus(200);
        $res->assertJsonPath('data.status', 'rejected');
        $res->assertJsonPath('data.rejection_reason', 'Fully booked on this day.');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->tourist->id,
            'type' => 'booking_rejected',
        ]);
    }

    /**
     * 10. Business owner can complete a booking and view statistics.
     */
    public function test_business_owner_can_complete_booking_and_view_statistics(): void
    {
        $booking = Booking::create([
            'user_id' => $this->tourist->id,
            'business_id' => $this->businessA->id,
            'customer_name' => $this->tourist->name,
            'customer_email' => $this->tourist->email,
            'booking_date' => now()->subDay()->format('Y-m-d'),
            'total_price' => 120.00,
            'status' => 'confirmed',
        ]);

        Sanctum::actingAs($this->ownerA, ['*']);

        $resComplete = $this->postJson("/api/business/bookings/{$booking->id}/complete");
        $resComplete->assertStatus(200);
        $resComplete->assertJsonPath('data.status', 'completed');
        $resComplete->assertJsonPath('data.payment_status', 'paid');

        // Check statistics
        $resStats = $this->getJson('/api/business/bookings/statistics');
        $resStats->assertStatus(200);
        $resStats->assertJsonPath('data.total_bookings', 1);
        $resStats->assertJsonPath('data.completed_bookings', 1);
        $resStats->assertJsonPath('data.total_revenue', 120);
    }

    /**
     * 11. Admin can view and manage all bookings.
     */
    public function test_admin_can_view_and_update_all_bookings(): void
    {
        $booking = Booking::create([
            'user_id' => $this->tourist->id,
            'business_id' => $this->businessA->id,
            'customer_name' => 'John Traveler',
            'customer_email' => 'john@test.com',
            'booking_date' => now()->addDays(2)->format('Y-m-d'),
            'status' => 'pending',
        ]);

        Sanctum::actingAs($this->admin, ['*']);

        $res = $this->getJson('/api/admin/bookings');
        $res->assertStatus(200);
        $res->assertJsonCount(1, 'data.bookings');

        $resUpdate = $this->putJson("/api/admin/bookings/{$booking->id}/status", [
            'status' => 'confirmed',
        ]);
        $resUpdate->assertStatus(200);
        $resUpdate->assertJsonPath('data.status', 'confirmed');
    }
}

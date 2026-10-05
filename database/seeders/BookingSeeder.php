<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Business;
use App\Models\BusinessService;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $touristMarcus = User::where('email', 'marcus.becker@example.com')->first();
        $touristSarah  = User::where('email', 'sarah.jenkins@example.com')->first();
        $defaultTourist = User::where('role', 'user')->first() ?? User::first();

        $marcus = $touristMarcus ?? $defaultTourist;
        $sarah = $touristSarah ?? $defaultTourist;

        $restaurant = Business::where('slug', 'angkor-heritage-restaurant-lounge')->first()
            ?? Business::where('verification_status', 'approved')->first();

        $resort = Business::where('slug', 'shinta-mani-wild-bensley-collection')->first()
            ?? Business::where('verification_status', 'approved')->skip(1)->first()
            ?? $restaurant;

        $cruise = Business::where('slug', 'mekong-kingdoms-luxury-cruises')->first()
            ?? Business::where('verification_status', 'approved')->skip(2)->first()
            ?? $restaurant;

        if (!$restaurant || !$marcus) {
            return;
        }

        $serviceRest = BusinessService::where('business_id', $restaurant->id)->first();
        $serviceResort = $resort ? BusinessService::where('business_id', $resort->id)->first() : null;
        $serviceCruise = $cruise ? BusinessService::where('business_id', $cruise->id)->first() : null;

        $bookings = [
            // 1. Pending Booking
            [
                'booking_reference' => 'BK-' . date('Ymd') . '-PND01',
                'user_id' => $marcus->id,
                'business_id' => $restaurant->id,
                'service_id' => $serviceRest?->id,
                'customer_name' => $marcus->name,
                'customer_email' => $marcus->email,
                'customer_phone' => $marcus->phone ?? '+855 12 777 888',
                'booking_date' => now()->addDays(4)->format('Y-m-d'),
                'booking_time' => '19:00',
                'number_of_guests' => 2,
                'total_price' => $serviceRest ? ($serviceRest->price * 2) : 50.00,
                'currency' => $serviceRest?->currency ?? 'USD',
                'status' => Booking::STATUS_PENDING,
                'payment_status' => Booking::PAYMENT_UNPAID,
                'special_requests' => 'Table by the garden near the Apsara performance stage.',
            ],
            // 2. Confirmed Booking
            [
                'booking_reference' => 'BK-' . date('Ymd') . '-CFM02',
                'user_id' => $sarah->id,
                'business_id' => $resort->id,
                'service_id' => $serviceResort?->id,
                'customer_name' => $sarah->name,
                'customer_email' => $sarah->email,
                'customer_phone' => $sarah->phone ?? '+855 12 999 111',
                'booking_date' => now()->addDays(10)->format('Y-m-d'),
                'booking_time' => '14:00',
                'number_of_guests' => 2,
                'total_price' => $serviceResort ? ($serviceResort->price * 2) : 250.00,
                'currency' => $serviceResort?->currency ?? 'USD',
                'status' => Booking::STATUS_CONFIRMED,
                'payment_status' => Booking::PAYMENT_PAID,
                'special_requests' => 'Airport pickup requested from Siem Reap International.',
                'confirmed_at' => now()->subDay(),
            ],
            // 3. Completed Booking
            [
                'booking_reference' => 'BK-' . date('Ymd') . '-CMP03',
                'user_id' => $marcus->id,
                'business_id' => $cruise ? $cruise->id : $restaurant->id,
                'service_id' => $serviceCruise?->id,
                'customer_name' => $marcus->name,
                'customer_email' => $marcus->email,
                'customer_phone' => $marcus->phone ?? '+855 12 777 888',
                'booking_date' => now()->subDays(5)->format('Y-m-d'),
                'booking_time' => '16:30',
                'number_of_guests' => 4,
                'total_price' => $serviceCruise ? ($serviceCruise->price * 4) : 180.00,
                'currency' => $serviceCruise?->currency ?? 'USD',
                'status' => Booking::STATUS_COMPLETED,
                'payment_status' => Booking::PAYMENT_PAID,
                'special_requests' => 'Sunset cocktail cruise celebration for wedding anniversary.',
                'confirmed_at' => now()->subDays(10),
                'completed_at' => now()->subDays(5),
            ],
            // 4. Cancelled Booking
            [
                'booking_reference' => 'BK-' . date('Ymd') . '-CAN04',
                'user_id' => $sarah->id,
                'business_id' => $restaurant->id,
                'service_id' => $serviceRest?->id,
                'customer_name' => $sarah->name,
                'customer_email' => $sarah->email,
                'customer_phone' => $sarah->phone ?? '+855 12 999 111',
                'booking_date' => now()->subDays(2)->format('Y-m-d'),
                'booking_time' => '18:30',
                'number_of_guests' => 1,
                'total_price' => $serviceRest ? $serviceRest->price : 25.00,
                'currency' => $serviceRest?->currency ?? 'USD',
                'status' => Booking::STATUS_CANCELLED,
                'payment_status' => Booking::PAYMENT_UNPAID,
                'cancellation_reason' => 'Flight delayed due to weather in Bangkok.',
                'cancelled_at' => now()->subDays(3),
            ],
        ];

        foreach ($bookings as $data) {
            $booking = Booking::firstOrCreate(
                ['booking_reference' => $data['booking_reference']],
                $data
            );

            // Seed notification for owner if pending
            if ($booking->status === Booking::STATUS_PENDING && $booking->business->owner_id) {
                Notification::createNotification([
                    'user_id' => $booking->business->owner_id,
                    'type' => 'booking_received',
                    'category' => 'Booking',
                    'title' => 'New Booking Request',
                    'description' => "New booking (#{$booking->booking_reference}) received from {$booking->customer_name} for {$booking->business->name}.",
                    'link' => "/business/bookings/{$booking->id}",
                    'data' => [
                        'booking_id' => $booking->id,
                        'booking_reference' => $booking->booking_reference,
                        'business_id' => $booking->business->id,
                        'customer_name' => $booking->customer_name,
                        'booking_date' => $booking->booking_date?->format('Y-m-d'),
                    ],
                ]);
            }
        }
    }
}

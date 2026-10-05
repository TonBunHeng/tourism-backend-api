<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Business;
use App\Models\BusinessService;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds with rich 2026 bookings for business performance analytics.
     */
    public function run(): void
    {
        // 1. Identify Tourist Users
        $marcus = User::where('email', 'marcus.becker@example.com')->first();
        $sarah  = User::where('email', 'sarah.jenkins@example.com')->first();
        $liam   = User::where('email', 'liam.smith@example.com')->first();
        $claire = User::where('email', 'claire.dubois@example.com')->first();
        $kenji  = User::where('email', 'kenji.takahashi@example.com')->first();
        $elena  = User::where('email', 'elena.rostova@example.com')->first();
        $chen   = User::where('email', 'chen.wei@example.com')->first();
        $minjun = User::where('email', 'minjun.park@example.com')->first();
        $cheat  = User::where('email', 'cheat.sok@example.com')->first();
        $vit    = User::where('email', 'vit.vong@example.com')->first();

        $defaultTourist = User::where('role', 'user')->first() ?? User::first();

        // 2. Identify Businesses
        $heritage = Business::where('slug', 'angkor-heritage-restaurant-lounge')->first()
            ?? Business::where('verification_status', 'approved')->first();

        $malis = Business::where('slug', 'malis-restaurant-phnom-penh')->first()
            ?? Business::where('verification_status', 'approved')->skip(1)->first()
            ?? $heritage;

        $damnak = Business::where('slug', 'cuisine-wat-damnak-siem-reap')->first()
            ?? Business::where('verification_status', 'approved')->skip(2)->first()
            ?? $heritage;

        $resort = Business::where('slug', 'shinta-mani-wild-bensley-collection')->first();
        $phare  = Business::where('slug', 'phare-the-cambodian-circus')->first();

        if (!$heritage || !$defaultTourist) {
            return;
        }

        // 3. Resolve Services
        $sHeritageDinner = BusinessService::where('business_id', $heritage->id)->first();
        $sHeritageShow   = BusinessService::where('business_id', $heritage->id)->skip(1)->first() ?? $sHeritageDinner;

        $sMalisDegustation = $malis ? BusinessService::where('business_id', $malis->id)->first() : null;
        $sMalisBreakfast   = $malis ? (BusinessService::where('business_id', $malis->id)->skip(1)->first() ?? $sMalisDegustation) : null;

        $sDamnakForaged    = $damnak ? BusinessService::where('business_id', $damnak->id)->first() : null;
        $sResortExp        = $resort ? BusinessService::where('business_id', $resort->id)->first() : null;
        $sPhareShow        = $phare  ? BusinessService::where('business_id', $phare->id)->first() : null;

        // 4. Comprehensive Booking Schedule Across 2026
        $seedBookings = [
            // =================================================================
            // JANUARY 2026
            // =================================================================
            [
                'ref' => 'BK-20260112-MC01',
                'user' => $marcus ?? $defaultTourist,
                'business' => $heritage,
                'service' => $sHeritageDinner,
                'date' => '2026-01-12',
                'time' => '19:00',
                'guests' => 2,
                'price' => 76.00,
                'status' => Booking::STATUS_COMPLETED,
                'payment' => Booking::PAYMENT_PAID,
                'requests' => 'Table by the garden near traditional orchestra.',
                'confirmed_at' => '2026-01-08 10:00:00',
                'completed_at' => '2026-01-12 21:30:00',
                'created_at' => '2026-01-08 09:30:00',
            ],
            [
                'ref' => 'BK-20260124-CL02',
                'user' => $claire ?? $defaultTourist,
                'business' => $malis,
                'service' => $sMalisDegustation,
                'date' => '2026-01-24',
                'time' => '19:30',
                'guests' => 2,
                'price' => 130.00,
                'status' => Booking::STATUS_COMPLETED,
                'payment' => Booking::PAYMENT_PAID,
                'requests' => 'Celebrating 5th anniversary, window table requested.',
                'confirmed_at' => '2026-01-20 14:00:00',
                'completed_at' => '2026-01-24 22:00:00',
                'created_at' => '2026-01-20 11:15:00',
            ],

            // =================================================================
            // FEBRUARY 2026
            // =================================================================
            [
                'ref' => 'BK-20260214-KJ03',
                'user' => $kenji ?? $defaultTourist,
                'business' => $damnak,
                'service' => $sDamnakForaged,
                'date' => '2026-02-14',
                'time' => '18:30',
                'guests' => 2,
                'price' => 96.00,
                'status' => Booking::STATUS_COMPLETED,
                'payment' => Booking::PAYMENT_PAID,
                'requests' => 'Valentine dinner tasting menu.',
                'confirmed_at' => '2026-02-10 12:00:00',
                'completed_at' => '2026-02-14 21:00:00',
                'created_at' => '2026-02-10 08:45:00',
            ],
            [
                'ref' => 'BK-20260220-EL04',
                'user' => $elena ?? $defaultTourist,
                'business' => $heritage,
                'service' => $sHeritageShow,
                'date' => '2026-02-20',
                'time' => '18:00',
                'guests' => 4,
                'price' => 112.00,
                'status' => Booking::STATUS_COMPLETED,
                'payment' => Booking::PAYMENT_PAID,
                'requests' => 'Front row seating for Apsara performance.',
                'confirmed_at' => '2026-02-16 15:30:00',
                'completed_at' => '2026-02-20 20:30:00',
                'created_at' => '2026-02-16 09:10:00',
            ],

            // =================================================================
            // MARCH 2026
            // =================================================================
            [
                'ref' => 'BK-20260308-CW05',
                'user' => $chen ?? $defaultTourist,
                'business' => $malis,
                'service' => $sMalisBreakfast,
                'date' => '2026-03-08',
                'time' => '08:30',
                'guests' => 3,
                'price' => 54.00,
                'status' => Booking::STATUS_COMPLETED,
                'payment' => Booking::PAYMENT_PAID,
                'requests' => 'Quiet corner for business breakfast.',
                'confirmed_at' => '2026-03-06 11:00:00',
                'completed_at' => '2026-03-08 10:00:00',
                'created_at' => '2026-03-06 09:00:00',
            ],
            [
                'ref' => 'BK-20260319-SJ06',
                'user' => $sarah ?? $defaultTourist,
                'business' => $heritage,
                'service' => $sHeritageDinner,
                'date' => '2026-03-19',
                'time' => '19:00',
                'guests' => 2,
                'price' => 76.00,
                'status' => Booking::STATUS_COMPLETED,
                'payment' => Booking::PAYMENT_PAID,
                'requests' => 'No peanuts please due to mild allergy.',
                'confirmed_at' => '2026-03-15 16:00:00',
                'completed_at' => '2026-03-19 21:00:00',
                'created_at' => '2026-03-15 11:20:00',
            ],
            [
                'ref' => 'BK-20260328-MJ07',
                'user' => $minjun ?? $defaultTourist,
                'business' => $malis,
                'service' => $sMalisDegustation,
                'date' => '2026-03-28',
                'time' => '19:00',
                'guests' => 4,
                'price' => 260.00,
                'status' => Booking::STATUS_COMPLETED,
                'payment' => Booking::PAYMENT_PAID,
                'requests' => 'Private dining room requested.',
                'confirmed_at' => '2026-03-22 17:00:00',
                'completed_at' => '2026-03-28 22:30:00',
                'created_at' => '2026-03-22 14:00:00',
            ],

            // =================================================================
            // APRIL 2026 (Khmer New Year Peak)
            // =================================================================
            [
                'ref' => 'BK-20260413-LM08',
                'user' => $liam ?? $defaultTourist,
                'business' => $heritage,
                'service' => $sHeritageShow,
                'date' => '2026-04-13',
                'time' => '18:00',
                'guests' => 5,
                'price' => 140.00,
                'status' => Booking::STATUS_COMPLETED,
                'payment' => Booking::PAYMENT_PAID,
                'requests' => 'Sangkran celebration dinner with family.',
                'confirmed_at' => '2026-04-05 10:00:00',
                'completed_at' => '2026-04-13 21:00:00',
                'created_at' => '2026-04-05 08:30:00',
            ],
            [
                'ref' => 'BK-20260414-MC09',
                'user' => $marcus ?? $defaultTourist,
                'business' => $damnak,
                'service' => $sDamnakForaged,
                'date' => '2026-04-14',
                'time' => '19:00',
                'guests' => 4,
                'price' => 192.00,
                'status' => Booking::STATUS_COMPLETED,
                'payment' => Booking::PAYMENT_PAID,
                'requests' => 'Khmer New Year celebration banquet.',
                'confirmed_at' => '2026-04-07 11:30:00',
                'completed_at' => '2026-04-14 21:45:00',
                'created_at' => '2026-04-07 09:00:00',
            ],
            [
                'ref' => 'BK-20260416-VT10',
                'user' => $vit ?? $defaultTourist,
                'business' => $heritage,
                'service' => $sHeritageDinner,
                'date' => '2026-04-16',
                'time' => '19:30',
                'guests' => 6,
                'price' => 228.00,
                'status' => Booking::STATUS_COMPLETED,
                'payment' => Booking::PAYMENT_PAID,
                'requests' => 'Large courtyard banquet table.',
                'confirmed_at' => '2026-04-10 14:00:00',
                'completed_at' => '2026-04-16 22:00:00',
                'created_at' => '2026-04-10 10:15:00',
            ],

            // =================================================================
            // MAY 2026
            // =================================================================
            [
                'ref' => 'BK-20260505-CL11',
                'user' => $claire ?? $defaultTourist,
                'business' => $damnak,
                'service' => $sDamnakForaged,
                'date' => '2026-05-05',
                'time' => '19:00',
                'guests' => 2,
                'price' => 96.00,
                'status' => Booking::STATUS_COMPLETED,
                'payment' => Booking::PAYMENT_PAID,
                'requests' => 'Returning guests from France.',
                'confirmed_at' => '2026-05-01 10:00:00',
                'completed_at' => '2026-05-05 21:15:00',
                'created_at' => '2026-05-01 08:00:00',
            ],
            [
                'ref' => 'BK-20260522-SC12',
                'user' => $cheat ?? $defaultTourist,
                'business' => $malis,
                'service' => $sMalisDegustation,
                'date' => '2026-05-22',
                'time' => '19:00',
                'guests' => 2,
                'price' => 130.00,
                'status' => Booking::STATUS_COMPLETED,
                'payment' => Booking::PAYMENT_PAID,
                'requests' => 'Outdoor pond garden table.',
                'confirmed_at' => '2026-05-18 16:00:00',
                'completed_at' => '2026-05-22 21:30:00',
                'created_at' => '2026-05-18 13:40:00',
            ],

            // =================================================================
            // JUNE 2026
            // =================================================================
            [
                'ref' => 'BK-20260611-KJ13',
                'user' => $kenji ?? $defaultTourist,
                'business' => $heritage,
                'service' => $sHeritageShow,
                'date' => '2026-06-11',
                'time' => '18:30',
                'guests' => 2,
                'price' => 56.00,
                'status' => Booking::STATUS_COMPLETED,
                'payment' => Booking::PAYMENT_PAID,
                'requests' => 'Show starts at 19:30, arriving early for drinks.',
                'confirmed_at' => '2026-06-08 12:00:00',
                'completed_at' => '2026-06-11 21:00:00',
                'created_at' => '2026-06-08 10:00:00',
            ],
            [
                'ref' => 'BK-20260627-EL14',
                'user' => $elena ?? $defaultTourist,
                'business' => $heritage,
                'service' => $sHeritageDinner,
                'date' => '2026-06-27',
                'time' => '19:00',
                'guests' => 3,
                'price' => 114.00,
                'status' => Booking::STATUS_COMPLETED,
                'payment' => Booking::PAYMENT_PAID,
                'requests' => 'Celebrating graduation.',
                'confirmed_at' => '2026-06-22 15:00:00',
                'completed_at' => '2026-06-27 21:30:00',
                'created_at' => '2026-06-22 11:20:00',
            ],

            // =================================================================
            // JULY 2026
            // =================================================================
            [
                'ref' => 'BK-20260708-CW15',
                'user' => $chen ?? $defaultTourist,
                'business' => $damnak,
                'service' => $sDamnakForaged,
                'date' => '2026-07-08',
                'time' => '19:00',
                'guests' => 2,
                'price' => 96.00,
                'status' => Booking::STATUS_COMPLETED,
                'payment' => Booking::PAYMENT_PAID,
                'requests' => 'Sommelier wine pairing recommendation.',
                'confirmed_at' => '2026-07-04 11:00:00',
                'completed_at' => '2026-07-08 21:30:00',
                'created_at' => '2026-07-04 09:30:00',
            ],
            [
                'ref' => 'BK-20260721-SJ16',
                'user' => $sarah ?? $defaultTourist,
                'business' => $malis,
                'service' => $sMalisBreakfast,
                'date' => '2026-07-21',
                'time' => '09:00',
                'guests' => 2,
                'price' => 36.00,
                'status' => Booking::STATUS_COMPLETED,
                'payment' => Booking::PAYMENT_PAID,
                'requests' => 'Fresh coconut water on arrival.',
                'confirmed_at' => '2026-07-17 14:00:00',
                'completed_at' => '2026-07-21 10:30:00',
                'created_at' => '2026-07-17 11:00:00',
            ],

            // =================================================================
            // AUGUST 2026
            // =================================================================
            [
                'ref' => 'BK-20260804-MJ17',
                'user' => $minjun ?? $defaultTourist,
                'business' => $heritage,
                'service' => $sHeritageShow,
                'date' => '2026-08-04',
                'time' => '18:30',
                'guests' => 3,
                'price' => 84.00,
                'status' => Booking::STATUS_COMPLETED,
                'payment' => Booking::PAYMENT_PAID,
                'requests' => 'High chair needed for child.',
                'confirmed_at' => '2026-08-01 10:00:00',
                'completed_at' => '2026-08-04 21:00:00',
                'created_at' => '2026-08-01 08:30:00',
            ],
            [
                'ref' => 'BK-20260818-LM18',
                'user' => $liam ?? $defaultTourist,
                'business' => $malis,
                'service' => $sMalisDegustation,
                'date' => '2026-08-18',
                'time' => '19:30',
                'guests' => 2,
                'price' => 130.00,
                'status' => Booking::STATUS_COMPLETED,
                'payment' => Booking::PAYMENT_PAID,
                'requests' => 'Kampot pepper crab dish requested if in season.',
                'confirmed_at' => '2026-08-14 16:00:00',
                'completed_at' => '2026-08-18 22:00:00',
                'created_at' => '2026-08-14 12:15:00',
            ],
            [
                'ref' => 'BK-20260830-MC19',
                'user' => $marcus ?? $defaultTourist,
                'business' => $heritage,
                'service' => $sHeritageDinner,
                'date' => '2026-08-30',
                'time' => '19:00',
                'guests' => 2,
                'price' => 76.00,
                'status' => Booking::STATUS_COMPLETED,
                'payment' => Booking::PAYMENT_PAID,
                'requests' => 'Returning traveler.',
                'confirmed_at' => '2026-08-25 11:00:00',
                'completed_at' => '2026-08-30 21:30:00',
                'created_at' => '2026-08-25 09:00:00',
            ],

            // =================================================================
            // SEPTEMBER 2026
            // =================================================================
            [
                'ref' => 'BK-20260912-KJ20',
                'user' => $kenji ?? $defaultTourist,
                'business' => $malis,
                'service' => $sMalisDegustation,
                'date' => '2026-09-12',
                'time' => '19:00',
                'guests' => 2,
                'price' => 130.00,
                'status' => Booking::STATUS_COMPLETED,
                'payment' => Booking::PAYMENT_PAID,
                'requests' => 'Table by the lotus fountain.',
                'confirmed_at' => '2026-09-08 12:00:00',
                'completed_at' => '2026-09-12 21:30:00',
                'created_at' => '2026-09-08 10:00:00',
            ],
            [
                'ref' => 'BK-20260920-EL21',
                'user' => $elena ?? $defaultTourist,
                'business' => $damnak,
                'service' => $sDamnakForaged,
                'date' => '2026-09-20',
                'time' => '18:30',
                'guests' => 2,
                'price' => 96.00,
                'status' => Booking::STATUS_COMPLETED,
                'payment' => Booking::PAYMENT_PAID,
                'requests' => 'Vegetarian option for one guest.',
                'confirmed_at' => '2026-09-15 15:00:00',
                'completed_at' => '2026-09-20 21:00:00',
                'created_at' => '2026-09-15 11:30:00',
            ],

            // =================================================================
            // OCTOBER 2026 (Current Active Month)
            // =================================================================
            [
                'ref' => 'BK-20261002-CL22',
                'user' => $claire ?? $defaultTourist,
                'business' => $heritage,
                'service' => $sHeritageShow,
                'date' => '2026-10-02',
                'time' => '18:00',
                'guests' => 2,
                'price' => 56.00,
                'status' => Booking::STATUS_COMPLETED,
                'payment' => Booking::PAYMENT_PAID,
                'requests' => 'Center stage view table.',
                'confirmed_at' => '2026-09-28 10:00:00',
                'completed_at' => '2026-10-02 21:00:00',
                'created_at' => '2026-09-28 09:00:00',
            ],
            [
                'ref' => 'BK-20261012-CW23',
                'user' => $chen ?? $defaultTourist,
                'business' => $malis,
                'service' => $sMalisDegustation,
                'date' => '2026-10-12',
                'time' => '19:00',
                'guests' => 2,
                'price' => 130.00,
                'status' => Booking::STATUS_CONFIRMED,
                'payment' => Booking::PAYMENT_PAID,
                'requests' => 'Business delegation dinner.',
                'confirmed_at' => '2026-10-03 14:00:00',
                'completed_at' => null,
                'created_at' => '2026-10-03 11:00:00',
            ],
            [
                'ref' => 'BK-20261018-MJ24',
                'user' => $minjun ?? $defaultTourist,
                'business' => $damnak,
                'service' => $sDamnakForaged,
                'date' => '2026-10-18',
                'time' => '19:00',
                'guests' => 4,
                'price' => 192.00,
                'status' => Booking::STATUS_CONFIRMED,
                'payment' => Booking::PAYMENT_PAID,
                'requests' => 'Private upstairs gallery table.',
                'confirmed_at' => '2026-10-04 16:30:00',
                'completed_at' => null,
                'created_at' => '2026-10-04 12:00:00',
            ],
            [
                'ref' => 'BK-20261025-LM25',
                'user' => $liam ?? $defaultTourist,
                'business' => $heritage,
                'service' => $sHeritageDinner,
                'date' => '2026-10-25',
                'time' => '19:30',
                'guests' => 2,
                'price' => 76.00,
                'status' => Booking::STATUS_PENDING,
                'payment' => Booking::PAYMENT_UNPAID,
                'requests' => 'Vegetarian options requested.',
                'confirmed_at' => null,
                'completed_at' => null,
                'created_at' => '2026-10-05 10:00:00',
            ],

            // =================================================================
            // NOVEMBER 2026 (Upcoming Peak)
            // =================================================================
            [
                'ref' => 'BK-20261110-MC26',
                'user' => $marcus ?? $defaultTourist,
                'business' => $heritage,
                'service' => $sHeritageShow,
                'date' => '2026-11-10',
                'time' => '18:00',
                'guests' => 4,
                'price' => 112.00,
                'status' => Booking::STATUS_CONFIRMED,
                'payment' => Booking::PAYMENT_PAID,
                'requests' => 'Water festival tourist group.',
                'confirmed_at' => '2026-10-04 14:00:00',
                'completed_at' => null,
                'created_at' => '2026-10-04 11:20:00',
            ],
            [
                'ref' => 'BK-20261122-SJ27',
                'user' => $sarah ?? $defaultTourist,
                'business' => $damnak,
                'service' => $sDamnakForaged,
                'date' => '2026-11-22',
                'time' => '19:00',
                'guests' => 2,
                'price' => 96.00,
                'status' => Booking::STATUS_CONFIRMED,
                'payment' => Booking::PAYMENT_UNPAID,
                'requests' => 'Anniversary table by garden pond.',
                'confirmed_at' => '2026-10-05 15:00:00',
                'completed_at' => null,
                'created_at' => '2026-10-05 13:00:00',
            ],

            // =================================================================
            // DECEMBER 2026 (Upcoming Festive Holiday Season)
            // =================================================================
            [
                'ref' => 'BK-20261224-EL28',
                'user' => $elena ?? $defaultTourist,
                'business' => $malis,
                'service' => $sMalisDegustation,
                'date' => '2026-12-24',
                'time' => '19:30',
                'guests' => 4,
                'price' => 260.00,
                'status' => Booking::STATUS_CONFIRMED,
                'payment' => Booking::PAYMENT_PAID,
                'requests' => 'Christmas Eve celebration dinner.',
                'confirmed_at' => '2026-10-02 11:00:00',
                'completed_at' => null,
                'created_at' => '2026-10-02 09:15:00',
            ],
            [
                'ref' => 'BK-20261231-KJ29',
                'user' => $kenji ?? $defaultTourist,
                'business' => $damnak,
                'service' => $sDamnakForaged,
                'date' => '2026-12-31',
                'time' => '20:00',
                'guests' => 6,
                'price' => 288.00,
                'status' => Booking::STATUS_CONFIRMED,
                'payment' => Booking::PAYMENT_PAID,
                'requests' => 'New Year Eve countdown reservation.',
                'confirmed_at' => '2026-10-03 16:00:00',
                'completed_at' => null,
                'created_at' => '2026-10-03 14:00:00',
            ],
        ];

        // 5. Insert or Update Records
        foreach ($seedBookings as $b) {
            $user = $b['user'];
            $business = $b['business'];
            $service = $b['service'];

            if (!$business) {
                continue;
            }

            $booking = Booking::updateOrCreate(
                ['booking_reference' => $b['ref']],
                [
                    'user_id' => $user->id,
                    'business_id' => $business->id,
                    'service_id' => $service?->id,
                    'customer_name' => $user->name,
                    'customer_email' => $user->email,
                    'customer_phone' => $user->phone ?? '+855 12 888 999',
                    'booking_date' => $b['date'],
                    'booking_time' => $b['time'],
                    'number_of_guests' => $b['guests'],
                    'total_price' => $b['price'],
                    'currency' => $service?->currency ?? 'USD',
                    'status' => $b['status'],
                    'payment_status' => $b['payment'],
                    'special_requests' => $b['requests'] ?? null,
                    'confirmed_at' => isset($b['confirmed_at']) ? Carbon::parse($b['confirmed_at']) : null,
                    'completed_at' => isset($b['completed_at']) ? Carbon::parse($b['completed_at']) : null,
                    'created_at' => Carbon::parse($b['created_at']),
                    'updated_at' => isset($b['completed_at']) ? Carbon::parse($b['completed_at']) : Carbon::parse($b['created_at']),
                ]
            );

            // Create notification for pending bookings
            if ($booking->status === Booking::STATUS_PENDING && $business->owner_id) {
                Notification::firstOrCreate(
                    [
                        'user_id' => $business->owner_id,
                        'type' => 'booking_received',
                        'link' => "/business/bookings/{$booking->id}",
                    ],
                    [
                        'category' => 'Booking',
                        'title' => 'New Booking Request',
                        'description' => "New booking (#{$booking->booking_reference}) received from {$booking->customer_name} for {$business->name}.",
                        'data' => [
                            'booking_id' => $booking->id,
                            'booking_reference' => $booking->booking_reference,
                            'business_id' => $business->id,
                            'customer_name' => $booking->customer_name,
                            'booking_date' => $booking->booking_date?->format('Y-m-d'),
                        ],
                    ]
                );
            }
        }
    }
}

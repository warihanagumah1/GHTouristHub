<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Currency;
use App\Models\Listing;
use App\Models\ListingMedia;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\TenantMember;
use App\Models\TenantReview;
use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $password = Hash::make('password');

        $this->seedCurrencies();
        $this->call(TouristAttractionSeeder::class);

        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@touristhub.test',
            'password' => $password,
            'user_role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        $adminStaff = User::factory()->create([
            'name' => 'Admin Staff',
            'email' => 'admin-staff@touristhub.test',
            'password' => $password,
            'user_role' => User::ROLE_ADMIN_STAFF,
            'email_verified_at' => now(),
        ]);

        $tourOwner = User::factory()->create([
            'name' => 'Tour Owner',
            'email' => 'tour-owner@touristhub.test',
            'password' => $password,
            'user_role' => User::ROLE_TOUR_OWNER,
            'email_verified_at' => now(),
        ]);

        $tourStaff = User::factory()->create([
            'name' => 'Tour Staff',
            'email' => 'tour-staff@touristhub.test',
            'password' => $password,
            'user_role' => User::ROLE_TOUR_STAFF,
            'email_verified_at' => now(),
        ]);

        $utilityOwner = User::factory()->create([
            'name' => 'Utility Owner',
            'email' => 'utility-owner@touristhub.test',
            'password' => $password,
            'user_role' => User::ROLE_UTILITY_OWNER,
            'email_verified_at' => now(),
        ]);

        $utilityStaff = User::factory()->create([
            'name' => 'Utility Staff',
            'email' => 'utility-staff@touristhub.test',
            'password' => $password,
            'user_role' => User::ROLE_UTILITY_STAFF,
            'email_verified_at' => now(),
        ]);

        $client = User::factory()->create([
            'name' => 'Client Traveler',
            'email' => 'client@touristhub.test',
            'password' => $password,
            'user_role' => User::ROLE_CLIENT,
            'email_verified_at' => now(),
        ]);

        $clientTwo = User::factory()->create([
            'name' => 'Client Explorer',
            'email' => 'client2@touristhub.test',
            'password' => $password,
            'user_role' => User::ROLE_CLIENT,
            'email_verified_at' => now(),
        ]);

        $tourTenant = Tenant::create([
            'name' => 'Sankofa Trails Ltd',
            'slug' => 'sankofa-trails',
            'type' => 'tour_company',
            'status' => 'approved',
            'owner_user_id' => $tourOwner->id,
        ]);

        $this->seedTenantMembers($tourTenant, [
            ['user' => $tourOwner, 'role' => User::ROLE_TOUR_OWNER],
            ['user' => $tourStaff, 'role' => User::ROLE_TOUR_STAFF],
        ]);

        VendorProfile::create([
            'tenant_id' => $tourTenant->id,
            'legal_business_name' => 'Sankofa Trails Limited',
            'country' => 'Ghana',
            'city' => 'Accra',
            'support_phone' => '+233200000000',
            'support_email' => 'support@sankofatrails.test',
            'logo_url' => 'https://images.unsplash.com/photo-1568602471122-7832951cc4c5?auto=format&fit=crop&w=300&q=80',
            'banner_url' => 'https://images.unsplash.com/photo-1489515217757-5fd1be406fef?auto=format&fit=crop&w=1600&q=80',
            'website_url' => 'https://sankofatrails.test',
            'founded_year' => 2017,
            'about' => 'Sankofa Trails curates immersive cultural and nature-led itineraries across West Africa with local guides and responsible travel partners.',
            'kyc_status' => 'approved',
            'submitted_at' => now()->subDays(12),
            'reviewed_at' => now()->subDays(8),
        ]);

        $utilityTenant = Tenant::create([
            'name' => 'Blue Horizon Utilities',
            'slug' => 'blue-horizon-utilities',
            'type' => 'utility_owner',
            'status' => 'approved',
            'owner_user_id' => $utilityOwner->id,
        ]);

        $this->seedTenantMembers($utilityTenant, [
            ['user' => $utilityOwner, 'role' => User::ROLE_UTILITY_OWNER],
            ['user' => $utilityStaff, 'role' => User::ROLE_UTILITY_STAFF],
        ]);

        VendorProfile::create([
            'tenant_id' => $utilityTenant->id,
            'legal_business_name' => 'Blue Horizon Hospitality & Mobility',
            'country' => 'Kenya',
            'city' => 'Nairobi',
            'support_phone' => '+254700000000',
            'support_email' => 'hello@bluehorizon.test',
            'logo_url' => 'https://images.unsplash.com/photo-1556157382-97eda2d62296?auto=format&fit=crop&w=300&q=80',
            'banner_url' => 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=1600&q=80',
            'website_url' => 'https://bluehorizon.test',
            'founded_year' => 2019,
            'about' => 'Blue Horizon operates premium utility services including boutique stays, airport transfers, and curated city experiences for independent travelers and groups.',
            'kyc_status' => 'approved',
            'submitted_at' => now()->subDays(11),
            'reviewed_at' => now()->subDays(7),
        ]);

        $accraTour = Listing::create([
            'tenant_id' => $tourTenant->id,
            'type' => 'tour',
            'title' => 'Accra Cultural Weekend',
            'slug' => 'accra-cultural-weekend',
            'summary' => 'A vibrant city immersion across markets, heritage sites, and coastal sunsets.',
            'description' => 'Spend three rich days exploring Accra\'s creative districts, local food culture, and iconic landmarks with a professional guide and private transfers.',
            'city' => 'Accra',
            'country' => 'Ghana',
            'address' => 'Independence Avenue, Accra',
            'price_from' => 4200,
            'currency_code' => 'GHS',
            'pricing_unit' => 'per traveler',
            'highlights' => ['Makola market walk', 'Jamestown art district', 'Sunset beach dinner'],
            'inclusions' => ['Hotel pickup', 'Guide', 'Museum tickets', '2 lunches'],
            'exclusions' => ['Flights', 'Travel insurance', 'Personal shopping'],
            'languages' => ['English', 'French'],
            'itinerary' => [
                'Arrival and city orientation with evening rooftop dinner.',
                'Full-day cultural exploration and curated artisan experiences.',
                'Coastal heritage stop and airport drop-off.',
            ],
            'duration_label' => '3 days / 2 nights',
            'group_size_label' => '2-12 travelers',
            'cancellation_policy' => 'Free cancellation up to 72 hours before start. 50% fee within 72 hours.',
            'booking_rules' => 'Booking closes 24 hours before departure. Passport details requested after confirmation.',
            'is_featured' => true,
            'status' => 'published',
        ]);

        $this->seedMedia($accraTour->id, [
            ['url' => 'https://images.unsplash.com/photo-1523805009345-7448845a9e53?auto=format&fit=crop&w=1600&q=80', 'caption' => 'Accra city skyline', 'is_cover' => true],
            ['url' => 'https://images.unsplash.com/photo-1516026672322-bc52d61a55d5?auto=format&fit=crop&w=1600&q=80', 'caption' => 'Coastal heritage walk'],
            ['url' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=1600&q=80', 'caption' => 'Local guide storytelling'],
            ['url' => 'https://images.unsplash.com/photo-1472396961693-142e6e269027?auto=format&fit=crop&w=1600&q=80', 'caption' => 'Nature and wildlife extension'],
        ]);

        $safariTour = Listing::create([
            'tenant_id' => $tourTenant->id,
            'type' => 'tour',
            'title' => 'Maasai Mara Safari Classic',
            'slug' => 'maasai-mara-safari-classic',
            'summary' => 'Signature wildlife circuit with game drives, camp stays, and cultural village visit.',
            'description' => 'A guided safari journey designed for photographers and first-time explorers with quality lodging and expert field trackers.',
            'city' => 'Narok',
            'country' => 'Kenya',
            'address' => 'Maasai Mara Reserve Gate',
            'price_from' => 98000,
            'currency_code' => 'KES',
            'pricing_unit' => 'per traveler',
            'highlights' => ['Dawn and dusk game drives', 'Campfire storytelling', 'Maasai village visit'],
            'inclusions' => ['Airport transfer', 'Lodge stay', 'Meals', 'Park fees'],
            'exclusions' => ['International airfare', 'Visa fees'],
            'languages' => ['English'],
            'itinerary' => ['Arrival and transfer to camp.', 'Two full safari days with breaks.', 'Final morning drive and departure.'],
            'duration_label' => '4 days / 3 nights',
            'group_size_label' => '2-8 travelers',
            'cancellation_policy' => 'Free cancellation up to 7 days before trip.',
            'booking_rules' => 'Booking closes 48 hours before departure.',
            'is_featured' => true,
            'status' => 'published',
        ]);

        $this->seedMedia($safariTour->id, [
            ['url' => 'https://images.unsplash.com/photo-1516426122078-c23e76319801?auto=format&fit=crop&w=1600&q=80', 'caption' => 'Safari plains', 'is_cover' => true],
            ['url' => 'https://images.unsplash.com/photo-1516934024742-b461fba47600?auto=format&fit=crop&w=1600&q=80', 'caption' => 'Game drive moment'],
            ['url' => 'https://images.unsplash.com/photo-1489392191049-fc10c97e64b6?auto=format&fit=crop&w=1600&q=80', 'caption' => 'Camp and sunset'],
        ]);

        $hotel = Listing::create([
            'tenant_id' => $utilityTenant->id,
            'type' => 'utility',
            'subtype' => 'hotel',
            'title' => 'Golden Coast Hotel',
            'slug' => 'golden-coast-hotel',
            'summary' => 'Boutique waterfront stay with breakfast, airport shuttle, and coworking lounge.',
            'description' => 'Golden Coast Hotel blends business-ready comfort with coastal relaxation, ideal for short and extended stays.',
            'city' => 'Accra',
            'country' => 'Ghana',
            'address' => 'Labadi Beach Road',
            'price_from' => 1200,
            'currency_code' => 'GHS',
            'pricing_unit' => 'per night',
            'amenities' => ['Ocean-view rooms', 'High-speed Wi-Fi', 'Airport transfer', 'Gym', 'Restaurant'],
            'duration_label' => 'Flexible stay',
            'group_size_label' => '1-4 guests per room',
            'cancellation_policy' => 'Free cancellation up to 48 hours before check-in.',
            'booking_rules' => 'Check-in 14:00. Check-out 11:00.',
            'is_featured' => true,
            'status' => 'published',
        ]);

        $this->seedMedia($hotel->id, [
            ['url' => 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=1600&q=80', 'caption' => 'Executive suite', 'is_cover' => true],
            ['url' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1600&q=80', 'caption' => 'Lobby and lounge'],
            ['url' => 'https://images.unsplash.com/photo-1501117716987-c8e1ecb210f9?auto=format&fit=crop&w=1600&q=80', 'caption' => 'Pool area'],
        ]);

        $transport = Listing::create([
            'tenant_id' => $utilityTenant->id,
            'type' => 'utility',
            'subtype' => 'transport',
            'title' => 'Safari Shuttle Line',
            'slug' => 'safari-shuttle-line',
            'summary' => 'Comfortable intercity shuttle service with luggage support and daily departures.',
            'description' => 'Reliable shared transport route connecting key city and safari corridor checkpoints with onboard support.',
            'city' => 'Nairobi',
            'country' => 'Kenya',
            'address' => 'Westlands Transit Hub',
            'price_from' => 4500,
            'currency_code' => 'KES',
            'pricing_unit' => 'per seat',
            'amenities' => ['Air conditioning', 'USB charging', 'Luggage assistance', 'Live trip updates'],
            'duration_label' => '2-6 hours routes',
            'group_size_label' => '1-24 passengers',
            'cancellation_policy' => 'Free cancellation up to 24 hours before departure.',
            'booking_rules' => 'Arrive 30 minutes before departure. Government ID required.',
            'is_featured' => true,
            'status' => 'published',
        ]);

        $this->seedMedia($transport->id, [
            ['url' => 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&w=1600&q=80', 'caption' => 'Comfort shuttle', 'is_cover' => true],
            ['url' => 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=1600&q=80', 'caption' => 'Premium fleet'],
            ['url' => 'https://images.unsplash.com/photo-1494515843206-f3117d3f51b7?auto=format&fit=crop&w=1600&q=80', 'caption' => 'On-route comfort stop'],
        ]);

        $attraction = Listing::create([
            'tenant_id' => $utilityTenant->id,
            'type' => 'utility',
            'subtype' => 'attraction',
            'title' => 'Nairobi City Lights Experience',
            'slug' => 'nairobi-city-lights-experience',
            'summary' => 'Evening city experience with skyline views, local tasting, and live music.',
            'description' => 'A relaxed urban attraction package for travelers looking to explore Nairobi nightlife with curated stops and hosted transport.',
            'city' => 'Nairobi',
            'country' => 'Kenya',
            'address' => 'Kenyatta Avenue',
            'price_from' => 6000,
            'currency_code' => 'KES',
            'pricing_unit' => 'per ticket',
            'amenities' => ['Hosted guide', 'Curated tasting stop', 'Music venue access'],
            'duration_label' => '4 hours',
            'group_size_label' => '1-18 guests',
            'cancellation_policy' => 'Free cancellation up to 24 hours.',
            'booking_rules' => 'Minimum age 18. Departure at 6:00 PM.',
            'is_featured' => false,
            'status' => 'published',
        ]);

        $this->seedMedia($attraction->id, [
            ['url' => 'https://images.unsplash.com/photo-1480714378408-67cf0d13bc1f?auto=format&fit=crop&w=1600&q=80', 'caption' => 'City skyline', 'is_cover' => true],
            ['url' => 'https://images.unsplash.com/photo-1514933651103-005eec06c04b?auto=format&fit=crop&w=1600&q=80', 'caption' => 'Dining and local taste'],
            ['url' => 'https://images.unsplash.com/photo-1470337458703-46ad1756a187?auto=format&fit=crop&w=1600&q=80', 'caption' => 'Nighttime street scene'],
        ]);

        $accraPaidBooking = $this->createBookingWithPayment($client, $accraTour, [
            'travelers_count' => 2,
            'status' => 'paid',
            'total_amount' => 8400,
            'paid_at' => now()->subDays(4),
        ], [
            'status' => 'paid',
            'provider_reference' => 'cs_test_paid_1001',
        ]);

        $this->createBookingWithPayment($client, $hotel, [
            'travelers_count' => 3,
            'status' => 'pending_payment',
            'total_amount' => 3600,
        ], [
            'status' => 'pending',
        ]);

        $safariConfirmedBooking = $this->createBookingWithPayment($clientTwo, $safariTour, [
            'travelers_count' => 1,
            'status' => 'confirmed',
            'total_amount' => 98000,
            'paid_at' => now()->subDays(9),
        ], [
            'status' => 'paid',
            'provider_reference' => 'cs_test_paid_1002',
        ]);

        $transportCompletedBooking = $this->createBookingWithPayment($clientTwo, $transport, [
            'travelers_count' => 4,
            'status' => 'completed',
            'total_amount' => 18000,
            'paid_at' => now()->subDays(15),
        ], [
            'status' => 'paid',
            'provider_reference' => 'cs_test_paid_1003',
        ]);

        $this->createBookingWithPayment($client, $attraction, [
            'travelers_count' => 2,
            'status' => 'cancelled',
            'total_amount' => 12000,
        ], [
            'status' => 'failed',
            'provider_reference' => 'cs_test_failed_1004',
        ]);

        $this->createReview($accraPaidBooking, 5, 'Excellent tour pacing and professional local guide.');
        $this->createReview($safariConfirmedBooking, 4, 'Wildlife views were amazing and logistics were smooth.');
        $this->createReview($transportCompletedBooking, 5, 'Comfortable shuttle and on-time departures.');

        $this->syncListingRatingsFromReviews();
    }

    /**
     * Seed tenant members.
     */
    protected function seedTenantMembers(Tenant $tenant, array $members): void
    {
        foreach ($members as $member) {
            TenantMember::create([
                'tenant_id' => $tenant->id,
                'user_id' => $member['user']->id,
                'role' => $member['role'],
                'permissions' => [
                    'listings.manage' => true,
                    'bookings.manage' => true,
                    'profile.manage' => true,
                ],
                'is_active' => true,
            ]);
        }
    }

    /**
     * Seed supported marketplace currencies.
     */
    protected function seedCurrencies(): void
    {
        $currencies = [
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'rate_from_usd' => 1, 'is_default' => true],
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'rate_from_usd' => 0.92, 'is_default' => false],
            ['code' => 'GBP', 'name' => 'British Pound', 'symbol' => '£', 'rate_from_usd' => 0.79, 'is_default' => false],
            ['code' => 'JPY', 'name' => 'Japanese Yen', 'symbol' => '¥', 'rate_from_usd' => 149.00, 'is_default' => false],
            ['code' => 'CAD', 'name' => 'Canadian Dollar', 'symbol' => 'C$', 'rate_from_usd' => 1.35, 'is_default' => false],
            ['code' => 'AUD', 'name' => 'Australian Dollar', 'symbol' => 'A$', 'rate_from_usd' => 1.52, 'is_default' => false],
            ['code' => 'CHF', 'name' => 'Swiss Franc', 'symbol' => 'CHF ', 'rate_from_usd' => 0.88, 'is_default' => false],
            ['code' => 'CNY', 'name' => 'Chinese Yuan', 'symbol' => '¥', 'rate_from_usd' => 7.18, 'is_default' => false],
            ['code' => 'INR', 'name' => 'Indian Rupee', 'symbol' => '₹', 'rate_from_usd' => 83.10, 'is_default' => false],
            ['code' => 'AED', 'name' => 'UAE Dirham', 'symbol' => 'AED ', 'rate_from_usd' => 3.67, 'is_default' => false],
            ['code' => 'EGP', 'name' => 'Egyptian Pound', 'symbol' => 'E£', 'rate_from_usd' => 49.20, 'is_default' => false],
            ['code' => 'SAR', 'name' => 'Saudi Riyal', 'symbol' => 'SAR ', 'rate_from_usd' => 3.75, 'is_default' => false],
            ['code' => 'ZAR', 'name' => 'South African Rand', 'symbol' => 'R', 'rate_from_usd' => 18.40, 'is_default' => false],
            ['code' => 'NZD', 'name' => 'New Zealand Dollar', 'symbol' => 'NZ$', 'rate_from_usd' => 1.64, 'is_default' => false],
            ['code' => 'SEK', 'name' => 'Swedish Krona', 'symbol' => 'SEK ', 'rate_from_usd' => 10.40, 'is_default' => false],
            ['code' => 'NOK', 'name' => 'Norwegian Krone', 'symbol' => 'NOK ', 'rate_from_usd' => 10.70, 'is_default' => false],
            ['code' => 'DKK', 'name' => 'Danish Krone', 'symbol' => 'DKK ', 'rate_from_usd' => 6.85, 'is_default' => false],
            ['code' => 'SGD', 'name' => 'Singapore Dollar', 'symbol' => 'S$', 'rate_from_usd' => 1.35, 'is_default' => false],
            ['code' => 'HKD', 'name' => 'Hong Kong Dollar', 'symbol' => 'HK$', 'rate_from_usd' => 7.81, 'is_default' => false],
            ['code' => 'BRL', 'name' => 'Brazilian Real', 'symbol' => 'R$', 'rate_from_usd' => 5.10, 'is_default' => false],
            ['code' => 'MXN', 'name' => 'Mexican Peso', 'symbol' => 'MX$', 'rate_from_usd' => 17.10, 'is_default' => false],
            ['code' => 'GHS', 'name' => 'Ghanaian Cedi', 'symbol' => 'GHc ', 'rate_from_usd' => 15.20, 'is_default' => false],
            ['code' => 'KES', 'name' => 'Kenyan Shilling', 'symbol' => 'KSh', 'rate_from_usd' => 130.00, 'is_default' => false],
            ['code' => 'NGN', 'name' => 'Nigerian Naira', 'symbol' => 'NGN ', 'rate_from_usd' => 1580.00, 'is_default' => false],
        ];

        foreach ($currencies as $currency) {
            Currency::updateOrCreate(
                ['code' => $currency['code']],
                [
                    'name' => $currency['name'],
                    'symbol' => $currency['symbol'],
                    'rate_from_usd' => $currency['rate_from_usd'],
                    'is_default' => $currency['is_default'],
                    'is_active' => true,
                    'last_synced_at' => now(),
                ]
            );
        }
    }

    /**
     * Seed listing media gallery records.
     */
    protected function seedMedia(int $listingId, array $items): void
    {
        foreach ($items as $index => $item) {
            ListingMedia::create([
                'listing_id' => $listingId,
                'type' => 'image',
                'url' => $item['url'],
                'thumbnail_url' => $item['url'],
                'alt_text' => $item['caption'] ?? 'Listing image',
                'caption' => $item['caption'] ?? null,
                'sort_order' => $index + 1,
                'is_cover' => (bool) ($item['is_cover'] ?? false),
            ]);
        }
    }

    /**
     * Seed booking and payment data for dashboard analytics.
     */
    protected function createBookingWithPayment(User $client, Listing $listing, array $booking, array $payment): Booking
    {
        $bookingRecord = Booking::create([
            'booking_no' => 'THB-'.Str::upper(Str::random(8)),
            'user_id' => $client->id,
            'tenant_id' => $listing->tenant_id,
            'listing_id' => $listing->id,
            'travelers_count' => $booking['travelers_count'] ?? 1,
            'special_requests' => $booking['special_requests'] ?? null,
            'total_amount' => $booking['total_amount'] ?? $listing->price_from,
            'currency' => $booking['currency'] ?? strtoupper((string) ($listing->currency_code ?: 'USD')),
            'status' => $booking['status'] ?? 'pending_payment',
            'paid_at' => $booking['paid_at'] ?? null,
        ]);

        Payment::create([
            'booking_id' => $bookingRecord->id,
            'provider' => 'stripe',
            'amount' => $bookingRecord->total_amount,
            'currency' => $bookingRecord->currency,
            'status' => $payment['status'] ?? 'pending',
            'provider_reference' => $payment['provider_reference'] ?? null,
            'payload' => [
                'seeded' => true,
                'booking_no' => $bookingRecord->booking_no,
            ],
        ]);

        return $bookingRecord;
    }

    /**
     * Seed a customer review tied to a booking.
     */
    protected function createReview(Booking $booking, int $rating, string $comment): void
    {
        TenantReview::create([
            'booking_id' => $booking->id,
            'tenant_id' => $booking->tenant_id,
            'listing_id' => $booking->listing_id,
            'user_id' => $booking->user_id,
            'rating' => $rating,
            'comment' => $comment,
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);
    }

    /**
     * Keep listing aggregate ratings in sync with stored reviews.
     */
    protected function syncListingRatingsFromReviews(): void
    {
        Listing::query()->update([
            'rating_average' => 0,
            'rating_count' => 0,
        ]);

        $stats = TenantReview::query()
            ->selectRaw('listing_id, COUNT(*) as reviews_count, AVG(rating) as average_rating')
            ->groupBy('listing_id')
            ->get();

        foreach ($stats as $row) {
            Listing::query()
                ->whereKey($row->listing_id)
                ->update([
                    'rating_count' => (int) $row->reviews_count,
                    'rating_average' => round((float) $row->average_rating, 2),
                ]);
        }
    }
}

<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Listing;
use App\Models\TenantReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_homepage_renders_marketplace_sections(): void
    {
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('Reach unforgettable trips and trusted services across top destinations.')
            ->assertSee('Featured Tours')
            ->assertSee('Featured Utilities');
    }

    public function test_tours_page_can_be_rendered(): void
    {
        $response = $this->get('/tours');

        $response
            ->assertOk()
            ->assertSee('Browse Tours');
    }

    public function test_utilities_page_can_be_rendered(): void
    {
        $response = $this->get('/utilities');

        $response
            ->assertOk()
            ->assertSee('Browse Utilities');
    }

    public function test_tourist_attractions_index_page_can_be_rendered(): void
    {
        $response = $this->get('/tourist-attractions');

        $response
            ->assertOk()
            ->assertSee('Tourist Attractions by Region')
            ->assertSee('Greater Accra');
    }

    public function test_tourist_attractions_region_page_can_be_rendered(): void
    {
        $response = $this->get('/tourist-attractions/greater-accra');

        $response
            ->assertOk()
            ->assertSee('Greater Accra Region Attractions')
            ->assertSee('Kwame Nkrumah Memorial Park');
    }

    public function test_tourist_attraction_detail_page_can_be_rendered(): void
    {
        $response = $this->get('/tourist-attractions/greater-accra/kwame-nkrumah-memorial-park-greater-accra');

        $response
            ->assertOk()
            ->assertSee('Kwame Nkrumah Memorial Park')
            ->assertSee('Visitor Information')
            ->assertSee('Travel Tips');
    }

    public function test_listing_page_can_be_rendered_for_known_slug(): void
    {
        $response = $this->get('/listings/accra-cultural-weekend');

        $response
            ->assertOk()
            ->assertSee('Accra Cultural Weekend')
            ->assertSee('5.0/5 (1 review)')
            ->assertSee('Excellent tour pacing and professional local guide.')
            ->assertSee('View Tour Operator Details')
            ->assertDontSee('See more reviews');
    }

    public function test_listing_page_shows_see_more_reviews_when_reviews_exceed_five(): void
    {
        $listing = Listing::query()
            ->where('slug', 'accra-cultural-weekend')
            ->firstOrFail();

        $clientIds = User::query()
            ->where('user_role', User::ROLE_CLIENT)
            ->limit(5)
            ->pluck('id')
            ->values();

        if ($clientIds->count() < 5) {
            $extraClientIds = User::factory()
                ->count(5 - $clientIds->count())
                ->create(['user_role' => User::ROLE_CLIENT])
                ->pluck('id');

            $clientIds = $clientIds->merge($extraClientIds)->values();
        }

        foreach ($clientIds as $index => $clientId) {
            $booking = Booking::query()->create([
                'booking_no' => 'BKG-TST-'.uniqid().'-'.$index,
                'user_id' => $clientId,
                'tenant_id' => $listing->tenant_id,
                'listing_id' => $listing->id,
                'travelers_count' => 1,
                'total_amount' => (float) $listing->price_from,
                'currency' => (string) ($listing->currency_code ?: 'USD'),
                'status' => 'completed',
                'paid_at' => now()->subDays($index + 1),
            ]);

            TenantReview::query()->create([
                'booking_id' => $booking->id,
                'tenant_id' => $listing->tenant_id,
                'listing_id' => $listing->id,
                'user_id' => $clientId,
                'rating' => 5,
                'comment' => 'Extra seeded review '.($index + 1),
            ]);
        }

        $listing->update([
            'rating_count' => (int) $listing->reviews()->count(),
            'rating_average' => round((float) $listing->reviews()->avg('rating'), 2),
        ]);

        $response = $this->get('/listings/accra-cultural-weekend');

        $response
            ->assertOk()
            ->assertSee('See more reviews');
    }

    public function test_vendor_page_can_be_rendered_for_known_vendor_slug(): void
    {
        $response = $this->get('/vendors/sankofa-trails');

        $response
            ->assertOk()
            ->assertSee('Sankofa Trails Ltd')
            ->assertSee('About the Company')
            ->assertSee('4.5/5 (2 reviews)')
            ->assertSee('Customer Reviews');
    }

    public function test_listing_page_shows_zero_rating_when_no_reviews(): void
    {
        $response = $this->get('/listings/golden-coast-hotel');

        $response
            ->assertOk()
            ->assertSee('Golden Coast Hotel')
            ->assertSee('0.0/5 (0 reviews)')
            ->assertSee('No reviews yet for this listing.');
    }
}

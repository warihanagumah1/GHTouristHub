<?php

namespace Tests\Feature;

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

    public function test_listing_page_can_be_rendered_for_known_slug(): void
    {
        $response = $this->get('/listings/accra-cultural-weekend');

        $response
            ->assertOk()
            ->assertSee('Accra Cultural Weekend')
            ->assertSee('5.0/5 (1 review)')
            ->assertSee('Excellent tour pacing and professional local guide.')
            ->assertSee('View Owner Details');
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

<?php

namespace Tests\Feature\Admin;

use App\Models\Booking;
use App\Models\Listing;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_filtered_analytics_page(): void
    {
        [$admin, $tenant, $listing, $client] = $this->makeFixture();

        Booking::create([
            'booking_no' => 'THB-'.Str::upper(Str::random(8)),
            'user_id' => $client->id,
            'tenant_id' => $tenant->id,
            'listing_id' => $listing->id,
            'travelers_count' => 2,
            'special_requests' => null,
            'total_amount' => 2000,
            'currency' => 'USD',
            'status' => 'paid',
            'stripe_checkout_session_id' => 'cs_analytics',
            'paid_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.analytics', [
            'status' => 'paid',
            'type' => 'tour',
            'tenant_id' => $tenant->id,
        ]));

        $response->assertOk();
        $response->assertSee('Analytics Filters');
        $response->assertSee('Bookings Trend');
        $response->assertSee('Revenue Trend (USD)');
    }

    public function test_analytics_rejects_ranges_over_ninety_days(): void
    {
        [$admin] = $this->makeFixture();

        $response = $this->actingAs($admin)->from(route('admin.analytics'))->get(route('admin.analytics', [
            'date_from' => now()->subDays(120)->toDateString(),
            'date_to' => now()->toDateString(),
        ]));

        $response->assertRedirect(route('admin.analytics'));
        $response->assertSessionHasErrors('date_from');
    }

    /**
     * @return array{0: \App\Models\User, 1: \App\Models\Tenant, 2: \App\Models\Listing, 3: \App\Models\User}
     */
    private function makeFixture(): array
    {
        $admin = User::factory()->create([
            'user_role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        $owner = User::factory()->create([
            'user_role' => User::ROLE_TOUR_OWNER,
            'email_verified_at' => now(),
        ]);

        $client = User::factory()->create([
            'user_role' => User::ROLE_CLIENT,
            'email_verified_at' => now(),
        ]);

        $tenant = Tenant::create([
            'name' => 'Analytics Tenant',
            'slug' => 'analytics-tenant-'.Str::lower(Str::random(5)),
            'type' => 'tour_company',
            'status' => 'approved',
            'owner_user_id' => $owner->id,
        ]);

        $listing = Listing::create([
            'tenant_id' => $tenant->id,
            'type' => 'tour',
            'title' => 'Analytics Listing',
            'slug' => 'analytics-listing-'.Str::lower(Str::random(5)),
            'summary' => 'Summary text',
            'description' => 'Listing description that is long enough for tests.',
            'city' => 'Accra',
            'country' => 'Ghana',
            'address' => 'Accra',
            'price_from' => 1000,
            'currency_code' => 'USD',
            'pricing_unit' => 'per traveler',
            'status' => 'published',
        ]);

        return [$admin, $tenant, $listing, $client];
    }
}

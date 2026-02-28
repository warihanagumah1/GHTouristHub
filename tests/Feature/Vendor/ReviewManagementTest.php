<?php

namespace Tests\Feature\Vendor;

use App\Models\Booking;
use App\Models\Listing;
use App\Models\Tenant;
use App\Models\TenantReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ReviewManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_can_view_reviews_for_their_company(): void
    {
        [$vendor, $tenant] = $this->makeVendorTenant('Tour Reviews Co', User::ROLE_TOUR_OWNER);
        $client = $this->makeClient();

        $listing = $this->createListingForTenant($tenant, 'Accra City Discovery');
        $this->createReview($tenant, $listing, $client, 5, 'Outstanding tour experience.');

        $response = $this->actingAs($vendor)->get(route('vendor.reviews.index'));

        $response
            ->assertOk()
            ->assertSee('Customer Reviews')
            ->assertSee('Accra City Discovery')
            ->assertSee('Outstanding tour experience.')
            ->assertSee('5.0/5');
    }

    public function test_vendor_only_sees_reviews_for_their_tenant(): void
    {
        [$vendorOne, $tenantOne] = $this->makeVendorTenant('Tenant One', User::ROLE_TOUR_OWNER);
        [$vendorTwo, $tenantTwo] = $this->makeVendorTenant('Tenant Two', User::ROLE_UTILITY_OWNER);

        $clientOne = $this->makeClient();
        $clientTwo = $this->makeClient();

        $listingOne = $this->createListingForTenant($tenantOne, 'Own Tenant Listing');
        $this->createReview($tenantOne, $listingOne, $clientOne, 4, 'Visible only to tenant one.');

        $listingTwo = $this->createListingForTenant($tenantTwo, 'Other Tenant Listing');
        $this->createReview($tenantTwo, $listingTwo, $clientTwo, 5, 'Must not be visible to tenant one.');

        $response = $this->actingAs($vendorOne)->get(route('vendor.reviews.index'));

        $response
            ->assertOk()
            ->assertSee('Own Tenant Listing')
            ->assertSee('Visible only to tenant one.')
            ->assertDontSee('Other Tenant Listing')
            ->assertDontSee('Must not be visible to tenant one.');
    }

    public function test_client_cannot_access_vendor_reviews_page(): void
    {
        $client = $this->makeClient();

        $this->actingAs($client)
            ->get(route('vendor.reviews.index'))
            ->assertForbidden();
    }

    /**
     * @return array{0: \App\Models\User, 1: \App\Models\Tenant}
     */
    private function makeVendorTenant(string $tenantName, string $role): array
    {
        $vendor = User::factory()->create([
            'user_role' => $role,
            'email_verified_at' => now(),
        ]);

        $tenant = Tenant::create([
            'name' => $tenantName,
            'slug' => Str::slug($tenantName).'-'.Str::lower(Str::random(6)),
            'type' => str_contains($role, 'utility') ? 'utility_owner' : 'tour_company',
            'status' => 'approved',
            'owner_user_id' => $vendor->id,
        ]);

        return [$vendor, $tenant];
    }

    private function makeClient(): User
    {
        return User::factory()->create([
            'user_role' => User::ROLE_CLIENT,
            'email_verified_at' => now(),
        ]);
    }

    private function createListingForTenant(Tenant $tenant, string $title): Listing
    {
        return Listing::create([
            'tenant_id' => $tenant->id,
            'type' => 'tour',
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(5)),
            'summary' => 'Short summary',
            'description' => 'Description long enough for listing test data and display checks.',
            'city' => 'Accra',
            'country' => 'Ghana',
            'price_from' => 250,
            'currency_code' => 'USD',
            'pricing_unit' => 'per traveler',
            'status' => 'published',
        ]);
    }

    private function createReview(Tenant $tenant, Listing $listing, User $client, int $rating, string $comment): TenantReview
    {
        $booking = Booking::create([
            'booking_no' => 'BK-'.Str::upper(Str::random(8)),
            'user_id' => $client->id,
            'tenant_id' => $tenant->id,
            'listing_id' => $listing->id,
            'travelers_count' => 1,
            'total_amount' => 250,
            'currency' => 'USD',
            'status' => 'completed',
            'paid_at' => now(),
        ]);

        return TenantReview::create([
            'booking_id' => $booking->id,
            'tenant_id' => $tenant->id,
            'listing_id' => $listing->id,
            'user_id' => $client->id,
            'rating' => $rating,
            'comment' => $comment,
        ]);
    }
}

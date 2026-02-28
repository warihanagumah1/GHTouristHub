<?php

namespace Tests\Feature\Admin;

use App\Models\Listing;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ListingModerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_block_and_feature_listing(): void
    {
        [$admin, $listing] = $this->makeAdminAndListing();

        $statusResponse = $this->actingAs($admin)->put(route('admin.listings.status', $listing), [
            'status' => 'blocked',
        ]);

        $statusResponse->assertRedirect();
        $this->assertDatabaseHas('listings', [
            'id' => $listing->id,
            'status' => 'blocked',
        ]);

        $featureResponse = $this->actingAs($admin)->put(route('admin.listings.featured', $listing), [
            'is_featured' => 1,
        ]);

        $featureResponse->assertRedirect();
        $this->assertDatabaseHas('listings', [
            'id' => $listing->id,
            'is_featured' => 1,
        ]);
    }

    /**
     * @return array{0: \App\Models\User, 1: \App\Models\Listing}
     */
    private function makeAdminAndListing(): array
    {
        $admin = User::factory()->create([
            'user_role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        $owner = User::factory()->create([
            'user_role' => User::ROLE_TOUR_OWNER,
            'email_verified_at' => now(),
        ]);

        $tenant = Tenant::create([
            'name' => 'Moderation Tenant',
            'slug' => 'moderation-tenant-'.Str::lower(Str::random(5)),
            'type' => 'tour_company',
            'status' => 'approved',
            'owner_user_id' => $owner->id,
        ]);

        $listing = Listing::create([
            'tenant_id' => $tenant->id,
            'type' => 'tour',
            'title' => 'Moderation Listing',
            'slug' => 'moderation-listing-'.Str::lower(Str::random(5)),
            'summary' => 'Summary text',
            'description' => 'Listing description that is long enough for tests.',
            'city' => 'Accra',
            'country' => 'Ghana',
            'address' => 'Accra',
            'price_from' => 700,
            'currency_code' => 'USD',
            'pricing_unit' => 'per traveler',
            'status' => 'published',
            'is_featured' => false,
        ]);

        return [$admin, $listing];
    }
}

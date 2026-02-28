<?php

namespace Tests\Feature\Vendor;

use App\Models\Booking;
use App\Models\Currency;
use App\Models\Listing;
use App\Models\ListingMedia;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ListingManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_can_hide_listing(): void
    {
        [$vendor, $listing] = $this->makeVendorAndListing();

        $response = $this->actingAs($vendor)->put(route('vendor.listings.visibility', $listing), [
            'action' => 'hide',
        ]);

        $response->assertRedirect(route('vendor.listings.index'));
        $this->assertDatabaseHas('listings', [
            'id' => $listing->id,
            'status' => 'paused',
            'deleted_at' => null,
        ]);
    }

    public function test_vendor_soft_delete_requires_confirmation_when_open_bookings_exist(): void
    {
        [$vendor, $listing, $tenant] = $this->makeVendorAndListing();

        $client = User::factory()->create([
            'user_role' => User::ROLE_CLIENT,
            'email_verified_at' => now(),
        ]);

        Booking::create([
            'booking_no' => 'BK-'.Str::upper(Str::random(8)),
            'user_id' => $client->id,
            'tenant_id' => $tenant->id,
            'listing_id' => $listing->id,
            'travelers_count' => 2,
            'special_requests' => null,
            'total_amount' => 900,
            'currency' => 'USD',
            'status' => 'confirmed',
            'stripe_checkout_session_id' => null,
            'paid_at' => now(),
        ]);

        $firstAttempt = $this->actingAs($vendor)->delete(route('vendor.listings.destroy', $listing));
        $firstAttempt->assertRedirect(route('vendor.listings.index'));
        $firstAttempt->assertSessionHas('warning');
        $this->assertDatabaseHas('listings', [
            'id' => $listing->id,
            'deleted_at' => null,
        ]);

        $secondAttempt = $this->actingAs($vendor)->delete(route('vendor.listings.destroy', $listing), [
            'confirm_delete' => '1',
        ]);
        $secondAttempt->assertRedirect(route('vendor.listings.index'));
        $this->assertSoftDeleted('listings', ['id' => $listing->id]);
    }

    public function test_vendor_can_soft_delete_listing_without_open_bookings(): void
    {
        [$vendor, $listing] = $this->makeVendorAndListing();

        $response = $this->actingAs($vendor)->delete(route('vendor.listings.destroy', $listing));

        $response->assertRedirect(route('vendor.listings.index'));
        $this->assertSoftDeleted('listings', ['id' => $listing->id]);
    }

    public function test_unapproved_vendor_cannot_create_listing(): void
    {
        $this->ensureCurrency();

        $vendor = User::factory()->create([
            'user_role' => User::ROLE_TOUR_OWNER,
            'email_verified_at' => now(),
        ]);

        $tenant = Tenant::create([
            'name' => 'Pending Tenant',
            'slug' => 'pending-tenant-'.Str::lower(Str::random(5)),
            'type' => 'tour_company',
            'status' => 'pending',
            'owner_user_id' => $vendor->id,
        ]);

        $this->actingAs($vendor)
            ->get(route('vendor.listings.create'))
            ->assertForbidden();

        $this->actingAs($vendor)
            ->post(route('vendor.listings.store'), [
                'title' => 'Should Not Create',
                'type' => 'tour',
                'description' => 'This description is long enough to satisfy minimum validation constraints.',
                'price_from' => 100,
                'currency_code' => 'USD',
                'status' => 'draft',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('listings', [
            'tenant_id' => $tenant->id,
            'title' => 'Should Not Create',
        ]);
    }

    public function test_vendor_can_create_listing_with_uploaded_images(): void
    {
        Storage::fake('public');
        $this->ensureCurrency();
        [$vendor, , $tenant] = $this->makeVendorAndListing();

        $response = $this->actingAs($vendor)->post(route('vendor.listings.store'), [
            'title' => 'New Safari Adventure',
            'type' => 'tour',
            'subtype' => null,
            'summary' => 'A compact safari package.',
            'description' => 'Guided safari experience with transport, meals, and local expert support for every traveler.',
            'city' => 'Nairobi',
            'country' => 'Kenya',
            'address' => 'Nairobi National Park Gate',
            'price_from' => 250,
            'currency_code' => 'USD',
            'pricing_unit' => 'per traveler',
            'duration_label' => '2 days',
            'group_size_label' => '2-8 travelers',
            'status' => 'draft',
            'booking_rules' => 'Passport required.',
            'cancellation_policy' => 'Cancel 48 hours before departure for full refund.',
            'images' => [
                UploadedFile::fake()->image('cover.jpg', 1200, 900)->size(512),
                UploadedFile::fake()->image('gallery.webp', 1280, 960)->size(700),
            ],
        ]);

        $response->assertRedirect(route('vendor.listings.index'));

        $listing = Listing::query()
            ->where('tenant_id', $tenant->id)
            ->where('title', 'New Safari Adventure')
            ->firstOrFail();

        $media = $listing->media()->orderBy('sort_order')->get();
        $this->assertCount(2, $media);
        $this->assertTrue((bool) $media->first()->is_cover);

        foreach ($media as $item) {
            $path = ltrim(Str::after((string) parse_url((string) $item->url, PHP_URL_PATH), '/storage/'), '/');
            Storage::disk('public')->assertExists($path);
        }
    }

    public function test_vendor_update_without_new_images_keeps_existing_gallery(): void
    {
        $this->ensureCurrency();
        [$vendor, $listing] = $this->makeVendorAndListing();

        ListingMedia::create([
            'listing_id' => $listing->id,
            'type' => 'image',
            'url' => 'https://example.com/existing-image.jpg',
            'thumbnail_url' => 'https://example.com/existing-image.jpg',
            'alt_text' => $listing->title,
            'caption' => 'Existing image',
            'sort_order' => 1,
            'is_cover' => true,
        ]);

        $response = $this->actingAs($vendor)->put(route('vendor.listings.update', $listing), [
            'title' => 'Sample Listing Updated',
            'type' => 'tour',
            'subtype' => null,
            'summary' => 'Updated summary',
            'description' => 'Updated description that remains long enough to pass validation requirements safely.',
            'city' => 'Accra',
            'country' => 'Ghana',
            'address' => 'Accra, Ghana',
            'price_from' => 1400,
            'currency_code' => 'USD',
            'pricing_unit' => 'per traveler',
            'duration_label' => '2 days',
            'group_size_label' => '2-6 travelers',
            'status' => 'published',
            'booking_rules' => 'Bring valid ID.',
            'cancellation_policy' => '24 hours notice required.',
        ]);

        $response->assertRedirect(route('vendor.listings.index'));

        $listing->refresh();
        $media = $listing->media()->get();
        $this->assertCount(1, $media);
        $this->assertSame('https://example.com/existing-image.jpg', $media->first()->url);
    }

    public function test_vendor_can_remove_and_add_gallery_images_in_single_update(): void
    {
        Storage::fake('public');
        $this->ensureCurrency();
        [$vendor, $listing] = $this->makeVendorAndListing();

        $firstMedia = ListingMedia::create([
            'listing_id' => $listing->id,
            'type' => 'image',
            'url' => '/storage/listings/'.$listing->id.'/first.jpg',
            'thumbnail_url' => '/storage/listings/'.$listing->id.'/first.jpg',
            'alt_text' => $listing->title,
            'caption' => 'First image',
            'sort_order' => 1,
            'is_cover' => true,
        ]);

        ListingMedia::create([
            'listing_id' => $listing->id,
            'type' => 'image',
            'url' => '/storage/listings/'.$listing->id.'/second.jpg',
            'thumbnail_url' => '/storage/listings/'.$listing->id.'/second.jpg',
            'alt_text' => $listing->title,
            'caption' => 'Second image',
            'sort_order' => 2,
            'is_cover' => false,
        ]);

        $response = $this->actingAs($vendor)->put(route('vendor.listings.update', $listing), [
            'title' => 'Sample Listing Gallery Update',
            'type' => 'tour',
            'subtype' => null,
            'summary' => 'Updated summary',
            'description' => 'Updated description that remains long enough to pass validation requirements safely.',
            'city' => 'Accra',
            'country' => 'Ghana',
            'address' => 'Accra, Ghana',
            'price_from' => 1400,
            'currency_code' => 'USD',
            'pricing_unit' => 'per traveler',
            'duration_label' => '2 days',
            'group_size_label' => '2-6 travelers',
            'status' => 'published',
            'booking_rules' => 'Bring valid ID.',
            'cancellation_policy' => '24 hours notice required.',
            'remove_media_ids' => [$firstMedia->id],
            'images' => [
                UploadedFile::fake()->image('new-image.jpg', 1200, 900)->size(512),
            ],
        ]);

        $response->assertRedirect(route('vendor.listings.index'));

        $media = $listing->fresh()->media()->orderBy('sort_order')->get();
        $this->assertCount(2, $media);
        $this->assertFalse($media->pluck('id')->contains($firstMedia->id));
        $this->assertStringStartsWith('/storage/listings/'.$listing->id.'/', (string) $media->last()->url);
    }

    public function test_vendor_can_choose_cover_image_from_existing_gallery(): void
    {
        $this->ensureCurrency();
        [$vendor, $listing] = $this->makeVendorAndListing();

        $firstMedia = ListingMedia::create([
            'listing_id' => $listing->id,
            'type' => 'image',
            'url' => '/storage/listings/'.$listing->id.'/first.jpg',
            'thumbnail_url' => '/storage/listings/'.$listing->id.'/first.jpg',
            'alt_text' => $listing->title,
            'caption' => 'First image',
            'sort_order' => 1,
            'is_cover' => true,
        ]);

        $secondMedia = ListingMedia::create([
            'listing_id' => $listing->id,
            'type' => 'image',
            'url' => '/storage/listings/'.$listing->id.'/second.jpg',
            'thumbnail_url' => '/storage/listings/'.$listing->id.'/second.jpg',
            'alt_text' => $listing->title,
            'caption' => 'Second image',
            'sort_order' => 2,
            'is_cover' => false,
        ]);

        $response = $this->actingAs($vendor)->put(route('vendor.listings.update', $listing), [
            'title' => 'Sample Listing Cover Update',
            'type' => 'tour',
            'subtype' => null,
            'summary' => 'Updated summary',
            'description' => 'Updated description that remains long enough to pass validation requirements safely.',
            'city' => 'Accra',
            'country' => 'Ghana',
            'address' => 'Accra, Ghana',
            'price_from' => 1400,
            'currency_code' => 'USD',
            'pricing_unit' => 'per traveler',
            'duration_label' => '2 days',
            'group_size_label' => '2-6 travelers',
            'status' => 'published',
            'booking_rules' => 'Bring valid ID.',
            'cancellation_policy' => '24 hours notice required.',
            'cover_media_id' => $secondMedia->id,
        ]);

        $response->assertRedirect(route('vendor.listings.index'));

        $this->assertDatabaseHas('listing_media', [
            'id' => $firstMedia->id,
            'is_cover' => false,
        ]);
        $this->assertDatabaseHas('listing_media', [
            'id' => $secondMedia->id,
            'is_cover' => true,
        ]);
    }

    /**
     * @return array{0: \App\Models\User, 1: \App\Models\Listing, 2: \App\Models\Tenant}
     */
    private function makeVendorAndListing(): array
    {
        $vendor = User::factory()->create([
            'user_role' => User::ROLE_TOUR_OWNER,
            'email_verified_at' => now(),
        ]);

        $tenant = Tenant::create([
            'name' => 'Test Tenant',
            'slug' => 'test-tenant-'.Str::lower(Str::random(5)),
            'type' => 'tour_company',
            'status' => 'approved',
            'owner_user_id' => $vendor->id,
        ]);

        $listing = Listing::create([
            'tenant_id' => $tenant->id,
            'type' => 'tour',
            'subtype' => null,
            'title' => 'Sample Listing',
            'slug' => 'sample-listing-'.Str::lower(Str::random(5)),
            'summary' => 'Sample summary',
            'description' => 'Sample listing description long enough to pass validation checks.',
            'city' => 'Accra',
            'country' => 'Ghana',
            'address' => 'Accra, Ghana',
            'price_from' => 1200,
            'currency_code' => 'USD',
            'pricing_unit' => 'per traveler',
            'status' => 'published',
        ]);

        return [$vendor, $listing, $tenant];
    }

    private function ensureCurrency(): void
    {
        Currency::updateOrCreate(
            ['code' => 'USD'],
            [
                'name' => 'US Dollar',
                'symbol' => '$',
                'rate_from_usd' => 1,
                'is_default' => true,
                'is_active' => true,
            ]
        );
    }
}

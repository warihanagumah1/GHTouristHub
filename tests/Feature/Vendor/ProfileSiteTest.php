<?php

namespace Tests\Feature\Vendor;

use App\Models\Tenant;
use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProfileSiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_can_upload_logo_and_banner_images_for_mini_website(): void
    {
        Storage::fake('public');
        [$vendor, $tenant] = $this->makeVendorWithTenant();

        $response = $this->actingAs($vendor)->put(route('vendor.site-profile.update'), [
            'tenant_name' => 'Sankofa Trails Updated',
            'city' => 'Accra',
            'country' => 'Ghana',
            'support_email' => 'support@example.test',
            'support_phone' => '+233200000000',
            'website_url' => 'https://example.test',
            'founded_year' => 2018,
            'about' => 'A trusted travel company profile.',
            'logo_image' => UploadedFile::fake()->image('logo.jpg', 400, 400)->size(512),
            'banner_image' => UploadedFile::fake()->image('banner.jpg', 1400, 500)->size(1024),
            'payout_mode' => 'platform_payouts',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $profile = VendorProfile::query()->where('tenant_id', $tenant->id)->firstOrFail();
        $this->assertStringStartsWith('/storage/vendor-profiles/'.$tenant->id.'/logo/', (string) $profile->logo_url);
        $this->assertStringStartsWith('/storage/vendor-profiles/'.$tenant->id.'/banner/', (string) $profile->banner_url);

        $logoPath = ltrim(Str::after((string) parse_url((string) $profile->logo_url, PHP_URL_PATH), '/storage/'), '/');
        $bannerPath = ltrim(Str::after((string) parse_url((string) $profile->banner_url, PHP_URL_PATH), '/storage/'), '/');
        Storage::disk('public')->assertExists($logoPath);
        Storage::disk('public')->assertExists($bannerPath);
    }

    public function test_existing_profile_images_are_kept_when_no_new_upload_is_sent(): void
    {
        [$vendor, $tenant] = $this->makeVendorWithTenant();

        VendorProfile::create([
            'tenant_id' => $tenant->id,
            'logo_url' => '/storage/vendor-profiles/'.$tenant->id.'/logo/existing-logo.jpg',
            'banner_url' => '/storage/vendor-profiles/'.$tenant->id.'/banner/existing-banner.jpg',
            'payout_mode' => 'platform_payouts',
        ]);

        $response = $this->actingAs($vendor)->put(route('vendor.site-profile.update'), [
            'tenant_name' => 'Sankofa Trails Updated',
            'city' => 'Accra',
            'country' => 'Ghana',
            'support_email' => 'support@example.test',
            'support_phone' => '+233200000000',
            'website_url' => 'https://example.test',
            'founded_year' => 2018,
            'about' => 'A trusted travel company profile.',
            'payout_mode' => 'platform_payouts',
        ]);

        $response->assertRedirect();

        $profile = VendorProfile::query()->where('tenant_id', $tenant->id)->firstOrFail();
        $this->assertSame('/storage/vendor-profiles/'.$tenant->id.'/logo/existing-logo.jpg', $profile->logo_url);
        $this->assertSame('/storage/vendor-profiles/'.$tenant->id.'/banner/existing-banner.jpg', $profile->banner_url);
    }

    /**
     * @return array{0: \App\Models\User, 1: \App\Models\Tenant}
     */
    private function makeVendorWithTenant(): array
    {
        $vendor = User::factory()->create([
            'user_role' => User::ROLE_TOUR_OWNER,
            'email_verified_at' => now(),
        ]);

        $tenant = Tenant::create([
            'name' => 'Sankofa Trails',
            'slug' => 'sankofa-trails-'.Str::lower(Str::random(5)),
            'type' => 'tour_company',
            'status' => 'approved',
            'owner_user_id' => $vendor->id,
        ]);

        return [$vendor, $tenant];
    }
}

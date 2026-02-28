<?php

namespace Tests\Feature\Vendor;

use App\Models\Booking;
use App\Models\Listing;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\PayoutRequestSubmittedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class PayoutRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_can_request_payout_within_available_balance(): void
    {
        Notification::fake();

        [$vendor, $tenant] = $this->makeVendorWithPaidEarnings(900);
        $admin = User::factory()->create([
            'user_role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($vendor)->post(route('vendor.payouts.store'), [
            'amount' => 300,
            'note' => 'Weekly settlement',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payout_requests', [
            'tenant_id' => $tenant->id,
            'requested_by_user_id' => $vendor->id,
            'amount' => 300,
            'status' => 'pending',
        ]);

        Notification::assertSentTo($vendor, PayoutRequestSubmittedNotification::class);
        Notification::assertSentTo($admin, PayoutRequestSubmittedNotification::class);
    }

    public function test_vendor_cannot_request_more_than_available_balance(): void
    {
        [$vendor] = $this->makeVendorWithPaidEarnings(250);

        $response = $this->actingAs($vendor)->from(route('vendor.payouts.index'))->post(route('vendor.payouts.store'), [
            'amount' => 500,
        ]);

        $response->assertRedirect(route('vendor.payouts.index'));
        $response->assertSessionHasErrors('amount');
        $this->assertDatabaseCount('payout_requests', 0);
    }

    public function test_pending_requests_reduce_available_balance_for_new_requests(): void
    {
        [$vendor] = $this->makeVendorWithPaidEarnings(400);

        $first = $this->actingAs($vendor)->post(route('vendor.payouts.store'), [
            'amount' => 300,
            'note' => 'First request',
        ]);
        $first->assertRedirect();

        $second = $this->actingAs($vendor)->from(route('vendor.payouts.index'))->post(route('vendor.payouts.store'), [
            'amount' => 150,
            'note' => 'Second request',
        ]);

        $second->assertRedirect(route('vendor.payouts.index'));
        $second->assertSessionHasErrors('amount');
        $this->assertDatabaseCount('payout_requests', 1);
    }

    /**
     * @return array{0: \App\Models\User, 1: \App\Models\Tenant}
     */
    private function makeVendorWithPaidEarnings(float $vendorNetAmount): array
    {
        $vendor = User::factory()->create([
            'user_role' => User::ROLE_TOUR_OWNER,
            'email_verified_at' => now(),
        ]);

        $client = User::factory()->create([
            'user_role' => User::ROLE_CLIENT,
            'email_verified_at' => now(),
        ]);

        $tenant = Tenant::create([
            'name' => 'Payout Tenant',
            'slug' => 'payout-tenant-'.Str::lower(Str::random(5)),
            'type' => 'tour_company',
            'status' => 'approved',
            'owner_user_id' => $vendor->id,
        ]);

        $listing = Listing::create([
            'tenant_id' => $tenant->id,
            'type' => 'tour',
            'title' => 'Payout Listing',
            'slug' => 'payout-listing-'.Str::lower(Str::random(5)),
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

        $booking = Booking::create([
            'booking_no' => 'THB-'.Str::upper(Str::random(8)),
            'user_id' => $client->id,
            'tenant_id' => $tenant->id,
            'listing_id' => $listing->id,
            'travelers_count' => 1,
            'special_requests' => null,
            'total_amount' => 1000,
            'currency' => 'USD',
            'status' => 'paid',
            'stripe_checkout_session_id' => 'cs_test',
            'paid_at' => now(),
        ]);

        Payment::create([
            'booking_id' => $booking->id,
            'provider' => 'stripe',
            'amount' => 1000,
            'currency' => 'USD',
            'commission_amount' => 100,
            'vendor_net_amount' => $vendorNetAmount,
            'transfer_mode' => 'platform',
            'status' => 'paid',
            'provider_reference' => 'pi_test',
        ]);

        return [$vendor, $tenant];
    }
}

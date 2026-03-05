<?php

namespace Tests\Feature\Client;

use App\Models\Booking;
use App\Models\Listing;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class StripeCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_shows_stripe_error_message_when_session_creation_fails(): void
    {
        [$client, $booking] = $this->makePendingBooking();
        config(['services.stripe.secret' => 'sk_test_123']);

        Http::fake([
            'https://api.stripe.com/v1/checkout/sessions' => Http::response([
                'error' => [
                    'message' => 'Invalid API Key provided',
                    'code' => 'api_key_invalid',
                ],
            ], 401),
        ]);

        $response = $this->actingAs($client)->post(route('client.bookings.checkout', $booking));

        $response->assertRedirect(route('client.bookings.show', $booking));
        $response->assertSessionHasErrors([
            'payment' => 'Stripe error: Invalid API Key provided',
        ]);
    }

    public function test_checkout_fails_fast_for_zero_amount_without_calling_stripe(): void
    {
        [$client, $booking] = $this->makePendingBooking([
            'total_amount' => 0,
        ]);
        config(['services.stripe.secret' => 'sk_test_123']);

        Http::fake();

        $response = $this->actingAs($client)->post(route('client.bookings.checkout', $booking));

        $response->assertRedirect(route('client.bookings.show', $booking));
        $response->assertSessionHasErrors([
            'payment' => 'Invalid booking amount. Please contact support.',
        ]);
        Http::assertNothingSent();
    }

    /**
     * @param  array<string, mixed>  $bookingOverrides
     * @return array{0: \App\Models\User, 1: \App\Models\Booking}
     */
    private function makePendingBooking(array $bookingOverrides = []): array
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
            'name' => 'Stripe Test Company',
            'slug' => 'stripe-test-company-'.Str::lower(Str::random(6)),
            'type' => 'tour_company',
            'status' => 'approved',
            'owner_user_id' => $vendor->id,
        ]);

        $listing = Listing::create([
            'tenant_id' => $tenant->id,
            'type' => 'tour',
            'title' => 'Stripe Test Listing',
            'slug' => 'stripe-test-listing-'.Str::lower(Str::random(6)),
            'summary' => 'Sample summary for Stripe checkout test.',
            'description' => 'Sample listing description long enough to satisfy listing constraints for booking and payment tests.',
            'city' => 'Accra',
            'country' => 'Ghana',
            'price_from' => 250,
            'currency_code' => 'USD',
            'pricing_unit' => 'per traveler',
            'status' => 'published',
        ]);

        $booking = Booking::create(array_merge([
            'booking_no' => 'BK-'.Str::upper(Str::random(8)),
            'user_id' => $client->id,
            'tenant_id' => $tenant->id,
            'listing_id' => $listing->id,
            'travelers_count' => 1,
            'special_requests' => null,
            'total_amount' => 250,
            'currency' => 'USD',
            'status' => 'pending_payment',
        ], $bookingOverrides));

        Payment::create([
            'booking_id' => $booking->id,
            'provider' => 'stripe',
            'amount' => $booking->total_amount,
            'currency' => $booking->currency,
            'commission_amount' => 25,
            'vendor_net_amount' => 225,
            'transfer_mode' => 'platform',
            'status' => 'pending',
        ]);

        return [$client, $booking];
    }
}

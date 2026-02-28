<?php

namespace Tests\Feature\Client;

use App\Models\Booking;
use App\Models\Listing;
use App\Models\Tenant;
use App\Models\TenantReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BookingReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_submit_review_for_confirmed_booking(): void
    {
        [$client, $booking, $listing] = $this->makeClientBooking('confirmed', 10, 4.50);

        $response = $this->actingAs($client)->post(route('client.bookings.review.store', $booking), [
            'rating' => 4,
            'comment' => 'Great experience and professional service.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('tenant_reviews', [
            'booking_id' => $booking->id,
            'tenant_id' => $booking->tenant_id,
            'listing_id' => $booking->listing_id,
            'user_id' => $client->id,
            'rating' => 4,
        ]);

        $listing->refresh();
        $this->assertSame(11, (int) $listing->rating_count);
        $this->assertEqualsWithDelta(4.45, (float) $listing->rating_average, 0.001);
    }

    public function test_client_cannot_submit_review_for_pending_payment_booking(): void
    {
        [$client, $booking] = $this->makeClientBooking('pending_payment');

        $this->actingAs($client)->post(route('client.bookings.review.store', $booking), [
            'rating' => 5,
            'comment' => 'Trying too early.',
        ])->assertForbidden();

        $this->assertDatabaseMissing('tenant_reviews', [
            'booking_id' => $booking->id,
        ]);
    }

    public function test_client_can_update_existing_review_for_booking(): void
    {
        [$client, $booking, $listing] = $this->makeClientBooking('completed', 5, 4.20);

        TenantReview::create([
            'booking_id' => $booking->id,
            'tenant_id' => $booking->tenant_id,
            'listing_id' => $booking->listing_id,
            'user_id' => $client->id,
            'rating' => 3,
            'comment' => 'Initial review',
        ]);

        $response = $this->actingAs($client)->post(route('client.bookings.review.store', $booking), [
            'rating' => 5,
            'comment' => 'Updated review after full trip.',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('tenant_reviews', [
            'booking_id' => $booking->id,
            'rating' => 5,
            'comment' => 'Updated review after full trip.',
        ]);
        $this->assertSame(1, TenantReview::query()->where('booking_id', $booking->id)->count());

        $listing->refresh();
        $this->assertSame(5, (int) $listing->rating_count);
        $this->assertEqualsWithDelta(4.60, (float) $listing->rating_average, 0.001);
    }

    /**
     * @return array{0: \App\Models\User, 1: \App\Models\Booking, 2: \App\Models\Listing}
     */
    private function makeClientBooking(string $status, int $ratingCount = 0, float $ratingAverage = 0): array
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
            'name' => 'Reviewable Company',
            'slug' => 'reviewable-company-'.Str::lower(Str::random(6)),
            'type' => 'tour_company',
            'status' => 'approved',
            'owner_user_id' => $vendor->id,
        ]);

        $listing = Listing::create([
            'tenant_id' => $tenant->id,
            'type' => 'tour',
            'title' => 'Reviewable Listing',
            'slug' => 'reviewable-listing-'.Str::lower(Str::random(6)),
            'summary' => 'Sample summary',
            'description' => 'Sample description long enough for validation and booking flow checks.',
            'city' => 'Accra',
            'country' => 'Ghana',
            'price_from' => 250,
            'currency_code' => 'USD',
            'pricing_unit' => 'per traveler',
            'status' => 'published',
            'rating_count' => $ratingCount,
            'rating_average' => $ratingAverage,
        ]);

        $booking = Booking::create([
            'booking_no' => 'BK-'.Str::upper(Str::random(8)),
            'user_id' => $client->id,
            'tenant_id' => $tenant->id,
            'listing_id' => $listing->id,
            'travelers_count' => 2,
            'special_requests' => null,
            'total_amount' => 500,
            'currency' => 'USD',
            'status' => $status,
            'stripe_checkout_session_id' => null,
            'paid_at' => $status === 'pending_payment' ? null : now(),
        ]);

        return [$client, $booking, $listing];
    }
}

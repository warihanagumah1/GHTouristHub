<?php

namespace Tests\Feature\Client;

use App\Models\Booking;
use App\Models\Listing;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\BookingCreatedNotification;
use App\Notifications\BookingInvoiceNotification;
use App\Notifications\BookingMessageReceivedNotification;
use App\Notifications\BookingStatusUpdatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

class BookingNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_creation_notifies_client_and_vendor(): void
    {
        Notification::fake();
        [$client, $vendor, $listing] = $this->makeClientVendorAndListing();

        $response = $this->actingAs($client)->post(route('client.bookings.store', $listing), [
            'travelers_count' => 2,
            'special_requests' => 'Please confirm pickup point.',
        ]);

        $response->assertRedirect();
        Notification::assertSentTo($client, BookingCreatedNotification::class);
        Notification::assertSentTo($vendor, BookingCreatedNotification::class);
    }

    public function test_vendor_confirming_booking_notifies_client(): void
    {
        Notification::fake();
        [$client, $vendor, $listing] = $this->makeClientVendorAndListing();
        $booking = $this->makeBooking($client, $listing, 'paid');

        $response = $this->actingAs($vendor)->put(route('vendor.bookings.status', $booking), [
            'status' => 'confirmed',
        ]);

        $response->assertRedirect();
        Notification::assertSentTo($client, BookingStatusUpdatedNotification::class);
    }

    public function test_vendor_cannot_revert_paid_booking_to_pending_payment(): void
    {
        Notification::fake();
        [$client, $vendor, $listing] = $this->makeClientVendorAndListing();
        $booking = $this->makeBooking($client, $listing, 'paid');

        $response = $this->actingAs($vendor)
            ->from(route('vendor.bookings.index'))
            ->put(route('vendor.bookings.status', $booking), [
                'status' => 'pending_payment',
            ]);

        $response->assertRedirect(route('vendor.bookings.index'));
        $response->assertSessionHasErrors('status');
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'paid',
        ]);
    }

    public function test_client_message_notifies_vendor_and_vendor_message_notifies_client(): void
    {
        Notification::fake();
        [$client, $vendor, $listing] = $this->makeClientVendorAndListing();
        $booking = $this->makeBooking($client, $listing, 'confirmed');

        $clientMessageResponse = $this->actingAs($client)->post(route('client.bookings.messages.store', $booking), [
            'message' => 'Hello, what time should we arrive?',
        ]);
        $clientMessageResponse->assertRedirect();

        Notification::assertSentTo($vendor, BookingMessageReceivedNotification::class);

        Notification::fake();

        $vendorMessageResponse = $this->actingAs($vendor)->post(route('vendor.bookings.messages.store', $booking), [
            'message' => 'Please arrive by 8:30 AM.',
        ]);
        $vendorMessageResponse->assertRedirect();

        Notification::assertSentTo($client, BookingMessageReceivedNotification::class);
    }

    public function test_vendor_can_email_invoice_to_client(): void
    {
        Notification::fake();
        [$client, $vendor, $listing] = $this->makeClientVendorAndListing();
        $booking = $this->makeBooking($client, $listing, 'paid');

        $response = $this->actingAs($vendor)->post(route('vendor.bookings.invoice.email', $booking));
        $response->assertRedirect();
        $response->assertSessionHas('status');

        Notification::assertSentTo($client, BookingInvoiceNotification::class);
    }

    public function test_signed_public_invoice_link_can_be_opened_from_email(): void
    {
        [$client, $vendor, $listing] = $this->makeClientVendorAndListing();
        $booking = $this->makeBooking($client, $listing, 'paid');

        $validUrl = URL::temporarySignedRoute(
            'bookings.invoice.public',
            now()->addDay(),
            [
                'booking' => $booking,
                'recipient' => sha1(strtolower(trim((string) $client->email))),
            ]
        );

        $this->get($validUrl)->assertOk()->assertSee('Invoice');

        $invalidRecipientUrl = URL::temporarySignedRoute(
            'bookings.invoice.public',
            now()->addDay(),
            [
                'booking' => $booking,
                'recipient' => sha1('wrong@example.com'),
            ]
        );

        $this->get($invalidRecipientUrl)->assertForbidden();
    }

    /**
     * @return array{0: \App\Models\User, 1: \App\Models\User, 2: \App\Models\Listing}
     */
    protected function makeClientVendorAndListing(): array
    {
        $client = User::factory()->create([
            'user_role' => User::ROLE_CLIENT,
            'email_verified_at' => now(),
        ]);

        $vendor = User::factory()->create([
            'user_role' => User::ROLE_TOUR_OWNER,
            'email_verified_at' => now(),
        ]);

        $tenant = Tenant::create([
            'name' => 'Booking Test Vendor',
            'slug' => 'booking-test-vendor-'.Str::lower(Str::random(5)),
            'type' => 'tour_company',
            'status' => 'approved',
            'owner_user_id' => $vendor->id,
        ]);

        $listing = Listing::create([
            'tenant_id' => $tenant->id,
            'type' => 'tour',
            'title' => 'Savannah Explorer',
            'slug' => 'savannah-explorer-'.Str::lower(Str::random(5)),
            'summary' => 'Guided wildlife experience.',
            'description' => 'A complete tour package with transfers, guide, and curated destination stops.',
            'city' => 'Accra',
            'country' => 'Ghana',
            'address' => 'Accra, Ghana',
            'price_from' => 250,
            'currency_code' => 'USD',
            'pricing_unit' => 'per traveler',
            'status' => 'published',
        ]);

        return [$client, $vendor, $listing];
    }

    protected function makeBooking(User $client, Listing $listing, string $status): Booking
    {
        return Booking::create([
            'booking_no' => 'THB-'.Str::upper(Str::random(8)),
            'user_id' => $client->id,
            'tenant_id' => $listing->tenant_id,
            'listing_id' => $listing->id,
            'travelers_count' => 2,
            'special_requests' => null,
            'total_amount' => 500,
            'currency' => 'USD',
            'status' => $status,
            'stripe_checkout_session_id' => null,
            'paid_at' => in_array($status, ['paid', 'confirmed', 'completed'], true) ? now() : null,
        ]);
    }
}

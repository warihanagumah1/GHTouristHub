<?php

namespace Tests\Feature\Client;

use App\Models\Booking;
use App\Models\Listing;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\BookingReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class BookingReminderCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_reminder_command_sends_pending_payment_and_upcoming_service_notifications(): void
    {
        Notification::fake();

        [$clientOne, $clientTwo, $listing, $tenant] = $this->makeFixture();

        $pendingBooking = Booking::create([
            'booking_no' => 'THB-'.Str::upper(Str::random(8)),
            'user_id' => $clientOne->id,
            'tenant_id' => $tenant->id,
            'listing_id' => $listing->id,
            'travelers_count' => 1,
            'total_amount' => 250,
            'currency' => 'USD',
            'status' => 'pending_payment',
        ]);
        $pendingBooking->forceFill([
            'created_at' => now()->subHours(7),
            'updated_at' => now()->subHours(7),
        ])->saveQuietly();

        $upcomingBooking = Booking::create([
            'booking_no' => 'THB-'.Str::upper(Str::random(8)),
            'user_id' => $clientTwo->id,
            'tenant_id' => $tenant->id,
            'listing_id' => $listing->id,
            'travelers_count' => 2,
            'total_amount' => 500,
            'currency' => 'USD',
            'status' => 'paid',
            'service_date' => now()->addDay()->toDateString(),
            'paid_at' => now()->subHour(),
        ]);

        $this->artisan('bookings:send-reminders')->assertExitCode(0);

        Notification::assertSentTo($clientOne, BookingReminderNotification::class, function (BookingReminderNotification $notification) use ($pendingBooking): bool {
            return $notification->type === BookingReminderNotification::TYPE_PENDING_PAYMENT
                && $notification->booking->is($pendingBooking);
        });

        Notification::assertSentTo($clientTwo, BookingReminderNotification::class, function (BookingReminderNotification $notification) use ($upcomingBooking): bool {
            return $notification->type === BookingReminderNotification::TYPE_UPCOMING_SERVICE
                && $notification->booking->is($upcomingBooking);
        });

        $this->assertNotNull($pendingBooking->fresh()->pending_payment_reminded_at);
        $this->assertNotNull($upcomingBooking->fresh()->upcoming_service_reminded_at);
    }

    /**
     * @return array{0: \App\Models\User, 1: \App\Models\User, 2: \App\Models\Listing, 3: \App\Models\Tenant}
     */
    private function makeFixture(): array
    {
        $vendor = User::factory()->create([
            'user_role' => User::ROLE_TOUR_OWNER,
            'email_verified_at' => now(),
        ]);

        $clientOne = User::factory()->create([
            'user_role' => User::ROLE_CLIENT,
            'email_verified_at' => now(),
        ]);

        $clientTwo = User::factory()->create([
            'user_role' => User::ROLE_CLIENT,
            'email_verified_at' => now(),
        ]);

        $tenant = Tenant::create([
            'name' => 'Reminder Tenant',
            'slug' => 'reminder-tenant-'.Str::lower(Str::random(6)),
            'type' => 'tour_company',
            'status' => 'approved',
            'owner_user_id' => $vendor->id,
        ]);

        $listing = Listing::create([
            'tenant_id' => $tenant->id,
            'type' => 'tour',
            'title' => 'Reminder Listing',
            'slug' => 'reminder-listing-'.Str::lower(Str::random(6)),
            'summary' => 'Summary',
            'description' => 'Listing description that is long enough to satisfy the required validation constraints.',
            'city' => 'Accra',
            'country' => 'Ghana',
            'price_from' => 250,
            'currency_code' => 'USD',
            'pricing_unit' => 'per traveler',
            'status' => 'published',
        ]);

        return [$clientOne, $clientTwo, $listing, $tenant];
    }
}

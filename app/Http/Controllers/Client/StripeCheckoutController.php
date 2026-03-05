<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use App\Notifications\BookingStatusUpdatedNotification;
use App\Services\PayoutService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StripeCheckoutController extends Controller
{
    /**
     * Create a Stripe checkout session for the booking.
     */
    public function createSession(Booking $booking): RedirectResponse
    {
        abort_unless($booking->user_id === request()->user()->id, 403);
        $booking->loadMissing(['listing', 'tenant.profile', 'payments']);

        if ($booking->status !== 'pending_payment') {
            return redirect()->route('client.bookings.show', $booking)->with('status', 'This booking is already paid or closed.');
        }

        $secret = trim((string) config('services.stripe.secret'));

        if ($secret === '') {
            // Local fallback when Stripe credentials are not configured.
            $booking->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);
            $booking->payments()->latest()->first()?->update([
                'status' => 'paid',
                'provider_reference' => 'local-simulated',
                'payload' => ['simulated' => true],
            ]);

            $this->notifyPaidBooking($booking);

            return redirect()->route('client.bookings.show', $booking)->with('status', 'Payment simulated locally (Stripe secret not configured).');
        }

        $successUrl = route('client.bookings.stripe.success', $booking).'?session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl = route('client.bookings.show', $booking);
        $grossCents = (int) round((float) $booking->total_amount * 100);
        $currency = strtolower(trim((string) $booking->currency));

        if ($grossCents < 1) {
            return redirect()->route('client.bookings.show', $booking)->withErrors([
                'payment' => 'Invalid booking amount. Please contact support.',
            ]);
        }

        if (! preg_match('/^[a-z]{3}$/', $currency)) {
            return redirect()->route('client.bookings.show', $booking)->withErrors([
                'payment' => 'Invalid booking currency. Please contact support.',
            ]);
        }

        /** @var PayoutService $payoutService */
        $payoutService = app(PayoutService::class);
        $split = $payoutService->splitCents($grossCents);

        $profile = $booking->tenant->profile;
        $connectEnabled = (bool) config('services.stripe.connect_destination_enabled', false);
        $useConnectDestination = $connectEnabled
            && ($profile?->payout_mode === 'connect_destination')
            && filled($profile?->stripe_connect_account_id);

        $payload = [
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'line_items[0][price_data][currency]' => $currency,
            'line_items[0][price_data][product_data][name]' => Str::limit(
                (string) ($booking->listing->title ?: "Booking {$booking->booking_no}"),
                120,
                ''
            ),
            'line_items[0][price_data][unit_amount]' => $grossCents,
            'line_items[0][quantity]' => 1,
            'metadata[booking_id]' => $booking->id,
            'metadata[booking_no]' => $booking->booking_no,
        ];

        if ($useConnectDestination) {
            $payload['payment_intent_data[application_fee_amount]'] = $split['commission'];
            $payload['payment_intent_data[transfer_data][destination]'] = (string) $profile->stripe_connect_account_id;
        }

        try {
            $response = Http::withBasicAuth($secret, '')
                ->asForm()
                ->timeout(20)
                ->post('https://api.stripe.com/v1/checkout/sessions', $payload);
        } catch (ConnectionException $exception) {
            Log::error('Stripe checkout session request failed to connect.', [
                'booking_id' => $booking->id,
                'booking_no' => $booking->booking_no,
                'message' => $exception->getMessage(),
            ]);

            return redirect()->route('client.bookings.show', $booking)->withErrors([
                'payment' => 'Unable to reach Stripe right now. Please try again.',
            ]);
        }

        if (! $response->successful()) {
            $stripeErrorMessage = trim((string) $response->json('error.message'));
            $stripeErrorCode = trim((string) $response->json('error.code'));

            Log::warning('Stripe checkout session initialization failed.', [
                'booking_id' => $booking->id,
                'booking_no' => $booking->booking_no,
                'http_status' => $response->status(),
                'currency' => $currency,
                'gross_cents' => $grossCents,
                'stripe_error_code' => $stripeErrorCode,
                'stripe_error_message' => $stripeErrorMessage,
                'response_body' => $response->json(),
            ]);

            $userMessage = $stripeErrorMessage !== ''
                ? "Stripe error: {$stripeErrorMessage}"
                : 'Unable to initialize Stripe checkout. Please try again.';

            return redirect()->route('client.bookings.show', $booking)->withErrors([
                'payment' => $userMessage,
            ]);
        }

        $sessionId = (string) $response->json('id');
        $checkoutUrl = (string) $response->json('url');

        if ($sessionId === '' || $checkoutUrl === '') {
            Log::warning('Stripe checkout session response missing id/url.', [
                'booking_id' => $booking->id,
                'booking_no' => $booking->booking_no,
                'response_body' => $response->json(),
            ]);

            return redirect()->route('client.bookings.show', $booking)->withErrors([
                'payment' => 'Stripe returned an incomplete checkout response. Please try again.',
            ]);
        }

        $booking->update([
            'stripe_checkout_session_id' => $sessionId,
        ]);

        $booking->payments()->latest()->first()?->update([
            'provider_reference' => $sessionId,
            'transfer_mode' => $useConnectDestination ? 'connect_destination' : 'platform',
            'payload' => $response->json(),
        ]);

        return redirect()->away($checkoutUrl);
    }

    /**
     * Mark booking/payment as paid after Stripe success redirect.
     */
    public function success(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->user_id === $request->user()->id, 403);

        if ($booking->status === 'paid') {
            return redirect()->route('client.bookings.show', $booking)->with('status', 'Payment already confirmed.');
        }

        $sessionId = (string) $request->query('session_id');
        $secret = trim((string) config('services.stripe.secret'));

        if ($secret === '' || $sessionId === '') {
            return redirect()->route('client.bookings.show', $booking)->withErrors([
                'payment' => 'Missing Stripe confirmation details.',
            ]);
        }

        try {
            $response = Http::withBasicAuth($secret, '')
                ->timeout(20)
                ->get("https://api.stripe.com/v1/checkout/sessions/{$sessionId}");
        } catch (ConnectionException $exception) {
            Log::error('Stripe checkout session verification failed to connect.', [
                'booking_id' => $booking->id,
                'booking_no' => $booking->booking_no,
                'session_id' => $sessionId,
                'message' => $exception->getMessage(),
            ]);

            return redirect()->route('client.bookings.show', $booking)->withErrors([
                'payment' => 'Unable to reach Stripe to verify payment. Please try again.',
            ]);
        }

        if (! $response->successful()) {
            $stripeErrorMessage = trim((string) $response->json('error.message'));
            $stripeErrorCode = trim((string) $response->json('error.code'));

            Log::warning('Stripe checkout session verification failed.', [
                'booking_id' => $booking->id,
                'booking_no' => $booking->booking_no,
                'session_id' => $sessionId,
                'http_status' => $response->status(),
                'stripe_error_code' => $stripeErrorCode,
                'stripe_error_message' => $stripeErrorMessage,
                'response_body' => $response->json(),
            ]);

            $userMessage = $stripeErrorMessage !== ''
                ? "Unable to verify Stripe payment status: {$stripeErrorMessage}"
                : 'Unable to verify Stripe payment status.';

            return redirect()->route('client.bookings.show', $booking)->withErrors([
                'payment' => $userMessage,
            ]);
        }

        $isPaid = $response->json('payment_status') === 'paid';

        if (! $isPaid) {
            return redirect()->route('client.bookings.show', $booking)->withErrors([
                'payment' => 'Payment not completed yet.',
            ]);
        }

        $booking->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $booking->payments()->latest()->first()?->update([
            'status' => 'paid',
            'provider_reference' => $sessionId,
            'payload' => $response->json(),
        ]);

        $this->notifyPaidBooking($booking);

        return redirect()->route('client.bookings.show', $booking)->with('status', 'Payment confirmed successfully.');
    }

    protected function notifyPaidBooking(Booking $booking): void
    {
        $booking->loadMissing(['listing', 'tenant', 'user']);

        $booking->user?->notify(new BookingStatusUpdatedNotification(
            $booking,
            "Payment confirmed for booking {$booking->booking_no}."
        ));

        $this->vendorUsersForBooking($booking)
            ->reject(fn (User $vendorUser) => (int) $vendorUser->id === (int) $booking->user_id)
            ->each(fn (User $vendorUser) => $vendorUser->notify(new BookingStatusUpdatedNotification(
                $booking,
                "Payment received for booking {$booking->booking_no}."
            )));
    }

    protected function vendorUsersForBooking(Booking $booking)
    {
        return User::query()
            ->where(function ($query) use ($booking): void {
                $query->whereHas('ownedTenants', fn ($owned) => $owned->where('tenants.id', $booking->tenant_id))
                    ->orWhereHas('tenantMemberships', function ($memberships) use ($booking): void {
                        $memberships->where('tenant_id', $booking->tenant_id)->where('is_active', true);
                    });
            })
            ->get();
    }
}

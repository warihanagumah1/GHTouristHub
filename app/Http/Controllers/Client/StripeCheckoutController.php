<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\PayoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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

        $secret = (string) config('services.stripe.secret');

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

            return redirect()->route('client.bookings.show', $booking)->with('status', 'Payment simulated locally (Stripe secret not configured).');
        }

        $successUrl = route('client.bookings.stripe.success', $booking).'?session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl = route('client.bookings.show', $booking);
        $grossCents = (int) round((float) $booking->total_amount * 100);

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
            'line_items[0][price_data][currency]' => strtolower($booking->currency),
            'line_items[0][price_data][product_data][name]' => $booking->listing->title,
            'line_items[0][price_data][unit_amount]' => $grossCents,
            'line_items[0][quantity]' => 1,
            'metadata[booking_id]' => $booking->id,
            'metadata[booking_no]' => $booking->booking_no,
        ];

        if ($useConnectDestination) {
            $payload['payment_intent_data[application_fee_amount]'] = $split['commission'];
            $payload['payment_intent_data[transfer_data][destination]'] = (string) $profile->stripe_connect_account_id;
        }

        $response = Http::withBasicAuth($secret, '')
            ->asForm()
            ->post('https://api.stripe.com/v1/checkout/sessions', $payload);

        if (! $response->successful()) {
            return redirect()->route('client.bookings.show', $booking)->withErrors([
                'payment' => 'Unable to initialize Stripe checkout. Please try again.',
            ]);
        }

        $sessionId = (string) $response->json('id');
        $checkoutUrl = (string) $response->json('url');

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
        $secret = (string) config('services.stripe.secret');

        if ($secret === '' || $sessionId === '') {
            return redirect()->route('client.bookings.show', $booking)->withErrors([
                'payment' => 'Missing Stripe confirmation details.',
            ]);
        }

        $response = Http::withBasicAuth($secret, '')
            ->get("https://api.stripe.com/v1/checkout/sessions/{$sessionId}");

        if (! $response->successful()) {
            return redirect()->route('client.bookings.show', $booking)->withErrors([
                'payment' => 'Unable to verify Stripe payment status.',
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

        return redirect()->route('client.bookings.show', $booking)->with('status', 'Payment confirmed successfully.');
    }
}

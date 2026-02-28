<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Listing;
use App\Models\Payment;
use App\Models\TenantReview;
use App\Services\PayoutService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    private const REVIEWABLE_STATUSES = ['paid', 'confirmed', 'completed'];

    /**
     * Display current user's bookings.
     */
    public function index(): View
    {
        $bookings = request()->user()
            ->bookings()
            ->with(['listing.tenant', 'payments'])
            ->latest()
            ->paginate(15);

        return view('client.bookings.index', [
            'bookings' => $bookings,
        ]);
    }

    /**
     * Display booking details.
     */
    public function show(Booking $booking): View
    {
        abort_unless($booking->user_id === request()->user()->id, 403);

        $booking->load(['listing.media', 'listing.tenant.profile', 'payments', 'review']);

        return view('client.bookings.show', [
            'booking' => $booking,
        ]);
    }

    /**
     * Submit or update a company review for a booking.
     */
    public function storeReview(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->user_id === $request->user()->id, 403);
        abort_unless(in_array($booking->status, self::REVIEWABLE_STATUSES, true), 403);

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $listing = $booking->listing;
        abort_unless($listing, 404);

        DB::transaction(function () use ($booking, $listing, $request, $validated): void {
            $existingReview = $booking->review;

            if ($existingReview && $existingReview->user_id !== $request->user()->id) {
                abort(403);
            }

            $review = TenantReview::updateOrCreate(
                ['booking_id' => $booking->id],
                [
                    'tenant_id' => $booking->tenant_id,
                    'listing_id' => $booking->listing_id,
                    'user_id' => $request->user()->id,
                    'rating' => (int) $validated['rating'],
                    'comment' => $validated['comment'] ?? null,
                ]
            );

            if (! $existingReview) {
                $this->applyNewReviewRating($listing, (int) $review->rating);
            } elseif ((int) $existingReview->rating !== (int) $review->rating) {
                $this->applyUpdatedReviewRating($listing, (int) $existingReview->rating, (int) $review->rating);
            }
        });

        return back()->with('status', 'Thank you. Your review has been saved.');
    }

    /**
     * Create a booking from a listing.
     */
    public function store(Request $request, Listing $listing): RedirectResponse
    {
        abort_unless($listing->status === 'published', 404);

        $validated = $request->validate([
            'travelers_count' => ['required', 'integer', 'min:1', 'max:50'],
            'special_requests' => ['nullable', 'string', 'max:1000'],
        ]);

        $split = app(PayoutService::class)->splitAmount((float) $listing->price_from * (int) $validated['travelers_count']);

        $booking = Booking::create([
            'booking_no' => 'THB-'.Str::upper(Str::random(8)),
            'user_id' => $request->user()->id,
            'tenant_id' => $listing->tenant_id,
            'listing_id' => $listing->id,
            'travelers_count' => $validated['travelers_count'],
            'special_requests' => $validated['special_requests'] ?? null,
            'total_amount' => (float) $listing->price_from * (int) $validated['travelers_count'],
            'currency' => strtoupper((string) ($listing->currency_code ?: 'USD')),
            'status' => 'pending_payment',
        ]);

        Payment::create([
            'booking_id' => $booking->id,
            'provider' => 'stripe',
            'amount' => $booking->total_amount,
            'currency' => $booking->currency,
            'commission_amount' => $split['commission'],
            'vendor_net_amount' => $split['vendor_net'],
            'transfer_mode' => 'platform',
            'status' => 'pending',
        ]);

        return redirect()->route('client.bookings.show', $booking)->with('status', 'Booking created. Continue to payment.');
    }

    protected function applyNewReviewRating(Listing $listing, int $rating): void
    {
        $currentCount = (int) $listing->rating_count;
        $currentAverage = (float) $listing->rating_average;
        $newCount = $currentCount + 1;
        $newAverage = (($currentAverage * $currentCount) + $rating) / max($newCount, 1);

        $listing->update([
            'rating_count' => $newCount,
            'rating_average' => round($newAverage, 2),
        ]);
    }

    protected function applyUpdatedReviewRating(Listing $listing, int $oldRating, int $newRating): void
    {
        $currentCount = max(1, (int) $listing->rating_count);
        $currentAverage = (float) $listing->rating_average;
        $currentTotal = $currentAverage * $currentCount;
        $updatedAverage = ($currentTotal - $oldRating + $newRating) / $currentCount;

        $listing->update([
            'rating_average' => round(max($updatedAverage, 0), 2),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Services\CurrencyService;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    /**
     * Show vendor analytics dashboard.
     */
    public function __invoke(CurrencyService $currencyService): View
    {
        $user = request()->user();
        $tenant = $user->primaryTenant();

        abort_unless($tenant, 403, 'No tenant assigned to this vendor account.');

        $tenant->load(['profile']);

        $bookings = $tenant->bookings()->with(['listing', 'user', 'payments'])->latest()->take(10)->get();

        $paidBookings = $tenant->bookings()->whereIn('status', ['paid', 'confirmed', 'completed'])->get(['total_amount', 'currency']);
        $revenueUsd = $paidBookings->sum(fn ($booking) => $currencyService->convertToUsd((float) $booking->total_amount, (string) $booking->currency));
        $totalReviews = (int) $tenant->reviews()->count();
        $averageRating = round((float) ($tenant->reviews()->avg('rating') ?? 0), 1);

        $stats = [
            'active_listings' => $tenant->listings()->where('status', 'published')->count(),
            'bookings_total' => $tenant->bookings()->count(),
            'pending_bookings' => $tenant->bookings()->where('status', 'pending_payment')->count(),
            'revenue_paid_usd' => $revenueUsd,
            'total_reviews' => $totalReviews,
            'average_rating' => $averageRating,
        ];

        return view('dashboards.vendor', [
            'tenant' => $tenant,
            'stats' => $stats,
            'bookings' => $bookings,
            'canManageTeam' => (int) $tenant->owner_user_id === (int) $user->id,
        ]);
    }
}

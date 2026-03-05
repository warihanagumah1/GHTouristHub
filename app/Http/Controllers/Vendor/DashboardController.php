<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Services\PayoutService;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    /**
     * Show vendor analytics dashboard.
     */
    public function __invoke(PayoutService $payoutService): View
    {
        $user = request()->user();
        $tenant = $user->primaryTenant();

        abort_unless($tenant, 403, 'No tenant assigned to this vendor account.');

        $tenant->load(['profile']);

        $bookings = $tenant->bookings()->with(['listing', 'user', 'payments'])->latest()->take(10)->get();

        $totalReviews = (int) $tenant->reviews()->count();
        $averageRating = round((float) ($tenant->reviews()->avg('rating') ?? 0), 1);

        $stats = [
            'active_listings' => $tenant->listings()->where('status', 'published')->count(),
            'bookings_total' => $tenant->bookings()->count(),
            'pending_bookings' => $tenant->bookings()->where('status', 'pending_payment')->count(),
            'operator_total_revenue_usd' => $payoutService->tenantTotalRevenueUsd($tenant),
            'operator_yet_to_be_paid_usd' => $payoutService->tenantYetToBePaidUsd($tenant),
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

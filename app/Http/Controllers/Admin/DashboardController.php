<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Listing;
use App\Models\Tenant;
use App\Models\User;
use App\Services\CurrencyService;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    /**
     * Show admin analytics.
     */
    public function __invoke(CurrencyService $currencyService): View
    {
        $bookings30d = Booking::query()
            ->where('created_at', '>=', now()->subDays(30))
            ->get(['total_amount', 'currency', 'status']);

        $gmvUsd30d = $bookings30d
            ->whereIn('status', ['paid', 'confirmed', 'completed'])
            ->sum(fn (Booking $booking) => $currencyService->convertToUsd((float) $booking->total_amount, (string) $booking->currency));

        $stats = [
            'pending_vendors' => Tenant::query()->where('status', 'pending')->count(),
            'approved_vendors' => Tenant::query()->where('status', 'approved')->count(),
            'published_listings' => Listing::query()->where('status', 'published')->count(),
            'bookings_30d' => Booking::query()->where('created_at', '>=', now()->subDays(30))->count(),
            'gmv_30d_usd' => $gmvUsd30d,
            'active_clients' => User::query()->where('user_role', User::ROLE_CLIENT)->count(),
            'active_vendors' => User::query()->whereIn('user_role', [
                User::ROLE_TOUR_OWNER,
                User::ROLE_TOUR_STAFF,
                User::ROLE_UTILITY_OWNER,
                User::ROLE_UTILITY_STAFF,
            ])->count(),
            'total_users' => User::query()->count(),
            'total_listings' => Listing::query()->count(),
        ];

        $recentBookings = Booking::query()
            ->with(['user', 'listing', 'tenant'])
            ->latest()
            ->take(12)
            ->get();

        $recentUsers = User::query()
            ->latest()
            ->take(8)
            ->get(['id', 'name', 'email', 'user_role', 'is_blocked', 'created_at']);

        $recentListings = Listing::query()
            ->with('tenant')
            ->latest()
            ->take(8)
            ->get(['id', 'tenant_id', 'title', 'type', 'status', 'created_at']);

        return view('dashboards.admin', [
            'stats' => $stats,
            'recentBookings' => $recentBookings,
            'recentUsers' => $recentUsers,
            'recentListings' => $recentListings,
        ]);
    }
}

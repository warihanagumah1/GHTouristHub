<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\CurrencyService;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    /**
     * Show client analytics and recent bookings.
     */
    public function __invoke(CurrencyService $currencyService): View
    {
        $user = request()->user();

        $bookings = $user->bookings()
            ->with(['listing.tenant', 'payments'])
            ->latest()
            ->take(8)
            ->get();

        $paidBookings = $user->bookings()->whereIn('status', ['paid', 'confirmed', 'completed'])->get(['total_amount', 'currency']);
        $totalSpentUsd = $paidBookings->sum(fn ($booking) => $currencyService->convertToUsd((float) $booking->total_amount, (string) $booking->currency));

        $stats = [
            'upcoming' => $user->bookings()->whereIn('status', ['paid', 'confirmed'])->count(),
            'pending_payment' => $user->bookings()->where('status', 'pending_payment')->count(),
            'completed' => $user->bookings()->where('status', 'completed')->count(),
            'total_spent_usd' => $totalSpentUsd,
        ];

        return view('dashboards.client', [
            'stats' => $stats,
            'bookings' => $bookings,
        ]);
    }
}

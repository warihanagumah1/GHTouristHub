<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\CurrencyService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AnalyticsController extends Controller
{
    public function __invoke(Request $request, CurrencyService $currencyService): View
    {
        $tenant = $request->user()->primaryTenant();
        abort_unless($tenant, 403);

        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'status' => ['nullable', 'in:pending_payment,paid,confirmed,cancelled,completed'],
            'type' => ['nullable', 'in:tour,utility'],
        ]);

        $dateFrom = isset($validated['date_from'])
            ? Carbon::parse($validated['date_from'])->startOfDay()
            : now()->subDays(29)->startOfDay();
        $dateTo = isset($validated['date_to'])
            ? Carbon::parse($validated['date_to'])->endOfDay()
            : now()->endOfDay();

        if ($dateFrom->diffInDays($dateTo) > 120) {
            throw ValidationException::withMessages([
                'date_from' => 'Please select a date range of 120 days or fewer.',
            ]);
        }

        $baseQuery = Booking::query()
            ->where('tenant_id', $tenant->id)
            ->with(['listing'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->when(
                isset($validated['status']) && $validated['status'] !== '',
                fn ($query) => $query->where('status', $validated['status'])
            )
            ->when(
                isset($validated['type']) && $validated['type'] !== '',
                fn ($query) => $query->whereHas('listing', fn ($listingQuery) => $listingQuery->where('type', $validated['type']))
            );

        $bookings = $baseQuery->get([
            'id',
            'tenant_id',
            'listing_id',
            'total_amount',
            'currency',
            'status',
            'created_at',
        ]);

        $paidStatuses = ['paid', 'confirmed', 'completed'];
        $paidBookings = $bookings->whereIn('status', $paidStatuses);
        $gmvUsd = $paidBookings
            ->sum(fn (Booking $booking) => $currencyService->convertToUsd((float) $booking->total_amount, (string) $booking->currency));

        $bookingsByStatus = collect(['pending_payment', 'paid', 'confirmed', 'cancelled', 'completed'])
            ->mapWithKeys(fn (string $status) => [$status => $bookings->where('status', $status)->count()]);

        $period = collect(CarbonPeriod::create($dateFrom->copy()->startOfDay(), $dateTo->copy()->startOfDay()));
        $countByDay = $bookings->groupBy(fn (Booking $booking) => $booking->created_at->toDateString());
        $revenueByDay = $paidBookings
            ->groupBy(fn (Booking $booking) => $booking->created_at->toDateString())
            ->map(fn ($items) => $items->sum(
                fn (Booking $booking) => $currencyService->convertToUsd((float) $booking->total_amount, (string) $booking->currency)
            ));

        $dailyTrend = $period->map(function (Carbon $day) use ($countByDay, $revenueByDay) {
            $bucket = $day->toDateString();
            $countItems = $countByDay->get($bucket);

            return [
                'date' => $bucket,
                'bookings' => (int) ($countItems?->count() ?? 0),
                'revenue_usd' => (float) ($revenueByDay->get($bucket, 0)),
            ];
        })->values();

        $topListings = $bookings
            ->filter(fn (Booking $booking) => $booking->listing)
            ->groupBy('listing_id')
            ->map(fn ($items) => (object) [
                'title' => (string) optional($items->first()->listing)->title,
                'bookings_count' => $items->count(),
            ])
            ->sortByDesc('bookings_count')
            ->take(8)
            ->values();

        $currencyBreakdown = $paidBookings
            ->groupBy('currency')
            ->map(fn ($items, $currency) => (object) ['currency' => $currency, 'total' => $items->sum('total_amount')])
            ->values();

        return view('vendor.analytics.index', [
            'tenant' => $tenant,
            'gmvUsd' => $gmvUsd,
            'bookingsByStatus' => $bookingsByStatus,
            'topListings' => $topListings,
            'currencyBreakdown' => $currencyBreakdown,
            'dailyTrend' => $dailyTrend,
            'currencyService' => $currencyService,
            'filters' => [
                'date_from' => $dateFrom->toDateString(),
                'date_to' => $dateTo->toDateString(),
                'status' => $validated['status'] ?? '',
                'type' => $validated['type'] ?? '',
            ],
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Tenant;
use App\Models\User;
use App\Services\CurrencyService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AnalyticsController extends Controller
{
    /**
     * Display marketplace analytics and operational KPIs.
     */
    public function __invoke(Request $request, CurrencyService $currencyService): View
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'status' => ['nullable', 'in:pending_payment,paid,confirmed,cancelled,completed'],
            'type' => ['nullable', 'in:tour,utility'],
            'tenant_id' => ['nullable', 'integer', 'exists:tenants,id'],
        ]);

        $dateFrom = isset($validated['date_from'])
            ? Carbon::parse($validated['date_from'])->startOfDay()
            : now()->subDays(29)->startOfDay();
        $dateTo = isset($validated['date_to'])
            ? Carbon::parse($validated['date_to'])->endOfDay()
            : now()->endOfDay();

        if ($dateFrom->diffInDays($dateTo) > 90) {
            throw ValidationException::withMessages([
                'date_from' => 'Please select a date range of 90 days or fewer.',
            ]);
        }

        $baseQuery = Booking::query()
            ->with(['listing', 'tenant'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->when(
                isset($validated['status']) && $validated['status'] !== '',
                fn ($query) => $query->where('status', $validated['status'])
            )
            ->when(
                isset($validated['tenant_id']) && $validated['tenant_id'] !== '',
                fn ($query) => $query->where('tenant_id', (int) $validated['tenant_id'])
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

        $topDestinations = $bookings
            ->filter(fn (Booking $booking) => $booking->listing)
            ->groupBy(fn (Booking $booking) => ($booking->listing->country ?: 'Unknown').'||'.($booking->listing->city ?: 'Unknown'))
            ->map(function ($items, string $key) {
                [$country, $city] = explode('||', $key);

                return (object) [
                    'country' => $country,
                    'city' => $city,
                    'total' => $items->count(),
                ];
            })
            ->sortByDesc('total')
            ->take(8)
            ->values();

        $tenantNames = Tenant::query()->pluck('name', 'id');
        $topVendors = $bookings
            ->groupBy('tenant_id')
            ->map(fn ($items, $tenantId) => (object) [
                'name' => $tenantNames[$tenantId] ?? 'Unknown Vendor',
                'bookings_count' => $items->count(),
            ])
            ->sortByDesc('bookings_count')
            ->take(8)
            ->values();

        $monthlyUsers = User::query()
            ->whereBetween('created_at', [now()->subMonths(11)->startOfMonth(), now()->endOfMonth()])
            ->get(['created_at'])
            ->groupBy(fn (User $user) => $user->created_at->format('Y-m'))
            ->map(fn ($items, $bucket) => (object) ['bucket' => $bucket, 'total' => $items->count()])
            ->sortBy('bucket')
            ->values();

        $currencyBreakdown = $paidBookings
            ->groupBy('currency')
            ->map(fn ($items, $currency) => (object) ['currency' => $currency, 'total' => $items->sum('total_amount')])
            ->values();

        return view('admin.analytics.index', [
            'gmvUsd30d' => $gmvUsd,
            'bookingsByStatus' => $bookingsByStatus,
            'topDestinations' => $topDestinations,
            'topVendors' => $topVendors,
            'monthlyUsers' => $monthlyUsers,
            'currencyBreakdown' => $currencyBreakdown,
            'dailyTrend' => $dailyTrend,
            'currencyService' => $currencyService,
            'tenants' => Tenant::query()->orderBy('name')->get(['id', 'name']),
            'filters' => [
                'date_from' => $dateFrom->toDateString(),
                'date_to' => $dateTo->toDateString(),
                'status' => $validated['status'] ?? '',
                'type' => $validated['type'] ?? '',
                'tenant_id' => isset($validated['tenant_id']) ? (string) $validated['tenant_id'] : '',
            ],
        ]);
    }
}

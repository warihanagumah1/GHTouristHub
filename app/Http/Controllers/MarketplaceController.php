<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Tenant;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarketplaceController extends Controller
{
    /**
     * Public homepage.
     */
    public function home(): View
    {
        $featuredTours = Listing::query()
            ->with(['tenant.profile', 'coverMedia', 'media'])
            ->published()
            ->where('type', 'tour')
            ->featured()
            ->latest()
            ->take(3)
            ->get();

        $featuredUtilities = Listing::query()
            ->with(['tenant.profile', 'coverMedia', 'media'])
            ->published()
            ->where('type', 'utility')
            ->featured()
            ->latest()
            ->take(4)
            ->get();

        $heroListings = Listing::query()
            ->with(['tenant.profile', 'coverMedia', 'media'])
            ->published()
            ->featured()
            ->latest()
            ->take(10)
            ->get();

        if ($heroListings->isEmpty()) {
            $fallbackListing = Listing::query()
                ->with(['tenant.profile', 'coverMedia', 'media'])
                ->published()
                ->latest()
                ->first();

            if ($fallbackListing) {
                $heroListings = collect([$fallbackListing]);
            }
        }

        $destinations = Listing::query()
            ->select('city', 'country', DB::raw('count(*) as listings_count'))
            ->published()
            ->whereNotNull('country')
            ->whereNotNull('city')
            ->groupBy('city', 'country')
            ->orderByDesc('listings_count')
            ->take(6)
            ->get();

        return view('marketplace.home', [
            'heroListings' => $heroListings,
            'featuredTours' => $featuredTours,
            'featuredUtilities' => $featuredUtilities,
            'destinations' => $destinations,
        ]);
    }

    /**
     * Public tours discovery page.
     */
    public function tours(Request $request): View
    {
        $query = Listing::query()
            ->with(['tenant.profile', 'coverMedia', 'media'])
            ->published()
            ->where('type', 'tour');

        if ($request->filled('q')) {
            $search = trim((string) $request->query('q'));
            $query->where(function ($inner) use ($search): void {
                $inner->where('title', 'like', "%{$search}%")
                    ->orWhere('summary', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('destination')) {
            $destination = trim((string) $request->query('destination'));
            $query->where(function ($inner) use ($destination): void {
                $inner->where('city', 'like', "%{$destination}%")
                    ->orWhere('country', 'like', "%{$destination}%");
            });
        }

        $sort = (string) $request->query('sort', 'newest');
        $query = match ($sort) {
            'price_low' => $query->orderBy('price_from'),
            'price_high' => $query->orderByDesc('price_from'),
            'rating' => $query->orderByDesc('rating_average')->orderByDesc('rating_count'),
            default => $query->latest(),
        };

        $tours = $query->paginate(12)->withQueryString();

        return view('marketplace.tours', [
            'tours' => $tours,
            'filters' => [
                'q' => (string) $request->query('q', ''),
                'destination' => (string) $request->query('destination', ''),
                'sort' => $sort,
            ],
        ]);
    }

    /**
     * Public utilities discovery page.
     */
    public function utilities(Request $request): View
    {
        $query = Listing::query()
            ->with(['tenant.profile', 'coverMedia', 'media'])
            ->published()
            ->where('type', 'utility');

        if ($request->filled('q')) {
            $search = trim((string) $request->query('q'));
            $query->where(function ($inner) use ($search): void {
                $inner->where('title', 'like', "%{$search}%")
                    ->orWhere('summary', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('destination')) {
            $destination = trim((string) $request->query('destination'));
            $query->where(function ($inner) use ($destination): void {
                $inner->where('city', 'like', "%{$destination}%")
                    ->orWhere('country', 'like', "%{$destination}%");
            });
        }

        if ($request->filled('subtype')) {
            $query->where('subtype', (string) $request->query('subtype'));
        }

        $sort = (string) $request->query('sort', 'newest');
        $query = match ($sort) {
            'price_low' => $query->orderBy('price_from'),
            'price_high' => $query->orderByDesc('price_from'),
            'rating' => $query->orderByDesc('rating_average')->orderByDesc('rating_count'),
            default => $query->latest(),
        };

        $utilities = $query->paginate(12)->withQueryString();

        $subtypes = Listing::query()
            ->published()
            ->where('type', 'utility')
            ->whereNotNull('subtype')
            ->select('subtype')
            ->distinct()
            ->orderBy('subtype')
            ->pluck('subtype');

        return view('marketplace.utilities', [
            'utilities' => $utilities,
            'subtypes' => $subtypes,
            'filters' => [
                'q' => (string) $request->query('q', ''),
                'destination' => (string) $request->query('destination', ''),
                'subtype' => (string) $request->query('subtype', ''),
                'sort' => $sort,
            ],
        ]);
    }

    /**
     * Public listing detail page.
     */
    public function listing(string $slug): View
    {
        $listing = Listing::query()
            ->with([
                'tenant.profile',
                'media' => fn ($query) => $query->orderBy('sort_order'),
            ])
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $reviews = $listing->reviews()
            ->with('user:id,name')
            ->latest()
            ->paginate(5, ['*'], 'reviews_page')
            ->withQueryString();

        return view('marketplace.listing', [
            'listing' => $listing,
            'reviews' => $reviews,
        ]);
    }

    /**
     * Public vendor profile and listings page.
     */
    public function vendor(string $slug): View
    {
        $tenant = Tenant::query()
            ->with([
                'profile',
                'listings' => fn ($query) => $query->published()->with(['coverMedia', 'media'])->latest(),
            ])
            ->where('slug', $slug)
            ->where('status', 'approved')
            ->firstOrFail();

        $reviews = $tenant->reviews()
            ->with(['user:id,name', 'listing:id,title,slug'])
            ->latest()
            ->take(20)
            ->get();

        $reviewCount = (int) $tenant->reviews()->count();
        $ratingAverage = round((float) ($tenant->reviews()->avg('rating') ?? 0), 1);

        return view('marketplace.vendor', [
            'tenant' => $tenant,
            'tenantReviews' => $reviews,
            'tenantRatingAverage' => $ratingAverage,
            'tenantReviewCount' => $reviewCount,
        ]);
    }
}

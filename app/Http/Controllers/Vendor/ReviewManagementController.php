<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class ReviewManagementController extends Controller
{
    /**
     * Show customer reviews for the authenticated vendor's primary tenant.
     */
    public function index(): View
    {
        $user = request()->user();
        $tenant = $user->primaryTenant();

        abort_unless($tenant, 403, 'No tenant assigned to this vendor account.');

        $reviews = $tenant->reviews()
            ->with(['listing:id,title,slug', 'user:id,name'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total_reviews' => (int) $tenant->reviews()->count(),
            'average_rating' => round((float) ($tenant->reviews()->avg('rating') ?? 0), 1),
            'reviews_with_comments' => (int) $tenant->reviews()->whereNotNull('comment')->where('comment', '!=', '')->count(),
            'reviewed_listings' => (int) $tenant->reviews()->distinct('listing_id')->count('listing_id'),
        ];

        return view('vendor.reviews.index', [
            'tenant' => $tenant,
            'reviews' => $reviews,
            'stats' => $stats,
        ]);
    }
}

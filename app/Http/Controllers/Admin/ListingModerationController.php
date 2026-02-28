<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ListingModerationController extends Controller
{
    /**
     * Show listings for moderation and status management.
     */
    public function index(Request $request): View
    {
        $query = Listing::query()->with('tenant');

        if ($request->filled('type')) {
            $query->where('type', (string) $request->query('type'));
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->query('status'));
        }

        if ($request->filled('featured')) {
            $query->where('is_featured', $request->boolean('featured'));
        }

        if ($request->filled('q')) {
            $search = trim((string) $request->query('q'));
            $query->where('title', 'like', "%{$search}%");
        }

        $listings = $query->latest()->paginate(25)->withQueryString();

        return view('admin.listings.index', [
            'listings' => $listings,
        ]);
    }

    /**
     * Update listing moderation status.
     */
    public function updateStatus(Request $request, Listing $listing): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:draft,pending_review,published,paused,blocked'],
        ]);

        $listing->update([
            'status' => $validated['status'],
        ]);

        return back()->with('status', 'Listing status updated.');
    }

    /**
     * Toggle blocked state for a listing.
     */
    public function toggleBlocked(Request $request, Listing $listing): RedirectResponse
    {
        $validated = $request->validate([
            'is_blocked' => ['required', 'boolean'],
        ]);

        $shouldBlock = (bool) $validated['is_blocked'];

        $listing->update([
            'status' => $shouldBlock ? 'blocked' : 'paused',
        ]);

        $message = $shouldBlock
            ? 'Listing has been blocked.'
            : 'Listing has been unblocked and moved to paused status.';

        return back()->with('status', $message);
    }

    /**
     * Toggle listing featured state.
     */
    public function updateFeatured(Request $request, Listing $listing): RedirectResponse
    {
        $validated = $request->validate([
            'is_featured' => ['required', 'boolean'],
        ]);

        $listing->update([
            'is_featured' => (bool) $validated['is_featured'],
        ]);

        return back()->with('status', 'Listing featured flag updated.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\TouristRegion;
use App\Models\TouristAttraction;
use Illuminate\Contracts\View\View;

class MarketplaceAttractionController extends Controller
{
    /**
     * Show all published tourism regions.
     */
    public function index(): View
    {
        $regions = TouristRegion::query()
            ->published()
            ->withCount([
                'attractions as attractions_count' => fn ($query) => $query->published(),
            ])
            ->orderBy('name')
            ->get();

        return view('attractions.index', [
            'regions' => $regions,
        ]);
    }

    /**
     * Show a single region and its published attractions.
     */
    public function show(string $slug): View
    {
        $region = TouristRegion::query()
            ->published()
            ->with([
                'attractions' => fn ($query) => $query->published()->orderBy('sort_order')->orderBy('name'),
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        return view('attractions.region', [
            'region' => $region,
        ]);
    }

    /**
     * Show a dedicated attraction details page.
     */
    public function attraction(string $regionSlug, string $attractionSlug): View
    {
        $region = TouristRegion::query()
            ->published()
            ->where('slug', $regionSlug)
            ->firstOrFail();

        $attraction = TouristAttraction::query()
            ->published()
            ->where('tourist_region_id', $region->id)
            ->where('slug', $attractionSlug)
            ->firstOrFail();

        $relatedAttractions = TouristAttraction::query()
            ->published()
            ->where('tourist_region_id', $region->id)
            ->where('id', '!=', $attraction->id)
            ->orderBy('is_featured', 'desc')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->take(6)
            ->get(['id', 'tourist_region_id', 'name', 'slug', 'hero_image_url', 'gallery_images']);

        return view('attractions.show', [
            'region' => $region,
            'attraction' => $attraction,
            'relatedAttractions' => $relatedAttractions,
        ]);
    }
}

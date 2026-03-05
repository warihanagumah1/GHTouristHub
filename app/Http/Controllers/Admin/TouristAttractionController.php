<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TouristAttraction;
use App\Models\TouristRegion;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TouristAttractionController extends Controller
{
    /**
     * Display region and attraction content management.
     */
    public function index(): View
    {
        $regions = TouristRegion::query()
            ->with([
                'attractions' => fn ($query) => $query->orderBy('sort_order')->orderBy('name'),
            ])
            ->orderBy('name')
            ->get();

        return view('admin.attractions.index', [
            'regions' => $regions,
        ]);
    }

    public function storeRegion(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('tourist_regions', 'name')],
            'overview' => ['nullable', 'string', 'max:4000'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        TouristRegion::create([
            'name' => $validated['name'],
            'slug' => $this->uniqueRegionSlug($validated['name']),
            'overview' => $validated['overview'] ?? null,
            'is_published' => (bool) ($validated['is_published'] ?? true),
        ]);

        return back()->with('status', 'Tourist region created.');
    }

    public function updateRegion(Request $request, TouristRegion $region): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('tourist_regions', 'name')->ignore($region->id)],
            'overview' => ['nullable', 'string', 'max:4000'],
            'is_published' => ['required', 'boolean'],
        ]);

        $region->update([
            'name' => $validated['name'],
            'slug' => $this->uniqueRegionSlug($validated['name'], $region->id),
            'overview' => $validated['overview'] ?? null,
            'is_published' => (bool) $validated['is_published'],
        ]);

        return back()->with('status', 'Tourist region updated.');
    }

    public function storeAttraction(Request $request, TouristRegion $region): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:7000'],
            'hero_image_url' => ['nullable', 'url', 'max:2048'],
            'gallery_images_text' => ['nullable', 'string', 'max:20000'],
            'visiting_hours' => ['nullable', 'string', 'max:255'],
            'entry_fee' => ['nullable', 'string', 'max:255'],
            'best_time_to_visit' => ['nullable', 'string', 'max:255'],
            'contact_info' => ['nullable', 'string', 'max:255'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'how_to_get_there' => ['nullable', 'string', 'max:5000'],
            'travel_tips' => ['nullable', 'string', 'max:5000'],
            'safety_notes' => ['nullable', 'string', 'max:5000'],
            'featured_activities_text' => ['nullable', 'string', 'max:5000'],
            'nearby_places_text' => ['nullable', 'string', 'max:5000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'is_featured' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        TouristAttraction::create([
            'tourist_region_id' => $region->id,
            'name' => $validated['name'],
            'slug' => $this->uniqueAttractionSlug($validated['name'], $region),
            'city' => $validated['city'] ?? null,
            'address' => $validated['address'] ?? null,
            'summary' => $validated['summary'] ?? null,
            'description' => $validated['description'] ?? null,
            'hero_image_url' => $validated['hero_image_url'] ?? null,
            'gallery_images' => $this->toArrayLines($validated['gallery_images_text'] ?? null),
            'visiting_hours' => $validated['visiting_hours'] ?? null,
            'entry_fee' => $validated['entry_fee'] ?? null,
            'best_time_to_visit' => $validated['best_time_to_visit'] ?? null,
            'contact_info' => $validated['contact_info'] ?? null,
            'website_url' => $validated['website_url'] ?? null,
            'how_to_get_there' => $validated['how_to_get_there'] ?? null,
            'travel_tips' => $validated['travel_tips'] ?? null,
            'safety_notes' => $validated['safety_notes'] ?? null,
            'featured_activities' => $this->toArrayLines($validated['featured_activities_text'] ?? null),
            'nearby_places' => $this->toArrayLines($validated['nearby_places_text'] ?? null),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_featured' => (bool) ($validated['is_featured'] ?? false),
            'is_published' => (bool) ($validated['is_published'] ?? true),
        ]);

        return back()->with('status', 'Attraction added.');
    }

    public function updateAttraction(Request $request, TouristAttraction $attraction): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:7000'],
            'hero_image_url' => ['nullable', 'url', 'max:2048'],
            'gallery_images_text' => ['nullable', 'string', 'max:20000'],
            'visiting_hours' => ['nullable', 'string', 'max:255'],
            'entry_fee' => ['nullable', 'string', 'max:255'],
            'best_time_to_visit' => ['nullable', 'string', 'max:255'],
            'contact_info' => ['nullable', 'string', 'max:255'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'how_to_get_there' => ['nullable', 'string', 'max:5000'],
            'travel_tips' => ['nullable', 'string', 'max:5000'],
            'safety_notes' => ['nullable', 'string', 'max:5000'],
            'featured_activities_text' => ['nullable', 'string', 'max:5000'],
            'nearby_places_text' => ['nullable', 'string', 'max:5000'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:100000'],
            'is_featured' => ['required', 'boolean'],
            'is_published' => ['required', 'boolean'],
        ]);

        $region = $attraction->region;

        $attraction->update([
            'name' => $validated['name'],
            'slug' => $this->uniqueAttractionSlug($validated['name'], $region, $attraction->id),
            'city' => $validated['city'] ?? null,
            'address' => $validated['address'] ?? null,
            'summary' => $validated['summary'] ?? null,
            'description' => $validated['description'] ?? null,
            'hero_image_url' => $validated['hero_image_url'] ?? null,
            'gallery_images' => $this->toArrayLines($validated['gallery_images_text'] ?? null),
            'visiting_hours' => $validated['visiting_hours'] ?? null,
            'entry_fee' => $validated['entry_fee'] ?? null,
            'best_time_to_visit' => $validated['best_time_to_visit'] ?? null,
            'contact_info' => $validated['contact_info'] ?? null,
            'website_url' => $validated['website_url'] ?? null,
            'how_to_get_there' => $validated['how_to_get_there'] ?? null,
            'travel_tips' => $validated['travel_tips'] ?? null,
            'safety_notes' => $validated['safety_notes'] ?? null,
            'featured_activities' => $this->toArrayLines($validated['featured_activities_text'] ?? null),
            'nearby_places' => $this->toArrayLines($validated['nearby_places_text'] ?? null),
            'sort_order' => (int) $validated['sort_order'],
            'is_featured' => (bool) $validated['is_featured'],
            'is_published' => (bool) $validated['is_published'],
        ]);

        return back()->with('status', 'Attraction updated.');
    }

    protected function uniqueRegionSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: Str::lower(Str::random(8));
        $slug = $base;
        $counter = 1;

        while (TouristRegion::query()
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    protected function uniqueAttractionSlug(string $name, TouristRegion $region, ?int $ignoreId = null): string
    {
        $base = Str::slug($name.'-'.$region->name) ?: Str::lower(Str::random(8));
        $slug = $base;
        $counter = 1;

        while (TouristAttraction::query()
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    /**
     * Convert newline-separated text into a trimmed array.
     *
     * @return array<int, string>|null
     */
    protected function toArrayLines(?string $value): ?array
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $lines = preg_split('/\r\n|\r|\n/', trim($value)) ?: [];
        $items = collect($lines)
            ->map(fn (string $line) => trim($line))
            ->filter(fn (string $line) => $line !== '')
            ->values()
            ->all();

        return $items === [] ? null : $items;
    }
}

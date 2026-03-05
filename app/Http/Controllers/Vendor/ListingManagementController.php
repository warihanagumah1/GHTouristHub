<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\ListingMedia;
use App\Models\Tenant;
use App\Models\User;
use App\Services\CurrencyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ListingManagementController extends Controller
{
    private const MAX_IMAGE_COUNT = 12;
    private const MAX_IMAGE_FILE_SIZE_KB = 5120;
    private const MIN_IMAGE_WIDTH = 800;
    private const MIN_IMAGE_HEIGHT = 600;
    private const MAX_LINE_ITEM_LENGTH = 180;
    private const MAX_LINE_ITEM_COUNT = 30;
    private const OPEN_BOOKING_STATUSES = ['pending_payment', 'paid', 'confirmed'];

    public function index(): View
    {
        $tenant = request()->user()->primaryTenant();
        abort_unless($tenant, 403);

        $listings = $tenant->listings()
            ->with('media')
            ->withCount([
                'bookings as open_bookings_count' => fn ($query) => $query->whereIn('status', self::OPEN_BOOKING_STATUSES),
            ])
            ->latest()
            ->paginate(20);

        return view('vendor.listings.index', [
            'tenant' => $tenant,
            'listings' => $listings,
        ]);
    }

    public function create(Request $request): View
    {
        $this->approvedTenantFor($request->user());

        $defaultType = $this->allowedListingTypeForUser($request->user());
        $requestedType = (string) $request->query('type', $defaultType);
        $type = in_array($requestedType, ['tour', 'utility'], true) ? $requestedType : $defaultType;
        $type = $type === $defaultType ? $type : $defaultType;

        return view('vendor.listings.form', [
            'listing' => new Listing([
                'type' => $type,
                'status' => 'draft',
                'pricing_unit' => 'per traveler',
                'currency_code' => 'USD',
            ]),
            'currencies' => app(CurrencyService::class)->activeCurrencies(),
            'countries' => $this->countryOptions(),
            'utilitySubtypes' => $this->utilitySubtypes(),
            'allowedListingType' => $defaultType,
            'isEdit' => false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant = $this->approvedTenantFor($request->user());

        $validated = $this->validateListing($request);

        $listing = Listing::create([
            ...$validated,
            'tenant_id' => $tenant->id,
            'slug' => $this->uniqueSlug($validated['title']),
            'highlights' => $this->toArrayLines($validated['highlights_text'] ?? null),
            'amenities' => $this->toArrayLines($validated['amenities_text'] ?? null),
            'itinerary' => $this->toArrayLines($validated['itinerary_text'] ?? null),
            'inclusions' => $this->toArrayLines($validated['inclusions_text'] ?? null),
            'exclusions' => $this->toArrayLines($validated['exclusions_text'] ?? null),
            'languages' => $this->toArrayLines($validated['languages_text'] ?? null),
        ]);

        $this->syncMedia($listing, $request->file('images', []));

        return redirect()->route('vendor.listings.index')->with('status', 'Listing created successfully.');
    }

    public function edit(Listing $listing): View
    {
        $tenant = request()->user()->primaryTenant();
        abort_unless($tenant && $listing->tenant_id === $tenant->id, 403);

        $listing->load('media');

        return view('vendor.listings.form', [
            'listing' => $listing,
            'currencies' => app(CurrencyService::class)->activeCurrencies(),
            'countries' => $this->countryOptions(),
            'utilitySubtypes' => $this->utilitySubtypes(),
            'allowedListingType' => $this->allowedListingTypeForUser(request()->user()),
            'isEdit' => true,
        ]);
    }

    public function update(Request $request, Listing $listing): RedirectResponse
    {
        $tenant = $request->user()->primaryTenant();
        abort_unless($tenant && $listing->tenant_id === $tenant->id, 403);

        $validated = $this->validateListing($request, $listing);

        if ($listing->status === 'blocked') {
            $validated['status'] = 'blocked';
        }

        $listing->update([
            ...$validated,
            'slug' => $this->uniqueSlug($validated['title'], $listing->id),
            'highlights' => $this->toArrayLines($validated['highlights_text'] ?? null),
            'amenities' => $this->toArrayLines($validated['amenities_text'] ?? null),
            'itinerary' => $this->toArrayLines($validated['itinerary_text'] ?? null),
            'inclusions' => $this->toArrayLines($validated['inclusions_text'] ?? null),
            'exclusions' => $this->toArrayLines($validated['exclusions_text'] ?? null),
            'languages' => $this->toArrayLines($validated['languages_text'] ?? null),
        ]);

        $this->syncMedia(
            $listing,
            $request->file('images', []),
            $request->input('remove_media_ids', []),
            $request->integer('cover_media_id') ?: null
        );

        return redirect()->route('vendor.listings.index')->with('status', 'Listing updated successfully.');
    }

    public function updateVisibility(Request $request, Listing $listing): RedirectResponse
    {
        $tenant = $request->user()->primaryTenant();
        abort_unless($tenant && $listing->tenant_id === $tenant->id, 403);

        $validated = $request->validate([
            'action' => ['required', Rule::in(['hide', 'show'])],
        ]);

        if ($validated['action'] === 'hide') {
            $listing->update(['status' => 'paused']);

            return redirect()->route('vendor.listings.index')->with('status', 'Listing hidden successfully.');
        }

        if ($listing->status === 'blocked') {
            return redirect()->route('vendor.listings.index')->with('warning', 'This listing is blocked by admin and cannot be made public.');
        }

        $listing->update(['status' => 'published']);

        return redirect()->route('vendor.listings.index')->with('status', 'Listing is visible again.');
    }

    public function destroy(Request $request, Listing $listing): RedirectResponse
    {
        $tenant = $request->user()->primaryTenant();
        abort_unless($tenant && $listing->tenant_id === $tenant->id, 403);

        $openBookingsCount = $listing->bookings()
            ->whereIn('status', self::OPEN_BOOKING_STATUSES)
            ->count();

        if ($openBookingsCount > 0 && ! $request->boolean('confirm_delete')) {
            return redirect()
                ->route('vendor.listings.index')
                ->with('warning', "This listing has {$openBookingsCount} open booking(s). Click delete again to confirm soft delete.")
                ->with('confirm_delete_listing_id', $listing->id);
        }

        $listing->update(['status' => 'paused']);
        $listing->delete();

        return redirect()->route('vendor.listings.index')->with('status', 'Listing deleted (soft delete).');
    }

    protected function validateListing(Request $request, ?Listing $existingListing = null): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'min:5', 'max:120'],
            'type' => ['required', Rule::in(['tour', 'utility'])],
            'subtype' => ['nullable', 'string', 'max:120', Rule::in(array_keys($this->utilitySubtypes())), 'required_if:type,utility'],
            'summary' => ['nullable', 'string', 'max:320'],
            'description' => ['required', 'string', 'min:30', 'max:5000'],
            'city' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
            'price_from' => ['required', 'numeric', 'min:0'],
            'currency_code' => ['required', 'string', 'size:3', 'exists:currencies,code'],
            'pricing_unit' => ['nullable', 'string', 'max:80'],
            'duration_label' => ['nullable', 'string', 'max:120'],
            'group_size_label' => ['nullable', 'string', 'max:120'],
            'status' => [
                'required',
                'in:draft,pending_review,published,paused,blocked',
                function (string $attribute, mixed $value, \Closure $fail) use ($existingListing): void {
                    if ((string) $value === 'blocked' && (! $existingListing || $existingListing->status !== 'blocked')) {
                        $fail('Only admin can set listing status to blocked.');
                    }
                },
            ],
            'is_featured' => ['nullable', 'boolean'],
            'cancellation_policy' => ['nullable', 'string', 'max:2000'],
            'booking_rules' => ['nullable', 'string', 'max:2000'],
            'highlights_text' => ['nullable', 'string', 'max:5000'],
            'amenities_text' => ['nullable', 'string', 'max:5000'],
            'itinerary_text' => ['nullable', 'string', 'max:8000'],
            'inclusions_text' => ['nullable', 'string', 'max:5000'],
            'exclusions_text' => ['nullable', 'string', 'max:5000'],
            'languages_text' => ['nullable', 'string', 'max:1500'],
            'images' => ['nullable', 'array', 'max:'.self::MAX_IMAGE_COUNT],
            'images.*' => [
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:'.self::MAX_IMAGE_FILE_SIZE_KB,
                'dimensions:min_width='.self::MIN_IMAGE_WIDTH.',min_height='.self::MIN_IMAGE_HEIGHT,
            ],
            'remove_media_ids' => ['nullable', 'array'],
            'remove_media_ids.*' => ['integer', 'exists:listing_media,id'],
            'cover_media_id' => ['nullable', 'integer', 'exists:listing_media,id'],
        ], [
            'images.max' => 'Maximum '.self::MAX_IMAGE_COUNT.' images are allowed per listing.',
            'images.*.mimes' => 'Images must be JPG, JPEG, PNG, or WEBP.',
            'images.*.max' => 'Each image must be 5MB or smaller.',
            'images.*.dimensions' => 'Each image must be at least '.self::MIN_IMAGE_WIDTH.'x'.self::MIN_IMAGE_HEIGHT.' pixels.',
        ]);

        if ($validated['type'] !== 'utility') {
            $validated['subtype'] = null;
        }

        $this->validateStructuredLines($validated);
        return $validated;
    }

    protected function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: Str::lower(Str::random(8));
        $slug = $base;
        $counter = 1;

        while (Listing::withTrashed()
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    protected function toArrayLines(?string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $value))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->values()
            ->all();
    }

    protected function syncMedia(
        Listing $listing,
        array $uploadedImages,
        array $removeMediaIds = [],
        ?int $coverMediaId = null
    ): void
    {
        $listing->loadMissing('media');

        $existingMedia = $listing->media
            ->sortBy('sort_order')
            ->values();

        $removeIds = collect($removeMediaIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($removeIds->isNotEmpty()) {
            $invalidIds = $removeIds->diff($existingMedia->pluck('id'));
            if ($invalidIds->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'remove_media_ids' => 'Some selected gallery images are invalid.',
                ]);
            }
        }

        $newImages = collect($uploadedImages)
            ->filter(fn ($image) => $image instanceof UploadedFile)
            ->values();

        $remainingCount = $existingMedia->count() - $removeIds->count();
        if ($remainingCount + $newImages->count() > self::MAX_IMAGE_COUNT) {
            throw ValidationException::withMessages([
                'images' => 'Maximum '.self::MAX_IMAGE_COUNT.' images are allowed per listing.',
            ]);
        }

        if ($removeIds->isNotEmpty()) {
            $mediaToRemove = $existingMedia->filter(fn (ListingMedia $media) => $removeIds->contains((int) $media->id));

            foreach ($mediaToRemove as $media) {
                $publicPath = $this->mediaUrlToPublicPath((string) $media->url);
                if ($publicPath) {
                    Storage::disk('public')->delete($publicPath);
                }

                $media->delete();
            }
        }

        if ($newImages->isNotEmpty()) {
            $nextSortOrder = (int) ($listing->media()->max('sort_order') ?? 0);

            foreach ($newImages as $index => $image) {
                $storedPath = $image->store('listings/'.$listing->id, 'public');
                $url = '/storage/'.$storedPath;

                ListingMedia::create([
                    'listing_id' => $listing->id,
                    'type' => 'image',
                    'url' => $url,
                    'thumbnail_url' => $url,
                    'alt_text' => $listing->title,
                    'caption' => "Gallery image ".($nextSortOrder + $index + 1),
                    'sort_order' => $nextSortOrder + $index + 1,
                    'is_cover' => false,
                ]);
            }
        }

        $this->normalizeMediaOrder($listing);

        if ($coverMediaId) {
            $this->setCoverMedia($listing, $coverMediaId);
        }
    }

    protected function normalizeMediaOrder(Listing $listing): void
    {
        $mediaItems = $listing->media()->orderBy('sort_order')->orderBy('id')->get();

        foreach ($mediaItems as $index => $media) {
            $sortOrder = $index + 1;
            $isCover = $index === 0;

            if ((int) $media->sort_order !== $sortOrder || (bool) $media->is_cover !== $isCover) {
                $media->update([
                    'sort_order' => $sortOrder,
                    'is_cover' => $isCover,
                ]);
            }
        }
    }

    protected function setCoverMedia(Listing $listing, int $coverMediaId): void
    {
        $coverMedia = $listing->media()->whereKey($coverMediaId)->first();
        if (! $coverMedia) {
            throw ValidationException::withMessages([
                'cover_media_id' => 'Selected cover image is not available for this listing.',
            ]);
        }

        $listing->media()->where('is_cover', true)->update(['is_cover' => false]);
        $coverMedia->update(['is_cover' => true]);
    }

    protected function mediaUrlToPublicPath(string $url): ?string
    {
        $path = (string) parse_url($url, PHP_URL_PATH);
        if (str_starts_with($path, '/storage/')) {
            return Str::after($path, '/storage/');
        }

        return null;
    }

    protected function allowedListingTypeForUser(User $user): string
    {
        return in_array($user->user_role, [User::ROLE_UTILITY_OWNER, User::ROLE_UTILITY_STAFF], true)
            ? 'utility'
            : 'tour';
    }

    protected function approvedTenantFor(User $user): Tenant
    {
        $tenant = $user->primaryTenant();
        abort_unless(
            $tenant && $tenant->status === 'approved',
            403,
            'Your account requires admin approval before you can create listings.'
        );

        return $tenant;
    }

    protected function utilitySubtypes(): array
    {
        return [
            'hotel_accommodation' => 'Hotel / Accommodation',
            'transport' => 'Transport',
            'attraction_experience' => 'Attraction / Experience',
            'event_space' => 'Event Space',
        ];
    }

    protected function countryOptions(): array
    {
        return [
            'Algeria',
            'Australia',
            'Botswana',
            'Brazil',
            'Cameroon',
            'Canada',
            'China',
            'Cote d\'Ivoire',
            'Egypt',
            'Ethiopia',
            'France',
            'Germany',
            'Ghana',
            'India',
            'Indonesia',
            'Italy',
            'Japan',
            'Kenya',
            'Malaysia',
            'Mauritius',
            'Mexico',
            'Morocco',
            'Namibia',
            'Netherlands',
            'New Zealand',
            'Nigeria',
            'Portugal',
            'Qatar',
            'Rwanda',
            'Saudi Arabia',
            'Senegal',
            'Seychelles',
            'Singapore',
            'South Africa',
            'Spain',
            'Switzerland',
            'Tanzania',
            'Thailand',
            'Tunisia',
            'Uganda',
            'United Arab Emirates',
            'United Kingdom',
            'United States',
            'Zambia',
            'Zimbabwe',
        ];
    }

    protected function validateStructuredLines(array $validated): void
    {
        $fields = [
            'highlights_text' => 'highlights',
            'amenities_text' => 'amenities',
            'itinerary_text' => 'itinerary',
            'inclusions_text' => 'inclusions',
            'exclusions_text' => 'exclusions',
            'languages_text' => 'languages',
        ];

        foreach ($fields as $field => $label) {
            $lines = $this->toArrayLines($validated[$field] ?? null);

            if (count($lines) > self::MAX_LINE_ITEM_COUNT) {
                throw ValidationException::withMessages([
                    $field => "Too many {$label} entries. Maximum ".self::MAX_LINE_ITEM_COUNT." lines.",
                ]);
            }

            foreach ($lines as $line) {
                if (mb_strlen($line) > self::MAX_LINE_ITEM_LENGTH) {
                    throw ValidationException::withMessages([
                        $field => ucfirst($label)." entries must be ".self::MAX_LINE_ITEM_LENGTH." characters or fewer.",
                    ]);
                }
            }
        }
    }

}

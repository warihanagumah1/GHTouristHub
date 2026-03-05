<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight">Admin • Tourist Attractions</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <x-alert variant="success">{{ session('status') }}</x-alert>
            @endif
            @if ($errors->any())
                <x-alert variant="danger">{{ $errors->first() }}</x-alert>
            @endif

            <x-card title="Add Region">
                <form method="POST" action="{{ route('admin.attractions.regions.store') }}" class="grid gap-3 md:grid-cols-4">
                    @csrf
                    <x-text-input name="name" placeholder="Region name" class="md:col-span-1" required />
                    <x-text-input name="overview" placeholder="Short region overview" class="md:col-span-2" />
                    <label class="inline-flex items-center gap-2 text-sm text-primary/75">
                        <input type="checkbox" name="is_published" value="1" checked>
                        Published
                    </label>
                    <div class="md:col-span-4">
                        <x-button type="submit" variant="secondary">Add Region</x-button>
                    </div>
                </form>
            </x-card>

            @foreach ($regions as $region)
                <x-card :title="$region->name">
                    <form method="POST" action="{{ route('admin.attractions.regions.update', $region) }}" class="mb-4 grid gap-3 rounded-lg border border-slate-200 bg-slate-50 p-3 md:grid-cols-4">
                        @csrf
                        @method('PUT')
                        <x-text-input name="name" :value="$region->name" required />
                        <x-text-input name="overview" :value="$region->overview" class="md:col-span-2" />
                        <x-select-input name="is_published">
                            <option value="1" @selected($region->is_published)>Published</option>
                            <option value="0" @selected(! $region->is_published)>Hidden</option>
                        </x-select-input>
                        <div class="md:col-span-4">
                            <x-button type="submit" variant="outline">Update Region</x-button>
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-200 text-left text-primary/70">
                                    <th class="py-2 pe-4">Attraction</th>
                                    <th class="py-2 pe-4">City</th>
                                    <th class="py-2 pe-4">Summary</th>
                                    <th class="py-2 pe-4">Order</th>
                                    <th class="py-2 pe-4">Flags</th>
                                    <th class="py-2 pe-4">Update</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($region->attractions as $attraction)
                                    <tr class="border-b border-slate-100 align-top">
                                        <td class="py-3 pe-4 text-primary">{{ $attraction->name }}</td>
                                        <td class="py-3 pe-4 text-primary/75">{{ $attraction->city ?: '—' }}</td>
                                        <td class="py-3 pe-4 text-primary/75">{{ $attraction->summary ?: '—' }}</td>
                                        <td class="py-3 pe-4 text-primary/75">{{ $attraction->sort_order }}</td>
                                        <td class="py-3 pe-4 text-primary/75">
                                            {{ $attraction->is_featured ? 'Featured' : 'Standard' }}
                                            •
                                            {{ $attraction->is_published ? 'Published' : 'Hidden' }}
                                        </td>
                                        <td class="py-3 pe-4">
                                            <form method="POST" action="{{ route('admin.attractions.update', $attraction) }}" class="grid gap-2">
                                                @csrf
                                                @method('PUT')
                                                <x-text-input name="name" :value="$attraction->name" class="text-xs" required />
                                                <x-text-input name="city" :value="$attraction->city" class="text-xs" placeholder="City" />
                                                <x-text-input name="address" :value="$attraction->address" class="text-xs" placeholder="Address" />
                                                <x-text-input name="hero_image_url" :value="$attraction->hero_image_url" class="text-xs" placeholder="Hero image URL" />
                                                <x-text-input name="summary" :value="$attraction->summary" class="text-xs" placeholder="Summary" />
                                                <x-textarea-input name="description" rows="2" class="text-xs" placeholder="Description">{{ $attraction->description }}</x-textarea-input>
                                                <x-textarea-input name="gallery_images_text" rows="2" class="text-xs" placeholder="Gallery image URLs (one per line)">{{ implode(PHP_EOL, $attraction->gallery_images ?? []) }}</x-textarea-input>
                                                <div class="grid gap-2 md:grid-cols-2">
                                                    <x-text-input name="visiting_hours" :value="$attraction->visiting_hours" class="text-xs" placeholder="Visiting hours" />
                                                    <x-text-input name="entry_fee" :value="$attraction->entry_fee" class="text-xs" placeholder="Entry fee" />
                                                    <x-text-input name="best_time_to_visit" :value="$attraction->best_time_to_visit" class="text-xs" placeholder="Best time to visit" />
                                                    <x-text-input name="contact_info" :value="$attraction->contact_info" class="text-xs" placeholder="Contact info" />
                                                    <x-text-input name="website_url" :value="$attraction->website_url" class="text-xs md:col-span-2" placeholder="Website URL" />
                                                </div>
                                                <x-textarea-input name="how_to_get_there" rows="2" class="text-xs" placeholder="How to get there">{{ $attraction->how_to_get_there }}</x-textarea-input>
                                                <x-textarea-input name="travel_tips" rows="2" class="text-xs" placeholder="Travel tips">{{ $attraction->travel_tips }}</x-textarea-input>
                                                <x-textarea-input name="safety_notes" rows="2" class="text-xs" placeholder="Safety notes">{{ $attraction->safety_notes }}</x-textarea-input>
                                                <x-textarea-input name="featured_activities_text" rows="2" class="text-xs" placeholder="Featured activities (one per line)">{{ implode(PHP_EOL, $attraction->featured_activities ?? []) }}</x-textarea-input>
                                                <x-textarea-input name="nearby_places_text" rows="2" class="text-xs" placeholder="Nearby places (one per line)">{{ implode(PHP_EOL, $attraction->nearby_places ?? []) }}</x-textarea-input>
                                                <div class="grid gap-2 md:grid-cols-3">
                                                    <x-text-input type="number" name="sort_order" :value="$attraction->sort_order" class="text-xs" placeholder="Sort order" />
                                                    <x-select-input name="is_featured" class="text-xs">
                                                        <option value="1" @selected($attraction->is_featured)>Featured</option>
                                                        <option value="0" @selected(! $attraction->is_featured)>Standard</option>
                                                    </x-select-input>
                                                    <x-select-input name="is_published" class="text-xs">
                                                        <option value="1" @selected($attraction->is_published)>Published</option>
                                                        <option value="0" @selected(! $attraction->is_published)>Hidden</option>
                                                    </x-select-input>
                                                </div>
                                                <x-button type="submit" variant="outline" class="text-[10px]">Save</x-button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-4 text-center text-primary/70">No attractions in this region yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <form method="POST" action="{{ route('admin.attractions.store', $region) }}" class="mt-4 grid gap-2 rounded-lg border border-slate-200 bg-slate-50 p-3 md:grid-cols-4">
                        @csrf
                        <x-text-input name="name" placeholder="New attraction name" required />
                        <x-text-input name="city" placeholder="City" />
                        <x-text-input name="address" placeholder="Address" />
                        <x-text-input name="hero_image_url" placeholder="Hero image URL" />
                        <x-text-input name="summary" placeholder="Short summary" class="md:col-span-2" />
                        <x-textarea-input name="description" rows="2" class="md:col-span-4" placeholder="Description (optional)"></x-textarea-input>
                        <x-textarea-input name="gallery_images_text" rows="2" class="md:col-span-4" placeholder="Gallery image URLs (one per line)"></x-textarea-input>
                        <x-text-input name="visiting_hours" placeholder="Visiting hours" />
                        <x-text-input name="entry_fee" placeholder="Entry fee" />
                        <x-text-input name="best_time_to_visit" placeholder="Best time to visit" />
                        <x-text-input name="contact_info" placeholder="Contact info" />
                        <x-text-input name="website_url" placeholder="Website URL" class="md:col-span-2" />
                        <x-textarea-input name="how_to_get_there" rows="2" class="md:col-span-4" placeholder="How to get there"></x-textarea-input>
                        <x-textarea-input name="travel_tips" rows="2" class="md:col-span-4" placeholder="Travel tips"></x-textarea-input>
                        <x-textarea-input name="safety_notes" rows="2" class="md:col-span-4" placeholder="Safety notes"></x-textarea-input>
                        <x-textarea-input name="featured_activities_text" rows="2" class="md:col-span-4" placeholder="Featured activities (one per line)"></x-textarea-input>
                        <x-textarea-input name="nearby_places_text" rows="2" class="md:col-span-4" placeholder="Nearby places (one per line)"></x-textarea-input>
                        <x-text-input type="number" name="sort_order" placeholder="Sort order" />
                        <label class="inline-flex items-center gap-2 text-sm text-primary/75">
                            <input type="checkbox" name="is_featured" value="1">
                            Featured
                        </label>
                        <label class="inline-flex items-center gap-2 text-sm text-primary/75">
                            <input type="checkbox" name="is_published" value="1" checked>
                            Published
                        </label>
                        <div>
                            <x-button type="submit" variant="secondary">Add Attraction</x-button>
                        </div>
                    </form>
                </x-card>
            @endforeach
        </div>
    </div>
</x-app-layout>

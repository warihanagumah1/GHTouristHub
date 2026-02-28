<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight">Admin • Listings Moderation</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <x-alert variant="success">{{ session('status') }}</x-alert>
            @endif

            <x-card title="Moderation Filters">
                <form method="GET" class="grid gap-3 md:grid-cols-5">
                    <x-text-input name="q" :value="request('q')" placeholder="Search listing title" />
                    <x-select-input name="type">
                        <option value="">All types</option>
                        <option value="tour" @selected(request('type') === 'tour')>Tour</option>
                        <option value="utility" @selected(request('type') === 'utility')>Utility</option>
                    </x-select-input>
                    <x-select-input name="status">
                        <option value="">All statuses</option>
                        @foreach (['draft', 'pending_review', 'published', 'paused', 'blocked'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </x-select-input>
                    <x-select-input name="featured">
                        <option value="">All featured states</option>
                        <option value="1" @selected(request('featured') === '1')>Featured only</option>
                        <option value="0" @selected(request('featured') === '0')>Not featured</option>
                    </x-select-input>
                    <x-button type="submit" variant="secondary">Apply Filters</x-button>
                </form>
            </x-card>

            <x-card title="Listings">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-left text-primary/70">
                                <th class="py-2 pe-4">Title</th>
                                <th class="py-2 pe-4">Vendor</th>
                                <th class="py-2 pe-4">Type</th>
                                <th class="py-2 pe-4">Status</th>
                                <th class="py-2 pe-4">Featured</th>
                                <th class="py-2 pe-4">Price</th>
                                <th class="py-2 pe-4">Update</th>
                                <th class="py-2 pe-4">Block / Unblock</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($listings as $listing)
                                <tr class="border-b border-slate-100 align-top">
                                    <td class="py-3 pe-4 text-primary">{{ $listing->title }}</td>
                                    <td class="py-3 pe-4 text-primary/75">{{ $listing->tenant->name }}</td>
                                    <td class="py-3 pe-4 capitalize text-primary/75">{{ $listing->type }}</td>
                                    <td class="py-3 pe-4 capitalize text-primary/75">{{ str_replace('_', ' ', $listing->status) }}</td>
                                    <td class="py-3 pe-4">
                                        <form method="POST" action="{{ route('admin.listings.featured', $listing) }}" class="flex items-center gap-2">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="is_featured" value="{{ $listing->is_featured ? 0 : 1 }}">
                                            <x-badge :variant="$listing->is_featured ? 'primary' : 'tertiary'">
                                                {{ $listing->is_featured ? 'Featured' : 'Standard' }}
                                            </x-badge>
                                            <x-button type="submit" variant="outline" class="text-[10px]">
                                                {{ $listing->is_featured ? 'Unfeature' : 'Feature' }}
                                            </x-button>
                                        </form>
                                    </td>
                                    <td class="py-3 pe-4 text-primary/80">
                                        <x-money :amount="$listing->price_from" :from="$listing->currency_code" show-original />
                                    </td>
                                    <td class="py-3 pe-4">
                                        <form method="POST" action="{{ route('admin.listings.status', $listing) }}" class="flex items-center gap-2">
                                            @csrf
                                            @method('PUT')
                                            <x-select-input name="status" class="w-40 text-xs">
                                                @foreach (['draft', 'pending_review', 'published', 'paused', 'blocked'] as $status)
                                                    <option value="{{ $status }}" @selected($listing->status === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                                @endforeach
                                            </x-select-input>
                                            <x-button type="submit" variant="outline" class="text-[10px]">Save</x-button>
                                        </form>
                                    </td>
                                    <td class="py-3 pe-4">
                                        <form method="POST" action="{{ route('admin.listings.block', $listing) }}">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="is_blocked" value="{{ $listing->status === 'blocked' ? 0 : 1 }}">
                                            <x-button type="submit" :variant="$listing->status === 'blocked' ? 'secondary' : 'danger'" class="text-[10px]">
                                                {{ $listing->status === 'blocked' ? 'Unblock' : 'Block' }}
                                            </x-button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-6 text-center text-primary/70">No listings found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">{{ $listings->links() }}</div>
            </x-card>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        @php
            $user = auth()->user();
            $createType = in_array($user->user_role, [\App\Models\User::ROLE_UTILITY_OWNER, \App\Models\User::ROLE_UTILITY_STAFF], true) ? 'utility' : 'tour';
        @endphp
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-primary leading-tight">Manage Listings</h2>
            <a href="{{ route('vendor.listings.create', ['type' => $createType]) }}" class="fc-btn fc-btn-secondary">Create Listing</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <x-alert variant="success" class="mb-4">{{ session('status') }}</x-alert>
            @endif
            @if (session('warning'))
                <x-alert variant="warning" class="mb-4">{{ session('warning') }}</x-alert>
            @endif

            <x-card>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-left text-primary/70">
                                <th class="py-2 pe-4">Title</th>
                                <th class="py-2 pe-4">Type</th>
                                <th class="py-2 pe-4">Status</th>
                                <th class="py-2 pe-4">Open Bookings</th>
                                <th class="py-2 pe-4">Price</th>
                                <th class="py-2 pe-4">Updated</th>
                                <th class="py-2 pe-4"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($listings as $listing)
                                <tr class="border-b border-slate-100">
                                    <td class="py-3 pe-4 font-medium text-primary">{{ $listing->title }}</td>
                                    <td class="py-3 pe-4 capitalize text-primary/75">{{ $listing->type }}</td>
                                    <td class="py-3 pe-4 capitalize text-primary/75">{{ str_replace('_', ' ', $listing->status) }}</td>
                                    <td class="py-3 pe-4 text-primary/75">{{ $listing->open_bookings_count }}</td>
                                    <td class="py-3 pe-4 text-primary/80">
                                        <x-money :amount="$listing->price_from" :from="$listing->currency_code" show-original />
                                    </td>
                                    <td class="py-3 pe-4 text-primary/70">{{ $listing->updated_at->diffForHumans() }}</td>
                                    <td class="py-3 pe-4">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <a href="{{ route('vendor.listings.edit', $listing) }}" class="fc-link">Edit</a>
                                            <a href="{{ route('marketplace.listing', $listing->slug) }}" class="fc-link" target="_blank">View</a>

                                            @php
                                                $isPaused = $listing->status === 'paused';
                                                $visibilityAction = $isPaused ? 'show' : 'hide';
                                                $visibilityVerb = $isPaused ? 'Show' : 'Hide';
                                                $visibilityMessage = $isPaused
                                                    ? 'This listing will become visible to users again.'
                                                    : 'This listing will be hidden from users until you show it again.';
                                                $deleteMessage = $listing->open_bookings_count > 0
                                                    ? "This listing has {$listing->open_bookings_count} open booking(s). Delete anyway? This is a soft delete."
                                                    : 'Delete this listing? This is a soft delete.';
                                            @endphp

                                            <x-confirm-action-form
                                                :name="'confirm-visibility-'.$listing->id"
                                                :action="route('vendor.listings.visibility', $listing)"
                                                method="PUT"
                                                :title="$visibilityVerb.' listing?'"
                                                :message="$visibilityMessage"
                                                :trigger-label="$visibilityVerb"
                                                confirm-label="Yes, continue"
                                                trigger-class="fc-link"
                                                confirm-class="fc-btn fc-btn-secondary"
                                            >
                                                <input type="hidden" name="action" value="{{ $visibilityAction }}">
                                            </x-confirm-action-form>

                                            <x-confirm-action-form
                                                :name="'confirm-delete-'.$listing->id"
                                                :action="route('vendor.listings.destroy', $listing)"
                                                method="DELETE"
                                                title="Delete listing?"
                                                :message="$deleteMessage"
                                                trigger-label="Delete"
                                                confirm-label="Yes, delete"
                                                trigger-class="text-sm font-medium text-secondary hover:underline"
                                                confirm-class="fc-btn fc-btn-danger"
                                            >
                                                <input type="hidden" name="confirm_delete" value="1">
                                            </x-confirm-action-form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-6 text-center text-primary/70">No listings created yet.</td>
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

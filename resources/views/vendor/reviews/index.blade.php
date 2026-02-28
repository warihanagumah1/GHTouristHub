<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight">
            Customer Reviews • {{ $tenant->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <x-alert variant="success">{{ session('status') }}</x-alert>
            @endif

            <div class="grid gap-4 md:grid-cols-4">
                <x-stat-card label="Total Reviews" :value="$stats['total_reviews']" />
                <x-stat-card label="Average Rating" :value="number_format((float) $stats['average_rating'], 1).'/5'" />
                <x-stat-card label="Listings Reviewed" :value="$stats['reviewed_listings']" />
                <x-stat-card label="With Comments" :value="$stats['reviews_with_comments']" />
            </div>

            <x-card title="Customer Reviews">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-left text-primary/70">
                                <th class="py-2 pe-4">Reviewer</th>
                                <th class="py-2 pe-4">Listing</th>
                                <th class="py-2 pe-4">Rating</th>
                                <th class="py-2 pe-4">Comment</th>
                                <th class="py-2 pe-4">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($reviews as $review)
                                <tr class="border-b border-slate-100 align-top">
                                    <td class="py-3 pe-4 text-primary">{{ $review->user?->name ?: 'Client' }}</td>
                                    <td class="py-3 pe-4 text-primary/80">
                                        @if ($review->listing)
                                            <a href="{{ route('marketplace.listing', $review->listing->slug) }}" class="fc-link" target="_blank" rel="noreferrer">
                                                {{ $review->listing->title }}
                                            </a>
                                        @else
                                            Listing unavailable
                                        @endif
                                    </td>
                                    <td class="py-3 pe-4 font-semibold text-primary">{{ $review->rating }}/5</td>
                                    <td class="py-3 pe-4 text-primary/80">{{ $review->comment ?: 'No written comment.' }}</td>
                                    <td class="py-3 pe-4 text-primary/70">{{ $review->created_at->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-primary/70">No reviews yet for your company.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $reviews->links() }}
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>

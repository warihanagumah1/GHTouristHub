<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight">Booking {{ $booking->booking_no }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-3 lg:px-8">
            <div class="space-y-6 lg:col-span-2">
                @if (session('status'))
                    <x-alert variant="success">{{ session('status') }}</x-alert>
                @endif
                @if ($errors->any())
                    <x-alert variant="danger">{{ $errors->first() }}</x-alert>
                @endif

                <x-card title="Listing Details">
                    <h3 class="text-lg font-semibold text-primary">{{ $booking->listing->title }}</h3>
                    <p class="mt-1 text-sm text-primary/75">{{ $booking->listing->city }}, {{ $booking->listing->country }}</p>
                    <p class="mt-4 text-sm text-primary/80">{{ $booking->listing->summary }}</p>
                    <a href="{{ route('marketplace.listing', $booking->listing->slug) }}" class="fc-link mt-3 inline-block">View listing page</a>
                </x-card>

                <x-card title="Payment Timeline">
                    <ul class="space-y-2 text-sm text-primary/80">
                        @foreach ($booking->payments as $payment)
                            <li>
                                <span class="font-semibold capitalize">{{ $payment->provider }}</span>
                                • {{ strtoupper($payment->status) }}
                                • <x-money :amount="$payment->amount" :from="$payment->currency" show-original />
                                @if ($payment->provider_reference)
                                    • Ref: {{ $payment->provider_reference }}
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </x-card>
            </div>

            <aside class="space-y-4">
                <x-card title="Booking Summary">
                    <p class="text-sm text-primary/75">Status</p>
                    <p class="text-lg font-semibold capitalize text-primary">{{ str_replace('_', ' ', $booking->status) }}</p>
                    <p class="mt-4 text-sm text-primary/75">Travelers</p>
                    <p class="text-base font-semibold text-primary">{{ $booking->travelers_count }}</p>
                    <p class="mt-4 text-sm text-primary/75">Total</p>
                    <p class="text-2xl font-bold text-primary">
                        <x-money :amount="$booking->total_amount" :from="$booking->currency" show-original />
                    </p>
                    <p class="mt-1 text-xs text-primary/60">Payment settlement currency: {{ strtoupper((string) $booking->currency) }}</p>
                </x-card>

                @php
                    $canReview = in_array($booking->status, ['paid', 'confirmed', 'completed'], true);
                    $review = $booking->review;
                @endphp
                <x-card title="Review This Company">
                    @if ($canReview)
                        <form method="POST" action="{{ route('client.bookings.review.store', $booking) }}" class="space-y-3">
                            @csrf
                            <div>
                                <x-input-label for="rating" value="Rating" />
                                <x-select-input id="rating" name="rating" class="mt-1 w-full" required>
                                    <option value="">Select rating</option>
                                    @foreach ([5, 4, 3, 2, 1] as $score)
                                        <option value="{{ $score }}" @selected((int) old('rating', $review?->rating) === $score)>
                                            {{ $score }} star{{ $score > 1 ? 's' : '' }}
                                        </option>
                                    @endforeach
                                </x-select-input>
                            </div>
                            <div>
                                <x-input-label for="comment" value="Comment (optional)" />
                                <x-textarea-input id="comment" name="comment" class="mt-1" rows="4" maxlength="1000">{{ old('comment', $review?->comment) }}</x-textarea-input>
                                <p class="mt-1 text-xs text-primary/60">Up to 1000 characters. Share your experience with this company for this listing.</p>
                            </div>
                            <x-button type="submit" variant="secondary" class="w-full">{{ $review ? 'Update Review' : 'Submit Review' }}</x-button>
                        </form>
                        @if ($review)
                            <p class="mt-3 text-xs text-primary/65">Last updated {{ $review->updated_at->diffForHumans() }}.</p>
                        @endif
                    @else
                        <p class="text-sm text-primary/75">
                            You can submit a review once this booking is paid, confirmed, or completed.
                        </p>
                    @endif
                </x-card>

                @if ($booking->status === 'pending_payment')
                    <x-card title="Complete Payment">
                        <form method="POST" action="{{ route('client.bookings.checkout', $booking) }}">
                            @csrf
                            <x-button type="submit" variant="secondary" class="w-full">Pay with Stripe</x-button>
                        </form>
                        <p class="mt-3 text-xs text-primary/65">You will be redirected to secure Stripe checkout.</p>
                    </x-card>
                @endif
            </aside>
        </div>
    </div>
</x-app-layout>

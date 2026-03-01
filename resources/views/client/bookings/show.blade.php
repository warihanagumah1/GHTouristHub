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

                @if (in_array((string) $booking->status, ['paid', 'confirmed', 'completed'], true))
                    <x-card>
                        <p class="text-sm text-emerald-700">Payment confirmed successfully.</p>
                        <p class="mt-2 text-sm text-primary/75">
                            Booking reference:
                            <span class="font-semibold text-primary">{{ $booking->booking_no }}</span>
                        </p>
                    </x-card>
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
                            </li>
                        @endforeach
                    </ul>
                </x-card>

                <x-card title="Messages with Company">
                    <div class="space-y-3">
                        @forelse ($booking->messages as $message)
                            @php
                                $mine = (int) $message->sender_user_id === (int) auth()->id();
                            @endphp
                            <div class="{{ $mine ? 'ms-auto bg-secondary/10 border-secondary/20' : 'me-auto bg-slate-50 border-slate-200' }} max-w-[85%] rounded-xl border p-3">
                                <div class="flex flex-wrap items-center gap-2 text-xs text-primary/65">
                                    <span class="font-semibold text-primary">{{ $message->sender?->name ?? 'Support' }}</span>
                                    <span>•</span>
                                    <span>{{ $message->created_at->format('M d, Y g:i A') }}</span>
                                </div>
                                <p class="mt-1 whitespace-pre-line text-sm text-primary/85">{{ $message->message }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-primary/70">No messages yet. You can message this company for booking updates.</p>
                        @endforelse
                    </div>

                    <form method="POST" action="{{ route('client.bookings.messages.store', $booking) }}" class="mt-4 space-y-2">
                        @csrf
                        <x-input-label for="message" value="Send message" />
                        <x-textarea-input id="message" name="message" rows="4" maxlength="2000" class="mt-1" placeholder="Ask a question or share arrival details...">{{ old('message') }}</x-textarea-input>
                        <p class="text-xs text-primary/60">Up to 2000 characters.</p>
                        <x-button type="submit" variant="secondary">Send Message</x-button>
                    </form>
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
                    <a href="{{ route('client.bookings.invoice', $booking) }}" class="fc-btn fc-btn-outline mt-4 w-full text-center">
                        View Invoice
                    </a>
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

                @if (in_array((string) $booking->status, ['paid', 'confirmed'], true))
                    <x-card title="Confirm Tour or Utility Service Completion">
                        <form method="POST" action="{{ route('client.bookings.complete', $booking) }}">
                            @csrf
                            <x-button type="submit" variant="outline" class="w-full">Mark Tour/Utility as Completed</x-button>
                        </form>
                        <p class="mt-3 text-xs text-primary/65">Use this after your booked service has been delivered.</p>
                    </x-card>
                @endif
            </aside>
        </div>
    </div>
</x-app-layout>

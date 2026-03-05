<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight">Manage Booking {{ $booking->booking_no }}</h2>
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

                <x-card title="Booking Details">
                    <p class="text-sm text-primary/70">Client</p>
                    <p class="text-base font-semibold text-primary">{{ $booking->user->name }} <span class="text-primary/70">({{ $booking->user->email }})</span></p>
                    <p class="mt-4 text-sm text-primary/70">Listing</p>
                    <p class="text-base font-semibold text-primary">{{ $booking->listing->title }}</p>
                    <p class="text-sm text-primary/75">{{ $booking->listing->city }}, {{ $booking->listing->country }}</p>
                    @if ($booking->special_requests)
                        <p class="mt-4 text-sm text-primary/70">Special requests</p>
                        <p class="text-sm text-primary/80 whitespace-pre-line">{{ $booking->special_requests }}</p>
                    @endif
                </x-card>

                <x-card title="Booking Messages">
                    <div class="space-y-3">
                        @forelse ($booking->messages as $message)
                            @php
                                $mine = (int) $message->sender_user_id === (int) auth()->id();
                            @endphp
                            <div class="{{ $mine ? 'ms-auto bg-secondary/10 border-secondary/20' : 'me-auto bg-slate-50 border-slate-200' }} max-w-[85%] rounded-xl border p-3">
                                <div class="flex flex-wrap items-center gap-2 text-xs text-primary/65">
                                    <span class="font-semibold text-primary">{{ $message->sender?->name ?? 'User' }}</span>
                                    <span>•</span>
                                    <span>{{ $message->created_at->format('M d, Y g:i A') }}</span>
                                </div>
                                <p class="mt-1 whitespace-pre-line text-sm text-primary/85">{{ $message->message }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-primary/70">No messages yet. Use this thread to coordinate booking details.</p>
                        @endforelse
                    </div>

                    <form method="POST" action="{{ route('vendor.bookings.messages.store', $booking) }}" class="mt-4 space-y-2">
                        @csrf
                        <x-input-label for="message" value="Send message to customer" />
                        <x-textarea-input id="message" name="message" rows="4" maxlength="2000" class="mt-1" placeholder="Share meeting point, schedule updates, and next steps...">{{ old('message') }}</x-textarea-input>
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
                    <a href="{{ route('vendor.bookings.invoice', $booking) }}" class="fc-btn fc-btn-outline mt-4 w-full text-center">View Invoice</a>
                    <form method="POST" action="{{ route('vendor.bookings.invoice.email', $booking) }}" class="mt-2">
                        @csrf
                        <x-button type="submit" variant="secondary" class="w-full">Email Invoice to Client</x-button>
                    </form>
                </x-card>

                <x-card title="Update Status">
                    <form method="POST" action="{{ route('vendor.bookings.status', $booking) }}" class="space-y-3">
                        @csrf
                        @method('PUT')
                        <x-select-input name="status" class="w-full">
                            @php
                                $allowedStatuses = match ($booking->status) {
                                    'pending_payment' => ['pending_payment', 'paid', 'cancelled'],
                                    'paid' => ['paid', 'confirmed', 'completed', 'cancelled'],
                                    'confirmed' => ['confirmed', 'completed', 'cancelled'],
                                    'completed' => ['completed'],
                                    default => ['cancelled'],
                                };
                            @endphp
                            @foreach ($allowedStatuses as $status)
                                <option value="{{ $status }}" @selected($booking->status === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                            @endforeach
                        </x-select-input>
                        <x-button type="submit" variant="outline" class="w-full">Save Status</x-button>
                    </form>
                    <p class="mt-3 text-xs text-primary/65">Set to completed after service is fully delivered.</p>
                </x-card>

                <x-card>
                    <a href="{{ route('vendor.bookings.index') }}" class="fc-link">← Back to all bookings</a>
                </x-card>
            </aside>
        </div>
    </div>
</x-app-layout>

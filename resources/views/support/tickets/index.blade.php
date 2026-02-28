<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-primary">Support Tickets</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-6xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <x-alert variant="success">{{ session('status') }}</x-alert>
            @endif
            @if ($errors->any())
                <x-alert variant="danger">{{ $errors->first() }}</x-alert>
            @endif

            <x-card title="Create Support Ticket">
                <form method="POST" action="{{ route('support.tickets.store') }}" class="grid gap-4 md:grid-cols-2">
                    @csrf
                    <div class="md:col-span-2">
                        <x-input-label for="subject" value="Subject" />
                        <x-text-input id="subject" name="subject" class="mt-1" :value="old('subject')" required maxlength="180" />
                    </div>
                    <div>
                        <x-input-label for="category" value="Category" />
                        <x-select-input id="category" name="category" class="mt-1">
                            @foreach (['general', 'booking', 'payments', 'listing', 'account'] as $category)
                                <option value="{{ $category }}" @selected(old('category', 'general') === $category)>{{ ucfirst($category) }}</option>
                            @endforeach
                        </x-select-input>
                    </div>
                    <div>
                        <x-input-label for="priority" value="Priority" />
                        <x-select-input id="priority" name="priority" class="mt-1">
                            @foreach (['low', 'medium', 'high', 'urgent'] as $priority)
                                <option value="{{ $priority }}" @selected(old('priority', 'medium') === $priority)>{{ ucfirst($priority) }}</option>
                            @endforeach
                        </x-select-input>
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="message" value="Message" />
                        <x-textarea-input id="message" name="message" rows="5" class="mt-1">{{ old('message') }}</x-textarea-input>
                    </div>
                    <div class="md:col-span-2">
                        <x-primary-button>Submit Ticket</x-primary-button>
                    </div>
                </form>
            </x-card>

            <x-card title="Your Tickets">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-left text-primary/70">
                                <th class="py-2 pe-4">ID</th>
                                <th class="py-2 pe-4">Subject</th>
                                <th class="py-2 pe-4">Priority</th>
                                <th class="py-2 pe-4">Status</th>
                                <th class="py-2 pe-4">Updated</th>
                                <th class="py-2 pe-4"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tickets as $ticket)
                                <tr class="border-b border-slate-100">
                                    <td class="py-3 pe-4 text-primary/80">#{{ $ticket->id }}</td>
                                    <td class="py-3 pe-4 font-medium text-primary">{{ $ticket->subject }}</td>
                                    <td class="py-3 pe-4 capitalize text-primary/80">{{ $ticket->priority }}</td>
                                    <td class="py-3 pe-4 capitalize text-primary/80">{{ str_replace('_', ' ', $ticket->status) }}</td>
                                    <td class="py-3 pe-4 text-primary/70">{{ $ticket->updated_at->diffForHumans() }}</td>
                                    <td class="py-3 pe-4">
                                        <a href="{{ route('support.tickets.show', $ticket) }}" class="fc-link">Open</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-6 text-center text-primary/70">No tickets created yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">{{ $tickets->links() }}</div>
            </x-card>
        </div>
    </div>
</x-app-layout>

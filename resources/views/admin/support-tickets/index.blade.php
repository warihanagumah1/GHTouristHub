<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight">Admin • Support Tickets</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <x-alert variant="success">{{ session('status') }}</x-alert>
            @endif

            <x-card title="Filters">
                <form method="GET" class="grid gap-3 md:grid-cols-4">
                    <x-text-input name="q" :value="request('q')" placeholder="Search subject/email" />
                    <x-select-input name="status">
                        <option value="">All statuses</option>
                        @foreach (['open', 'in_progress', 'resolved', 'closed'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </x-select-input>
                    <x-select-input name="priority">
                        <option value="">All priorities</option>
                        @foreach (['low', 'medium', 'high', 'urgent'] as $priority)
                            <option value="{{ $priority }}" @selected(request('priority') === $priority)>{{ ucfirst($priority) }}</option>
                        @endforeach
                    </x-select-input>
                    <x-button type="submit" variant="secondary">Apply</x-button>
                </form>
            </x-card>

            <x-card title="Tickets Queue">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-left text-primary/70">
                                <th class="py-2 pe-4">ID</th>
                                <th class="py-2 pe-4">User</th>
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
                                    <td class="py-3 pe-4 text-primary/80">{{ $ticket->user->email }}</td>
                                    <td class="py-3 pe-4 font-medium text-primary">{{ $ticket->subject }}</td>
                                    <td class="py-3 pe-4 capitalize text-primary/80">{{ $ticket->priority }}</td>
                                    <td class="py-3 pe-4 capitalize text-primary/80">{{ str_replace('_', ' ', $ticket->status) }}</td>
                                    <td class="py-3 pe-4 text-primary/70">{{ $ticket->updated_at->diffForHumans() }}</td>
                                    <td class="py-3 pe-4">
                                        <a href="{{ route('admin.support-tickets.show', $ticket) }}" class="fc-link">Respond</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-6 text-center text-primary/70">No support tickets found.</td>
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

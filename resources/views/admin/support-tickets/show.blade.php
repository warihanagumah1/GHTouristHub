<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-primary leading-tight">Admin • Ticket #{{ $ticket->id }}</h2>
            <a href="{{ route('admin.support-tickets.index') }}" class="fc-link">Back to Queue</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <x-alert variant="success">{{ session('status') }}</x-alert>
            @endif
            @if ($errors->any())
                <x-alert variant="danger">{{ $errors->first() }}</x-alert>
            @endif

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-2">
                    <x-card title="{{ $ticket->subject }}">
                        <div class="space-y-3">
                            @forelse ($ticket->comments->reverse() as $comment)
                                @continue($comment->is_internal)
                                <div class="rounded-lg border border-slate-200 p-3 {{ in_array($comment->user?->user_role, [\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_ADMIN_STAFF], true) ? 'bg-secondary/5' : 'bg-slate-50' }}">
                                    <div class="mb-1 flex items-center justify-between gap-3 text-xs text-primary/70">
                                        <span>{{ $comment->user?->name ?? 'System' }}</span>
                                        <span>{{ $comment->created_at->format('Y-m-d H:i') }}</span>
                                    </div>
                                    <p class="whitespace-pre-line text-sm text-primary/85">{{ $comment->body }}</p>
                                </div>
                            @empty
                                <p class="text-sm text-primary/70">No comments yet.</p>
                            @endforelse
                        </div>
                    </x-card>

                    <x-card title="Reply to User">
                        <form method="POST" action="{{ route('admin.support-tickets.comments.store', $ticket) }}" class="space-y-3">
                            @csrf
                            <x-textarea-input id="body" name="body" rows="4" class="mt-1">{{ old('body') }}</x-textarea-input>
                            <label class="inline-flex items-center gap-2 text-sm text-primary/75">
                                <x-checkbox-input id="is_internal" name="is_internal" value="1" />
                                Internal note (not emailed to user)
                            </label>
                            <x-primary-button>Post Comment</x-primary-button>
                        </form>
                    </x-card>
                </div>

                <div class="space-y-6">
                    <x-card title="Ticket Meta">
                        <p class="text-sm text-primary/80"><span class="font-semibold">User:</span> {{ $ticket->user->name }} ({{ $ticket->user->email }})</p>
                        <p class="mt-1 text-sm text-primary/80"><span class="font-semibold">Priority:</span> {{ ucfirst($ticket->priority) }}</p>
                        <p class="mt-1 text-sm text-primary/80"><span class="font-semibold">Category:</span> {{ ucfirst($ticket->category) }}</p>
                        <p class="mt-1 text-sm text-primary/80"><span class="font-semibold">Status:</span> {{ str_replace('_', ' ', $ticket->status) }}</p>
                        @if ($ticket->tenant)
                            <p class="mt-1 text-sm text-primary/80"><span class="font-semibold">Tenant:</span> {{ $ticket->tenant->name }}</p>
                        @endif
                    </x-card>

                    <x-card title="Update Status">
                        <form method="POST" action="{{ route('admin.support-tickets.status', $ticket) }}" class="space-y-3">
                            @csrf
                            @method('PUT')
                            <x-select-input name="status">
                                @foreach (['open', 'in_progress', 'resolved', 'closed'] as $status)
                                    <option value="{{ $status }}" @selected($ticket->status === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                @endforeach
                            </x-select-input>
                            <x-button type="submit" variant="secondary" class="w-full">Save Status</x-button>
                        </form>
                    </x-card>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

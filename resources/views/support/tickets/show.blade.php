<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold leading-tight text-primary">
                Ticket #{{ $ticket->id }} • {{ $ticket->subject }}
            </h2>
            <a href="{{ $isAdmin ? route('admin.support-tickets.index') : route('support.tickets') }}" class="fc-link">Back to Tickets</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-6xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <x-alert variant="success">{{ session('status') }}</x-alert>
            @endif
            @if ($errors->any())
                <x-alert variant="danger">{{ $errors->first() }}</x-alert>
            @endif

            <x-card title="Ticket Details">
                <div class="grid gap-3 text-sm md:grid-cols-4">
                    <p class="text-primary/80"><span class="font-semibold">Status:</span> {{ str_replace('_', ' ', $ticket->status) }}</p>
                    <p class="text-primary/80"><span class="font-semibold">Priority:</span> {{ ucfirst($ticket->priority) }}</p>
                    <p class="text-primary/80"><span class="font-semibold">Category:</span> {{ ucfirst($ticket->category) }}</p>
                    <p class="text-primary/80"><span class="font-semibold">Opened:</span> {{ $ticket->created_at->format('Y-m-d H:i') }}</p>
                </div>
            </x-card>

            <x-card title="Conversation">
                <div class="space-y-3">
                    @forelse ($ticket->comments->reverse() as $comment)
                        <div class="rounded-lg border border-slate-200 p-3 {{ $comment->user_id === auth()->id() ? 'bg-secondary/5' : 'bg-slate-50' }}">
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

            @if (! $isAdmin)
                <x-card title="Add Comment">
                    <form method="POST" action="{{ route('support.tickets.comments.store', $ticket) }}" class="space-y-3">
                        @csrf
                        <x-textarea-input id="body" name="body" rows="4" class="mt-1">{{ old('body') }}</x-textarea-input>
                        <x-primary-button>Send Reply</x-primary-button>
                    </form>
                </x-card>
            @endif
        </div>
    </div>
</x-app-layout>

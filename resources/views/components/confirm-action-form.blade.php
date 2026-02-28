@props([
    'name',
    'action',
    'method' => 'POST',
    'title',
    'message',
    'confirmLabel' => 'Confirm',
    'cancelLabel' => 'Cancel',
    'triggerLabel',
    'triggerClass' => 'fc-link',
    'confirmClass' => 'fc-btn fc-btn-secondary',
])

<button type="button" class="{{ $triggerClass }}" x-on:click.prevent="$dispatch('open-modal', '{{ $name }}')">
    {{ $triggerLabel }}
</button>

<x-modal :name="$name" maxWidth="md" focusable>
    <div class="p-6">
        <h3 class="text-lg font-semibold text-primary">{{ $title }}</h3>
        <p class="mt-2 text-sm text-primary/75">{{ $message }}</p>

        <form method="POST" action="{{ $action }}" class="mt-6 flex items-center justify-end gap-3">
            @csrf
            @if (! in_array(strtoupper($method), ['GET', 'POST'], true))
                @method($method)
            @endif

            {{ $slot }}

            <button type="button" class="fc-btn fc-btn-outline" x-on:click.prevent="$dispatch('close-modal', '{{ $name }}')">
                {{ $cancelLabel }}
            </button>
            <button type="submit" class="{{ $confirmClass }}">
                {{ $confirmLabel }}
            </button>
        </form>
    </div>
</x-modal>

@props(['type' => 'button'])

<x-button :type="$type" variant="ghost" {{ $attributes }}>
    {{ $slot }}
</x-button>

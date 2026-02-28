@props(['type' => 'button'])

<x-button :type="$type" variant="outline" {{ $attributes }}>
    {{ $slot }}
</x-button>

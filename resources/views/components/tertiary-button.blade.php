@props(['type' => 'button'])

<x-button :type="$type" variant="tertiary" {{ $attributes }}>
    {{ $slot }}
</x-button>

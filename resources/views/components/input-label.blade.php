@props(['value'])

<label {{ $attributes->merge(['class' => 'fc-label']) }}>
    {{ $value ?? $slot }}
</label>

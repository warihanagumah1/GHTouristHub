@props(['alt' => 'GH Tourist Hub logo'])

<img src="{{ asset('images/logo/logo.png') }}" alt="{{ $alt }}" {{ $attributes->merge(['class' => 'w-auto']) }}
/>

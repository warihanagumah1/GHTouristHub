@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-secondary text-start text-base font-medium text-primary bg-secondary/10 focus:outline-none focus:text-primary focus:bg-secondary/15 focus:border-secondary transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-primary/75 hover:text-primary hover:bg-primary/5 hover:border-secondary/70 focus:outline-none focus:text-primary focus:bg-primary/5 focus:border-secondary transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>

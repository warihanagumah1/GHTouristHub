@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'fc-input']) }}>

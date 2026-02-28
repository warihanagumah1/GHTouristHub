@props(['disabled' => false])

<input type="radio" @disabled($disabled) {{ $attributes->merge(['class' => 'fc-radio']) }}>

@props(['disabled' => false])

<select @disabled($disabled) {{ $attributes->merge(['class' => 'fc-select']) }}>
    {{ $slot }}
</select>

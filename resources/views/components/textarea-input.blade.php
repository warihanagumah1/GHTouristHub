@props(['disabled' => false, 'rows' => 4])

<textarea @disabled($disabled) rows="{{ $rows }}" {{ $attributes->merge(['class' => 'fc-textarea']) }}>{{ $slot }}</textarea>

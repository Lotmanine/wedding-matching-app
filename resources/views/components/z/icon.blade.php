@props(['name', 'filled' => false])

@php
    // Arrows point the other way on right-to-left (Arabic) pages.
    $directional = in_array($name, ['arrow_forward', 'arrow_back', 'chevron_right', 'chevron_left', 'arrow_right_alt', 'east', 'west', 'login', 'logout', 'open_in_new'], true);
@endphp

<span {{ $attributes->class(['material-symbols-outlined', 'is-filled' => $filled, 'rtl:-scale-x-100' => $directional]) }} aria-hidden="true">{{ $name }}</span>

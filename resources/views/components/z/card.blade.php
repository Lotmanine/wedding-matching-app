@props(['hover' => false])

@php
    $interactive = $hover ? 'cursor-pointer hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200' : '';
    $classes = 'bg-white rounded-xl border border-[#D4CFC7]/60 shadow-sm '.$interactive;
@endphp

<div {{ $attributes->class($classes) }}>{{ $slot }}</div>

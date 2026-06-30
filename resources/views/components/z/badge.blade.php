@props(['variant' => 'default'])

@php
    $variants = [
        'default' => 'bg-[#F7F4F0] text-[#6B665E] border-[#D4CFC7]',
        'accent'  => 'bg-[#F0D9B5] text-[#7A5530] border-[#C8956C]/30',
        'success' => 'bg-green-50 text-green-700 border-green-200',
        'warning' => 'bg-amber-50 text-amber-700 border-amber-200',
        'danger'  => 'bg-red-50 text-[#A84936] border-red-100',
        'muted'   => 'bg-[#F7F4F0] text-[#9A958D] border-[#D4CFC7]',
        'blue'    => 'bg-blue-50 text-blue-700 border-blue-200',
        'dark'    => 'bg-[#1A1A1A] text-white border-transparent',
    ];
    $classes = 'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold tracking-wider uppercase border '.($variants[$variant] ?? $variants['default']);
@endphp

<span {{ $attributes->class($classes) }}>{{ $slot }}</span>

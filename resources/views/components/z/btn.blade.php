@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'icon' => null,
    'type' => 'button',
])

@php
    $base = 'inline-flex items-center justify-center gap-2 font-semibold rounded-lg transition-all duration-200 active:scale-[0.97] focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed select-none';

    $variants = [
        'primary'         => 'bg-[#1A1A1A] text-white hover:bg-black shadow-sm hover:shadow-md',
        'accent'          => 'bg-[#C8956C] text-white hover:bg-[#b8845b] shadow-sm hover:shadow-md',
        'outline'         => 'bg-white border border-[#D4CFC7] text-[#1A1A1A] hover:bg-[#F7F4F0] hover:border-[#C8956C]',
        'ghost'           => 'bg-transparent text-[#1A1A1A] hover:bg-[#F7F4F0]',
        'ghost-white'     => 'bg-transparent border border-white/25 text-white hover:bg-white/10',
        'danger'          => 'bg-[#A84936] text-white hover:bg-[#963e2d] shadow-sm',
        'whatsapp'        => 'bg-[#25D366] text-white hover:bg-[#1ebe5d] shadow-sm hover:shadow-md',
        'muted'           => 'bg-[#F7F4F0] text-[#6B665E] hover:bg-[#ede9e4]',
        'accent-outline'  => 'bg-transparent border border-[#C8956C] text-[#C8956C] hover:bg-[#F0D9B5]/40',
    ];

    $sizes = [
        'xs' => 'px-3 py-1.5 text-xs',
        'sm' => 'px-4 py-2 text-sm',
        'md' => 'px-5 py-2.5 text-sm',
        'lg' => 'px-7 py-3.5 text-base',
        'xl' => 'px-10 py-4 text-lg',
    ];

    $classes = $base.' '.($variants[$variant] ?? $variants['primary']).' '.($sizes[$size] ?? $sizes['md']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        @if ($icon)<span class="material-symbols-outlined text-[1.1em] leading-none {{ in_array($icon, ['arrow_forward', 'arrow_back', 'chevron_right', 'chevron_left', 'login', 'logout'], true) ? 'rtl:-scale-x-100' : '' }}" aria-hidden="true">{{ $icon }}</span>@endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>
        @if ($icon)<span class="material-symbols-outlined text-[1.1em] leading-none {{ in_array($icon, ['arrow_forward', 'arrow_back', 'chevron_right', 'chevron_left', 'login', 'logout'], true) ? 'rtl:-scale-x-100' : '' }}" aria-hidden="true">{{ $icon }}</span>@endif
        {{ $slot }}
    </button>
@endif

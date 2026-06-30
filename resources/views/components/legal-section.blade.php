@props(['title'])

<div>
    <h2 class="font-serif text-2xl font-bold text-[#1A1A1A] mb-4 pb-3 border-b border-[#D4CFC7]/40">{{ $title }}</h2>
    <div class="space-y-3 text-[#6B665E] leading-relaxed text-[15px]">{{ $slot }}</div>
</div>

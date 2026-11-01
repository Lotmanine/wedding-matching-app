# Wedding Matching App: Design System (clay / ink editorial identity)

The foundation lives in `tailwind.config.js` (named `z-*` colors and fonts), `resources/css/app.css` (base styles) and `resources/views/components/z/*` (reusable components).

## Palette

Use the hex directly as Tailwind arbitrary values, for example `bg-[#1A1A1A]`, or the named token.

| Role | Hex | Tailwind token |
|------|-----|----------------|
| Page background | `#FDFAF6` | `z-bg` |
| Surface (cards) | `#FFFFFF` | `z-surface` |
| Low surface / muted bg | `#F7F4F0` | `z-low` |
| Sand / accent fill | `#F0D9B5` | `z-alight` |
| Outline / border | `#D4CFC7` | `z-outline` |
| Ink / primary text and primary buttons | `#1A1A1A` | `z-primary` |
| Secondary text | `#6B665E` | `z-secondary` |
| Tertiary / faint text | `#9A958D` | `z-tertiary` |
| Clay accent | `#C8956C` | `z-accent` |
| Clay hover | `#B8845B` | `z-accent-dark` |
| Deep clay text | `#7A5530` | `z-accent-deep` |
| Danger | `#A84936` | `z-danger` |
| Danger hover | `#963E2D` | `z-danger-dark` |
| WhatsApp | `#25D366` | `z-whatsapp` |

**Semantics:** primary action = ink `#1A1A1A`. Accent, highlight and active states = clay `#C8956C`. Active nav pills = sand `#F0D9B5` (provider) or clay (admin).

## Type

- Display, headings, wordmark, big numbers: **Noto Serif**, class `font-serif`.
- Body and UI: **Plus Jakarta Sans** (default `font-sans`, already the body font).
- Eyebrows and labels: uppercase, `tracking-widest`, `text-[10px]` or `text-[11px]`, `font-bold`, color `#9A958D`.

## Icons

Material Symbols Outlined. Use the component:
`<x-z.icon name="dashboard" />` or `<x-z.icon name="check" :filled="true" class="text-xl" />`.

## Reusable components (in `resources/views/components/z/`)

- `<x-z.btn variant="primary|accent|outline|ghost|ghost-white|danger|whatsapp|muted|accent-outline" size="xs|sm|md|lg|xl" :href="route(...)" icon="send">Label</x-z.btn>` renders `<a>` if `href` is given, otherwise `<button>`. Pass `type="submit"` for forms.
- `<x-z.badge variant="default|accent|success|warning|danger|muted|blue|dark">…</x-z.badge>`
- `<x-z.card hover>…</x-z.card>`: white rounded-xl card with outline and shadow.
- `<x-z.icon name="…" :filled="bool" />`

Cards: `bg-white rounded-xl border border-[#D4CFC7]/60 shadow-sm`. Inputs:
`w-full px-4 py-3.5 bg-white border border-[#D4CFC7] rounded-lg text-sm outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15`.

## Rules for UI changes

1. A visual change touches presentation only: markup structure, Tailwind classes, icons, copy styling.
2. Preserve every `wire:model`, `wire:click`, `wire:submit`, `@entangle`, `x-data`, `@csrf`, `@method`, `route(...)`, `{{ $variable }}`, `@foreach`/`@if`, form field `name=` attribute, validation and `@error` block, and `__('...')` translation call.
3. Keep the same `@extends('layouts.…')` / `@section('content')`, or Livewire root element, that the file already uses.
4. Keep all amounts, currency and data formatting as they are. Prices go through `App\Support\Money`.
5. Every visible string needs its English and French versions in `lang/`.

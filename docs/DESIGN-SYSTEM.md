# Zawaj Pro — Design System (clay / ink editorial identity)

Source of truth: the React/JSX mockups in `../claude-design/`. This document is the
contract for porting them faithfully into Blade/Livewire. Foundation already wired:
`tailwind.config.js` (named `z-*` colors + fonts), `resources/css/app.css` (base styles),
and `resources/views/components/z/*` (reusable components). Layouts are done.

## Palette (use the hex directly as Tailwind arbitrary values, e.g. `bg-[#1A1A1A]`)

| Role | Hex | Tailwind token |
|------|-----|----------------|
| Page background | `#FDFAF6` | `z-bg` |
| Surface (cards) | `#FFFFFF` | `z-surface` |
| Low surface / muted bg | `#F7F4F0` | `z-low` |
| Sand / accent fill | `#F0D9B5` | `z-alight` |
| Outline / border | `#D4CFC7` | `z-outline` |
| Ink / primary text + primary buttons | `#1A1A1A` | `z-primary` |
| Secondary text | `#6B665E` | `z-secondary` |
| Tertiary / faint text | `#9A958D` | `z-tertiary` |
| Clay accent | `#C8956C` | `z-accent` |
| Clay hover | `#B8845B` | `z-accent-dark` |
| Deep clay text | `#7A5530` | `z-accent-deep` |
| Danger | `#A84936` | `z-danger` |
| WhatsApp | `#25D366` | `z-whatsapp` |

**Semantics:** primary action = ink `#1A1A1A`. Accent/highlight/active = clay `#C8956C`.
Active nav pills = sand `#F0D9B5` (provider) or clay (admin). Never use emerald/green or
gold `#D4A843` — those are the OLD identity being replaced.

## Type

- Display / headings / wordmark / big numbers: **Noto Serif** → class `font-serif`.
- Body / UI: **Plus Jakarta Sans** (default `font-sans`, already the body font).
- Eyebrows/labels: uppercase, `tracking-widest`, `text-[10px]`/`text-[11px]`, `font-bold`, color `#9A958D`.

## Icons

Material Symbols Outlined (loaded in every layout). Use the component:
`<x-z.icon name="dashboard" />` or `<x-z.icon name="check" :filled="true" class="text-xl" />`.

## Reusable components (in `resources/views/components/z/`)

- `<x-z.btn variant="primary|accent|outline|ghost|ghost-white|danger|whatsapp|muted|accent-outline" size="xs|sm|md|lg|xl" :href="route(...)" icon="send">Label</x-z.btn>` — renders `<a>` if `href` given, else `<button>`. Pass `type="submit"` for forms.
- `<x-z.badge variant="default|accent|success|warning|danger|muted|blue|dark">…</x-z.badge>`
- `<x-z.card hover>…</x-z.card>` — white rounded-xl card with outline + shadow.
- `<x-z.icon name="…" :filled="bool" />`

Cards: `bg-white rounded-xl border border-[#D4CFC7]/60 shadow-sm`. Inputs:
`w-full px-4 py-3.5 bg-white border border-[#D4CFC7] rounded-lg text-sm outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15`.

## PORTING RULES (critical — do not break functionality)

1. **Only change presentation** — markup structure, Tailwind classes, icons, copy styling.
2. **Preserve EVERY** `wire:model`, `wire:click`, `wire:submit`, `@entangle`, `x-data`,
   `@csrf`, `@method`, `route(...)`, `{{ $variable }}`, `@foreach/@if`, form field `name=`
   attributes, validation/`@error` blocks, and existing `__('...')` translation calls.
3. Keep the same `@extends('layouts.…')` / `@section('content')` (or Livewire root element)
   the file already uses. Do not change controllers, routes, or Livewire PHP classes.
4. Replace old green/gold classes (`bg-brand`, `text-brand`, `emerald-*`, `text-gold`,
   `font-display`, `bg-ink`) with the clay/ink equivalents above. `font-display` → `font-serif`.
5. Keep all amounts/currency/data formatting exactly as-is.
6. Do NOT run `npm run build` or any build — the orchestrator builds at the end.

@extends('layouts.admin')

@section('content')
    <div class="max-w-6xl">
        <div class="mb-6">
            <h1 class="font-serif text-2xl font-bold text-[#1A1A1A]">Feedback des prestataires</h1>
            <p class="text-sm text-[#6B665E] mt-1">Messages envoyés depuis l'espace prestataire : bugs, remarques, suggestions.</p>
        </div>

        {{-- Status filter tabs --}}
        <div class="flex items-center gap-1 p-1 bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm w-fit mb-5">
            @foreach (['new' => 'Nouveaux', 'handled' => 'Traités', 'all' => 'Tous'] as $st => $label)
                <a href="{{ route('admin.feedback.index', ['status' => $st]) }}"
                   @class([
                       'flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold transition-all',
                       'bg-[#1A1A1A] text-white' => $status === $st,
                       'text-[#6B665E] hover:text-[#1A1A1A]' => $status !== $st,
                   ])>
                    {{ $label }}
                    @if (isset($counts[$st]) && $counts[$st] > 0)
                        <span @class([
                            'text-[10px] font-bold px-1.5 py-0.5 rounded-full',
                            'bg-[#C8956C] text-white' => $status === $st,
                            'bg-[#F0D9B5] text-[#7A5530]' => $status !== $st,
                        ])>{{ $counts[$st] }}</span>
                    @endif
                </a>
            @endforeach
        </div>

        @if ($feedback->isEmpty())
            <div class="bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm p-10 text-center text-sm text-[#9A958D]">
                Aucun message.
            </div>
        @else
            <div class="space-y-4">
                @foreach ($feedback as $f)
                    <div class="bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm p-5">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span @class([
                                        'text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full',
                                        'bg-[#F0D9B5] text-[#7A5530]' => $f->status === 'new',
                                        'bg-green-100 text-green-800' => $f->status === 'handled',
                                    ])>{{ $f->status === 'new' ? 'Nouveau' : 'Traité' }}</span>
                                    <span class="font-semibold text-sm text-[#1A1A1A]">{{ $f->subject }}</span>
                                    <span class="text-xs text-[#9A958D]">· {{ $f->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-sm text-[#6B665E] mt-1.5">
                                    Prestataire :
                                    <a href="{{ route('admin.providers.show', $f->user_id) }}" class="text-[#C8956C] hover:underline">{{ $f->user?->providerProfile?->business_name ?? $f->user?->name }}</a>
                                    · {{ $f->user?->email }}
                                </p>
                                <p class="text-sm text-[#1A1A1A] mt-3 whitespace-pre-line break-words">{{ $f->message }}</p>
                                @if ($f->status === 'handled')
                                    <p class="text-xs text-[#9A958D] mt-3">
                                        Traité par {{ $f->handledBy?->name ?? 'Non renseigné' }} {{ $f->handled_at?->diffForHumans() }}
                                    </p>
                                @endif
                            </div>

                            @if ($f->status === 'new')
                                <form method="POST" action="{{ route('admin.feedback.handle', $f->id) }}" class="shrink-0">
                                    @csrf
                                    <x-z.btn type="submit" variant="accent" size="sm"><x-z.icon name="check" class="text-base" /> Marquer comme traité</x-z.btn>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-5">{{ $feedback->links() }}</div>
        @endif
    </div>
@endsection

@extends('layouts.admin')

@section('content')
    <div class="max-w-4xl" x-data>
        <h1 class="font-serif text-2xl font-bold text-[#1A1A1A]">Catalogue</h1>
        <p class="text-sm text-[#6B665E] mt-1 mb-6">Activez ou désactivez les services et les wilayas proposés. Une entrée désactivée disparaît des nouveaux formulaires (demande couple, inscription prestataire) sans toucher aux données existantes.</p>

        <form method="POST" action="{{ route('admin.catalog.update') }}" class="space-y-6">
            @csrf @method('PUT')

            {{-- Services --}}
            <div class="bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-[#9A958D]">Services (activités)</p>
                    <span class="text-xs text-[#9A958D]">{{ count($activeServices) }} / {{ count($services) }} actifs</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                    @foreach ($services as $svc)
                        <label class="flex items-center gap-2 px-3 py-2.5 rounded-lg border text-sm font-medium cursor-pointer transition-colors border-[#D4CFC7] bg-white text-[#9A958D] hover:border-[#C8956C]/50 has-[:checked]:border-[#C8956C] has-[:checked]:bg-[#F0D9B5]/40 has-[:checked]:text-[#1A1A1A]">
                            <input type="checkbox" name="active_services[]" value="{{ $svc->value }}"
                                   @checked(in_array($svc->value, $activeServices, true))
                                   class="w-3.5 h-3.5 rounded border-[#D4CFC7] text-[#C8956C] focus:ring-[#C8956C]/30" />
                            <span>{{ $svc->label() }}</span>
                        </label>
                    @endforeach
                </div>
                @error('active_services') <p class="mt-2 text-xs text-[#A84936]">{{ $message }}</p> @enderror
            </div>

            {{-- Wilayas --}}
            <div class="bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm p-5">
                <div class="flex items-center justify-between mb-1">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-[#9A958D]">Wilayas couvertes</p>
                    <span class="text-xs text-[#9A958D]">{{ $wilayas->where('active', true)->count() }} / {{ $wilayas->count() }} actives</span>
                </div>
                <div class="flex items-center gap-3 mb-4">
                    <button type="button" class="text-xs font-semibold text-[#C8956C] hover:underline"
                            @click="[...$el.closest('form').querySelectorAll('input[name=\'active_wilayas[]\']')].forEach(c => c.checked = true)">
                        Tout activer
                    </button>
                    <span class="text-[#D4CFC7]">·</span>
                    <button type="button" class="text-xs font-semibold text-[#9A958D] hover:underline"
                            @click="[...$el.closest('form').querySelectorAll('input[name=\'active_wilayas[]\']')].forEach(c => c.checked = false)">
                        Tout désactiver
                    </button>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2">
                    @foreach ($wilayas as $w)
                        <label class="flex items-center gap-2 px-3 py-2 rounded-lg border text-xs font-medium cursor-pointer transition-colors border-[#D4CFC7] bg-white text-[#9A958D] hover:border-[#C8956C]/50 has-[:checked]:border-[#C8956C] has-[:checked]:bg-[#F0D9B5]/40 has-[:checked]:text-[#1A1A1A]">
                            <input type="checkbox" name="active_wilayas[]" value="{{ $w->code }}"
                                   @checked($w->active)
                                   class="w-3.5 h-3.5 rounded border-[#D4CFC7] text-[#C8956C] focus:ring-[#C8956C]/30" />
                            <span class="truncate">{{ $w->code }} · {{ $w->name_fr }}</span>
                        </label>
                    @endforeach
                </div>
                @error('active_wilayas') <p class="mt-2 text-xs text-[#A84936]">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center justify-between">
                <p class="text-xs text-[#9A958D]">Les demandes et prestataires existants ne sont pas modifiés.</p>
                <x-z.btn type="submit" variant="accent">Enregistrer</x-z.btn>
            </div>
        </form>
    </div>
@endsection

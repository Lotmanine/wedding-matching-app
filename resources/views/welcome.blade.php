<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Zawaj Pro — Trouvez les meilleurs prestataires pour votre mariage en Algérie.">
    <title>{{ __('form.brand') }} — Algérie</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'DM Sans', system-ui, sans-serif; background: #FEFDF8; }
        .font-display { font-family: 'Playfair Display', serif; }
        .bg-brand { background-color: #1B5E20; }
        .text-brand { color: #1B5E20; }
        .border-brand { border-color: #1B5E20; }
        .bg-gold { background-color: #D4A843; }
        .text-gold { color: #D4A843; }
        .hover\:bg-brand-dark:hover { background-color: #0f3a14; }
    </style>
</head>
<body class="antialiased text-gray-800">

    <div class="min-h-screen flex flex-col">

        <header class="px-6 sm:px-10 py-5 border-b border-emerald-100/60">
            <div class="max-w-6xl mx-auto flex items-center justify-between">
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <span class="inline-block w-9 h-9 rounded-full bg-brand flex items-center justify-center text-white text-lg">💍</span>
                    <span class="font-display text-2xl font-bold text-brand tracking-tight">{{ __('form.brand') }}</span>
                </a>
                <span class="text-xs text-gray-400 uppercase tracking-widest hidden sm:inline">Algérie 🇩🇿</span>
            </div>
        </header>

        <main class="flex-1 flex items-center justify-center px-6 py-16 sm:py-24">
            <div class="max-w-3xl text-center space-y-10">

                <div class="space-y-5">
                    <span class="inline-block px-4 py-1.5 rounded-full bg-emerald-50 text-brand text-sm font-medium">
                        ✨ Plateforme n°1 pour les mariages en Algérie
                    </span>
                    <h1 class="font-display text-4xl sm:text-5xl lg:text-6xl font-bold text-gray-900 leading-[1.1] tracking-tight">
                        {{ __('form.landing_hero_title') }}
                    </h1>
                    <p class="text-lg sm:text-xl text-gray-600 max-w-2xl mx-auto leading-relaxed">
                        {{ __('form.landing_hero_sub') }}
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 justify-center items-center">
                    <a
                        href="{{ route('couple.request.create') }}"
                        class="inline-flex items-center justify-center px-8 py-4 rounded-xl bg-brand hover:bg-brand-dark text-white font-semibold text-base shadow-lg shadow-emerald-900/10 transition-all hover:scale-[1.02] w-full sm:w-auto"
                    >
                        {{ __('form.btn_start') }}
                    </a>
                    <a
                        href="{{ route('home') }}#provider-soon"
                        class="inline-flex items-center justify-center px-8 py-4 rounded-xl border-2 border-emerald-200 hover:border-brand text-brand font-semibold text-base bg-white transition-colors w-full sm:w-auto"
                    >
                        {{ __('form.landing_secondary_cta') }}
                    </a>
                </div>

                <div class="flex flex-wrap gap-x-6 gap-y-3 justify-center items-center text-sm text-gray-600 pt-4">
                    <span class="flex items-center gap-2"><span class="text-brand">✓</span> {{ __('form.landing_trust_free') }}</span>
                    <span class="flex items-center gap-2"><span class="text-brand">✓</span> {{ __('form.landing_trust_verified') }}</span>
                    <span class="flex items-center gap-2"><span class="text-brand">✓</span> 58 wilayas couvertes</span>
                </div>

                <p id="provider-soon" class="text-xs text-amber-700 bg-amber-50 inline-block px-4 py-2 rounded-full border border-amber-100">
                    ⚙️ {{ __('form.landing_secondary_notice') }}
                </p>

            </div>
        </main>

        <footer class="border-t border-emerald-100/60 px-6 py-8 bg-white/60">
            <div class="max-w-6xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3 text-sm text-gray-500">
                <p>{{ __('form.footer_copyright', ['year' => date('Y')]) }}</p>
                <div class="flex items-center gap-6">
                    <a href="#" class="hover:text-brand">{{ __('form.footer_legal') }}</a>
                    <a href="#" class="hover:text-brand">{{ __('form.footer_privacy') }}</a>
                </div>
            </div>
        </footer>

    </div>

</body>
</html>

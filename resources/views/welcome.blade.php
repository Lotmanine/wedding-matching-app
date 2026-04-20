<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Zawaj Pro — Algérie</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white antialiased">

    <div class="min-h-screen flex flex-col">

        {{-- Navigation --}}
        <nav class="bg-white border-b border-emerald-100 px-6 py-4">
            <div class="max-w-5xl mx-auto flex items-center justify-between">
                <span class="text-2xl font-bold text-emerald-700 tracking-tight">Zawaj Pro</span>
                <span class="text-xs text-gray-400 uppercase tracking-widest">Algérie 🇩🇿</span>
            </div>
        </nav>

        {{-- Hero --}}
        <main class="flex-1 flex items-center justify-center px-6 py-20">
            <div class="max-w-2xl text-center space-y-8">

                <div class="space-y-3">
                    <h1 class="text-4xl font-extrabold text-gray-900 leading-tight">
                        Trouvez les meilleurs prestataires<br>
                        <span class="text-emerald-600">pour votre mariage</span>
                    </h1>
                    <p class="text-lg text-gray-500 font-medium" dir="rtl">
                        نحن قيد البناء — Plateforme en construction
                    </p>
                </div>

                <p class="text-gray-400 text-sm">
                    La plateforme n°1 pour les mariages en Algérie. Photographes, traiteurs,
                    salles, décorateurs et bien plus — en quelques clics.
                </p>

                {{-- CTA Buttons (disabled placeholder) --}}
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <button
                        disabled
                        class="inline-flex items-center justify-center px-8 py-3 rounded-xl bg-emerald-600 text-white font-semibold text-base opacity-50 cursor-not-allowed"
                    >
                        💍 Je planifie mon mariage
                    </button>
                    <button
                        disabled
                        class="inline-flex items-center justify-center px-8 py-3 rounded-xl border-2 border-emerald-600 text-emerald-700 font-semibold text-base opacity-50 cursor-not-allowed"
                    >
                        🏢 Je suis un prestataire
                    </button>
                </div>

                <p class="text-xs text-amber-600 bg-amber-50 inline-block px-4 py-2 rounded-full">
                    ⚙️ Lancement prévu prochainement — Restez à l'écoute !
                </p>

            </div>
        </main>

        {{-- Footer --}}
        <footer class="bg-gray-50 border-t border-gray-100 px-6 py-6">
            <div class="max-w-5xl mx-auto text-center">
                <p class="text-sm text-gray-400">
                    © 2026 Zawaj Pro — Algérie &nbsp;|&nbsp;
                    <span class="text-emerald-600 font-medium">zawaj-pro.dz</span>
                </p>
            </div>
        </footer>

    </div>

</body>
</html>

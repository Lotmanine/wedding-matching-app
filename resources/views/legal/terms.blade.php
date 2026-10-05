@extends('layouts.couple')

@section('content')
<x-legal-page :title="__('Conditions d\'utilisation')" subtitle="Zawaj Pro" :updated="__('5 octobre 2026')">

    <x-legal-section :title="__('1. Objet')">
        <p>{{ __("Les présentes conditions d'utilisation (CGU) régissent l'accès et l'utilisation de la plateforme Zawaj Pro, disponible à l'adresse zawajpro.dz, par toute personne physique ou morale (ci-après « l'Utilisateur »).") }}</p>
    </x-legal-section>

    <x-legal-section :title="__('2. Acceptation des conditions')">
        <p>{{ __("L'utilisation de la plateforme vaut acceptation pleine et entière des présentes CGU. Si vous n'acceptez pas ces conditions, veuillez ne pas utiliser le service.") }}</p>
    </x-legal-section>

    <x-legal-section :title="__('3. Description du service')">
        <p>{{ __("Zawaj Pro est une plateforme de mise en relation entre des couples planifiant leur mariage et des prestataires de services événementiels basés en Algérie. La plateforme ne fournit pas elle-même les services de mariage et n'est pas partie aux contrats conclus entre couples et prestataires.") }}</p>
        <p>{{ __("Le service est ouvert progressivement, wilaya par wilaya. Pour chaque service demandé, la demande est proposée à des prestataires vérifiés de la wilaya concernée ; le premier qui l'accepte reçoit les coordonnées du couple. Zawaj Pro ne garantit pas qu'un prestataire acceptera chaque demande.") }}</p>
    </x-legal-section>

    <x-legal-section :title="__('4. Utilisation par les couples')">
        <div class="mt-2">
            <h3 class="font-semibold text-[#1A1A1A] mb-2">{{ __('4.1 Gratuité') }}</h3>
            <p>{{ __("L'utilisation du service est entièrement gratuite pour les couples. Zawaj Pro ne facture aucune commission sur les prestations réalisées.") }}</p>
        </div>
        <div class="mt-4">
            <h3 class="font-semibold text-[#1A1A1A] mb-2">{{ __('4.2 Exactitude des informations') }}</h3>
            <p>{{ __("L'Utilisateur s'engage à fournir des informations exactes et sincères lors de la soumission d'une demande. Toute utilisation frauduleuse entraînera la suppression immédiate de la demande.") }}</p>
        </div>
        <div class="mt-4">
            <h3 class="font-semibold text-[#1A1A1A] mb-2">{{ __('4.3 Usage personnel') }}</h3>
            <p>{{ __('Le service est destiné à un usage strictement personnel. Il est interdit de soumettre des demandes fictives ou à des fins commerciales non autorisées.') }}</p>
        </div>
    </x-legal-section>

    <x-legal-section :title="__('5. Utilisation par les prestataires')">
        <div class="mt-2">
            <h3 class="font-semibold text-[#1A1A1A] mb-2">{{ __('5.1 Éligibilité') }}</h3>
            <p>{{ __("Seuls les prestataires exerçant légalement en Algérie et ayant été vérifiés par l'équipe Zawaj Pro peuvent accéder aux demandes de couples.") }}</p>
        </div>
        <div class="mt-4">
            <h3 class="font-semibold text-[#1A1A1A] mb-2">{{ __('5.2 Tarifs') }}</h3>
            <p>{{ __('Zawaj Pro se réserve le droit de modifier les tarifs des crédits avec un préavis de 30 jours. Les crédits déjà achetés restent acquis.') }}</p>
        </div>
        <div class="mt-4">
            <h3 class="font-semibold text-[#1A1A1A] mb-2">{{ __('5.3 Comportement') }}</h3>
            <p>{{ __('Il est interdit de contacter les couples à des fins autres que la prestation demandée, de partager les coordonnées obtenues avec des tiers, ou de tenir des propos harcelants ou frauduleux.') }}</p>
        </div>
    </x-legal-section>

    <x-legal-section :title="__('6. Crédits et paiement (CGV)')">
        <p>{{ __("Zawaj Pro commercialise des « crédits » permettant aux prestataires d'accéder aux coordonnées des couples. Accepter une demande débite le nombre de crédits indiqué au moment de l'acceptation.") }}</p>
        <p>{!! __("<strong>Prix et paiement :</strong> les prix sont indiqués en dinars algériens (DA). Selon les modes proposés au moment de l'achat, le paiement se fait en ligne par carte CIB ou Edahabia (via Chargily Pay), ou par versement bancaire ou CCP. En cas de versement, les crédits sont ajoutés après vérification de la réception des fonds par notre équipe.") !!}</p>
        <p>{!! __("<strong>Remboursement :</strong> les crédits achetés ne sont ni transférables ni remboursables en argent. Les crédits dépensés pour une demande sont recrédités sur le compte du prestataire lorsqu'il signale cette demande et que notre équipe confirme le problème (numéro erroné ou injoignable, absence de réponse du couple, demande factice), ou en cas de dysfonctionnement technique avéré.") !!}</p>
        <p>{!! __("<strong>Reçu :</strong> un reçu est émis pour chaque achat et reste disponible dans l'espace prestataire.") !!}</p>
    </x-legal-section>

    <x-legal-section :title="__('7. Propriété intellectuelle')">
        <p>{{ __("L'ensemble des éléments de la plateforme (logo, design, textes, code) est la propriété exclusive de Zawaj Pro. Toute reproduction non autorisée est interdite.") }}</p>
    </x-legal-section>

    <x-legal-section :title="__('8. Limitation de responsabilité')">
        <p>{{ __("Zawaj Pro n'est pas responsable des manquements des prestataires, des litiges nés de l'exécution des prestations, ni des dommages indirects liés à l'utilisation du service. La responsabilité de Zawaj Pro est limitée au montant des crédits achetés le cas échéant.") }}</p>
    </x-legal-section>

    <x-legal-section :title="__('9. Modification des CGU')">
        <p>{{ __("Zawaj Pro se réserve le droit de modifier les présentes CGU à tout moment. La version en vigueur est celle publiée sur cette page ; les prestataires sont informés des changements importants via leur espace. L'utilisation continuée du service vaut acceptation des nouvelles conditions.") }}</p>
    </x-legal-section>

    <x-legal-section :title="__('10. Droit applicable')">
        <p>{{ __("Les présentes CGU sont soumises au droit algérien. Tout litige sera soumis à la compétence exclusive des tribunaux d'Alger.") }}</p>
    </x-legal-section>

</x-legal-page>
@endsection

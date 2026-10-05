@extends('layouts.couple')

@section('content')
<x-legal-page :title="__('Politique de confidentialité')" subtitle="Zawaj Pro" :updated="__('5 octobre 2026')">

    <x-legal-section :title="__('1. Identité du responsable de traitement')">
        <p>{{ __('Zawaj Pro (ci-après « la Société ») est responsable du traitement des données personnelles collectées via la plateforme zawajpro.dz. La Société est domiciliée à Alger, Algérie, et opère conformément à la loi n° 18-07 du 10 juin 2018 relative à la protection des personnes physiques dans le traitement des données à caractère personnel (loi ANPDP).') }}</p>
        <p>{!! __('Contact : <strong>privacy@zawajpro.dz</strong>') !!}</p>
    </x-legal-section>

    <x-legal-section :title="__('2. Données collectées')">
        <div class="mt-2">
            <h3 class="font-semibold text-[#1A1A1A] mb-2">{{ __('2.1 Données des couples') }}</h3>
            <p>{{ __("Lors de la soumission d'une demande, nous collectons : votre prénom, votre numéro WhatsApp (seul moyen de contact demandé : aucune adresse e-mail n'est collectée), la wilaya et, si vous l'indiquez, la commune, la date du mariage, le budget indicatif, les services recherchés et, selon le cas, le nombre d'invités et une description libre.") }}</p>
        </div>
        <div class="mt-4">
            <h3 class="font-semibold text-[#1A1A1A] mb-2">{{ __('2.2 Données des prestataires') }}</h3>
            <p>{{ __("Lors de l'inscription, nous collectons : nom et raison sociale, numéro de téléphone professionnel, adresse e-mail, wilaya(s) d'intervention, spécialité(s), liens vers portfolio ou réseaux sociaux, numéro de registre de commerce (facultatif), informations liées à vos achats de crédits.") }}</p>
        </div>
        <div class="mt-4">
            <h3 class="font-semibold text-[#1A1A1A] mb-2">{{ __('2.3 Données techniques') }}</h3>
            <p>{{ __("Lors de l'envoi d'une demande, nous enregistrons votre adresse IP sous forme anonymisée (sa dernière partie est masquée) et le type de navigateur, uniquement pour prévenir les abus. Nous n'utilisons aucun outil de mesure d'audience ni de suivi publicitaire. Les actions effectuées dans l'espace prestataire et dans l'administration sont journalisées, avec l'adresse IP, à des fins de sécurité.") }}</p>
        </div>
    </x-legal-section>

    <x-legal-section :title="__('3. Finalités du traitement')">
        <p>{{ __('Vos données sont traitées pour : (a) mettre en relation les couples avec des prestataires de mariage ; (b) gérer les comptes prestataires et les transactions de crédits ; (c) envoyer des notifications liées à votre demande ; (d) améliorer nos services ; (e) respecter nos obligations légales.') }}</p>
    </x-legal-section>

    <x-legal-section :title="__('4. Durée de conservation')">
        <p>{!! __('<strong>Données des couples :</strong> votre demande expire :days jours après son envoi. Votre prénom, votre numéro WhatsApp, votre commune et votre description sont alors effacés de notre base. Si un prestataire a accepté votre demande, il conserve vos coordonnées :days jours après son acceptation, puis elles sont effacées à leur tour. Nous ne gardons ensuite que des données anonymes (wilaya, services demandés, tranche de budget) à des fins statistiques.', ['days' => \App\Support\LocaleCount::days((int) config('zawaj.anpdp.retention_days', 14))]) !!}</p>
        <p>{!! __('<strong>Données des prestataires :</strong> conservées pendant la durée du compte, puis 3 ans après sa clôture à des fins comptables.') !!}</p>
        <p>{!! __("<strong>Données techniques :</strong> l'adresse IP anonymisée est supprimée avec la demande ; les journaux de sécurité sont conservés aussi longtemps que la sécurité de la plateforme l'exige.") !!}</p>
    </x-legal-section>

    <x-legal-section :title="__('5. Partage des données')">
        <p>{{ __("Votre numéro WhatsApp n'est communiqué qu'au prestataire qui accepte votre demande, pour le service concerné. Zawaj Pro ne vend jamais vos données à des tiers. Nous pouvons partager des données avec des sous-traitants techniques (hébergement, messagerie) dans le strict cadre de nos services : hébergement, envoi des messages WhatsApp (Meta) et, pour classer les prestataires à solliciter, un service d'intelligence artificielle qui reçoit le service demandé, la wilaya, la commune, la date, le budget et le nombre d'invités, sans votre prénom ni votre numéro, ainsi que le profil professionnel des prestataires candidats.") }}</p>
    </x-legal-section>

    <x-legal-section :title="__('6. Vos droits')">
        <p>{!! __('Conformément à la loi ANPDP 18-07, vous disposez des droits suivants : accès, rectification, suppression, portabilité, opposition au traitement. Pour exercer ces droits, contactez-nous à <strong>privacy@zawajpro.dz</strong>. Nous répondrons dans un délai de 30 jours.') !!}</p>
    </x-legal-section>

    <x-legal-section :title="__('7. Sécurité')">
        <p>{{ __('Nous appliquons des mesures techniques et organisationnelles appropriées pour protéger vos données : chiffrement TLS, accès restreint, journalisation des accès, audits réguliers.') }}</p>
    </x-legal-section>

    {{-- ANPDP compliance anchor (linked from footer) --}}
    <div id="anpdp" class="scroll-mt-28">
        <div class="bg-[#F0D9B5]/30 border border-[#C8956C]/20 rounded-xl p-5 mb-6 flex gap-4">
            <x-z.icon name="verified_user" :filled="true" class="text-[#C8956C] text-3xl shrink-0 mt-1" />
            <div>
                <p class="font-bold text-[#1A1A1A] mb-1">{{ __('Zawaj Pro est conforme à la loi n° 18-07') }}</p>
                <p class="text-sm text-[#6B665E]">{{ __('Loi du 10 juin 2018 relative à la protection des personnes physiques dans le traitement des données à caractère personnel, publiée au Journal officiel n° 34.') }}</p>
            </div>
        </div>
        <x-legal-section :title="__('8. Conformité ANPDP & principes appliqués')">
            <p>{{ __('Zawaj Pro applique les principes fondamentaux de la loi 18-07 :') }}</p>
            <ul class="list-disc ps-5 space-y-1 mt-2">
                <li>{{ __('Licéité, loyauté et transparence du traitement') }}</li>
                <li>{{ __('Limitation des finalités : données utilisées uniquement pour la mise en relation') }}</li>
                <li>{{ __('Minimisation des données : seules les données nécessaires sont collectées') }}</li>
                <li>{{ __('Exactitude : les utilisateurs peuvent corriger leurs données à tout moment') }}</li>
                <li>{{ __('Limitation de la conservation : suppression automatique des données des couples') }}</li>
                <li>{{ __("Intégrité et confidentialité : chiffrement et contrôle d'accès strict") }}</li>
                <li>{{ __("Responsabilité : désignation d'un délégué à la protection des données (DPD)") }}</li>
            </ul>
            <p class="mt-4">{!! __("Délégué à la Protection des Données : <strong>dpo@zawajpro.dz</strong>. Vous pouvez également adresser une réclamation à l'Autorité Nationale de Protection des Données Personnelles (ANPDP).") !!}</p>
        </x-legal-section>
    </div>

</x-legal-page>
@endsection

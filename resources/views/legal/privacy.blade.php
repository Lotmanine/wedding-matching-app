@extends('layouts.couple')

@section('content')
<x-legal-page :title="__('Politique de confidentialité')" subtitle="Zawaj Pro" :updated="__('5 octobre 2026')">

    <x-legal-section :title="__('1. Identité du responsable de traitement')">
        <p>{{ __('Zawaj Pro (ci-après « la Société ») est responsable du traitement des données personnelles collectées via la plateforme zawajpro.dz. La Société est domiciliée à Alger, Algérie. Ce traitement est encadré par la loi n° 18-07 du 10 juin 2018 relative à la protection des personnes physiques dans le traitement des données à caractère personnel.') }}</p>
        <p>{!! __('Contact : <strong>privacy@zawajpro.dz</strong>') !!}</p>
    </x-legal-section>

    <x-legal-section :title="__('2. Données collectées')">
        <div class="mt-2">
            <h3 class="font-semibold text-[#1A1A1A] mb-2">{{ __('2.1 Données des couples') }}</h3>
            <p>{{ __("Lors de l'envoi d'une demande, nous collectons : votre prénom, votre numéro WhatsApp (seul moyen de contact demandé : aucune adresse e-mail n'est collectée), la wilaya et, si vous l'indiquez, la commune, la date du mariage, le budget indicatif, les services recherchés et, selon le cas, le nombre d'invités et une description libre.") }}</p>
        </div>
        <div class="mt-4">
            <h3 class="font-semibold text-[#1A1A1A] mb-2">{{ __('2.2 Données des prestataires') }}</h3>
            <p>{{ __("Lors de l'inscription, nous collectons : votre nom et le nom de votre entreprise, son statut juridique, vos numéros de téléphone et WhatsApp professionnels, votre adresse e-mail, vos wilayas d'intervention et vos frais de déplacement, vos services, votre fourchette de prix, vos années d'expérience, les langues parlées, une description de votre activité, des liens vers votre portfolio ou vos réseaux sociaux, les photos et le logo que vous ajoutez, votre numéro de registre de commerce (facultatif). Nous enregistrons aussi les informations liées à vos achats de crédits et aux demandes que vous acceptez ou refusez.") }}</p>
        </div>
        <div class="mt-4">
            <h3 class="font-semibold text-[#1A1A1A] mb-2">{{ __('2.3 Données techniques') }}</h3>
            <p>{{ __("Lors de l'envoi d'une demande, nous enregistrons votre adresse IP sous forme anonymisée (sa dernière partie est masquée) et le type de navigateur, uniquement pour prévenir les abus. Nous n'utilisons aucun outil de mesure d'audience ni de suivi publicitaire. Les actions effectuées dans l'administration sont journalisées, avec l'adresse IP et le type de navigateur, à des fins de sécurité.") }}</p>
            <p class="mt-3">{{ __("Le site n'utilise que des cookies nécessaires à son fonctionnement : un cookie de session, un cookie de sécurité des formulaires, un cookie qui mémorise la langue choisie (français ou arabe) pendant un an et, si un prestataire le demande à la connexion, un cookie qui maintient sa connexion.") }}</p>
        </div>
    </x-legal-section>

    <x-legal-section :title="__('3. Finalités du traitement')">
        <p>{{ __('Vos données sont traitées pour : (a) mettre en relation les couples avec des prestataires de mariage ; (b) gérer les comptes prestataires et les transactions de crédits ; (c) envoyer des notifications liées à votre demande ; (d) améliorer nos services ; (e) respecter nos obligations légales.') }}</p>
    </x-legal-section>

    <x-legal-section :title="__('4. Durée de conservation')">
        <p>{!! __("<strong>Données des couples :</strong> votre demande expire :days jours après son envoi. Le prestataire qui accepte votre demande peut consulter vos coordonnées dans son espace pendant :days jours à compter de son acceptation. Votre prénom, votre numéro WhatsApp, votre commune et votre description sont effacés de notre base une fois la demande expirée et ces périodes de consultation terminées ; cet effacement est effectué par un traitement automatique quotidien. Il ne s'étend pas à ce qu'un prestataire a noté ou enregistré en dehors de la plateforme. Nous ne gardons ensuite que des données anonymes (wilaya, date du mariage, services demandés, nombre d'invités, tranche de budget) à des fins statistiques.", ['days' => \App\Support\LocaleCount::days((int) config('zawaj.anpdp.retention_days', 14))]) !!}</p>
        <p>{!! __('<strong>Données des prestataires :</strong> conservées pendant la durée du compte.') !!}</p>
        <p>{!! __("<strong>Données techniques :</strong> l'adresse IP anonymisée et le type de navigateur enregistrés avec une demande sont effacés en même temps que les données d'identification de cette demande ; les journaux de sécurité sont conservés aussi longtemps que la sécurité de la plateforme l'exige.") !!}</p>
    </x-legal-section>

    <x-legal-section :title="__('5. Partage des données')">
        <p>{{ __("Les prestataires à qui votre demande est proposée voient ses caractéristiques (service, wilaya, commune, date, budget, nombre d'invités, description) et l'initiale de votre prénom. Parmi eux, seul le prestataire qui accepte votre demande reçoit votre prénom et votre numéro WhatsApp, pour le service concerné. Zawaj Pro ne vend jamais vos données à des tiers. Nous partageons des données avec des sous-traitants techniques dans le strict cadre de nos services : l'hébergement, l'envoi des messages WhatsApp (Meta), le paiement en ligne des achats de crédits des prestataires (Chargily Pay, qui reçoit le nom, l'adresse e-mail et le numéro de téléphone du prestataire) et, pour classer les prestataires à solliciter, un service d'intelligence artificielle qui reçoit le service demandé, la wilaya, la commune, la date, le budget et le nombre d'invités, sans votre prénom ni votre numéro, ainsi que le profil professionnel des prestataires candidats.") }}</p>
    </x-legal-section>

    <x-legal-section :title="__('6. Vos droits')">
        <p>{!! __('Conformément à la loi ANPDP 18-07, vous disposez des droits suivants : accès, rectification, suppression, portabilité, opposition au traitement. Pour exercer ces droits, contactez-nous à <strong>privacy@zawajpro.dz</strong>. Nous répondrons dans un délai de 30 jours.') !!}</p>
    </x-legal-section>

    <x-legal-section :title="__('7. Sécurité')">
        <p>{{ __("Nous appliquons des mesures techniques et organisationnelles appropriées pour protéger vos données : chiffrement des échanges (TLS), accès restreint selon le rôle de chaque compte, journalisation des actions effectuées dans l'administration.") }}</p>
    </x-legal-section>

    {{-- Personal-data protection anchor (linked from footer) --}}
    <div id="anpdp" class="scroll-mt-28">
        <div class="bg-[#F0D9B5]/30 border border-[#C8956C]/20 rounded-xl p-5 mb-6 flex gap-4">
            <x-z.icon name="shield" :filled="true" class="text-[#C8956C] text-3xl shrink-0 mt-1" />
            <div>
                <p class="font-bold text-[#1A1A1A] mb-1">{{ __('Vos données personnelles et la loi n° 18-07') }}</p>
                <p class="text-sm text-[#6B665E]">{{ __('Loi du 10 juin 2018 relative à la protection des personnes physiques dans le traitement des données à caractère personnel, publiée au Journal officiel n° 34.') }}</p>
            </div>
        </div>
        <x-legal-section :title="__('8. Protection des données personnelles')">
            <p>{{ __('Pour protéger vos données personnelles dans le cadre de la loi n° 18-07, Zawaj Pro applique les principes suivants :') }}</p>
            <ul class="list-disc ps-5 space-y-1 mt-2">
                <li>{{ __('Licéité, loyauté et transparence du traitement') }}</li>
                <li>{{ __('Limitation des finalités : données utilisées uniquement pour les finalités décrites dans cette politique') }}</li>
                <li>{{ __('Minimisation des données : seules les données nécessaires sont collectées') }}</li>
                <li>{{ __("Exactitude : les prestataires modifient leur profil dans leur espace ; les couples, qui n'ont pas de compte, demandent la rectification de leurs données à l'adresse de contact indiquée dans cette politique") }}</li>
                <li>{{ __("Limitation de la conservation : effacement automatique des données d'identification des couples à l'issue des durées indiquées à la section 4") }}</li>
                <li>{{ __('Intégrité et confidentialité : chiffrement des échanges (TLS) et accès limité selon le rôle de chaque compte') }}</li>
                <li>{{ __("Responsabilité : désignation d'un délégué à la protection des données (DPD)") }}</li>
            </ul>
            <p class="mt-4">{!! __("Délégué à la Protection des Données : <strong>dpo@zawajpro.dz</strong>. Vous pouvez également adresser une réclamation à l'Autorité Nationale de Protection des Données Personnelles (ANPDP).") !!}</p>
        </x-legal-section>
    </div>

</x-legal-page>
@endsection

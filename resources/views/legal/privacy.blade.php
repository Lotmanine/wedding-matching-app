@extends('layouts.couple')

@section('content')
<x-legal-page title="Politique de confidentialité" subtitle="Zawaj Pro" updated="21 avril 2026">

    <x-legal-section title="1. Identité du responsable de traitement">
        <p>Zawaj Pro (ci-après « la Société ») est responsable du traitement des données personnelles collectées via la plateforme zawajpro.dz. La Société est domiciliée à Alger, Algérie, et opère conformément à la loi n° 18-07 du 10 juin 2018 relative à la protection des personnes physiques dans le traitement des données à caractère personnel (loi ANPDP).</p>
        <p>Contact : <strong>privacy@zawajpro.dz</strong></p>
    </x-legal-section>

    <x-legal-section title="2. Données collectées">
        <div class="mt-2">
            <h3 class="font-semibold text-[#1A1A1A] mb-2">2.1 Données des couples</h3>
            <p>Lors de la soumission d'une demande, nous collectons : prénom(s), numéro de téléphone WhatsApp, adresse e-mail (optionnel), wilaya et commune, date approximative du mariage, nombre d'invités estimé, budget indicatif, services recherchés, description libre du mariage.</p>
        </div>
        <div class="mt-4">
            <h3 class="font-semibold text-[#1A1A1A] mb-2">2.2 Données des prestataires</h3>
            <p>Lors de l'inscription, nous collectons : nom et raison sociale, numéro de téléphone professionnel, adresse e-mail, wilaya(s) d'intervention, spécialité(s), liens vers portfolio ou réseaux sociaux, numéro de registre de commerce (optionnel), informations de facturation.</p>
        </div>
        <div class="mt-4">
            <h3 class="font-semibold text-[#1A1A1A] mb-2">2.3 Données de navigation</h3>
            <p>Nous collectons automatiquement : adresse IP (anonymisée), type de navigateur, pages consultées, durée des sessions. Ces données sont utilisées à des fins statistiques uniquement.</p>
        </div>
    </x-legal-section>

    <x-legal-section title="3. Finalités du traitement">
        <p>Vos données sont traitées pour : (a) mettre en relation les couples avec des prestataires de mariage ; (b) gérer les comptes prestataires et les transactions de crédits ; (c) envoyer des notifications liées à votre demande ; (d) améliorer nos services ; (e) respecter nos obligations légales.</p>
    </x-legal-section>

    <x-legal-section title="4. Durée de conservation">
        <p><strong>Données des couples :</strong> vos données personnelles sont supprimées automatiquement après le délai de rétention défini dans nos engagements ANPDP, à compter de la soumission de votre demande.</p>
        <p><strong>Données des prestataires :</strong> conservées pendant la durée du compte, puis 3 ans après sa clôture à des fins comptables.</p>
        <p><strong>Données de navigation :</strong> conservées 13 mois maximum.</p>
    </x-legal-section>

    <x-legal-section title="5. Partage des données">
        <p>Votre numéro WhatsApp n'est communiqué qu'aux prestataires ayant débloqué votre demande. Zawaj Pro ne vend jamais vos données à des tiers. Nous pouvons partager des données avec des sous-traitants techniques (hébergement, messagerie) dans le strict cadre de nos services.</p>
    </x-legal-section>

    <x-legal-section title="6. Vos droits">
        <p>Conformément à la loi ANPDP 18-07, vous disposez des droits suivants : accès, rectification, suppression, portabilité, opposition au traitement. Pour exercer ces droits, contactez-nous à <strong>privacy@zawajpro.dz</strong>. Nous répondrons dans un délai de 30 jours.</p>
    </x-legal-section>

    <x-legal-section title="7. Sécurité">
        <p>Nous appliquons des mesures techniques et organisationnelles appropriées pour protéger vos données : chiffrement TLS, accès restreint, journalisation des accès, audits réguliers.</p>
    </x-legal-section>

    {{-- ANPDP compliance anchor (linked from footer) --}}
    <div id="anpdp" class="scroll-mt-28">
        <div class="bg-[#F0D9B5]/30 border border-[#C8956C]/20 rounded-xl p-5 mb-6 flex gap-4">
            <x-z.icon name="verified_user" :filled="true" class="text-[#C8956C] text-3xl shrink-0 mt-1" />
            <div>
                <p class="font-bold text-[#1A1A1A] mb-1">Zawaj Pro est conforme à la loi n° 18-07</p>
                <p class="text-sm text-[#6B665E]">Loi du 10 juin 2018 relative à la protection des personnes physiques dans le traitement des données à caractère personnel, publiée au Journal officiel n° 34.</p>
            </div>
        </div>
        <x-legal-section title="8. Conformité ANPDP & principes appliqués">
            <p>Zawaj Pro applique les principes fondamentaux de la loi 18-07 :</p>
            <ul class="list-disc pl-5 space-y-1 mt-2">
                <li>Licéité, loyauté et transparence du traitement</li>
                <li>Limitation des finalités — données utilisées uniquement pour la mise en relation</li>
                <li>Minimisation des données — seules les données nécessaires sont collectées</li>
                <li>Exactitude — les utilisateurs peuvent corriger leurs données à tout moment</li>
                <li>Limitation de la conservation — suppression automatique des données des couples</li>
                <li>Intégrité et confidentialité — chiffrement et contrôle d'accès strict</li>
                <li>Responsabilité — désignation d'un délégué à la protection des données (DPD)</li>
            </ul>
            <p class="mt-4">Délégué à la Protection des Données : <strong>dpo@zawajpro.dz</strong>. Vous pouvez également adresser une réclamation à l'Autorité Nationale de Protection des Données Personnelles (ANPDP).</p>
        </x-legal-section>
    </div>

</x-legal-page>
@endsection

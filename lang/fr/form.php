<?php

declare(strict_types=1);

return [

    // Brand + layout
    'brand'             => 'Zawaj Pro',
    'brand_tagline'     => 'Votre mariage, facilité.',
    'footer_copyright'  => '© :year Zawaj Pro · Algérie',
    'footer_legal'      => 'Mentions légales',
    'footer_privacy'    => 'Protection des données personnelles',

    // Public navigation
    'nav_provider_area'   => 'Espace prestataire',
    'nav_home'            => 'Accueil',
    'nav_submit_request'  => 'Faire une demande',
    'nav_for_providers'   => 'Pour les prestataires',
    'nav_cta'             => 'Commencer ma demande',
    'cta_free_note'       => 'Gratuit pour les futurs mariés',

    // Progress
    'step_of'           => 'Étape :current sur :total',
    'sidebar_journey'   => 'Votre demande',
    'services_count'    => '{1} :count service sélectionné|[2,*] :count services sélectionnés',

    // Step titles
    'step_1_title'      => 'Comment vous contacter ?',
    'step_2_title'      => 'Où aura lieu le mariage ?',
    'step_3_title'      => 'Budget et services',
    'step_4_title'      => 'Date du mariage',
    'step_5_title'      => 'Vérifiez votre demande',

    // Step intros
    'step_1_intro'      => 'Pour vous contacter sur WhatsApp.',
    'step_2_intro'      => 'Choisissez la wilaya du mariage.',
    'step_3_intro'      => 'Indiquez le budget total et les services recherchés.',
    'step_4_intro'      => 'Indiquez la date prévue.',
    'step_5_intro'      => 'Vérifiez les informations avant l\'envoi.',

    // Field labels
    'label_first_name'     => 'Prénom',
    'label_whatsapp'       => 'Numéro WhatsApp',
    'label_wilaya'         => 'Wilaya',
    'label_commune'        => 'Commune',
    'label_wedding_date'   => 'Date du mariage',
    'label_guest_count'    => 'Nombre d\'invités',
    'label_budget'         => 'Budget total du mariage (DA)',
    'label_services'       => 'Services recherchés',
    'label_description'    => 'Précisions (facultatif)',
    // Consent: the checkbox statement, then what is shared and with whom (help_accept_terms).
    'label_accept_terms'   => 'J\'accepte le partage de ma demande avec les prestataires vérifiés qui couvrent ma wilaya.',
    'help_accept_terms'    => 'Zawaj Pro leur transmet la wilaya, la commune, la date, le nombre d\'invités, le budget, les services et les précisions. Pour chaque service, mon prénom et mon numéro WhatsApp ne sont communiqués qu\'au prestataire qui accepte ma demande. Les durées de conservation sont indiquées dans la :link.',
    'accept_terms_link'    => 'politique de confidentialité',

    // Placeholders / helpers
    'placeholder_first_name'  => 'Ex. : Amina',
    'placeholder_whatsapp'    => '+213661234567',
    'placeholder_wilaya'      => 'Choisir une wilaya…',
    'placeholder_commune'     => 'Choisir une commune…',
    'placeholder_guest_count' => 'Choisir le nombre d\'invités',
    'placeholder_budget'      => 'Quel est le budget total de votre mariage ?',
    'placeholder_description' => 'Ex. : style des photos, menu ou décoration souhaités.',

    'help_whatsapp'      => 'Avec +213, sans le premier 0 ni espaces. Exemple : +213661234567.',
    'help_whatsapp_use'  => 'Le prestataire qui accepte votre demande vous contacte sur ce numéro.',
    'help_commune'       => 'Choisissez d\'abord la wilaya.',
    'help_services'      => 'Choisissez au moins un service.',
    'help_guest_count'   => 'Nécessaire pour le traiteur ou la salle des fêtes.',
    'help_char_count'    => ':count / :max caractères',

    // Buttons
    'btn_start'         => 'Commencer ma demande',
    'btn_previous'      => 'Retour',
    'btn_next'          => 'Continuer',
    'btn_submit'        => 'Envoyer ma demande',
    'btn_submitting'    => 'Envoi en cours…',
    'btn_home'          => 'Retour à l\'accueil',
    'btn_copy'          => 'Copier la référence',
    'btn_copied'        => 'Référence copiée',

    // Review
    'review_title'       => 'Récapitulatif',
    'review_couple'      => 'Vos coordonnées',
    'review_location'    => 'Lieu du mariage',
    'review_date'        => 'Date du mariage',
    'review_date_guests' => 'Date et invités',
    'review_budget'      => 'Budget',
    'review_services'    => 'Services recherchés',
    'review_notes'       => 'Précisions',
    'review_none'        => 'Non renseigné',

    // Landing
    'landing_hero_title'    => 'Trouvez les prestataires de votre mariage',
    'landing_hero_sub'      => 'Indiquez vos besoins. Nous envoyons votre demande aux prestataires vérifiés de votre wilaya. Pour chaque service, le premier qui accepte vous contacte sur WhatsApp.',
    'landing_trust_free'    => 'Gratuit pour les futurs mariés',
    'landing_trust_verified' => 'Prestataires vérifiés',
    'landing_trust_wilayas' => 'Alger, Blida et Médéa pour commencer',
    'landing_secondary_cta' => 'Je suis un prestataire',
    'landing_secondary_notice' => 'L\'espace prestataires arrive très prochainement, restez à l\'écoute !',

    // Confirmation
    'confirm_title'       => 'Votre demande a été envoyée',
    'confirm_subtitle'    => 'Nous la transmettons aux prestataires vérifiés de votre wilaya.',
    'confirm_reference'   => 'Référence de la demande',
    'confirm_keep_link'   => 'Gardez ce lien pour suivre votre demande.',
    'confirm_next_steps'  => 'Et maintenant ?',
    'confirm_step_1'      => 'Pour chaque service, nous prévenons jusqu\'à :per_round prestataires vérifiés de votre wilaya. Chacun a :hours heures pour répondre.',
    'confirm_step_2'      => 'Le premier qui accepte vous contacte sur WhatsApp. Vous convenez du prix avec lui. Sans acceptation, nous prévenons d\'autres prestataires, puis la demande devient visible par tous les prestataires éligibles de votre wilaya.',
    'confirm_step_3'      => 'Suivez l\'avancement sur cette page. Sans acceptation avant la date d\'expiration, la demande expire.',
    'confirm_services'    => 'Services recherchés',
    'confirm_status_searching' => 'Recherche en cours',
    'confirm_status_found'     => 'Demande acceptée',
    'confirm_status_open'      => 'Recherche élargie',
    'confirm_status_open_help' => 'La demande est maintenant visible par tous les prestataires éligibles de votre wilaya.',
    'confirm_status_none'      => 'Expirée sans acceptation',
    'confirm_expiry'      => 'Votre demande expire le :date. Si un prestataire l\'accepte, il garde l\'accès à vos coordonnées pendant :days jours après son acceptation. Vos données personnelles sont effacées de la plateforme une fois la demande expirée et cette période terminée.',
    'confirm_new_request' => 'Faire une nouvelle demande',

    // Submission errors
    'rate_limit'          => 'Trop de demandes envoyées. Réessayez dans :minutes min.',
    'submission_failed'   => 'Votre demande n\'a pas été enregistrée. Réessayez.',
];

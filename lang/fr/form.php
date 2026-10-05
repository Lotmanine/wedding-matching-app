<?php

declare(strict_types=1);

return [

    // Brand + layout
    'brand'             => 'Zawaj Pro',
    'brand_tagline'     => 'Votre mariage, facilité.',
    'footer_copyright'  => '© :year Zawaj Pro · Algérie',
    'footer_legal'      => 'Mentions légales',
    'footer_privacy'    => 'Confidentialité (ANPDP)',

    // Public navigation
    'nav_provider_area'   => 'Espace prestataire',
    'nav_for_couples'     => 'Pour les couples',
    'nav_submit_request'  => 'Soumettre une demande',
    'nav_become_provider' => 'Devenez prestataire',
    'nav_cta'             => 'Décrire mon mariage',

    // Progress
    'step_of'           => 'Étape :current sur :total',
    'sidebar_journey'   => 'Votre parcours',
    'services_count'    => '{1} :count service sélectionné|[2,*] :count services sélectionnés',

    // Step titles
    'step_1_title'      => 'Parlez-nous de vous',
    'step_2_title'      => 'Où se déroule le mariage ?',
    'step_3_title'      => 'Budget et prestataires recherchés',
    'step_4_title'      => 'Détails du mariage',
    'step_5_title'      => 'Finalisez votre demande',

    // Step intros
    'step_1_intro'      => 'Quelques informations pour vous contacter.',
    'step_2_intro'      => 'Sélectionnez la wilaya (et la commune si vous la connaissez).',
    'step_3_intro'      => 'Choisissez les prestataires dont vous avez besoin.',
    'step_4_intro'      => 'Précisez la date et les détails de votre mariage.',
    'step_5_intro'      => 'Vérifiez vos informations et envoyez votre demande.',

    // Field labels
    'label_first_name'     => 'Prénom',
    'label_whatsapp'       => 'Numéro WhatsApp',
    'label_wilaya'         => 'Wilaya',
    'label_commune'        => 'Commune',
    'label_wedding_date'   => 'Date du mariage',
    'label_wedding_season' => 'Saison du mariage',
    'label_guest_count'    => 'Nombre d\'invités',
    'label_budget'         => 'Budget global',
    'label_services'       => 'Prestataires recherchés',
    'label_description'    => 'Précisions (facultatif)',
    'label_accept_terms'   => 'J\'accepte que mes informations soient transmises aux prestataires sélectionnés et supprimées automatiquement après 14 jours, conformément à la loi 18-07 (ANPDP).',

    // Placeholders / helpers
    'placeholder_first_name'  => 'Ex. Amina',
    'placeholder_whatsapp'    => '+213661234567',
    'placeholder_wilaya'      => 'Choisir une wilaya…',
    'placeholder_commune'     => 'Choisir une commune…',
    'placeholder_guest_count' => 'Combien d\'invités attendus ?',
    'placeholder_budget'      => 'Quel est votre budget global ?',
    'placeholder_description' => 'Ex. mariage traditionnel kabyle, 2 salles, besoin d\'une pâtisserie orientale…',

    'help_whatsapp'      => 'Format : +213 suivi de 9 chiffres, sans espaces. Les prestataires vous contacteront sur ce numéro WhatsApp.',
    'help_commune'       => 'Choisissez d\'abord une wilaya. La commune est facultative.',
    'help_date_or_season' => 'Saisissez une date précise ou, à défaut, une saison approximative.',
    'help_char_count'    => ':count / :max caractères',

    // Buttons
    'btn_start'         => 'Je planifie mon mariage',
    'btn_previous'      => 'Retour',
    'btn_next'          => 'Continuer',
    'btn_submit'        => 'Envoyer ma demande',
    'btn_home'          => 'Retour à l\'accueil',
    'btn_copy'          => 'Copier',
    'btn_copied'        => 'Copié !',

    // Seasons
    'season_printemps'  => 'Printemps',
    'season_ete'        => 'Été',
    'season_automne'    => 'Automne',
    'season_hiver'      => 'Hiver',
    'season_placeholder' => 'Choisir une saison…',

    // Review
    'review_title'      => 'Récapitulatif',
    'review_couple'     => 'Couple',
    'review_location'   => 'Localisation',
    'review_wedding'    => 'Mariage',
    'review_budget'     => 'Budget',
    'review_services'   => 'Prestataires',
    'review_notes'      => 'Précisions',
    'review_none'       => 'Non renseigné',
    'review_date_only'  => 'Date : :date',
    'review_season_only' => 'Saison : :season',

    // Landing
    'landing_hero_title'    => 'Trouvez les meilleurs prestataires pour votre mariage',
    'landing_hero_sub'      => 'Remplissez un formulaire rapide. Nous vous connectons gratuitement avec les meilleurs prestataires de votre région.',
    'landing_trust_free'    => 'Gratuit pour les couples',
    'landing_trust_verified' => 'Prestataires vérifiés',
    'landing_trust_wilayas' => 'Alger, Blida et Médéa pour commencer',
    'landing_secondary_cta' => 'Je suis un prestataire',
    'landing_secondary_notice' => 'L\'espace prestataires arrive très prochainement, restez à l\'écoute !',

    // Confirmation
    'confirm_title'       => 'Merci :name ! Votre demande est bien reçue 🎉',
    'confirm_subtitle'    => 'Nous transmettons votre demande aux prestataires vérifiés de votre wilaya.',
    'confirm_reference'   => 'Votre numéro de référence',
    'confirm_next_steps'  => 'Et maintenant ?',
    'confirm_step_1'      => 'Pour chaque service, nous prévenons jusqu\'à :per_round prestataires vérifiés de votre wilaya. Chacun a :hours heures pour répondre.',
    'confirm_step_2'      => 'Le premier qui accepte votre demande vous contacte directement sur WhatsApp. Sans réponse, nous en sollicitons d\'autres, puis la demande est ouverte à tous les prestataires de votre wilaya.',
    'confirm_step_3'      => 'Vous suivez l\'avancement sur cette page : gardez-en le lien. Si personne n\'a accepté avant la date d\'expiration, c\'est qu\'aucun prestataire n\'était disponible.',
    'confirm_services'    => 'Prestataires demandés',
    'confirm_status_searching' => 'En recherche…',
    'confirm_status_found'     => 'Prestataire trouvé',
    'confirm_status_open'      => 'Ouverte à tous les prestataires',
    'confirm_status_none'      => 'Aucun prestataire trouvé',
    'confirm_expiry'      => 'Votre demande est valable jusqu\'au :date. Après cette date, vos informations sont automatiquement supprimées.',

    // Submission errors
    'rate_limit'          => 'Trop de demandes. Merci de réessayer dans :minutes minute(s).',
    'submission_failed'   => 'Nous n\'avons pas pu enregistrer votre demande. Merci de réessayer dans un instant.',
];

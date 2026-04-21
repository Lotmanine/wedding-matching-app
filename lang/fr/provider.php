<?php

declare(strict_types=1);

return [

    'brand'          => 'Zawaj Pro',
    'dashboard_title' => 'Tableau de bord prestataire',

    'nav' => [
        'dashboard' => 'Tableau de bord',
        'profile'   => 'Profil',
        'credits'   => 'Crédits',
        'history'   => 'Historique',
        'logout'    => 'Déconnexion',
    ],

    'auth' => [
        'register_title'   => 'Créer un compte prestataire',
        'register_sub'     => 'Rejoignez les meilleurs prestataires de mariage en Algérie.',
        'login_title'      => 'Connexion prestataire',
        'login_sub'        => 'Accédez à votre tableau de bord Zawaj Pro.',
        'forgot_title'     => 'Mot de passe oublié',
        'forgot_sub'       => 'Saisissez votre adresse e-mail pour recevoir un lien de réinitialisation.',
        'reset_title'      => 'Réinitialiser le mot de passe',
        'email'            => 'Adresse e-mail',
        'password'         => 'Mot de passe',
        'password_confirm' => 'Confirmer le mot de passe',
        'remember_me'      => 'Se souvenir de moi',
        'btn_login'        => 'Se connecter',
        'btn_logout'       => 'Déconnexion',
        'btn_register'     => 'Créer mon compte',
        'btn_send_link'    => 'Envoyer le lien',
        'btn_reset'        => 'Réinitialiser',
        'no_account'       => 'Pas encore de compte ?',
        'has_account'      => 'Déjà un compte ?',
        'create_account'   => 'Créer un compte',
        'forgot_link'      => 'Mot de passe oublié ?',
        'failed'           => 'Identifiants incorrects.',
        'not_provider'     => 'Ce compte n\'est pas un compte prestataire.',
    ],

    'registration' => [
        'step_of'   => 'Étape :current sur :total',
        'step_1'    => 'Compte',
        'step_2'    => 'Entreprise',
        'step_3'    => 'Services',
        'step_4'    => 'Couverture',
        'step_5'    => 'Portfolio',

        'full_name'        => 'Nom complet',
        'phone'            => 'Téléphone',
        'whatsapp'         => 'Numéro WhatsApp',
        'accept_terms'     => 'J\'accepte les conditions d\'utilisation de la plateforme.',
        'accept_anpdp'     => 'J\'accepte le traitement de mes données conformément à la loi 18-07 (ANPDP).',

        'business_name'    => 'Nom commercial',
        'business_type'    => 'Forme juridique',
        'business_type_options' => [
            'auto-entrepreneur' => 'Auto-entrepreneur',
            'sarl'              => 'SARL',
            'eurl'              => 'EURL',
            'particulier'       => 'Particulier',
        ],
        'trade_register'   => 'Numéro de registre de commerce (optionnel)',
        'years_experience' => 'Années d\'expérience',
        'description'      => 'Description de l\'activité',
        'primary_wilaya'   => 'Wilaya principale',
        'price_tier'       => 'Niveau tarifaire',
        'price_tier_options' => [
            'budget'  => 'Budget (petit prix)',
            'mid'     => 'Intermédiaire',
            'premium' => 'Premium',
            'luxury'  => 'Luxe',
        ],
        'min_price'        => 'Prix min (DZD)',
        'max_price'        => 'Prix max (DZD)',
        'languages'        => 'Langues parlées',
        'languages_options' => [
            'fr'  => 'Français',
            'ar'  => 'Arabe',
            'ber' => 'Berbère',
            'en'  => 'Anglais',
        ],

        'services_hint'     => 'Sélectionnez au moins une catégorie de service.',

        'coverage_hint'     => 'Les wilayas que vous desservez. Votre wilaya principale est incluse automatiquement (sans frais de déplacement).',
        'coverage_primary_badge' => 'Principale (sans frais)',
        'coverage_travel_fee' => 'Frais de déplacement (DZD)',

        'portfolio_urls_label' => 'Liens vers vos réseaux / portfolio (min 1, max 5)',
        'portfolio_url_placeholder' => 'https://instagram.com/votre-compte',
        'portfolio_images' => 'Photos (max 10, 3 Mo chacune)',
        'logo'             => 'Logo (optionnel)',
        'add_url'          => '+ Ajouter un lien',
        'remove_url'       => 'Supprimer',

        'previous'         => '← Retour',
        'next'             => 'Continuer →',
        'submit'           => 'Créer mon compte',

        'success' => 'Votre compte a été créé. Votre profil est en cours de vérification.',
        'failed'  => 'Nous n\'avons pas pu créer votre compte. Veuillez réessayer.',
    ],

    'dashboard' => [
        'greeting'         => 'Bonjour, :name',
        'pending_banner'   => '⏳ Votre profil est en cours de vérification. Vous recevrez les demandes une fois approuvé.',
        'stat_received'    => 'Demandes reçues',
        'stat_accepted'    => 'Demandes acceptées',
        'stat_credits'     => 'Crédits disponibles',
        'stat_rate'        => 'Taux d\'acceptation',
        'empty_title'      => 'Aucune demande pour le moment',
        'empty_sub'        => 'Les nouvelles demandes de mariage apparaîtront ici.',
        'complete_profile_cta' => 'Compléter mon profil',
    ],

    'profile' => [
        'title'              => 'Mon profil',
        'section_personal'   => 'Informations personnelles',
        'section_business'   => 'Entreprise',
        'section_services'   => 'Services',
        'section_coverage'   => 'Couverture géographique',
        'section_portfolio'  => 'Portfolio',
        'logo_current'       => 'Logo actuel',
        'delete_logo'        => 'Supprimer le logo',
        'existing_images'    => 'Images existantes',
        'new_images'         => 'Ajouter des images',
        'delete_image'       => 'Supprimer',
        'save'               => 'Enregistrer',
        'saved'              => 'Vos modifications ont été enregistrées.',
        'save_failed'        => 'Impossible d\'enregistrer. Veuillez réessayer.',
    ],
];

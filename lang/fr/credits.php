<?php

declare(strict_types=1);

return [

    'title'           => 'Crédits',
    'balance_label'   => 'Solde de crédits',
    'unlock_cost_line' => '[0,1] Accepter une demande coûte :count crédit.|[2,*] Accepter une demande coûte :count crédits.',
    // Nombre + unité dans une même phrase (« 6 crédits »).
    'count'           => '[0,1] :count crédit|[2,*] :count crédits',
    // Légende placée sous un chiffre affiché à part.
    'unit'            => '[0,1] crédit|[2,*] crédits',
    'added_label'     => 'Crédits ajoutés',
    'packs_title'     => 'Acheter des crédits',
    'pack_per_unlock' => ':price DA par crédit',
    'pack_cta'        => 'Choisir ce pack',

    'recent_title'    => 'Transactions récentes',
    'full_history'    => 'Voir tout l\'historique',
    'no_transactions' => 'Aucune transaction pour le moment.',

    'history_title'   => 'Historique des crédits',
    'history_export'  => 'Exporter en CSV',
    'history_filter'  => 'Type',
    'history_all'     => 'Toutes',

    'type' => [
        'purchase'         => 'Achat',
        'unlock'           => 'Acceptation',
        'refund'           => 'Crédits recrédités',
        'bonus'            => 'Bonus',
        'admin_adjustment' => 'Ajustement du solde',
        'adjustment'       => 'Ajustement du solde',
        'other'            => 'Mouvement de crédits',
    ],

    // Descriptions des écritures créées par la plateforme (voir CreditLedger::systemDescription()).
    'entry' => [
        'purchase_pack'  => 'Achat du :pack · :uid',
        'purchase'       => 'Achat de crédits',
        'unlock_request' => 'Acceptation de la demande :uid',
        'unlock'         => 'Acceptation d\'une demande',
        'refund_request' => 'Crédits recrédités après signalement · demande :uid',
        'refund'         => 'Crédits recrédités après signalement',
        'welcome_bonus'  => 'Crédits de bienvenue',
        'bonus'          => 'Crédits offerts',
        'adjustment'     => 'Ajustement du solde',
    ],

    'success_title'  => 'Paiement confirmé',
    'success_sub'    => 'Vos crédits ont été ajoutés.',
    'success_cta'    => 'Voir les demandes',
    'success_pdf'    => 'Télécharger le reçu',
    'success_pending_title' => 'Paiement en cours de confirmation',
    'success_pending_sub'   => 'Les crédits seront ajoutés après confirmation du paiement. Cette page s\'actualise automatiquement.',

    'pay' => [
        'title_choice'  => 'Mode de paiement',
        'title_single'  => 'Mode de paiement',
        'intro_both'    => 'Payez par carte ou par versement bancaire ou CCP.',
        'online_body'   => 'Paiement par carte via Chargily Pay. Les crédits sont ajoutés après confirmation du paiement.',
        'offline_body'  => 'Effectuez le versement, puis envoyez sa référence. Les crédits sont ajoutés après vérification par notre équipe.',
    ],

    'failure_title'   => 'Paiement non finalisé',
    'failure_sub'     => 'Votre paiement n\'a pas été finalisé.',
    'failure_retry'   => 'Réessayer',
    'failure_contact' => 'Nous contacter',
    'failure_reasons' => [
        'gateway_failed'       => 'Le service de paiement nous a signalé que le paiement a échoué.',
        'user_cancelled'       => 'Le paiement a été annulé avant d\'être validé.',
        'checkout_init_failed' => 'La page de paiement n\'a pas pu être ouverte.',
        'offline_rejected'     => 'Votre versement n\'a pas été validé par notre équipe.',
        'unknown'              => 'Le paiement n\'a pas abouti.',
    ],
    'failure_team_note'    => 'Motif indiqué par notre équipe :',
    'failure_causes_title' => 'Causes possibles d\'un échec de paiement par carte :',
    'failure_causes'       => [
        'funds'  => 'solde de la carte insuffisant ;',
        'limit'  => 'plafond de paiement atteint ;',
        'refuse' => 'refus de la transaction par la banque.',
    ],
    'failure_next'         => 'Vous pouvez réessayer ou utiliser une autre carte.',
    'failure_next_offline' => 'Vous pouvez réessayer, utiliser une autre carte ou payer par versement bancaire ou CCP.',

    'checkout' => [
        'failed'       => 'Impossible de démarrer le paiement. Merci de réessayer.',
        'no_url'       => 'Réponse inattendue du service de paiement. Merci de réessayer.',
        'not_verified' => 'L\'achat de crédits est possible après validation de votre profil.',
        'no_method'    => "Aucun mode de paiement n'est disponible pour le moment. Merci de contacter notre équipe.",
    ],

    'offline' => [
        'submitted'      => 'Versement signalé. Les crédits seront ajoutés après vérification par notre équipe.',
        'pending_status' => 'En cours de vérification',
        'pending_note'   => 'Versement en cours de vérification. Les crédits seront ajoutés après validation.',
    ],

    'receipt' => [
        'title'        => 'Reçu de paiement',
        'download_pdf' => 'Télécharger le reçu',
        'issued_to'    => 'Émis à',
        'paid_on'      => 'Payé le',
        'reference'    => 'Référence',
        'gateway_ref'  => 'Référence Chargily',
        'transfer_ref' => 'Référence du versement',
        'status'       => 'Statut',
        'amount'       => 'Montant',
        'unlocks'      => 'Crédits',
        'footer'       => 'Zawaj Pro · Plateforme de mariage en Algérie. Auto-entrepreneur. Paiements en ligne traités par Chargily Pay.',
    ],

    'low_balance_warning' => 'Crédits insuffisants pour accepter une demande. Achetez des crédits pour continuer.',
    'insufficient_redirect' => 'Crédits insuffisants. Achetez un pack pour accepter cette demande.',
];

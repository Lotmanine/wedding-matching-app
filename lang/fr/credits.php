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
    'packs_title'     => 'Recharger mon compte',
    'pack_per_unlock' => ':price DA par crédit',
    'pack_cta'        => 'Acheter',

    'recent_title'    => 'Transactions récentes',
    'full_history'    => 'Voir tout l\'historique',
    'no_transactions' => 'Aucune transaction pour le moment.',

    'history_title'   => 'Historique des transactions',
    'history_export'  => 'Exporter en CSV',
    'history_filter'  => 'Type',
    'history_all'     => 'Toutes',

    'type' => [
        'purchase'         => 'Achat',
        'unlock'           => 'Déblocage',
        'refund'           => 'Remboursement',
        'bonus'            => 'Bonus',
        'admin_adjustment' => 'Ajustement du solde',
        'adjustment'       => 'Ajustement du solde',
        'other'            => 'Mouvement de crédits',
    ],

    // Descriptions des écritures créées par la plateforme (voir CreditLedger::systemDescription()).
    'entry' => [
        'purchase_pack'  => 'Achat du :pack · :uid',
        'purchase'       => 'Achat de crédits',
        'unlock_request' => 'Déblocage de la demande :uid',
        'unlock'         => 'Déblocage d\'une demande',
        'refund_request' => 'Remboursement après signalement · demande :uid',
        'refund'         => 'Remboursement après signalement',
        'welcome_bonus'  => 'Crédits de bienvenue',
        'bonus'          => 'Crédits offerts',
        'adjustment'     => 'Ajustement du solde',
    ],

    'success_title'  => 'Paiement réussi 🎉',
    'success_sub'    => 'Vos crédits ont été ajoutés à votre compte.',
    'success_cta'    => 'Voir les demandes',
    'success_pdf'    => 'Télécharger le reçu',
    'success_pending_title' => 'Paiement en cours de traitement…',
    'success_pending_sub'   => 'Vos crédits sont ajoutés dès que le service de paiement nous confirme le paiement. Cette page s\'actualise automatiquement.',

    'pay' => [
        'title_choice'  => 'Choisissez votre mode de paiement',
        'title_single'  => 'Mode de paiement',
        'intro_both'    => 'Payez en ligne par carte Edahabia ou CIB via Chargily Pay, ou par versement (CCP ou banque). En ligne, vos crédits sont ajoutés dès que Chargily Pay nous confirme le paiement ; par versement, ils sont ajoutés après vérification du versement par notre équipe.',
        'intro_online'  => 'Paiement en ligne par carte Edahabia ou CIB via Chargily Pay. Vos crédits sont ajoutés dès que Chargily Pay nous confirme le paiement.',
        'intro_offline' => 'Paiement par versement (CCP ou banque). Vos crédits sont ajoutés après vérification du versement par notre équipe.',
        'online_body'   => 'Vous êtes redirigé vers Chargily Pay pour payer par carte. Vos crédits sont ajoutés dès que Chargily Pay nous confirme le paiement.',
        'offline_body'  => 'Effectuez le versement, puis déclarez-le ci-dessous. Vos crédits sont ajoutés après vérification du versement par notre équipe.',
    ],

    'failure_title'   => 'Paiement non abouti',
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
    'failure_next_offline' => 'Vous pouvez réessayer, utiliser une autre carte ou payer par versement (CCP ou banque).',

    'checkout' => [
        'failed'       => 'Impossible de démarrer le paiement. Merci de réessayer.',
        'no_url'       => 'Réponse inattendue du service de paiement. Merci de réessayer.',
        'not_verified' => 'Votre compte doit être vérifié par notre équipe avant de pouvoir acheter des crédits.',
        'no_method'    => "Aucun mode de paiement n'est disponible pour le moment. Merci de contacter notre équipe.",
    ],

    'offline' => [
        'submitted'      => 'Votre versement a été déclaré. Vos crédits sont ajoutés après vérification du versement par notre équipe.',
        'pending_status' => 'En attente de vérification',
        'pending_note'   => 'Votre versement est en attente de vérification. Vos crédits sont ajoutés après vérification du versement par notre équipe.',
    ],

    'receipt' => [
        'title'        => 'Reçu de paiement',
        'download_pdf' => 'Télécharger le PDF',
        'issued_to'    => 'Émis à',
        'paid_on'      => 'Payé le',
        'reference'    => 'Référence',
        'gateway_ref'  => 'Référence Chargily',
        'transfer_ref' => 'Référence du versement',
        'status'       => 'Statut',
        'amount'       => 'Montant',
        'unlocks'      => 'Crédits de déblocage',
        'footer'       => 'Zawaj Pro · Plateforme de mariage en Algérie. Auto-entrepreneur. Paiements en ligne traités par Chargily Pay.',
    ],

    'low_balance_warning' => '⚠️ Crédits insuffisants pour accepter une demande. Rechargez votre compte.',
    'insufficient_redirect' => 'Crédits insuffisants. Achetez un pack pour accepter cette demande.',
];

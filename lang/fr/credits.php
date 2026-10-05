<?php

declare(strict_types=1);

return [

    'title'           => 'Crédits',
    'balance_label'   => 'Votre solde',
    'balance_unit'    => ':count crédit(s) disponible(s)',
    'balance_tooltip' => '1 crédit = 1 déblocage de demande',
    'packs_title'     => 'Recharger mon compte',
    'pack_per_unlock' => ':price DA par crédit',
    'pack_popular'    => 'Populaire',
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
        'admin_adjustment' => 'Ajustement admin',
    ],

    'success_title'  => 'Paiement réussi 🎉',
    'success_sub'    => 'Vos crédits ont été ajoutés à votre compte.',
    'success_cta'    => 'Voir les demandes',
    'success_pdf'    => 'Télécharger le reçu',
    'success_pending_title' => 'Paiement en cours de traitement…',
    'success_pending_sub'   => 'Ne fermez pas cette page. Vos crédits seront disponibles dans un instant.',

    'failure_title'   => 'Paiement non abouti',
    'failure_sub'     => 'Votre paiement n\'a pas été finalisé.',
    'failure_reason'  => 'Motif',
    'failure_retry'   => 'Réessayer',
    'failure_contact' => 'Nous contacter',
    'failure_tips'    => [
        'funds'  => 'Solde insuffisant sur votre carte.',
        'limit'  => 'Plafond de paiement atteint.',
        'refuse' => 'Votre banque a refusé la transaction.',
        'retry'  => 'Essayez avec une autre carte, ou choisissez le versement.',
    ],

    'checkout' => [
        'failed'       => 'Impossible de démarrer le paiement. Merci de réessayer.',
        'no_url'       => 'Réponse inattendue du prestataire de paiement.',
        'not_verified' => 'Votre compte doit être vérifié par notre équipe avant de pouvoir acheter des crédits.',
        'no_method'    => "Aucun mode de paiement n'est disponible pour le moment. Merci de contacter notre équipe.",
    ],

    'offline' => [
        'submitted' => 'Votre versement a été déclaré. Vos crédits seront ajoutés dès validation par notre équipe.',
    ],

    'receipt' => [
        'title'        => 'Reçu de paiement',
        'download_pdf' => 'Télécharger le PDF',
        'issued_to'    => 'Émis à',
        'paid_on'      => 'Payé le',
        'reference'    => 'Référence',
        'gateway_ref'  => 'Référence Chargily',
        'status'       => 'Statut',
        'amount'       => 'Montant',
        'unlocks'      => 'Crédits de déblocage',
        'footer'       => 'Zawaj Pro · Plateforme de mariage en Algérie. Auto-entrepreneur. Paiements sécurisés via Chargily Pay.',
    ],

    'low_balance_warning' => '⚠️ Crédits insuffisants pour accepter une demande. Rechargez votre compte.',
    'insufficient_redirect' => 'Crédits insuffisants. Achetez un pack pour accepter cette demande.',
];

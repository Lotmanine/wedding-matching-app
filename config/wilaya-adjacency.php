<?php

declare(strict_types=1);

/**
 * Wilaya adjacency map — each key is a wilaya code, each value is the list of
 * adjacent-wilaya codes (land borders).
 *
 * V1 stub — only populate pairs you care about. Missing entries fall back to
 * the "far" distance bucket. Safe to expand incrementally without code changes.
 *
 * Used by the ranking-candidates API to classify a provider's distance from
 * the couple's wilaya as: same_wilaya | adjacent | far.
 */
return [
    // Major-city neighbours (examples — expand as needed)
    '09' => ['16', '26', '35', '42', '44'],     // Blida ↔ Alger, Médéa, Boumerdès, Tipaza, Aïn Defla
    '16' => ['09', '35', '42'],                  // Alger ↔ Blida, Boumerdès, Tipaza
    '19' => ['05', '15', '18', '25', '28', '34', '43'], // Sétif
    '23' => ['21', '24', '36', '41'],            // Annaba ↔ Skikda, Guelma, El Tarf, Souk Ahras
    '25' => ['04', '19', '21', '24', '27', '43'], // Constantine
    '31' => ['13', '22', '27', '29', '46'],      // Oran ↔ Tlemcen, SBA, Mostaganem, Mascara, Aïn Témouchent
    '35' => ['09', '10', '15', '16'],            // Boumerdès
    '42' => ['09', '16', '44'],                  // Tipaza
];

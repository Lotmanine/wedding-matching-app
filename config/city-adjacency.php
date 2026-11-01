<?php

declare(strict_types=1);

/**
 * City adjacency map: each key is a city code, each value is the list of
 * neighbouring city codes.
 *
 * Missing entries fall back to the "far" distance bucket. Safe to expand
 * incrementally without code changes.
 *
 * Used by the ranking-candidates API to classify a provider's distance from
 * the couple's city as: same_city | adjacent | far.
 */
return [
    'MA' => ['NI'], // Marseille ↔ Nice
    'NI' => ['MA'],
];

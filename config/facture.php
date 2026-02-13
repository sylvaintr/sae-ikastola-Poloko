<?php

return [
    // Montant de participation par enfant (utilisé pour le calcul des factures)
    'cost_per_child_participation' => env('COST_PER_CHILD_PARTICIPATION', 9.65),

    // Montant de participation Seaska (si applicable)
    'seaska_participation_amount'  => env('SEASKA_PARTICIPATION_AMOUNT', 7.7),

    // Mois de l'année pour lesquels les factures mensuelles doivent être des régularisations (ex: 2 pour février, 8 pour août)
    'MONTHS_REGULATING'            => explode(',', env('MONTHS_REGULATING', '2,8')),
];

<?php

return [
    // Montant de participation par enfant (utilisé pour le calcul des factures)
    'cost_per_child_participation' => env('COST_PER_CHILD_PARTICIPATION', 9.65),

    // Montant de participation Seaska (si applicable)
    'seaska_participation_amount'  => env('SEASKA_PARTICIPATION_AMOUNT', 7.7),
];

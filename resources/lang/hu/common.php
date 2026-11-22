<?php

// Közös felületi szövegek (fejléc, lábléc). Helyőrzők – a végleges szöveget a fejlesztő írja (CG 1.).
// Shared UI texts (header, footer). Placeholders – the final wording is up to the developer (CG ch. 1).
return [
    'app_name' => 'Mesegenerátor',

    // Fejléc menüpontjai szerepkör szerint (App Flow K-01) / Header menu items by role (App Flow K-01)
    'nav' => [
        'label' => 'Főmenü',
        'toggle' => 'Menü',
        'create' => 'Mese készítése',
        'login' => 'Belépés',
        'register' => 'Regisztráció',
        'stories' => 'Meséim',
        'account' => 'Fiókom',
        'admin' => 'Admin',
        'logout' => 'Kilépés',
    ],

    // A témaváltó a KÖVETKEZŐ állapotot mondja ki (CG 7.4) / The theme switch names the NEXT state (CG 7.4)
    'theme' => [
        'to_night' => 'Esti mód',
        'to_day' => 'Nappali mód',
    ],

    'footer' => [
        'label' => 'Lábléc',
        'tagline' => 'Esti mesék, amelyekben a gyereked a főszereplő.',
        'privacy' => 'Adatkezelési tájékoztató',
    ],
];

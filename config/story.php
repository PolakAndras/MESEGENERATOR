<?php

// A mesekészítés minden számszerű korlátja egy helyen, hogy kódmódosítás nélkül változtatható legyen
// (PRD 7., Backend Schema 6.). A felület is ezekből dolgozik, számot nem éget be.
// Every numeric limit of story creation in one place, so it can change without code changes
// (PRD 7., Backend Schema 6.). The UI reads these values too and hard-codes no numbers.
return [
    // PRD 7.: felhasználótípusonkénti korlátok (guest = vendég, user = bejelentkezett, az admin is)
    // PRD 7.: limits per user type (guest, user = logged in, admin included)
    // BR-01 (PRD 1.4): 4–10 oldal, a tulajdonos kérésére a korábbi 8–10 helyett.
    // BR-01 (PRD 1.4): 4–10 pages, at the owner's request instead of the former 8–10.
    'limits' => [
        'guest' => ['pages_min' => 4, 'pages_max' => 10, 'animals_per_page' => 4],
        'user' => ['pages_min' => 4, 'pages_max' => 10, 'animals_per_page' => 4],
    ],

    // Véletlenszerű kitöltés (PRD US-21): oldalanként legfeljebb ennyi állat, és ekkora eséllyel szerepel a gyerek.
    // Random fill (PRD US-21): at most this many animals per page, and this chance that the child appears.
    'random' => [
        'animals_max' => 2,
        'child_chance' => 75,  // százalék / percent
    ],

    // Típusfüggetlen korlátok / Limits independent of the user type
    'child_name_max' => 50,   // BR-14
    'child_age_min' => 2,     // BR-10
    'child_age_max' => 10,
    'context_max' => 1000,    // BR-13

    // BR-05, BR-06: cselekvések szereplőtípusonként (alaphelyzettel együtt)
    // BR-05, BR-06: actions per character type (including the default action)
    'actions_max' => [
        'animal' => 10,
        'child_figure' => 5,
    ],

    // BR-15 és a belépési korlát (Tech Stack 4.1, Backend Schema NY-01: 5 / perc)
    // BR-15 and the login limit (Tech Stack 4.1, Backend Schema NY-01: 5 per minute)
    'rate_limits' => [
        'generations_per_minute' => 3,
        'login_attempts_per_minute' => 5,
    ],

    // NFR-04, Content Guidelines 8.4
    'placement' => [
        'default_scale' => 35,
        'half_width_factor' => 0.375,  // 1:1 kép a 4:3 vásznon / a 1:1 image on the 4:3 canvas
        'child' => ['x' => 50, 'y' => 90],
        'animal_slots' => [
            1 => ['x' => 20, 'y' => 90],
            2 => ['x' => 40, 'y' => 90],
            3 => ['x' => 60, 'y' => 90],
            4 => ['x' => 80, 'y' => 90],
        ],
    ],

    // Tech Stack 5.1: demo | groq | mistral. A demo AI-hívás nélkül, kézzel írt mintaszöveggel dolgozik.
    // Tech Stack 5.1: demo | groq | mistral. demo works without an AI call, using hand-written sample text.
    'generator' => env('STORY_GENERATOR', 'demo'),

    // Az admin fiók a seederhez (PRD US-19). Konfigurációban, mert config:cache után az env() máshol üres értéket ad.
    // The admin account for the seeder (PRD US-19). In config, because after config:cache env() is empty elsewhere.
    'admin' => [
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],

    'ai_timeout' => 45,  // NFR-09

    // Az OpenAI-kompatibilis szolgáltatók (Tech Stack 5.3). A kulcs csak a .env-ben van (US-07 AC10).
    // OpenAI-compatible providers (Tech Stack 5.3). The key lives only in .env (US-07 AC10).
    'providers' => [
        'groq' => [
            'base_url' => 'https://api.groq.com/openai/v1',
            'api_key' => env('GROQ_API_KEY'),
            'model' => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
        ],
        'mistral' => [
            'base_url' => 'https://api.mistral.ai/v1',
            'api_key' => env('MISTRAL_API_KEY'),
            'model' => env('MISTRAL_MODEL', 'mistral-small-latest'),
        ],
    ],
];

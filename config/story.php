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

    // A mesegyűjtemény jeleneteinek képi jelölése (PRD 1.6 US-21, US-24). A jelenet csak típust ad meg (pl. „mozog”),
    // a konkrét cselekvést és hátteret ez a táblázat választja ki, így új póz vagy háttér felvételekor nem kell az
    // összes jelenetet átírni. Ha a szereplőnek nincs az adott típusú póza, alaphelyzetben (áll) marad.
    // Picture hints of the story collection's scenes (PRD 1.6 US-21, US-24). A scene only names a kind (e.g. "move");
    // this table maps it to real actions and backgrounds, so adding a pose or background needs no scene rewrite.
    // A character without a pose of that kind stays in its default (áll).
    'scenes' => [
        'pose_kinds' => [
            'stand' => ['áll'],
            'move' => ['fut', 'szalad', 'ugrik', 'ugrál'],
            'rest' => ['ül', 'legel', 'összegömbölyödik', 'mosakszik', 'szimatol', 'répát eszik', 'sajtot eszik', 'almát visz', 'ágaskodik'],
            'sleep' => ['alszik'],
            'happy' => ['integet', 'pacsit ad', 'ugrál', 'ugrik', 'brekeg'],
        ],
        'background_kinds' => [
            'home' => 'Esti hálószoba',
            'day' => 'Napos rét',
            'night' => 'Éjszakai erdő',
            'snow' => 'Havas táj',
        ],
        // Véletlen oldalon: a társ a gyerek melletti helyek egyikén, a mellékszereplő a túloldali szélen.
        // On a random page: the companion on a slot next to the child, the extra animal on the far other side.
        'companion_slots' => [2, 3],
    ],

    // Típusfüggetlen korlátok / Limits independent of the user type
    'child_name_max' => 50,   // BR-14
    'child_age_min' => 2,     // BR-10
    'child_age_max' => 10,

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

    // templates | groq | mistral (Tech Stack 1.4). Alapból a mesegyűjtemény (AI nélkül, PRD US-24); az AI későbbre,
    // kikapcsolva marad, a STORY_GENERATOR=groq kapcsolja be. Ismeretlen érték esetén is a mesegyűjtemény fut.
    // templates | groq | mistral (Tech Stack 1.4). By default the story collection (no AI, PRD US-24); AI stays switched
    // off for later and STORY_GENERATOR=groq turns it on. Any unknown value also falls back to the story collection.
    'generator' => env('STORY_GENERATOR', 'templates'),

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

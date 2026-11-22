<?php

// A Laravel validációs üzeneteinek magyar fordítása, tegező, udvarias hangnemben, felkiáltójel nélkül (CG 9.3, 10.).
// Hungarian translation of Laravel's validation messages, informal but polite, without exclamation marks (CG 9.3, 10.).
return [

    'accepted' => 'Fogadd el: :attribute.',
    'accepted_if' => 'Fogadd el: :attribute, ha a(z) :other értéke :value.',
    'active_url' => 'A(z) :attribute nem érvényes webcím.',
    'after' => 'A(z) :attribute legyen :date utáni dátum.',
    'after_or_equal' => 'A(z) :attribute legyen :date vagy annál későbbi dátum.',
    'alpha' => 'A(z) :attribute csak betűket tartalmazhat.',
    'alpha_dash' => 'A(z) :attribute csak betűket, számokat, kötőjelet és aláhúzást tartalmazhat.',
    'alpha_num' => 'A(z) :attribute csak betűket és számokat tartalmazhat.',
    'array' => 'A(z) :attribute legyen lista.',
    'before' => 'A(z) :attribute legyen :date előtti dátum.',
    'before_or_equal' => 'A(z) :attribute legyen :date vagy annál korábbi dátum.',
    'between' => [
        'numeric' => 'A(z) :attribute legyen :min és :max között.',
        'file' => 'A(z) :attribute mérete legyen :min és :max kilobájt között.',
        'string' => 'A(z) :attribute hossza legyen :min és :max karakter között.',
        'array' => 'A(z) :attribute legalább :min és legfeljebb :max elemet tartalmazhat.',
    ],
    'boolean' => 'A(z) :attribute értéke igen vagy nem lehet.',
    'confirmed' => 'A(z) :attribute és a megerősítése nem egyezik.',
    'current_password' => 'A jelszó nem megfelelő.',
    'date' => 'A(z) :attribute nem érvényes dátum.',
    'date_equals' => 'A(z) :attribute legyen :date.',
    'date_format' => 'A(z) :attribute formátuma legyen :format.',
    'declined' => 'Utasítsd el: :attribute.',
    'declined_if' => 'Utasítsd el: :attribute, ha a(z) :other értéke :value.',
    'different' => 'A(z) :attribute és a(z) :other legyen különböző.',
    'digits' => 'A(z) :attribute legyen :digits számjegyű.',
    'digits_between' => 'A(z) :attribute legyen :min és :max számjegy közötti.',
    'dimensions' => 'A(z) :attribute képmérete nem megfelelő.',
    'distinct' => 'A(z) :attribute egy elem kétszer szerepel benne.',
    'email' => 'Adj meg érvényes e-mail-címet.',
    'ends_with' => 'A(z) :attribute ezek egyikére végződjön: :values.',
    'enum' => 'A választott :attribute érvénytelen.',
    'exists' => 'A választott :attribute nem található.',
    'file' => 'A(z) :attribute legyen fájl.',
    'filled' => 'Töltsd ki: :attribute.',
    'gt' => [
        'numeric' => 'A(z) :attribute legyen nagyobb, mint :value.',
        'file' => 'A(z) :attribute legyen nagyobb, mint :value kilobájt.',
        'string' => 'A(z) :attribute legyen hosszabb, mint :value karakter.',
        'array' => 'A(z) :attribute több mint :value elemet tartalmazzon.',
    ],
    'gte' => [
        'numeric' => 'A(z) :attribute legyen legalább :value.',
        'file' => 'A(z) :attribute legyen legalább :value kilobájt.',
        'string' => 'A(z) :attribute legyen legalább :value karakter.',
        'array' => 'A(z) :attribute legalább :value elemet tartalmazzon.',
    ],
    'image' => 'A(z) :attribute legyen kép.',
    'in' => 'A választott :attribute érvénytelen.',
    'in_array' => 'A(z) :attribute nem szerepel itt: :other.',
    'integer' => 'A(z) :attribute legyen egész szám.',
    'ip' => 'A(z) :attribute legyen érvényes IP-cím.',
    'ipv4' => 'A(z) :attribute legyen érvényes IPv4-cím.',
    'ipv6' => 'A(z) :attribute legyen érvényes IPv6-cím.',
    'json' => 'A(z) :attribute legyen érvényes JSON.',
    'lt' => [
        'numeric' => 'A(z) :attribute legyen kisebb, mint :value.',
        'file' => 'A(z) :attribute legyen kisebb, mint :value kilobájt.',
        'string' => 'A(z) :attribute legyen rövidebb, mint :value karakter.',
        'array' => 'A(z) :attribute kevesebb mint :value elemet tartalmazzon.',
    ],
    'lte' => [
        'numeric' => 'A(z) :attribute legfeljebb :value lehet.',
        'file' => 'A(z) :attribute legfeljebb :value kilobájt lehet.',
        'string' => 'A(z) :attribute legfeljebb :value karakter lehet.',
        'array' => 'A(z) :attribute legfeljebb :value elemet tartalmazhat.',
    ],
    'mac_address' => 'A(z) :attribute legyen érvényes MAC-cím.',
    'max' => [
        'numeric' => 'A(z) :attribute legfeljebb :max lehet.',
        'file' => 'A(z) :attribute legfeljebb :max kilobájt lehet.',
        'string' => 'A(z) :attribute legfeljebb :max karakter lehet.',
        'array' => 'A(z) :attribute legfeljebb :max elemet tartalmazhat.',
    ],
    'mimes' => 'A(z) :attribute formátuma ezek egyike legyen: :values.',
    'mimetypes' => 'A(z) :attribute formátuma ezek egyike legyen: :values.',
    'min' => [
        'numeric' => 'A(z) :attribute legalább :min legyen.',
        'file' => 'A(z) :attribute legalább :min kilobájt legyen.',
        'string' => 'A(z) :attribute legalább :min karakter legyen.',
        'array' => 'A(z) :attribute legalább :min elemet tartalmazzon.',
    ],
    'multiple_of' => 'A(z) :attribute legyen :value többszöröse.',
    'not_in' => 'A választott :attribute érvénytelen.',
    'not_regex' => 'A(z) :attribute formátuma érvénytelen.',
    'numeric' => 'A(z) :attribute legyen szám.',
    'password' => 'A jelszó nem megfelelő.',
    'present' => 'A(z) :attribute mezőnek szerepelnie kell.',
    'prohibited' => 'A(z) :attribute mező nem tölthető ki.',
    'prohibited_if' => 'A(z) :attribute mező nem tölthető ki, ha a(z) :other értéke :value.',
    'prohibited_unless' => 'A(z) :attribute mező csak akkor tölthető ki, ha a(z) :other értéke ezek egyike: :values.',
    'prohibits' => 'Ha a(z) :attribute ki van töltve, a(z) :other nem tölthető ki.',
    'regex' => 'A(z) :attribute formátuma érvénytelen.',
    'required' => 'Add meg: :attribute.',
    'required_array_keys' => 'A(z) :attribute tartalmazza ezeket: :values.',
    'required_if' => 'Add meg: :attribute.',
    'required_unless' => 'Add meg: :attribute, kivéve ha a(z) :other értéke ezek egyike: :values.',
    'required_with' => 'Add meg: :attribute, ha a(z) :values ki van töltve.',
    'required_with_all' => 'Add meg: :attribute, ha ezek ki vannak töltve: :values.',
    'required_without' => 'Add meg: :attribute, ha a(z) :values nincs kitöltve.',
    'required_without_all' => 'Add meg: :attribute, ha ezek egyike sincs kitöltve: :values.',
    'same' => 'A(z) :attribute és a(z) :other egyezzen.',
    'size' => [
        'numeric' => 'A(z) :attribute legyen :size.',
        'file' => 'A(z) :attribute legyen :size kilobájt.',
        'string' => 'A(z) :attribute legyen :size karakter.',
        'array' => 'A(z) :attribute :size elemet tartalmazzon.',
    ],
    'starts_with' => 'A(z) :attribute ezek egyikével kezdődjön: :values.',
    'string' => 'A(z) :attribute legyen szöveg.',
    'timezone' => 'A(z) :attribute legyen érvényes időzóna.',
    'unique' => 'Ez a(z) :attribute már foglalt.',
    'uploaded' => 'A(z) :attribute feltöltése nem sikerült. Próbáld újra.',
    'url' => 'A(z) :attribute legyen érvényes webcím.',
    'uuid' => 'A(z) :attribute legyen érvényes UUID.',

    /*
    | Egyedi üzenetek mezőnként, ahol az általános mondat nem elég barátságos.
    | Custom per-field messages where the generic sentence is not friendly enough.
    */
    'custom' => [
        'child_name' => [
            'required' => 'Add meg a gyerek nevét.',
        ],
        'child_age' => [
            'required' => 'Add meg a gyerek életkorát.',
            'between' => 'Az életkor :min és :max év között lehet.',
        ],
        'child_gender' => [
            'required' => 'Válaszd ki, hogy lány vagy fiú.',
        ],
        'child_hair_color' => [
            'required' => 'Válaszd ki a hajszínt.',
        ],
        'context' => [
            'required' => 'Írd le röviden, miről szóljon a mese.',
        ],
        'page_count' => [
            'required' => 'Válaszd ki, hány oldalas legyen a mese.',
            'between' => 'A mese :min és :max oldal között lehet.',
        ],
        'animals' => [
            'max' => 'Egy oldalon legfeljebb :max állat lehet.',
        ],
        'animals.*.character_id' => [
            'distinct' => 'Ugyanaz az állat egy oldalon csak egyszer szerepelhet.',
            'exists' => 'A választott állat nem található.',
        ],
        'privacy_accepted' => [
            'accepted' => 'A regisztrációhoz fogadd el az adatkezelési tájékoztatót.',
        ],
        'password' => [
            'current_password' => 'A jelszó nem megfelelő.',
        ],
    ],

    /*
    | A mezőnevek magyarul, hogy a hibaüzenet mondata természetes legyen.
    | Field names in Hungarian, so error messages read naturally.
    */
    'attributes' => [
        'name' => 'név',
        'email' => 'e-mail-cím',
        'password' => 'jelszó',
        'child_name' => 'a gyerek neve',
        'child_age' => 'életkor',
        'child_gender' => 'nem',
        'child_hair_color' => 'hajszín',
        'context' => 'a mese témája',
        'page_count' => 'oldalszám',
        'background_id' => 'háttér',
        'animals' => 'állatok',
        'type' => 'típus',
        'gender' => 'nem',
        'hair_color' => 'hajszín',
        'image' => 'kép',
        'default_action_name' => 'az alaphelyzet neve',
        'default_action_image' => 'az alaphelyzet képe',
    ],

];

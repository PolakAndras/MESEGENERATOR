<?php

// Az adatkezelési tájékoztató (S-02) szövegei. HELYŐRZŐ – a végleges szöveget a projekt tulajdonosa írja
// (PRD US-20 AC2). A szakaszok a US-20 AC2 kötelező pontjai és a Backend Schema E-03 szerint.
// Texts of the privacy notice (S-02). PLACEHOLDER – the final text is written by the project owner
// (PRD US-20 AC2). Sections follow the required points of US-20 AC2 and Backend Schema E-03.
return [
    'title' => 'Adatkezelési tájékoztató',
    'lead' => 'Ez a tájékoztató ideiglenes változat. Leírja, milyen adatokat kezel a Mesegenerátor, és mi történik velük.',

    'sections' => [
        [
            'heading' => 'Milyen adatokat kezelünk',
            'text' => 'A mese elkészítéséhez megadod a gyereked nevét, korát, nemét és hajszínét, és kiválasztod a mese témáját. Ha regisztrálsz, a nevedet, az e-mail-címedet és a jelszavad titkosított változatát is tároljuk, valamint azt, hogy mikor fogadtad el ezt a tájékoztatót.',
        ],
        [
            'heading' => 'Vendégként',
            'text' => 'Ha nem jelentkezel be, a mesédet nem mentjük el. A félkész vagy elkészült mese csak a munkamenetedben van: a szerver adatbázisában, a munkamenet lejártáig, amely 60 perc tétlenség után következik be. A munkamenethez a Laravel alapbeállítása szerint az IP-címed és a böngésződ azonosítója is tárolódik.',
        ],
        [
            'heading' => 'Hogyan készül a mese szövege',
            'text' => 'A mese szövegét a Mesegenerátor a saját, előre megírt mesegyűjteményéből állítja össze, a szerveren. Az adataid ehhez nem kerülnek külső szolgáltatóhoz, és mesterséges intelligenciát sem használunk. Ha ez a jövőben megváltozik, előtte frissítjük ezt a tájékoztatót.',
        ],
        [
            'heading' => 'Esti és nappali mód',
            'text' => 'Az esti vagy nappali mód választását a böngésződ tárolja a saját gépeden. Ez az adat nem kerül a szerverünkre.',
        ],
        [
            'heading' => 'A fiókod törlése',
            'text' => 'A fiókodat bármikor törölheted a Fiókom oldalon. A törlés végleges: a fiókoddal együtt az összes elmentett meséd is törlődik.',
        ],
    ],
];

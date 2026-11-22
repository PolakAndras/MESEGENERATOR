<?php

// Hibaoldalak (App Flow 6.2, CG 9.3): mi történt + mit tehet a felhasználó, felkiáltójel nélkül.
// Error pages (App Flow 6.2, CG 9.3): what happened + what the user can do, no exclamation marks.
return [
    'back_home' => 'Vissza a kezdőoldalra',
    'new_story' => 'Új mese',
    'summary' => 'Nézd át a kiemelt mezőket:',
    '403' => ['title' => 'Ehhez nincs hozzáférésed', 'text' => 'Ez az oldal csak a jogosult felhasználóknak érhető el.'],
    '404' => ['title' => 'Ez az oldal nem található', 'text' => 'Lehet, hogy elírtad a címet, vagy az oldal már nem létezik.'],
    '419' => ['title' => 'Lejárt a munkamenet', 'text' => 'Egy ideje nem történt semmi, ezért a folyamatban lévő mese elveszett. Kezdj egy újat.'],
    '429' => ['title' => 'Túl sok próbálkozás', 'text' => 'Várj egy kicsit, és próbáld újra.'],
    '500' => ['title' => 'Valami elromlott', 'text' => 'Váratlan hiba történt. Próbáld újra később.'],
];

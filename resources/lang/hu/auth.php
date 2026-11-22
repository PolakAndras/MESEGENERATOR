<?php

// Belépési üzenetek. A failed szándékosan általános: nem árulja el, melyik adat hibás (PRD US-12 AC1).
// Login messages. failed is intentionally generic: it does not reveal which field is wrong (PRD US-12 AC1).
return [
    'failed' => 'Az e-mail-cím vagy a jelszó nem megfelelő.',
    'password' => 'A jelszó nem megfelelő.',
    'throttle' => 'Túl sok belépési próbálkozás. Próbáld újra :seconds másodperc múlva.',
];

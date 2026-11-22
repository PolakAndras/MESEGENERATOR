# Mesegenerátor / Story Generator

## Project overview / A projekt áttekintése
Portfólióprojekt: webalkalmazás, amelyben a szülő 8–10 oldalas, illusztrált esti
mesét készít, amelynek a gyereke a főszereplője (és végig gyerek marad). Minden
oldal képe rétegekből áll: 1 háttér + 0–4 állat + 0–1 gyerekfigura, mindegyik a
kiválasztott cselekvés képével. A szülő állítja össze az oldalakat; az
AI-szolgáltató (tervezett: Groq) csak a címet és az oldalankénti szöveget írja,
képet nem generál.

A „fejlesztő” ebben a fájlban a projekt tulajdonosa (a dokumentumokban így szerepel).

## Tech stack / Technológia
- Laravel ^8.83, PHP 8.1, MySQL 8.4, Blade + natív JavaScript (ES-modulok),
  Windows, helyben XAMPP PHP + php artisan serve.
- NINCS frontend-build (nincs npm, Vite, Mix). A CSS és a JS a public/ mappában van.
- Az alkalmazás szerveroldalon renderelt, nem SPA.
- Csak ingyenes eszközök. Az egyetlen hozzáadott csomag a
  league/flysystem-aws-s3-v3. Breeze, Jetstream, Fortify és UI-csomag tilos.
- Új Composer-csomag, külső könyvtár, CDN vagy külső betűtípus-kérés csak a
  fejlesztő jóváhagyásával. composer update csak kérésre (a composer.lock rögzít).

## Project documentation / Dokumentáció
- A tervek a docs/ mappában: PRD, App Flow, Tech Stack, Content Guidelines,
  Backend Schema, Implementation Plan.
- Ütközés esetén a PRD az irányadó, kivéve a Backend Schema jóváhagyott
  eltéréseit és az Implementation Plan IP-E jelű eltéréseit.
- Feladat előtt olvasd el a vonatkozó részt. Ha a feladat eltérne tőlük,
  jelezd, mielőtt továbblépsz; ne térj el hallgatólagosan.
- „Nyitott kérdés” (NY-xx) jelölésű pontban ne feltételezz: kérdezz.
- A „NEM implementálandó” és „Későbbi fázis” részeket (2. fázis, backlog,
  Tech Stack 13.) ne valósítsd meg.
- A dokumentumok elsődleges példánya a tervező chatben van (claude.ai-projekt,
  ahol a tervek és a ticketek útmutatói készülnek). Ha a docs/ eltérni látszik,
  jelezd.

## Workflow / Munkamód
- Ticketenként dolgozunk (Implementation Plan). Csak az aktuális ticket
  hatókörében dolgozz; ami nincs benne, azt csak jelezd.
- A ticketben szereplő nevek javasolt nevek; a fejlesztő meglévő kódja az irányadó.
- A lépésenkénti útmutatót a tervező chat adja; te a kijelölt részeket írod,
  és kérésre átnézed a fejlesztő kódját (javaslattal, nem átírással).
- Mindig a legegyszerűbb, működő megoldás.

## Code ownership / Munkamegosztás
- A kód nagy részét a fejlesztő írja. Csak ott írj kódot, ahol CLAUDE-jelölés van
  (PHP/JS: // CLAUDE:, Blade: {{-- CLAUDE: --}}, CSS: /* CLAUDE: */), vagy ahol a
  fejlesztő a kérésében kifejezetten megnevezi a fájlt.
- A megírt rész fölött a CLAUDE-jelölést cseréld le a kétnyelvű, „miért így”
  kommentre, hogy ne maradjon nyitott feladatjel.
- Nevet (változó, függvény, osztály, fájl) ne változtass kérdezés nélkül.
- A fejlesztő kódját ne módosítsd engedély nélkül: javasolj, és csak jóváhagyás
  után változtass.

## Commands / Parancsok
- Szerver: php artisan serve
- Migráció és mintaadat: php artisan migrate --seed
- Csak engedéllyel: php artisan test, php artisan migrate:fresh (adatot töröl),
  composer require, composer update
- Git: commitot, pusht, ágváltást, összevonást nem végzel (a fejlesztő most
  tanulja a Gitet). Commitüzenetet javasolhatsz: kétnyelvű Conventional Commits,
  pl. „feat(wizard): 1. lépés validációja / step 1 validation”.
  Ágnév: <típus>/<issue-szám>-<rövid-leírás>, pl. feature/14-child-step-validation.

## Architecture rules / Architektúra
- Az üzleti logika service-osztályokban (app/Services) és Form Requestekben van;
  a kontrollerek vékonyak, a Blade-nézetek csak megjelenítenek.
- Minden adatmódosítás űrlapküldéssel (POST/PUT/DELETE) történik, és sikeres
  feldolgozás után átirányítással zárul (PRG minta).
- A félkész mese a mentésig csak a munkamenetben van; csak a StoryDraftService
  olvassa és írja.
- A mese strukturált adatként tárolódik (stories → story_pages →
  story_page_placements), nem HTML-ként.
- Korlátok és számok csak a config/story.php-ból, nem beégetve.
- Az AI-hívás csak a StoryTextGenerator interfészen keresztül, a Laravel Http
  kliensével (SDK nélkül); a válasz JSON-ját minden felhasználás előtt ellenőrizni kell.
- Képek csak a Storage::disk('images') tárolón keresztül (helyben mappa, élesben
  Cloudflare R2); az adatbázis a relatív útvonalat tárolja.
- Feltöltött és kezdő SVG csak <img>-ként jelenhet meg. Kivétel: a repóban lévő
  Tabler-ikonok, amelyek az <x-icon> komponensen keresztül kerülnek az oldalba.
- Katalóguselem (szereplő, cselekvés, háttér) nem törölhető; soft delete nincs.

## Frontend rules / Frontend
- Csak dizájntokenek (public/css/tokens.css), BEM-osztálynevek.
  Kivétel: a vászon figuráinak left, top, height, z-index értéke inline stílusban.
- JS: natív ES-modulok, külső könyvtár nélkül; elemkeresés data- attribútummal;
  adatmódosítás és navigáció csak űrlapküldéssel (nincs fetch).
- Minden felületi szöveg a resources/lang/hu fájlokból.

## Coding conventions / Kódolási szabályok
- Kódnevek angolul.
- Az általad írt kódhoz kétnyelvű (magyar / angol) komment, amely azt is leírja,
  MIÉRT így oldottad meg.
- Válaszaidban magyarul írj, az új szakszót első előforduláskor magyarázd el.

## Testing / Tesztelés
- PHPUnit feature tesztek, RefreshDatabase traittel; teszt-adatbázis:
  mesegenerator_test (MySQL, SQLite nem).
- AI-hívás csak Http::fake()-kel, képek Storage::fake('images')-zel;
  valódi API-hívás tesztben soha.
- Tesztet csak engedélykérés után futtatsz.

## Definition of done / Mikor kész egy ticket?
- A ticket „Kész, ha” pontjai teljesülnek, a tesztjei zöldek (engedéllyel futtatva).
- Frontend-ticketnél a Content Guidelines 13. ellenőrzőlistája rendben.
- A fázis-ellenőrzőlista a fázis végére vonatkozik.
- A PR összevonását és az issue lezárását a fejlesztő végzi; te jelzed, ha
  szerinted a ticket kész.

## Forbidden / Tiltott
- .env: ne olvasd és ne módosítsd (a .env.example szerkeszthető). Ha új kulcs
  kell, mondd meg a fejlesztőnek, mit írjon bele.
- vendor/: ne módosítsd.
- docs/: csak a fejlesztő engedélyével.
- Már lefutott migrációt ne módosíts; helyette új migráció.
- database/seeders/images/, storage/app/public/images/, public/fonts/:
  fájlt ne törölj és ne nevezz át.
- Naplóba (log) nem kerülhet gyereknév, kontextus, AI-szöveg vagy kulcs.

# Mesegenerátor

> [Magyar](#magyar) · [English](#english)

**Élő demó / Live demo:** _hamarosan / coming soon_ (Render – az első betöltés kb. egy percig tarthat / the first load may take about a minute)

---

## Magyar

Webalkalmazás, amelyben a szülő 4–10 oldalas, illusztrált esti mesét készít, és a mese főszereplője a saját gyereke. Oldalanként kiválasztja a hátteret, a gyerekfigurát és legfeljebb négy állatot, a figurákat a helyükre húzza és tükrözheti, a mese címét és szövegét pedig egy AI-szolgáltató írja meg. Ha nincs ideje összeállítani, a „Meglepetés mese” véletlenszerűen tölti ki az oldalakat. A kész mese böngészőben lapozható, esti és nappali módban.

### Funkciók
- Varázsló három lépésben: a gyerek adatai, a mese témája és hossza, oldalszerkesztő húzható és tükrözhető figurákkal (egér és érintés).
- Véletlenszerű mese vagy oldal: amit a szülő üresen hagy, azt a rendszer tölti ki.
- Mesegenerálás egyetlen AI-kéréssel, a válasz ellenőrzésével és egy automatikus újrapróbálással; bemutató mód AI nélkül.
- Olvasó címoldallal, lapozással, esti/nappali móddal és az AI által írt szöveg jelölésével.
- Vendégként is használható; belépés vagy regisztráció után a félkész mese megmarad és menthető.
- Fiók: regisztráció, belépés, Meséim, fióktörlés.
- Admin: szereplők, cselekvések, hátterek kezelése képfeltöltéssel; az összes mentett mese listája.

### Technológia
Laravel 8 · PHP 8.1 · MySQL 8.4 · Blade + natív JavaScript-modulok (build nélkül) · saját CSS dizájntokenekkel · PHPUnit · Docker (Render) · Aiven MySQL · Cloudflare R2 · Groq (OpenAI-kompatibilis API).

### Telepítés helyben
1. `composer install`
2. `cp .env.example .env`, majd töltsd ki az adatbázis adatait és az `ADMIN_EMAIL` / `ADMIN_PASSWORD` értékét.
3. `php artisan key:generate`
4. `php artisan storage:link`
5. `php artisan migrate --seed`
6. `php artisan serve` → http://127.0.0.1:8000

Az AI nélküli bemutató módhoz a `STORY_GENERATOR=demo` beállítás elég. Valódi szöveghez: `STORY_GENERATOR=groq` és `GROQ_API_KEY`.

### Tesztek
Külön `mesegenerator_test` MySQL-adatbázisban: `php artisan test`. Az AI-hívás a tesztekben mindig szimulált (`Http::fake()`).

### Élesítés
A `Dockerfile` és a `render.yaml` a Render ingyenes szintjére készült. A titkos értékeket (adatbázis, R2, API-kulcs, admin) a Render felületén kell megadni, az Aiven CA-tanúsítványát „Secret File”-ként (`/etc/secrets/aiven-ca.pem`). Az Aiven a nem használt ingyenes adatbázist leállíthatja, ilyenkor újraindítható.

### Dokumentáció
A tervdokumentumok a `docs/` mappában vannak (PRD, App Flow, Tech Stack, Content Guidelines, Backend Schema, Implementation Plan), a megvalósítás döntései a `docs/DECISIONS.md` fájlban.

### Felhasznált források
- Betűtípusok: Nunito, Literata, Fraunces – SIL Open Font License 1.1 (`public/fonts/LICENSES`).
- Ikonok: Tabler Icons – MIT-licenc (`resources/views/components/icons/LICENSE-tabler.txt`).

---

## English

A web application where a parent creates an illustrated 4–10 page bedtime story starring their own child. For each page they pick a background, the child figure and up to four animals, drag the figures into place and can flip them; an AI provider writes the title and the text. Short on time? The "Surprise story" fills the pages randomly. The finished story can be paged through in the browser, in night or day mode.

### Features
- Three-step wizard: the child's details, the story's topic and length, a page editor with draggable, flippable figures (mouse and touch).
- Random story or page: whatever the parent leaves empty is filled in by the system.
- Story generation with a single AI request, validated response and one automatic retry; a demo mode without AI.
- Reader with a title page, paging, night/day mode and labelling of AI-written text.
- Works for guests; after logging in or registering, the story in progress is kept and can be saved.
- Account: registration, login, my stories, account deletion.
- Admin: managing characters, actions and backgrounds with image upload; a list of all saved stories.

### Tech stack
Laravel 8 · PHP 8.1 · MySQL 8.4 · Blade + native JavaScript modules (no build step) · custom CSS with design tokens · PHPUnit · Docker (Render) · Aiven MySQL · Cloudflare R2 · Groq (OpenAI-compatible API).

### Local setup
1. `composer install`
2. `cp .env.example .env`, then fill in the database settings and `ADMIN_EMAIL` / `ADMIN_PASSWORD`.
3. `php artisan key:generate`
4. `php artisan storage:link`
5. `php artisan migrate --seed`
6. `php artisan serve` → http://127.0.0.1:8000

For the demo mode without AI, `STORY_GENERATOR=demo` is enough. For real text: `STORY_GENERATOR=groq` and `GROQ_API_KEY`.

### Tests
In a separate `mesegenerator_test` MySQL database: `php artisan test`. AI calls are always simulated in tests (`Http::fake()`).

### Deployment
The `Dockerfile` and `render.yaml` target Render's free tier. Secrets (database, R2, API key, admin) are set in the Render dashboard, and Aiven's CA certificate as a "Secret File" (`/etc/secrets/aiven-ca.pem`). Aiven may pause an unused free database; it can be restarted.

### Documentation
The planning documents are in `docs/` (PRD, App Flow, Tech Stack, Content Guidelines, Backend Schema, Implementation Plan); implementation decisions are in `docs/DECISIONS.md`.

### Credits
- Fonts: Nunito, Literata, Fraunces – SIL Open Font License 1.1 (`public/fonts/LICENSES`).
- Icons: Tabler Icons – MIT License (`resources/views/components/icons/LICENSE-tabler.txt`).

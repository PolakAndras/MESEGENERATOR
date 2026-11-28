# Mesegenerátor

> [Magyar](#magyar) · [English](#english)

**Élő demó / Live demo:** _hamarosan / coming soon_

---

## Magyar

Webalkalmazás, amelyben a szülő 4–10 oldalas, illusztrált esti mesét készít, és a mese főszereplője a saját gyereke. Oldalanként kiválasztja a hátteret, a gyerekfigurát és legfeljebb négy állatot, a figurákat a helyükre húzza és tükrözheti, a mese címét és szövegét pedig egy AI-szolgáltató írja meg. Ha nincs ideje összeállítani, a „Meglepetés mese” véletlenszerűen tölti ki az oldalakat. A kész mese böngészőben lapozható, esti és nappali módban.

### Funkciók
- Varázsló három lépésben: a gyerek adatai, a mese témája és hossza, oldalszerkesztő húzható és tükrözhető figurákkal (egér és érintés).
- Véletlenszerű mese vagy oldal: amit a szülő üresen hagy, azt a rendszer tölti ki, a mese történetéhez illő képpel (lefekvéskor alvó gyerek a hálószobában, végig ugyanaz az állattárs).
- Mesegyűjtemény AI nélkül: 15 választható téma, témánként két kézzel írt, összefüggő történet 10–10 jelenettel, jelenetenként két megfogalmazással. Rövidebb mesében a történet magja marad meg; ha a képen valaki mást csinál, mint a jelenetben, a szöveg megemlíti; ugyanazokkal a szereplőkkel is mindig más mese készül.
- Pózok: minden állatnak 5, a gyerekfigurának 5 kiválasztható mozdulata van (áll, ül, fut, alszik, integet stb.).
- Az AI-szolgáltató (Groq) a kódban megmaradt, de kikapcsolva; a `STORY_GENERATOR=groq` kapcsolja be.
- Olvasó címoldallal, lapozással és esti/nappali móddal (AI-szövegnél AI-jelöléssel).
- Vendégként is használható; belépés vagy regisztráció után a félkész mese megmarad és menthető.
- Fiók: regisztráció, belépés, Meséim, fióktörlés.
- Admin: szereplők, cselekvések, hátterek kezelése képfeltöltéssel; az összes mentett mese listája.

### Technológia
Laravel 8 · PHP 8.1 · MySQL 8.4 · Blade + natív JavaScript-modulok (build nélkül) · saját CSS dizájntokenekkel · PHPUnit · Pint · saját VPS (nginx + PHP-FPM + MySQL) · Groq (OpenAI-kompatibilis API).

### Telepítés helyben
1. `composer install`
2. `cp .env.example .env`, majd töltsd ki az adatbázis adatait és az `ADMIN_EMAIL` / `ADMIN_PASSWORD` értékét.
3. `php artisan key:generate`
4. `php artisan storage:link`
5. `php artisan migrate --seed`
6. `php artisan serve` → http://127.0.0.1:8000

Alapból a mesegyűjtemény fut (`STORY_GENERATOR=templates`), AI nélkül. Ha később AI kell: `STORY_GENERATOR=groq` és `GROQ_API_KEY`.

### Tesztek
Külön `mesegenerator_test` MySQL-adatbázisban: `php artisan test`. Az AI-hívás a tesztekben mindig szimulált (`Http::fake()`).

### Élesítés
Saját VPS-re (Ubuntu, nginx, PHP-FPM, MySQL), felhőszolgáltatás nélkül; a képek a szerver `storage/app/public/images` mappájában vannak. Első telepítéskor: `composer install --no-dev --optimize-autoloader`, a `.env` kitöltése (`APP_ENV=production`, `APP_DEBUG=false`), `php artisan key:generate`, `php artisan storage:link`, `php artisan migrate --force`, `php artisan db:seed --class="Database\\Seeders\\AdminUserSeeder" --force`, `php artisan catalog:install`, `php artisan db:seed --class="Database\\Seeders\\StoryTemplateSeeder" --force` (a mesegyűjtemény; minden frissítéskor futtatható), végül `php artisan config:cache` és `php artisan route:cache`. Az nginx dokumentumgyökere a `public` mappa; a `storage` és a `bootstrap/cache` legyen a `www-data` felhasználó írható mappája. Frissítéskor a `catalog:install` nem írja felül az admin módosításait.

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
- Random story or page: whatever the parent leaves empty is filled in by the system with a picture matching the story (a sleeping child in the bedroom at bedtime, the same animal companion throughout).
- Story collection without AI: 15 selectable themes, two hand-written, coherent stories of 10 scenes per theme, two wordings per scene. A shorter story keeps the core of the plot; when someone in the picture does something other than in the scene, the text mentions it; the same characters always give a different story.
- Poses: every animal and the child figure have 5 selectable movements (stand, sit, run, sleep, wave etc.).
- The AI provider (Groq) is still in the code but switched off; `STORY_GENERATOR=groq` turns it on.
- Reader with a title page, paging and night/day mode (with an AI label for AI text).
- Works for guests; after logging in or registering, the story in progress is kept and can be saved.
- Account: registration, login, my stories, account deletion.
- Admin: managing characters, actions and backgrounds with image upload; a list of all saved stories.

### Tech stack
Laravel 8 · PHP 8.1 · MySQL 8.4 · Blade + native JavaScript modules (no build step) · custom CSS with design tokens · PHPUnit · Pint · own VPS (nginx + PHP-FPM + MySQL) · Groq (OpenAI-compatible API).

### Local setup
1. `composer install`
2. `cp .env.example .env`, then fill in the database settings and `ADMIN_EMAIL` / `ADMIN_PASSWORD`.
3. `php artisan key:generate`
4. `php artisan storage:link`
5. `php artisan migrate --seed`
6. `php artisan serve` → http://127.0.0.1:8000

By default the story collection runs (`STORY_GENERATOR=templates`), without AI. If AI is needed later: `STORY_GENERATOR=groq` and `GROQ_API_KEY`.

### Tests
In a separate `mesegenerator_test` MySQL database: `php artisan test`. AI calls are always simulated in tests (`Http::fake()`).

### Deployment
On an own VPS (Ubuntu, nginx, PHP-FPM, MySQL), without cloud services; images live in the server's `storage/app/public/images` folder. First deploy: `composer install --no-dev --optimize-autoloader`, fill in `.env` (`APP_ENV=production`, `APP_DEBUG=false`), `php artisan key:generate`, `php artisan storage:link`, `php artisan migrate --force`, `php artisan db:seed --class="Database\\Seeders\\AdminUserSeeder" --force`, `php artisan catalog:install`, `php artisan db:seed --class="Database\\Seeders\\StoryTemplateSeeder" --force` (the story collection; safe to run on every update), then `php artisan config:cache` and `php artisan route:cache`. nginx's document root is the `public` folder; `storage` and `bootstrap/cache` must be writable by the `www-data` user. On updates `catalog:install` never overwrites the admin's changes.

### Documentation
The planning documents are in `docs/` (PRD, App Flow, Tech Stack, Content Guidelines, Backend Schema, Implementation Plan); implementation decisions are in `docs/DECISIONS.md`.

### Credits
- Fonts: Nunito, Literata, Fraunces – SIL Open Font License 1.1 (`public/fonts/LICENSES`).
- Icons: Tabler Icons – MIT License (`resources/views/components/icons/LICENSE-tabler.txt`).

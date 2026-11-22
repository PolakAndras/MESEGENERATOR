<?php

namespace App\Services;

use App\Models\Character;
use App\Support\PlacementRules;

/**
 * A munkamenetben tárolt félkész mese egyetlen kezelője (Backend Schema 9.). Kontroller és nézet nem nyúl
 * közvetlenül a story_draft kulcshoz, így a szerkezet egy helyen változtatható, és könnyen tesztelhető.
 * A munkamenetbe csak azonosítók és számok kerülnek; a nevek és képek a katalógusból töltődnek (9.2).
 *
 * The single owner of the story draft kept in the session (Backend Schema 9.). Controllers and views never touch
 * the story_draft key directly, so its shape changes in one place and is easy to test.
 * Only ids and numbers go into the session; names and images are loaded from the catalog (9.2).
 */
class StoryDraftService
{
    public const KEY = 'story_draft';

    public const STATUS_EDITING = 'editing';

    public const STATUS_GENERATED = 'generated';

    public const STATUS_SAVED = 'saved';

    public function get(): ?array
    {
        return session(self::KEY);
    }

    public function exists(): bool
    {
        return $this->get() !== null;
    }

    public function status(): ?string
    {
        return $this->get()['status'] ?? null;
    }

    // Generálás után a varázsló lépései zárolva vannak (PRD US-01 AC4, App Flow 4.2 lépésvédelem).
    // After generation the wizard steps are locked (PRD US-01 AC4, App Flow 4.2 step guard).
    public function isLocked(): bool
    {
        return in_array($this->status(), [self::STATUS_GENERATED, self::STATUS_SAVED], true);
    }

    public function child(): ?array
    {
        return $this->get()['child'] ?? null;
    }

    public function settings(): ?array
    {
        return $this->get()['settings'] ?? null;
    }

    public function pages(): array
    {
        return $this->get()['pages'] ?? [];
    }

    public function page(int $number): array
    {
        return $this->pages()[$number] ?? ['background_id' => null, 'placements' => []];
    }

    public function pageCount(): int
    {
        return (int) ($this->settings()['page_count'] ?? 0);
    }

    public function generated(): ?array
    {
        return $this->get()['generated'] ?? null;
    }

    public function savedStoryId(): ?int
    {
        return $this->get()['saved_story_id'] ?? null;
    }

    public function forget(): void
    {
        session()->forget(self::KEY);
    }

    /**
     * 1. lépés mentése. Ha a gyerekfigura megváltozik, minden oldalon lecseréli: pozíció marad, cselekvés
     * alaphelyzetbe áll (App Flow D-07, Backend Schema 9.3).
     * Saves step 1. If the child figure changes, it is swapped on every page: position stays, action resets
     * to the default (App Flow D-07, Backend Schema 9.3).
     *
     * @return bool igaz, ha a figura legalább egy oldalon lecserélődött / true if the figure was swapped on a page
     */
    public function saveChild(array $data, Character $figure): bool
    {
        $draft = $this->get() ?? $this->blank();
        $oldId = $draft['child']['character_id'] ?? null;
        $swapped = false;

        if ($oldId !== null && $oldId !== $figure->id) {
            $defaultActionId = $figure->defaultAction->id;
            foreach ($draft['pages'] as $n => $page) {
                foreach ($page['placements'] as $i => $placement) {
                    if ($placement['character_id'] === $oldId) {
                        $draft['pages'][$n]['placements'][$i]['character_id'] = $figure->id;
                        $draft['pages'][$n]['placements'][$i]['action_id'] = $defaultActionId;
                        $swapped = true;
                    }
                }
            }
        }

        $draft['child'] = [
            'name' => $data['child_name'],
            'age' => (int) $data['child_age'],
            'gender' => $data['child_gender'],
            'hair_color' => $data['child_hair_color'],
            'character_id' => $figure->id,
        ];
        if ($draft['last_step'] === 'child') {
            $draft['last_step'] = 'settings';
        }

        $this->put($draft);

        return $swapped;
    }

    /**
     * Azok a törlendő oldalak, amelyeken már van tartalom (App Flow S-08: csak ekkor kell megerősítés).
     * Pages that would be removed and already have content (App Flow S-08: confirmation is only needed then).
     */
    public function pagesLostOnReduce(int $newCount): array
    {
        return array_values(array_filter(
            array_keys($this->pages()),
            fn ($n) => $n > $newCount && $this->pageHasContent($n)
        ));
    }

    public function saveSettings(string $context, int $pageCount): void
    {
        $draft = $this->get();
        $draft['settings'] = ['context' => $context, 'page_count' => $pageCount];

        // Növeléskor új, üres oldalak; csökkentéskor a felesleges oldalak törlése (Backend Schema 9.3).
        // Growing adds empty pages; shrinking drops the extra pages (Backend Schema 9.3).
        $pages = [];
        for ($n = 1; $n <= $pageCount; $n++) {
            $pages[$n] = $draft['pages'][$n] ?? ['background_id' => null, 'placements' => []];
        }
        $draft['pages'] = $pages;

        if ($draft['last_step'] === 'settings') {
            $draft['last_step'] = 'page';
            $draft['last_page'] = null;
        }
        if ($draft['last_page'] !== null && $draft['last_page'] > $pageCount) {
            $draft['last_page'] = $pageCount;
        }

        $this->put($draft);
    }

    /**
     * Egy oldal mentése a beküldött (validált) adatokból. A meglévő figurák megtartják a hozzáadási helyüket,
     * az újak a lista végére kerülnek, mert a rétegsorrend egyenlő pos_y-nál ezt használja (CG 8.4).
     * Saves one page from the submitted (validated) data. Existing figures keep their insertion position and new ones
     * are appended, because the layer order uses it for equal pos_y (CG 8.4).
     */
    public function savePage(int $number, array $data): void
    {
        $draft = $this->get();
        $childId = $draft['child']['character_id'];
        $scale = PlacementRules::scale();

        $incoming = [];
        if (! empty($data['child_enabled'])) {
            $incoming[$childId] = [
                'character_id' => $childId,
                'action_id' => $this->actionOrDefault($childId, $data['child']['action_id'] ?? null),
                'pos_x' => (float) $data['child']['pos_x'],
                'pos_y' => (float) $data['child']['pos_y'],
                'scale' => $scale,
                'slot' => null,
                'flipped' => ! empty($data['child']['flipped']),
            ];
        }
        foreach ($data['animals'] ?? [] as $animal) {
            $id = (int) $animal['character_id'];
            $incoming[$id] = [
                'character_id' => $id,
                'action_id' => $this->actionOrDefault($id, $animal['action_id'] ?? null),
                'pos_x' => (float) $animal['pos_x'],
                'pos_y' => (float) $animal['pos_y'],
                'scale' => $scale,
                'slot' => (int) $animal['slot'],
                'flipped' => ! empty($animal['flipped']),
            ];
        }

        $placements = [];
        foreach ($draft['pages'][$number]['placements'] ?? [] as $old) {
            if (isset($incoming[$old['character_id']])) {
                $placements[] = $incoming[$old['character_id']];
                unset($incoming[$old['character_id']]);
            }
        }
        foreach ($incoming as $new) {
            $placements[] = $new;
        }

        $draft['pages'][$number] = [
            'background_id' => isset($data['background_id']) ? (int) $data['background_id'] : null,
            'placements' => $placements,
        ];
        $draft['last_step'] = 'page';
        $draft['last_page'] = $number;

        $this->put($draft);
    }

    /**
     * Kész oldal beírása (véletlen kitöltés, PRD US-21); a „hol tartok” adatot nem módosítja.
     * Stores a ready page (random fill, PRD US-21); it does not change the "where am I" data.
     */
    public function putPage(int $number, array $page): void
    {
        $draft = $this->get();
        $draft['pages'][$number] = $page;
        $this->put($draft);
    }

    public function firstPageWithoutBackground(): ?int
    {
        foreach ($this->pages() as $n => $page) {
            if ($page['background_id'] === null) {
                return $n;
            }
        }

        return null;
    }

    public function setGenerated(string $title, array $pages): void
    {
        $draft = $this->get();
        $draft['generated'] = ['title' => $title, 'pages' => $pages];
        $draft['status'] = self::STATUS_GENERATED;
        $this->put($draft);
    }

    public function markSaved(int $storyId): void
    {
        $draft = $this->get();
        $draft['status'] = self::STATUS_SAVED;
        $draft['saved_story_id'] = $storyId;
        $this->put($draft);
    }

    /**
     * Hová vigye a „Folytatom” gomb, illetve a belépés utáni átirányítás (App Flow S-05, D-23).
     * Where "Continue" and the post-login redirect lead (App Flow S-05, D-23).
     */
    public function resumeUrl(): ?string
    {
        $draft = $this->get();
        if ($draft === null || $draft['status'] === self::STATUS_SAVED) {
            return null;
        }
        if ($draft['status'] === self::STATUS_GENERATED) {
            return route('stories.result');
        }

        switch ($draft['last_step']) {
            case 'page':
                return route('stories.create.page', ['page' => $draft['last_page'] ?? 1]);
            case 'settings':
                return route('stories.create.settings');
            default:
                return route('stories.create.child');
        }
    }

    /**
     * Lépésvédelem (App Flow 4.2): ha a kért lépés nem elérhető, a cél URL-t adja vissza, egyébként null.
     * Step guard (App Flow 4.2): returns the target URL when the requested step is not available, otherwise null.
     */
    public function guard(string $step): ?string
    {
        if ($this->isLocked()) {
            return route('stories.result');
        }
        if (in_array($step, ['settings', 'page'], true) && $this->child() === null) {
            return route('stories.create.child');
        }
        if ($step === 'page' && $this->settings() === null) {
            return route('stories.create.settings');
        }

        return null;
    }

    private function pageHasContent(int $number): bool
    {
        $page = $this->page($number);

        return $page['background_id'] !== null || $page['placements'] !== [];
    }

    // Üres cselekvésnél a szereplő alaphelyzete (BR-09); a munkamenetben az action_id mindig ki van töltve (9.2).
    // An empty action means the character's default action (BR-09); the session always holds an action_id (9.2).
    private function actionOrDefault(int $characterId, $actionId): int
    {
        if (! empty($actionId)) {
            return (int) $actionId;
        }

        return Character::findOrFail($characterId)->defaultAction->id;
    }

    private function blank(): array
    {
        return [
            'status' => self::STATUS_EDITING,
            'last_step' => 'child',
            'last_page' => null,
            'child' => null,
            'settings' => null,
            'pages' => [],
            'generated' => null,
            'saved_story_id' => null,
        ];
    }

    private function put(array $draft): void
    {
        session()->put(self::KEY, $draft);
    }
}

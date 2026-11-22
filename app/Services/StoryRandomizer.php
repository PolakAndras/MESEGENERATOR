<?php

namespace App\Services;

use App\Models\Background;
use App\Models\Character;
use App\Support\PlacementRules;

/**
 * Véletlenszerű oldalak (PRD US-21): ha a szülőnek nincs ideje összeállítani a képeket, a rendszer választ hátteret,
 * dönti el, szerepel-e a gyerek, és tesz az oldalra néhány állatot véletlen cselekvéssel és iránnyal.
 * A kimenet a munkamenet oldalszerkezete (Backend Schema 9.2), így utána ugyanaz a validáció, mentés és megjelenítés
 * fut rajta, mint a kézzel összeállított oldalakon. A pozíciók a kezdőhelyek (NFR-04), így a vásznon belül maradnak.
 *
 * Random pages (PRD US-21): when the parent has no time to compose the pictures, the system picks a background,
 * decides whether the child appears and adds a few animals with a random action and direction.
 * The output is the session page shape (Backend Schema 9.2), so the same validation, saving and rendering run on it
 * as on hand-made pages. Positions are the start slots (NFR-04), so figures stay on the canvas.
 */
class StoryRandomizer
{
    private ?array $catalog = null;

    /**
     * Egy teljes, véletlen oldal. / One complete random page.
     */
    public function page(int $childId, bool $forceChild = false): array
    {
        $catalog = $this->catalog();
        $placement = config('story.placement');
        $scale = PlacementRules::scale();
        $placements = [];

        if ($forceChild || random_int(1, 100) <= (int) config('story.random.child_chance')) {
            $child = $catalog['figures'][$childId] ?? Character::with('actions')->find($childId);
            $placements[] = [
                'character_id' => $childId,
                'action_id' => $child->actions->random()->id,
                'pos_x' => (float) $placement['child']['x'],
                'pos_y' => (float) $placement['child']['y'],
                'scale' => $scale,
                'slot' => null,
                'flipped' => false,
            ];
        }

        $max = min(
            (int) config('story.random.animals_max'),
            (int) config('story.limits.user.animals_per_page'),
            $catalog['animals']->count()
        );
        $count = $max > 0 ? random_int(1, $max) : 0;
        foreach ($catalog['animals']->random($count)->values() as $i => $animal) {
            $slot = $i + 1;
            $placements[] = [
                'character_id' => $animal->id,
                'action_id' => $animal->actions->random()->id,
                'pos_x' => (float) $placement['animal_slots'][$slot]['x'],
                'pos_y' => (float) $placement['animal_slots'][$slot]['y'],
                'scale' => $scale,
                'slot' => $slot,
                // Az állat a gyerek felé nézzen: a bal oldaliak jobbra (alap), a jobb oldaliak balra (tükrözve).
                // Animals face the child: those on the left look right (default), those on the right left (flipped).
                'flipped' => $placement['animal_slots'][$slot]['x'] > $placement['child']['x'],
            ];
        }

        return ['background_id' => $catalog['backgrounds']->random(), 'placements' => $placements];
    }

    public function backgroundId(): int
    {
        return $this->catalog()['backgrounds']->random();
    }

    /**
     * A vázlat hiányzó részeinek kitöltése: a teljesen üres oldal véletlen oldalt kap, a háttér nélküli csak hátteret.
     * A szülő által összeállított figurák nem változnak.
     * Fills the missing parts of the draft: a completely empty page gets a random page, a page without a background
     * only a background. Figures composed by the parent stay unchanged.
     */
    public function fillMissing(StoryDraftService $drafts): void
    {
        $childId = $drafts->child()['character_id'];
        $last = $drafts->pageCount();

        foreach ($drafts->pages() as $number => $page) {
            if ($page['background_id'] === null && $page['placements'] === []) {
                // Az első és az utolsó oldalon mindig ott a gyerek: ő a főszereplő (BR-11).
                // The child is always on the first and last page: they are the main character (BR-11).
                $drafts->putPage($number, $this->page($childId, $number === 1 || $number === $last));
            } elseif ($page['background_id'] === null) {
                $drafts->putPage($number, ['background_id' => $this->backgroundId()] + $page);
            }
        }
    }

    private function catalog(): array
    {
        return $this->catalog ??= [
            'backgrounds' => Background::pluck('id'),
            'animals' => Character::where('type', Character::TYPE_ANIMAL)->whereHas('actions')->with('actions')->get(),
            'figures' => Character::where('type', Character::TYPE_CHILD_FIGURE)->with('actions')->get()->keyBy('id')->all(),
        ];
    }
}

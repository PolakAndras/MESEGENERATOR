<?php

namespace App\Services;

use App\Models\Background;
use App\Models\Character;
use App\Models\StoryScene;
use App\Support\PlacementRules;

/**
 * Véletlenszerű oldalak (PRD 1.6 US-21): ha a szülőnek nincs ideje összeállítani a képeket, a rendszer a mese terve
 * (StoryPlanner) szerinti jelenetből rakja össze az oldalt: a jelenet helyszínéhez illő háttér, a gyerek és az állattárs
 * a jelenethez illő pózban (pl. lefekvéskor alszanak), és ha a jelenetben valaki új érkezik, egy mellékszereplő is.
 * Így a kép ugyanazt meséli, mint a szöveg, és a társ minden oldalon ugyanaz az állat.
 * A kimenet a munkamenet oldalszerkezete (Backend Schema 9.2), így utána ugyanaz a validáció, mentés és megjelenítés
 * fut rajta, mint a kézzel összeállított oldalakon. A pozíciók a kezdőhelyek (NFR-04), így a vásznon belül maradnak.
 *
 * Random pages (PRD 1.6 US-21): when the parent has no time to compose the pictures, the system builds the page from
 * the scene of the story plan (StoryPlanner): a background matching the scene's place, the child and the animal
 * companion in poses matching the scene (e.g. asleep at bedtime), plus an extra animal when someone new arrives.
 * So the picture tells what the text tells, and the companion is the same animal on every page.
 * The output is the session page shape (Backend Schema 9.2), so the same validation, saving and rendering run on it
 * as on hand-made pages. Positions are the start slots (NFR-04), so figures stay on the canvas.
 */
class StoryRandomizer
{
    private StoryPlanner $planner;

    private ?array $catalog = null;

    public function __construct(StoryPlanner $planner)
    {
        $this->planner = $planner;
    }

    /**
     * Az oldal teljes, a jelenethez illő képe. / The page's complete picture matching its scene.
     */
    public function page(StoryDraftService $drafts, int $number): array
    {
        $scene = $this->planner->scene($drafts, $number);
        $plan = $drafts->plan();
        $placement = config('story.placement');
        $childX = (float) $placement['child']['x'];

        $placements = [$this->figure($drafts->child()['character_id'], $scene->child_pose, $placement['child'], null, false)];

        // A társ a gyerek mellett, felé fordulva; a mellékszereplő a túloldali szélen. Az állat alapból jobbra néz,
        // ezért a gyerektől jobbra állót tükrözzük.
        // The companion next to the child, facing them; the extra animal at the far other side. Animals face right by
        // default, so the one right of the child is flipped.
        $companionSlot = collect(config('story.scenes.companion_slots'))->random();
        $animals = [[$plan['companion_id'], $companionSlot]];
        if ($scene->extra_animal) {
            $animals[] = [$plan['extra_id'], $placement['animal_slots'][$companionSlot]['x'] > $childX ? 1 : count($placement['animal_slots'])];
        }
        foreach ($animals as [$id, $slot]) {
            if ($id !== null && isset($this->catalog()['characters'][$id])) {
                $at = $placement['animal_slots'][$slot];
                $placements[] = $this->figure($id, $scene->friend_pose, $at, $slot, $at['x'] > $childX);
            }
        }

        return ['background_id' => $this->backgroundId($scene), 'placements' => $placements];
    }

    /**
     * A vázlat hiányzó részeinek kitöltése: a teljesen üres oldal a jelenet képét kapja, a háttér nélküli csak a
     * jelenethez illő hátteret. A szülő által összeállított figurák nem változnak.
     * Fills the missing parts of the draft: a completely empty page gets its scene's picture, a page without a
     * background only the matching background. Figures composed by the parent stay unchanged.
     */
    public function fillMissing(StoryDraftService $drafts): void
    {
        foreach ($drafts->pages() as $number => $page) {
            if ($page['background_id'] === null && $page['placements'] === []) {
                $drafts->putPage($number, $this->page($drafts, $number));
            } elseif ($page['background_id'] === null) {
                $drafts->putPage($number, ['background_id' => $this->backgroundId($this->planner->scene($drafts, $number))] + $page);
            }
        }
    }

    private function figure(int $characterId, string $poseKind, array $at, ?int $slot, bool $flipped): array
    {
        return [
            'character_id' => $characterId,
            'action_id' => $this->actionId($characterId, $poseKind),
            'pos_x' => (float) $at['x'],
            'pos_y' => (float) $at['y'],
            'scale' => PlacementRules::scale(),
            'slot' => $slot,
            'flipped' => $flipped,
        ];
    }

    // A póztípushoz illő cselekvések közül egy véletlen; ha a szereplőnek nincs ilyen, az alaphelyzete (BR-09).
    // A random action of the pose kind; if the character has none, its default action (BR-09).
    private function actionId(int $characterId, string $poseKind): int
    {
        $actions = $this->catalog()['characters'][$characterId]->actions;
        $names = config('story.scenes.pose_kinds.'.$poseKind, []);
        $matching = $actions->filter(fn ($action) => in_array($action->name, $names, true));

        return ($matching->isNotEmpty() ? $matching->random() : $actions->firstWhere('is_default', true) ?? $actions->first())->id;
    }

    // A jelenet helyszínéhez tartozó háttér; ha az admin átnevezte, egy véletlen háttér.
    // The background of the scene's place; if the admin renamed it, a random background.
    private function backgroundId(StoryScene $scene): int
    {
        $name = config('story.scenes.background_kinds.'.$scene->place);
        $backgrounds = $this->catalog()['backgrounds'];

        return $backgrounds->get($name) ?? $backgrounds->random();
    }

    private function catalog(): array
    {
        return $this->catalog ??= [
            'backgrounds' => Background::pluck('id', 'name'),
            'characters' => Character::whereHas('actions')->with('actions')->get()->keyBy('id')->all(),
        ];
    }
}

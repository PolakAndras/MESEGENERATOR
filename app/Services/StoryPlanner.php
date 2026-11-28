<?php

namespace App\Services;

use App\Models\Character;
use App\Models\StoryScene;
use Illuminate\Support\Collection;

/**
 * A mese „forgatókönyve” (PRD 1.6 US-21, US-24): a téma két cselekménye közül az egyik, oldalanként egy jelenet,
 * a mese állandó állattársa és egy mellékszereplő. A terv a munkamenetben, a vázlattal együtt él (StoryDraftService),
 * így a véletlen kép (StoryRandomizer) és a szöveg (TemplateStoryGenerator) ugyanabból a jelenetből dolgozik, és az
 * „Új szöveg” is ugyanazt a történetet meséli újra, más megfogalmazásban. A téma vagy az oldalszám változásakor
 * a terv törlődik, és a következő használatkor új készül.
 *
 * The story's "script" (PRD 1.6 US-21, US-24): one of the theme's two plots, one scene per page, the story's constant
 * animal companion and an extra animal. The plan lives in the session with the draft (StoryDraftService), so the random
 * picture (StoryRandomizer) and the text (TemplateStoryGenerator) work from the same scene, and "New text" retells the
 * same story in other words. When the theme or the page count changes the plan is dropped and rebuilt on next use.
 */
class StoryPlanner
{
    /**
     * A vázlat terve; ha még nincs, most készül el és mentődik. / The draft's plan; built and stored if missing.
     *
     * @return array ['plot' => int, 'steps' => [oldal => jelenet], 'companion_id' => ?int, 'extra_id' => ?int]
     */
    public function plan(StoryDraftService $drafts): array
    {
        if (($plan = $drafts->plan()) !== null) {
            return $plan;
        }

        $themeId = $drafts->settings()['theme_id'];
        $plot = (int) StoryScene::where('story_theme_id', $themeId)->distinct()->pluck('plot')->random();
        $scenes = StoryScene::where('story_theme_id', $themeId)->where('plot', $plot)->pluck('weight', 'step')->all();
        $animals = Character::where('type', Character::TYPE_ANIMAL)->whereHas('actions')->pluck('id')->shuffle();

        $plan = [
            'plot' => $plot,
            'steps' => self::steps($scenes, $drafts->pageCount()),
            'companion_id' => $animals->get(0),
            'extra_id' => $animals->get(1),
        ];
        $drafts->savePlan($plan);

        return $plan;
    }

    /**
     * Az oldal jelenete a tervből. / The page's scene from the plan.
     */
    public function scene(StoryDraftService $drafts, int $page): StoryScene
    {
        $plan = $this->plan($drafts);

        return StoryScene::where('story_theme_id', $drafts->settings()['theme_id'])
            ->where('plot', $plan['plot'])
            ->where('step', $plan['steps'][$page])
            ->firstOrFail();
    }

    /**
     * Melyik jelenet melyik oldalra kerüljön: az első és az utolsó jelenet mindig, utána a nagyobb súlyúak (azonos
     * súlynál véletlenül), végül időrendbe rendezve. Így 4 oldalon a cselekmény magja (kiindulás, gond, megoldás,
     * lefekvés) marad meg, 10 oldalon a teljes történet. Több oldal nem lehet, mint jelenet (config: pages_max = 10).
     * Which scene goes on which page: the first and last scene always, then the heavier ones (random among equal
     * weights), finally sorted in time order. So 4 pages keep the plot's core (setup, trouble, resolution, bedtime)
     * and 10 pages the whole story. There are never more pages than scenes (config: pages_max = 10).
     *
     * @param  array  $weights  [jelenet => súly] / [step => weight]
     * @return array [1 => jelenet, 2 => jelenet, …] / [1 => step, 2 => step, …]
     */
    public static function steps(array $weights, int $pageCount): array
    {
        $first = min(array_keys($weights));
        $last = max(array_keys($weights));
        $middle = Collection::make($weights)->except([$first, $last])
            ->map(fn ($weight, $step) => [$step, $weight + mt_rand() / mt_getrandmax()])
            ->sortByDesc(fn ($pair) => $pair[1])
            ->take(max(0, $pageCount - 2))
            ->map(fn ($pair) => $pair[0])
            ->all();

        $steps = array_merge([$first], $middle, $pageCount > 1 ? [$last] : []);
        sort($steps);

        return array_combine(range(1, count($steps)), $steps);
    }
}

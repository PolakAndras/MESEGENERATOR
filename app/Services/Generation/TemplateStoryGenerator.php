<?php

namespace App\Services\Generation;

use App\Contracts\StoryTextGenerator;
use App\Models\Character;
use App\Models\StoryScene;
use App\Models\StorySentence;
use App\Models\StoryTheme;
use App\Services\StoryPlanner;
use Illuminate\Support\Str;

/**
 * Mese a mesegyűjteményből, AI nélkül (PRD 1.6 US-24). Ez az alapértelmezett generátor; az AI-szolgáltató a
 * StoryTextGenerator interfész mögött bekapcsolható marad (STORY_GENERATOR=groq), de jelenleg nem használjuk.
 *
 * A mese gerince egy kézzel írt cselekmény: oldalanként egy jelenet a mese tervéből (StoryPlanner), két megfogalmazás
 * közül véletlenszerűen választva, így a történet összefüggő, mégis minden generáláskor más szöveg születik.
 * A társ a képeken legtöbbször szereplő állat (kézi összeállításnál is), különben a terv állata.
 * A képhez igazodó kiegészítések, ha a korhoz illő hossz (BR-17) engedi:
 * - pózmondat, ha a képen egy szereplő mást csinál, mint amit a jelenet amúgy is elmesél (pl. a szülő alvó rókát tett
 *   a születésnapi jelenetbe), így a kép mozdulata a szövegben is megjelenik;
 * - helyszínmondat, ha a szülő a jelenettől eltérő hátteret választott (és az előző oldalhoz képest is új a helyszín).
 * A jelenet szövege a saját helyszínét és mozdulatait amúgy is elmeséli, ezért a véletlen módban készült képekhez nem
 * kell kiegészítés – így nem kerülhet esős jelenetbe „sütött a nap”-féle ellentmondás.
 * A legkisebbek (2–4 év) csak a jelenet első két mondatát kapják. A válasz ugyanabban a JSON-formában jön, mint az AI-é,
 * így ugyanaz az ellenőrzés fut rajta (StoryResponseParser).
 *
 * A story from the story collection, without AI (PRD 1.6 US-24). This is the default generator; the AI provider stays
 * switchable behind the StoryTextGenerator interface (STORY_GENERATOR=groq) but is currently unused.
 * The backbone is a hand-written plot: one scene per page from the story plan (StoryPlanner), one of two wordings
 * picked at random, so the story is coherent yet every generation gives a different text. The companion is the animal
 * appearing most often in the pictures (also when composed by hand), otherwise the plan's animal.
 * Additions following the picture, when the age-appropriate length (BR-17) allows: a pose sentence when a character
 * in the picture does something other than the scene tells anyway; a place sentence when the parent chose a background
 * other than the scene's (and new since the previous page). The scene text already tells its own place and moves, so
 * pictures built in random mode need no additions – no "the sun was shining" can slip into a rainy scene. The youngest (2–4) only get the scene's first two sentences. The answer
 * uses the AI's JSON shape, so the same validation runs on it (StoryResponseParser).
 */
class TemplateStoryGenerator implements StoryTextGenerator
{
    // A mondathatár: írásjel után nagybetű, nyitó idézőjel vagy új megszólalás („– Nagybetű”). A „! – kiáltotta”
    // folytatás nem határ, mert a gondolatjel után kisbetű áll.
    // Sentence boundary: punctuation followed by a capital, an opening quote or a new utterance ("– Capital").
    // "! – kiáltotta" is not a boundary, as a lowercase letter follows the dash.
    private const SENTENCE_BOUNDARY = '/(?<=[.!?…])\s+(?=\p{Lu}|„|– \p{Lu})/u';

    private array $used = [];

    private array $pool = [];

    public function generate(array $story): string
    {
        $this->used = [];
        $theme = StoryTheme::find($story['theme_id'] ?? null) ?? StoryTheme::whereHas('scenes')->inRandomOrder()->firstOrFail();
        $pages = array_values($story['pages']);
        $count = count($pages);

        $plot = $story['plot'] ?? (int) $theme->scenes()->distinct()->pluck('plot')->random();
        $scenes = $theme->scenes()->where('plot', $plot)->get()->keyBy('step');
        $steps = array_values($story['steps'] ?? StoryPlanner::steps($scenes->pluck('weight', 'step')->all(), $count));
        $this->pool = StorySentence::where('story_theme_id', $theme->id)->orWhereNull('story_theme_id')->get()
            ->groupBy(fn ($s) => $s->kind.'|'.$s->key)->map(fn ($g) => $g->pluck('text', 'id')->all())->all();

        $child = $story['child'];
        $companion = $this->companion($pages, $story['companion'] ?? null);
        $vars = [
            ':name' => $child['name'],
            ':kid' => $child['gender'] === 'fiú' ? 'kisfiú' : 'kislány',
            ':Friend' => Str::ucfirst($this->withArticle($companion)),
            ':friend' => $this->withArticle($companion),
        ];

        $out = [];
        $previousPlace = null;
        foreach ($pages as $i => $page) {
            $scene = $scenes[$steps[$i]];
            $sentences = $this->sentences(strtr(collect($scene->texts)->random(), $vars));

            if ((int) $child['age'] <= 4) {
                $sentences = array_slice($sentences, 0, 2);
            } else {
                $room = max(0, $this->sentenceTarget((int) $child['age']) - count($sentences));
                $placeChanged = $page['background'] !== $previousPlace
                    && $page['background'] !== config('story.scenes.background_kinds.'.$scene->place);
                $sentences = $this->withExtras($sentences, $page, $scene, $child['name'], $companion, $placeChanged, $i === $count - 1, $room);
            }
            $previousPlace = $page['background'];

            $out[] = ['page' => $page['page'], 'text' => implode(' ', $sentences)];
        }

        $title = strtr($this->pick('title|') ?? ':name meséje', $vars);

        return json_encode(['title' => $title, 'pages' => $out], JSON_UNESCAPED_UNICODE);
    }

    // BR-17: oldalanként 5–7 év: 2–4 mondat; 8–10 év: 3–6 mondat (a jelenet mondataival együtt).
    // BR-17: per page ages 5–7: 2–4 sentences; 8–10: 3–6 (including the scene's sentences).
    private function sentenceTarget(int $age): int
    {
        return $age <= 7 ? random_int(2, 4) : random_int(3, 6);
    }

    private function sentences(string $text): array
    {
        return preg_split(self::SENTENCE_BOUNDARY, trim($text));
    }

    /**
     * A jelenet mondatai a képhez igazodó kiegészítésekkel, legfeljebb $room új mondattal, a jelenet után (az utolsó
     * oldalon a búcsú, „Jó éjszakát!” elé), hogy a jelenet gondolatmenetét ne szakítsák meg.
     * The scene's sentences with picture-based additions, at most $room new sentences, after the scene (on the last
     * page before the goodbye), so they do not break the scene's train of thought.
     */
    private function withExtras(array $sentences, array $page, StoryScene $scene, string $childName, string $companion, bool $placeChanged, bool $isLast, int $room): array
    {
        // A gyerek és a társ előbb, mert a szöveg róluk szól; utána a többi állat.
        // Child and companion first, as the text is about them; the other animals after.
        $main = [];
        $others = [];
        if ($this->differs($page['child_action'], $scene->child_pose)) {
            $main[] = [$childName, $page['child_action']];
        }
        foreach ($page['animals'] as $animal) {
            if ($this->differs($animal['action'], $scene->friend_pose)) {
                $pose = [$this->withArticle($animal['name']), $animal['action']];
                if ($animal['name'] === $companion) {
                    $main[] = $pose;
                } else {
                    $others[] = $pose;
                }
            }
        }

        $poseSentences = [];
        foreach (array_merge($main, $others) as $pose) {
            if (count($poseSentences) >= $room) {
                break;
            }
            if (($sentence = $this->poseSentence($pose[0], $pose[1])) !== null) {
                $poseSentences[] = $sentence;
            }
        }

        $place = null;
        if ($placeChanged && $page['background'] !== '' && count($poseSentences) < $room) {
            $place = $this->pick('place|'.$page['background']);
        }

        $tail = $isLast && count($sentences) > 1 ? [array_pop($sentences)] : [];

        return array_values(array_filter(array_merge($sentences, [$place], $poseSentences, $tail)));
    }

    // Eltér-e a kép a jelenettől: az alaphelyzet (áll) sosem kap külön mondatot, és a jelenethez illő póz sem.
    // Does the picture differ from the scene: the default (áll) never gets its own sentence, nor a matching pose.
    private function differs(?string $action, string $poseKind): bool
    {
        if ($action === null || $action === '' || in_array($action, config('story.scenes.pose_kinds.stand', []), true)) {
            return false;
        }

        return ! in_array($action, config('story.scenes.pose_kinds.'.$poseKind, []), true);
    }

    // A póz saját mondatai; az általános mondat csak ismeretlen (pl. admin által felvett) cselekvésnél jön elő.
    // The pose's own sentences; the generic sentence only applies to unknown (e.g. admin-added) actions.
    private function poseSentence(string $who, string $action): ?string
    {
        $sentence = isset($this->pool['action|'.$action]) ? $this->pick('action|'.$action) : $this->pick('action|');

        return $sentence === null ? null : strtr($sentence, [':Who' => Str::ucfirst($who), ':who' => $who, ':action' => $action]);
    }

    /**
     * A társ: a képeken legtöbbször szereplő állat (egyenlőségnél a korábban felbukkanó), ennek híján a terv állata,
     * végső esetben egy véletlen állat a katalógusból.
     * The companion: the animal appearing most often in the pictures (on a tie the earlier one), otherwise the plan's
     * animal, as a last resort a random animal from the catalog.
     */
    private function companion(array $pages, ?string $planned): string
    {
        $counts = [];
        foreach ($pages as $page) {
            foreach ($page['animals'] as $animal) {
                $counts[$animal['name']] = ($counts[$animal['name']] ?? 0) + 1;
            }
        }
        if ($counts !== []) {
            return array_search(max($counts), $counts, true);
        }

        return $planned ?? Character::where('type', Character::TYPE_ANIMAL)->inRandomOrder()->value('name') ?? 'Mackó';
    }

    // Véletlen, a mesében még nem használt mondat; ha elfogyott, a kiegészítés elmarad (null), hogy semmi ne ismétlődjön.
    // A random sentence not used in this story yet; when none is left the addition is skipped (null), so nothing repeats.
    private function pick(string $slot): ?string
    {
        $fresh = array_diff_key($this->pool[$slot] ?? [], $this->used);
        if ($fresh === []) {
            return null;
        }
        $id = array_rand($fresh);
        $this->used[$id] = true;

        return $fresh[$id];
    }

    private function withArticle(string $name): string
    {
        $noun = Str::lower($name);

        return (preg_match('/^[aáeéiíoóöőuúüű]/u', $noun) ? 'az ' : 'a ').$noun;
    }
}

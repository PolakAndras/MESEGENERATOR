<?php

namespace Tests\Feature\Wizard;

use App\Models\Action;
use App\Models\StoryScene;
use App\Models\StorySentence;
use App\Models\StoryTheme;
use App\Services\Generation\StoryResponseParser;
use App\Services\Generation\TemplateStoryGenerator;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsStories;
use Tests\TestCase;

// PRD 1.6 US-24: mesegyűjtemény AI nélkül – 15 téma × 2 kézzel írt cselekmény; a szöveg a cselekményt követi,
// a társ állandó, a kép eltérő mozdulata megjelenik, változatos, a korhoz igazított hossz.
// PRD 1.6 US-24: story collection without AI – 15 themes × 2 hand-written plots; the text follows the plot, the
// companion is constant, a differing pose in the picture shows up, varied, length fitted to age.
class TemplateStoryGeneratorTest extends TestCase
{
    use RefreshDatabase, BuildsStories;

    private const BOUNDARY = '/(?<=[.!?…])\s+(?=\p{Lu}|„|– \p{Lu})/u';

    protected function setUp(): void
    {
        parent::setUp();
        $this->makeCatalog();
    }

    private function input(array $overrides = [], string $childAction = 'áll', string $foxAction = 'áll'): array
    {
        $pages = [];
        for ($n = 1; $n <= 10; $n++) {
            $pages[] = ['page' => $n, 'background' => 'Napos rét', 'child_action' => $childAction,
                'animals' => [['name' => 'Róka', 'action' => $foxAction]]];
        }

        return array_merge([
            'child' => ['name' => 'Panni', 'age' => 6, 'gender' => 'lány'],
            'context' => 'Barátság',
            'theme_id' => $this->theme->id,
            'plot' => 1,
            'steps' => array_combine(range(1, 10), range(1, 10)),
            'companion' => 'Nyúl',
            'pages' => $pages,
        ], $overrides);
    }

    private function generate(array $input): array
    {
        return StoryResponseParser::parse((new TemplateStoryGenerator())->generate($input), count($input['pages']));
    }

    public function test_collection_has_15_themes_with_two_complete_plots()
    {
        $kinds = array_keys(config('story.scenes.pose_kinds'));
        $places = array_keys(config('story.scenes.background_kinds'));
        $this->assertSame(15, StoryTheme::count());

        foreach (StoryTheme::all() as $theme) {
            $this->assertGreaterThanOrEqual(3, $theme->sentences()->where('kind', 'title')->count(), $theme->slug);
            foreach ([1, 2] as $plot) {
                $scenes = $theme->scenes()->where('plot', $plot)->orderBy('step')->get();
                $label = "{$theme->slug}/$plot";
                $this->assertSame(range(1, 10), $scenes->pluck('step')->all(), $label);
                // A nyitás és a lefekvés mindig benne van, és a mag elfér a legrövidebb mesében is.
                // Opening and bedtime are always in, and the core fits even the shortest story.
                $this->assertSame(3, $scenes->first()->weight, $label);
                $this->assertSame(3, $scenes->last()->weight, $label);
                $this->assertLessThanOrEqual(config('story.limits.guest.pages_min'), $scenes->where('weight', 3)->count(), $label);
                $this->assertSame('sleep', $scenes->last()->child_pose, $label);
                foreach ($scenes as $scene) {
                    $this->assertCount(2, $scene->texts, "$label/{$scene->step}");
                    $this->assertContains($scene->place, $places);
                    $this->assertContains($scene->child_pose, $kinds);
                    $this->assertContains($scene->friend_pose, $kinds);
                }
            }
        }
    }

    public function test_every_seeded_pose_and_background_has_its_own_sentences()
    {
        $this->seed(CatalogSeeder::class);

        foreach (Action::where('is_default', false)->distinct()->pluck('name') as $name) {
            $this->assertTrue(StorySentence::where('kind', 'action')->where('key', $name)->exists(), "missing pose sentences: $name");
        }
        foreach (config('story.scenes.background_kinds') as $background) {
            $this->assertTrue(StorySentence::where('kind', 'place')->where('key', $background)->exists(), "missing place sentences: $background");
        }
    }

    // Minden téma minden jelenete kitölthető: nem marad helyőrző, és a társ neve névelővel kerül be.
    // Every scene of every theme fills in: no placeholder is left and the companion gets its article.
    public function test_every_scene_fills_in_without_placeholders()
    {
        foreach (StoryScene::distinct()->get(['story_theme_id', 'plot']) as $plot) {
            $story = $this->generate($this->input(['theme_id' => $plot->story_theme_id, 'plot' => $plot->plot]));
            $text = $story['title'].' '.implode(' ', $story['pages']);

            $this->assertDoesNotMatchRegularExpression('/:[a-zA-Z]/', $text, "theme {$plot->story_theme_id}/{$plot->plot}");
            $this->assertStringNotContainsString(' Róka', $text);
        }
    }

    // A szöveg a terv jeleneteit követi, időrendben. / The text follows the plan's scenes in order.
    public function test_pages_follow_the_planned_scenes_in_order()
    {
        $steps = [1 => 1, 2 => 3, 3 => 8, 4 => 10];
        $input = $this->input(['steps' => $steps]);
        $input['pages'] = array_slice($input['pages'], 0, 4);
        $story = $this->generate($input);
        $scenes = $this->theme->scenes()->where('plot', 1)->get()->keyBy('step');

        foreach ($steps as $page => $step) {
            $openings = collect($scenes[$step]->texts)
                ->map(fn ($t) => preg_split(self::BOUNDARY, strtr($t, [':name' => 'Panni', ':Friend' => 'A róka', ':friend' => 'a róka', ':kid' => 'kislány']))[0]);
            $this->assertTrue($openings->contains(fn ($first) => str_starts_with($story['pages'][$page], $first)), "page $page");
        }
    }

    // A társ a képeken legtöbbször szereplő állat, nem a terv állata (kézi összeállításnál ez a helyes).
    // The companion is the animal appearing most in the pictures, not the plan's animal (right for manual pages).
    public function test_companion_is_the_animal_in_the_pictures()
    {
        $text = implode(' ', $this->generate($this->input())['pages']);

        $this->assertStringContainsString('a róka', mb_strtolower($text));
        $this->assertStringNotContainsString('nyúl', mb_strtolower($text));
    }

    public function test_companion_falls_back_to_the_planned_animal()
    {
        $input = $this->input();
        foreach (array_keys($input['pages']) as $i) {
            $input['pages'][$i]['animals'] = [];
        }

        $this->assertStringContainsString('a nyúl', mb_strtolower(implode(' ', $this->generate($input)['pages'])));
    }

    // Ha a képen más mozdulat van, mint amit a jelenet elmesél, a szöveg megemlíti; az alaphelyzet (áll) soha.
    // A pose differing from the scene shows up in the text; the default (áll) never does.
    public function test_differing_pose_appears_and_default_pose_does_not()
    {
        $older = ['child' => ['name' => 'Panni', 'age' => 9, 'gender' => 'lány']];
        $sleeping = StorySentence::where('kind', 'action')->where('key', 'alszik')->pluck('text')
            ->map(fn ($t) => str_replace(':Who ', 'A róka ', $t));
        $anyPose = StorySentence::where('kind', 'action')->pluck('text')->map(fn ($t) => str_replace(':Who ', '', $t));

        $text = implode(' ', $this->generate($this->input($older, 'áll', 'alszik'))['pages']);
        $this->assertTrue($sleeping->contains(fn ($s) => str_contains($text, $s)), 'Az alvó róka nem jelent meg a szövegben.');

        $text = implode(' ', $this->generate($this->input($older))['pages']);
        $this->assertFalse($anyPose->contains(fn ($s) => str_contains($text, $s)), 'Alaphelyzethez nem kell pózmondat.');
    }

    // Helyszínmondat csak akkor, ha a szülő a jelenettől eltérő hátteret választott (a jelenet a sajátját leírja).
    // A place sentence only when the parent chose a background other than the scene's (the scene tells its own).
    public function test_place_sentence_only_for_a_background_differing_from_the_scene()
    {
        $older = ['child' => ['name' => 'Panni', 'age' => 9, 'gender' => 'lány']];
        $snow = StorySentence::where('kind', 'place')->where('key', 'Havas táj')->pluck('text');
        $anyPlace = StorySentence::where('kind', 'place')->pluck('text');

        $input = $this->input($older);
        $input['pages'][0]['background'] = 'Havas táj';
        $first = $this->generate($input)['pages'][1];
        $this->assertTrue($snow->contains(fn ($s) => str_contains($first, $s)), 'A havas háttér nem jelent meg az 1. oldalon.');

        // Barátság 1. cselekmény: a jelenetek helyszínével egyező hátterek → nincs helyszínmondat.
        // Friendship plot 1: backgrounds matching the scenes' places → no place sentence.
        $input = $this->input($older);
        foreach ($this->theme->scenes()->where('plot', 1)->orderBy('step')->get() as $i => $scene) {
            $input['pages'][$i]['background'] = config('story.scenes.background_kinds.'.$scene->place);
        }
        $text = implode(' ', $this->generate($input)['pages']);
        $this->assertFalse($anyPlace->contains(fn ($s) => str_contains($text, $s)));
    }

    public function test_the_same_input_gives_different_stories()
    {
        $texts = collect(range(1, 5))->map(fn () => implode(' ', $this->generate($this->input())['pages']));

        $this->assertGreaterThan(1, $texts->unique()->count());
    }

    public function test_no_sentence_repeats_within_a_story()
    {
        $input = $this->input(['child' => ['name' => 'Panni', 'age' => 9, 'gender' => 'lány']], 'ül', 'fut');
        $sentences = collect($this->generate($input)['pages'])
            ->flatMap(fn ($page) => preg_split(self::BOUNDARY, $page))
            ->filter(fn ($s) => mb_strlen($s) > 25);

        $this->assertSame($sentences->count(), $sentences->unique()->count());
    }

    // BR-17: 2–4 évesnek oldalanként legfeljebb 2 mondat. / BR-17: at most 2 sentences per page for ages 2–4.
    public function test_young_children_get_short_pages()
    {
        $input = $this->input(['child' => ['name' => 'Panni', 'age' => 3, 'gender' => 'lány']], 'alszik', 'fut');

        foreach ($this->generate($input)['pages'] as $page) {
            $this->assertLessThanOrEqual(2, count(preg_split(self::BOUNDARY, trim($page))));
        }
    }
}

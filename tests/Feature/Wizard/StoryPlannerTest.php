<?php

namespace Tests\Feature\Wizard;

use App\Models\Action;
use App\Models\Background;
use App\Models\StoryScene;
use App\Models\StoryTheme;
use App\Services\StoryDraftService;
use App\Services\StoryPlanner;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\StoryTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

// PRD 1.6 US-21, US-24: a mese terve (cselekmény, jelenetek, állattárs) – a véletlen képek a jelenetet mutatják.
// PRD 1.6 US-21, US-24: the story plan (plot, scenes, companion) – random pictures show the scene.
class StoryPlannerTest extends TestCase
{
    use RefreshDatabase;

    private StoryTheme $theme;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('images');
        $this->seed([CatalogSeeder::class, StoryTemplateSeeder::class]);
        $this->theme = StoryTheme::where('slug', 'baratsag')->firstOrFail();

        $this->post(route('stories.create.child.store'), [
            'child_name' => 'Panni', 'child_age' => 6, 'child_gender' => 'girl', 'child_hair_color' => 'red',
        ]);
    }

    private function weights(int $plot = 1): array
    {
        return $this->theme->scenes()->where('plot', $plot)->pluck('weight', 'step')->all();
    }

    public function test_short_story_keeps_the_core_scenes_in_order()
    {
        $core = array_keys(array_filter($this->weights(), fn ($w) => $w === 3));

        $this->assertSame(array_combine(range(1, 4), $core), StoryPlanner::steps($this->weights(), 4));
        $this->assertSame(array_combine(range(1, 10), range(1, 10)), StoryPlanner::steps($this->weights(), 10));

        $six = StoryPlanner::steps($this->weights(), 6);
        $this->assertCount(6, $six);
        $this->assertSame(collect($six)->unique()->sort()->values()->all(), array_values($six));
        foreach ($core as $step) {
            $this->assertContains($step, $six);
        }
    }

    // „Meglepetés mese”: minden oldal képe a jelenetéhez illik, a társ végig ugyanaz az állat.
    // "Surprise story": every page's picture fits its scene, the companion is the same animal throughout.
    public function test_surprise_story_pictures_follow_the_plot()
    {
        $this->post(route('stories.surprise'), ['theme_id' => $this->theme->id, 'page_count' => 6])
            ->assertRedirect(route('stories.result', ['page' => 0]));

        $draft = session(StoryDraftService::KEY);
        $plan = $draft['plan'];
        $backgrounds = Background::pluck('name', 'id');
        $actions = Action::pluck('name', 'id');
        $childId = $draft['child']['character_id'];

        foreach ($draft['pages'] as $number => $page) {
            $scene = StoryScene::where('story_theme_id', $this->theme->id)->where('plot', $plan['plot'])
                ->where('step', $plan['steps'][$number])->firstOrFail();
            $figures = collect($page['placements'])->keyBy('character_id');

            $this->assertSame(config('story.scenes.background_kinds.'.$scene->place), $backgrounds[$page['background_id']], "page $number");
            $this->assertContains($actions[$figures[$childId]['action_id']], config('story.scenes.pose_kinds.'.$scene->child_pose), "page $number");
            $this->assertTrue($figures->has($plan['companion_id']), "page $number: companion missing");
            $this->assertSame($scene->extra_animal, $figures->has($plan['extra_id']), "page $number: extra animal");
        }

        // A mese lefekvéssel zárul: hálószoba, alvó gyerek. / The story ends at bedtime: bedroom, sleeping child.
        $last = $draft['pages'][6];
        $this->assertSame('Esti hálószoba', $backgrounds[$last['background_id']]);
        $this->assertSame('alszik', $actions[collect($last['placements'])->firstWhere('character_id', $childId)['action_id']]);
    }

    public function test_random_page_button_builds_the_scene_of_that_page()
    {
        $this->post(route('stories.create.settings.store'), ['theme_id' => $this->theme->id, 'page_count' => 4]);

        $this->post(route('stories.create.page.store', 4), ['target' => 'random', 'child_enabled' => 0])
            ->assertRedirect(route('stories.create.page', 4));

        $page = session(StoryDraftService::KEY)['pages'][4];
        $this->assertSame('Esti hálószoba', Background::find($page['background_id'])->name);
        $this->assertNotNull(session(StoryDraftService::KEY)['plan']);
    }

    // Más téma vagy oldalszám más tervet kér; változatlan beállítás mellett a terv (és így a képek) megmarad.
    // Another theme or page count needs a new plan; with unchanged settings the plan (and so the pictures) stays.
    public function test_plan_is_reset_only_when_theme_or_page_count_changes()
    {
        $other = StoryTheme::where('slug', 'havazas')->firstOrFail();
        $settings = ['theme_id' => $this->theme->id, 'page_count' => 5];
        $this->post(route('stories.create.settings.store'), $settings);
        $this->post(route('stories.create.page.store', 1), ['target' => 'random', 'child_enabled' => 0]);
        $plan = session(StoryDraftService::KEY)['plan'];

        $this->post(route('stories.create.settings.store'), $settings);
        $this->assertSame($plan, session(StoryDraftService::KEY)['plan']);

        $this->post(route('stories.create.settings.store'), ['theme_id' => $other->id, 'page_count' => 5]);
        $this->assertNull(session(StoryDraftService::KEY)['plan']);
    }
}

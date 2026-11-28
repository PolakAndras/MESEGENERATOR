<?php

namespace Tests\Feature\Wizard;

use App\Services\StoryDraftService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Concerns\BuildsStories;
use Tests\TestCase;

// F2: US-01 AC1, AC3 · US-02 AC1, AC3 · lépésvédelem (App Flow 4.2). / step guard.
class WizardStepsTest extends TestCase
{
    use RefreshDatabase, BuildsStories;

    protected function setUp(): void
    {
        parent::setUp();
        $this->makeCatalog();
    }

    public function test_entry_point_starts_with_step_one()
    {
        $this->get(route('stories.create'))->assertRedirect(route('stories.create.child'));
        $this->get(route('stories.create.child'))->assertOk()->assertSee(__('wizard.child.title'));
    }

    public function test_step_one_saves_child_data_into_the_session()
    {
        $this->post(route('stories.create.child.store'), $this->childData())
            ->assertRedirect(route('stories.create.settings'));

        $this->assertSame('Panni', session(StoryDraftService::KEY)['child']['name']);
        $this->assertSame($this->figure->id, session(StoryDraftService::KEY)['child']['character_id']);
    }

    public function test_step_one_rejects_missing_and_invalid_data_with_hungarian_messages()
    {
        $this->from(route('stories.create.child'))
            ->post(route('stories.create.child.store'), $this->childData(['child_name' => '', 'child_age' => 11, 'child_gender' => 'x']))
            ->assertRedirect(route('stories.create.child'))
            ->assertSessionHasErrors(['child_name', 'child_age', 'child_gender']);

        $this->assertSame('Add meg a gyerek nevét.', session('errors')->first('child_name'));
        $this->assertNull(session(StoryDraftService::KEY));
    }

    public function test_step_one_rejects_a_name_longer_than_fifty_characters()
    {
        $this->post(route('stories.create.child.store'), $this->childData(['child_name' => Str::repeat('a', 51)]))
            ->assertSessionHasErrors('child_name');
    }

    public function test_step_one_rejects_a_combination_without_child_figure()
    {
        $this->post(route('stories.create.child.store'), $this->childData(['child_hair_color' => 'blond']))
            ->assertSessionHasErrors('child_gender');
    }

    // US-24: a téma a 15 közül; a context a téma neve (ez kerül a mentett mesébe).
    // US-24: the theme out of 15; context is the theme name (stored with the saved story).
    public function test_step_two_saves_theme_and_page_count()
    {
        $this->post(route('stories.create.child.store'), $this->childData());
        $this->get(route('stories.create.settings'))->assertSee($this->theme->name)->assertSee('name="theme_id"', false);

        $this->post(route('stories.create.settings.store'), ['theme_id' => $this->theme->id, 'page_count' => 9])
            ->assertRedirect(route('stories.create.page', 1));

        $settings = session(StoryDraftService::KEY)['settings'];
        $this->assertSame(9, $settings['page_count']);
        $this->assertSame($this->theme->id, $settings['theme_id']);
        $this->assertSame('Barátság', $settings['context']);
        $this->assertArrayNotHasKey('favorite', $settings);
        $this->assertCount(9, session(StoryDraftService::KEY)['pages']);
    }

    public function test_step_two_requires_a_valid_theme_and_page_count()
    {
        $this->post(route('stories.create.child.store'), $this->childData());

        $this->post(route('stories.create.settings.store'), ['page_count' => 11])
            ->assertSessionHasErrors(['theme_id', 'page_count']);
        $this->post(route('stories.create.settings.store'), ['theme_id' => 999999, 'page_count' => 8])
            ->assertSessionHasErrors('theme_id');
        $this->post(route('stories.create.settings.store'), ['theme_id' => $this->theme->id, 'page_count' => 3])
            ->assertSessionHasErrors('page_count');
    }

    // BR-01 (PRD 1.4): a legrövidebb mese 4 oldalas. / The shortest story has 4 pages.
    public function test_four_page_story_is_allowed()
    {
        $this->post(route('stories.create.child.store'), $this->childData());

        $this->post(route('stories.create.settings.store'), ['theme_id' => $this->theme->id, 'page_count' => 4])
            ->assertSessionHasNoErrors();
        $this->assertCount(4, session(StoryDraftService::KEY)['pages']);
        $this->get(route('stories.create.settings'))->assertSee(__('wizard.settings.pages_option', ['count' => 4]));
    }

    public function test_reducing_pages_with_content_needs_confirmation()
    {
        $this->completeSteps(10);
        $this->post(route('stories.create.page.store', 10), $this->pageData(['target' => 'prev']));

        $this->post(route('stories.create.settings.store'), ['theme_id' => $this->theme->id, 'page_count' => 8])
            ->assertRedirect(route('stories.create.settings'))
            ->assertSessionHas('confirm_reduce');
        $this->assertCount(10, session(StoryDraftService::KEY)['pages']);

        $this->post(route('stories.create.settings.store'), ['theme_id' => $this->theme->id, 'page_count' => 8, 'confirm_reduce' => 1]);
        $this->assertCount(8, session(StoryDraftService::KEY)['pages']);
    }

    public function test_steps_are_guarded()
    {
        $this->get(route('stories.create.settings'))->assertRedirect(route('stories.create.child'));
        $this->get(route('stories.create.page', 1))->assertRedirect(route('stories.create.child'));
        $this->get(route('stories.result'))->assertRedirect(route('stories.create'));

        $this->post(route('stories.create.child.store'), $this->childData());
        $this->get(route('stories.create.page', 1))->assertRedirect(route('stories.create.settings'));

        $this->post(route('stories.create.settings.store'), ['theme_id' => $this->theme->id, 'page_count' => 8]);
        $this->get(route('stories.create.page', 99))->assertRedirect(route('stories.create.page', 1));
        $this->get(route('stories.create.page', 1))->assertOk();
    }

    public function test_existing_draft_offers_continue_or_restart()
    {
        $this->post(route('stories.create.child.store'), $this->childData());

        $this->get(route('stories.create'))->assertOk()->assertSee(__('wizard.create.continue'));

        $this->post(route('stories.create.reset'))->assertRedirect(route('stories.create.child'));
        $this->assertNull(session(StoryDraftService::KEY));
    }

    public function test_changing_the_child_figure_swaps_it_on_every_page()
    {
        $boy = \App\Models\Character::factory()->childFigure('boy', 'red')->create();
        $this->completeSteps();
        $this->post(route('stories.create.page.store', 1), $this->pageData());

        $this->post(route('stories.create.child.store'), $this->childData(['child_gender' => 'boy']))
            ->assertSessionHas('flash');

        $placements = session(StoryDraftService::KEY)['pages'][1]['placements'];
        $this->assertSame($boy->id, $placements[0]['character_id']);
        $this->assertSame($boy->defaultAction->id, $placements[0]['action_id']);
        $this->assertSame(50.0, $placements[0]['pos_x']);
    }
}

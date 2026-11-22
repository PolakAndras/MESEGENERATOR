<?php

namespace Tests\Feature\Wizard;

use App\Models\Character;
use App\Services\StoryDraftService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsStories;
use Tests\TestCase;

// F3: US-04 AC1, AC2 · US-05 AC1 · US-06 AC3 (szerveroldali tartomány) · oldalmentés háttér nélkül.
// F3: US-04 AC1, AC2 · US-05 AC1 · US-06 AC3 (server-side range) · saving a page without a background.
class PageEditorTest extends TestCase
{
    use RefreshDatabase, BuildsStories;

    protected function setUp(): void
    {
        parent::setUp();
        $this->makeCatalog();
        $this->completeSteps();
    }

    private function animals(int $count): array
    {
        $animals = [];
        $characters = Character::factory()->count($count)->animal()->create();
        foreach ($characters as $i => $c) {
            $animals[] = ['character_id' => $c->id, 'pos_x' => 20, 'pos_y' => 90, 'slot' => min($i + 1, 4)];
        }

        return $animals;
    }

    public function test_page_renders_canvas_and_controls()
    {
        $this->get(route('stories.create.page', 1))
            ->assertOk()
            ->assertSee('data-story-canvas', false)
            ->assertSee($this->forest->name)
            ->assertSee($this->fox->name);
    }

    public function test_page_is_saved_and_navigates_to_target()
    {
        $this->post(route('stories.create.page.store', 1), $this->pageData())
            ->assertRedirect(route('stories.create.page', 2));

        $page = session(StoryDraftService::KEY)['pages'][1];
        $this->assertSame($this->forest->id, $page['background_id']);
        $this->assertCount(2, $page['placements']);
        // Választás nélkül az alaphelyzet (BR-09). / Without a choice the default action (BR-09).
        $this->assertSame($this->fox->defaultAction->id, $page['placements'][1]['action_id']);
    }

    public function test_page_can_be_saved_without_background()
    {
        $this->post(route('stories.create.page.store', 1), $this->pageData(['background_id' => null, 'target' => 'page:3']))
            ->assertRedirect(route('stories.create.page', 3))
            ->assertSessionHasNoErrors();
    }

    public function test_up_to_four_animals_but_not_five()
    {
        $this->post(route('stories.create.page.store', 1), $this->pageData(['animals' => $this->animals(4)]))
            ->assertSessionHasNoErrors();

        $this->post(route('stories.create.page.store', 2), $this->pageData(['animals' => $this->animals(5)]))
            ->assertSessionHasErrors('animals');
    }

    public function test_the_same_animal_cannot_be_added_twice()
    {
        $animals = [
            ['character_id' => $this->fox->id, 'pos_x' => 20, 'pos_y' => 90, 'slot' => 1],
            ['character_id' => $this->fox->id, 'pos_x' => 40, 'pos_y' => 90, 'slot' => 2],
        ];

        $this->post(route('stories.create.page.store', 1), $this->pageData(['animals' => $animals]))
            ->assertSessionHasErrors('animals.0.character_id');
    }

    public function test_child_figure_is_optional_and_at_most_once()
    {
        $this->post(route('stories.create.page.store', 1), $this->pageData(['child_enabled' => 0]))
            ->assertSessionHasNoErrors();
        $this->assertCount(1, session(StoryDraftService::KEY)['pages'][1]['placements']);

        // A gyerekfigura nem adható hozzá állatként (BR-07): csak egy child_enabled mező van.
        // The child figure cannot be added as an animal (BR-07): there is only one child_enabled field.
        $this->post(route('stories.create.page.store', 2), $this->pageData([
            'animals' => [['character_id' => $this->figure->id, 'pos_x' => 20, 'pos_y' => 90, 'slot' => 1]],
        ]))->assertSessionHasErrors('animals.0.character_id');
    }

    public function test_positions_outside_the_canvas_are_rejected()
    {
        $this->post(route('stories.create.page.store', 1), $this->pageData([
            'child' => ['action_id' => null, 'pos_x' => 5, 'pos_y' => 90],
        ]))->assertSessionHasErrors('placements');

        $this->post(route('stories.create.page.store', 1), $this->pageData([
            'animals' => [['character_id' => $this->fox->id, 'pos_x' => 50, 'pos_y' => 20, 'slot' => 1]],
        ]))->assertSessionHasErrors('placements');

        $this->post(route('stories.create.page.store', 1), $this->pageData([
            'animals' => [['character_id' => $this->fox->id, 'pos_x' => 86.875, 'pos_y' => 35, 'slot' => 1]],
        ]))->assertSessionHasNoErrors();
    }

    public function test_an_action_of_another_character_is_rejected()
    {
        $this->post(route('stories.create.page.store', 1), $this->pageData([
            'animals' => [['character_id' => $this->fox->id, 'action_id' => $this->rabbit->defaultAction->id, 'pos_x' => 20, 'pos_y' => 90, 'slot' => 1]],
        ]))->assertSessionHasErrors('placements');
    }

    public function test_random_page_button_fills_the_current_page()
    {
        $this->post(route('stories.create.page.store', 2), $this->pageData(['background_id' => null, 'child_enabled' => 0, 'animals' => [], 'target' => 'random']))
            ->assertRedirect(route('stories.create.page', 2));

        $page = session(StoryDraftService::KEY)['pages'][2];
        $this->assertNotNull($page['background_id']);
        $this->assertNotEmpty($page['placements']);
        foreach ($page['placements'] as $p) {
            $this->assertTrue(\App\Support\PlacementRules::isWithin($p['pos_x'], $p['pos_y']));
        }
    }

    public function test_figures_can_be_flipped()
    {
        $this->post(route('stories.create.page.store', 1), $this->pageData([
            'child' => ['action_id' => null, 'pos_x' => 50, 'pos_y' => 90, 'flipped' => 1],
        ]))->assertSessionHasNoErrors();

        $placements = session(StoryDraftService::KEY)['pages'][1]['placements'];
        $this->assertTrue($placements[0]['flipped']);
        $this->assertFalse($placements[1]['flipped']);

        $this->get(route('stories.create.page', 1))->assertSee('story-canvas__figure--flipped', false);
    }

    public function test_layer_order_puts_lower_figures_in_front()
    {
        $this->post(route('stories.create.page.store', 1), $this->pageData([
            'child' => ['action_id' => null, 'pos_x' => 50, 'pos_y' => 95],
            'animals' => [['character_id' => $this->fox->id, 'pos_x' => 20, 'pos_y' => 60, 'slot' => 1]],
        ]));

        $state = \App\Support\CanvasState::fromDraftPage(session(StoryDraftService::KEY)['pages'][1]);
        $this->assertSame([$this->fox->id, $this->figure->id], array_column($state['placements'], 'character_id'));
        $this->assertSame([1, 2], array_column($state['placements'], 'z_index'));
    }
}

<?php

namespace Tests\Feature\Stories;

use App\Models\Story;
use App\Models\User;
use App\Services\StoryDraftService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsStories;
use Tests\TestCase;

// F7: US-10 AC4 · US-14 AC1, AC2 · US-15 AC1, AC2, AC4 · US-16 AC2 · admin hozzáférés (Backend Schema E-02).
class SavedStoriesTest extends TestCase
{
    use RefreshDatabase, BuildsStories;

    public function test_guest_cannot_save()
    {
        $this->makeCatalog();
        $this->generateStory();

        $this->post(route('stories.store'))->assertRedirect(route('login'));
        $this->assertDatabaseCount('stories', 0);
    }

    public function test_saving_stores_the_whole_story_once()
    {
        $this->makeCatalog();
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->generateStory();

        $this->post(route('stories.store'))->assertSessionHas('flash.type', 'success');

        $story = $user->stories()->with('pages.placements')->first();
        $this->assertSame('Panni', $story->child_name);
        $this->assertSame(5, $story->child_age);
        $this->assertSame('girl', $story->child_gender);
        $this->assertSame('red', $story->child_hair_color);
        $this->assertSame('Barátság', $story->context);  // a választott téma neve / the chosen theme's name
        $this->assertCount(8, $story->pages);
        $this->assertNotSame('', $story->pages[0]->text);
        $this->assertSame($this->forest->id, (int) $story->pages[0]->background_id);
        $this->assertCount(2, $story->pages[0]->placements);
        $this->assertFalse($story->pages[0]->placements[0]->flipped);
        $this->assertSame(StoryDraftService::STATUS_SAVED, session(StoryDraftService::KEY)['status']);

        // Egyszer menthető (US-14 AC2). / Can be saved once (US-14 AC2).
        $this->post(route('stories.store'))->assertSessionHas('flash.type', 'error');
        $this->assertSame(1, $user->stories()->count());
    }

    public function test_my_stories_lists_only_own_stories_newest_first()
    {
        $user = User::factory()->create();
        $older = Story::factory()->create(['user_id' => $user->id, 'title' => 'Régebbi mese', 'created_at' => now()->subDay()]);
        $newer = Story::factory()->create(['user_id' => $user->id, 'title' => 'Újabb mese']);
        Story::factory()->create(['title' => 'Más meséje']);

        $this->actingAs($user)->get(route('stories.index'))
            ->assertOk()
            ->assertSeeInOrder(['Újabb mese', 'Régebbi mese'])
            ->assertDontSee('Más meséje');
    }

    public function test_empty_list_invites_to_create()
    {
        $this->actingAs(User::factory()->create())->get(route('stories.index'))
            ->assertSee(__('stories.empty_title'));
    }

    public function test_another_users_story_returns_404()
    {
        $story = Story::factory()->withPages()->create();
        $other = User::factory()->create();

        $this->actingAs($other)->get(route('stories.show', $story))->assertNotFound();
        $this->actingAs($other)->delete(route('stories.destroy', $story))->assertNotFound();
        $this->assertDatabaseHas('stories', ['id' => $story->id]);
    }

    public function test_owner_reads_saved_story()
    {
        $story = Story::factory()->withPages()->create();

        $this->actingAs($story->user)->get(route('stories.show', ['story' => $story, 'page' => 2]))
            ->assertOk()
            ->assertSee('A(z) 2. oldal szövege.');

        $this->actingAs($story->user)->get(route('stories.show', $story))
            ->assertSee(__('story.main_character', ['name' => $story->child_name]));
    }

    public function test_renamed_catalog_items_still_render_in_saved_story()
    {
        $story = Story::factory()->withPages()->create();
        $page = $story->pages()->first();
        $page->background->update(['name' => 'Átnevezett háttér']);

        $this->actingAs($story->user)->get(route('stories.show', ['story' => $story, 'page' => 1]))
            ->assertOk()
            ->assertSee('Átnevezett háttér');
    }

    public function test_delete_is_permanent_and_own_only()
    {
        $story = Story::factory()->withPages()->create();

        $this->actingAs($story->user)->delete(route('stories.destroy', $story))->assertRedirect(route('stories.index'));

        $this->assertDatabaseMissing('stories', ['id' => $story->id]);
        $this->assertDatabaseCount('story_pages', 0);
        $this->assertDatabaseCount('story_page_placements', 0);
    }

    public function test_admin_can_view_and_delete_any_story()
    {
        $story = Story::factory()->withPages()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('stories.show', $story))->assertOk();
        $this->actingAs($admin)->get(route('admin.stories.index'))->assertOk()->assertSee($story->title);

        // Az admin Meséim listája csak a sajátjait mutatja (Backend Schema 10.3). / Admin's own list only.
        $this->actingAs($admin)->get(route('stories.index'))->assertDontSee($story->title);

        $this->actingAs($admin)->delete(route('admin.stories.destroy', $story))->assertRedirect(route('admin.stories.index'));
        $this->assertDatabaseMissing('stories', ['id' => $story->id]);
    }
}

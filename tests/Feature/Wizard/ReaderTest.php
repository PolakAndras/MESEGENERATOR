<?php

namespace Tests\Feature\Wizard;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsStories;
use Tests\TestCase;

// F4: US-08 AC1, AC2, AC5 · US-10 AC3 – az eredményoldal olvasója. / The result page reader.
class ReaderTest extends TestCase
{
    use RefreshDatabase, BuildsStories;

    protected function setUp(): void
    {
        parent::setUp();
        $this->makeCatalog();
        $this->generateStory();
    }

    public function test_title_page_shows_the_full_ai_notice_and_night_theme()
    {
        $this->get(route('stories.result', ['page' => 0]))
            ->assertOk()
            ->assertSee(__('story.ai_notice_full'))
            ->assertSee(__('story.main_character', ['name' => 'Panni']))
            ->assertSee('data-story-theme="night"', false)
            ->assertSee(__('story.cover'));
    }

    public function test_story_pages_show_text_canvas_and_compact_ai_badge()
    {
        $this->get(route('stories.result', ['page' => 3]))
            ->assertOk()
            ->assertSee(__('story.ai_badge'))
            ->assertSee(__('story.page_of', ['page' => 3, 'count' => 8]))
            ->assertSee('Róka – áll', false);
    }

    public function test_invalid_page_falls_back_to_the_title_page()
    {
        $this->get(route('stories.result', ['page' => 99]))->assertOk()->assertSee(__('story.cover'));
    }

    public function test_guest_sees_save_info_instead_of_save_button()
    {
        $this->get(route('stories.result'))
            ->assertSee(__('story.guest_title'))
            ->assertDontSee('action="'.route('stories.store').'"', false);
    }
}

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

    public function test_title_page_shows_main_character_and_night_theme()
    {
        $this->get(route('stories.result', ['page' => 0]))
            ->assertOk()
            ->assertSee(__('story.main_character', ['name' => 'Panni']))
            ->assertSee('data-story-theme="night"', false)
            ->assertSee(__('story.cover'));
    }

    public function test_story_pages_show_text_and_canvas()
    {
        $this->get(route('stories.result', ['page' => 3]))
            ->assertOk()
            ->assertSee(__('story.page_of', ['page' => 3, 'count' => 8]))
            ->assertSee('Róka – áll', false);
    }

    // AI-jelölés csak AI-szöveghez kell (US-08 AC5); a mesegyűjteményes mesén nincs, AI bekapcsolásakor megjelenik.
    // The AI label only belongs to AI text (US-08 AC5): none on a story-collection story, shown once AI is switched on.
    public function test_ai_label_only_appears_when_an_ai_provider_is_active()
    {
        $this->get(route('stories.result', ['page' => 0]))->assertDontSee(__('story.ai_notice_full'));
        $this->get(route('stories.result', ['page' => 3]))->assertDontSee(__('story.ai_badge'));

        config(['story.generator' => 'groq']);
        $this->get(route('stories.result', ['page' => 0]))->assertSee(__('story.ai_notice_full'));
        $this->get(route('stories.result', ['page' => 3]))->assertSee(__('story.ai_badge'));
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

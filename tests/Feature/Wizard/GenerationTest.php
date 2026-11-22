<?php

namespace Tests\Feature\Wizard;

use App\Services\StoryDraftService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Tests\Concerns\BuildsStories;
use Tests\TestCase;

// F4 + F10: US-03 AC2 · US-07 AC4, AC5, AC6, AC9 · US-09 · US-10 AC1. Az AI-hívás mindig Http::fake()-kel.
// F4 + F10: US-03 AC2 · US-07 AC4, AC5, AC6, AC9 · US-09 · US-10 AC1. The AI call is always faked with Http::fake().
class GenerationTest extends TestCase
{
    use RefreshDatabase, BuildsStories;

    protected function setUp(): void
    {
        parent::setUp();
        $this->makeCatalog();
        RateLimiter::clear('story-generation|127.0.0.1');
    }

    private function useGroq(): void
    {
        config(['story.generator' => 'groq', 'story.providers.groq.api_key' => 'test-key']);
    }

    private function aiAnswer(int $pages, string $title = 'Panni és a róka'): array
    {
        $content = json_encode([
            'title' => $title,
            'pages' => array_map(fn ($n) => ['page' => $n, 'text' => "Szöveg $n."], range(1, $pages)),
        ]);

        return ['choices' => [['message' => ['content' => $content]]]];
    }

    public function test_guest_can_generate_a_story_with_the_demo_generator()
    {
        $this->generateStory()->assertRedirect(route('stories.result', ['page' => 0]));

        $draft = session(StoryDraftService::KEY);
        $this->assertSame(StoryDraftService::STATUS_GENERATED, $draft['status']);
        $this->assertCount(8, $draft['generated']['pages']);
        $this->assertNotSame('', $draft['generated']['title']);
    }

    // US-21: a háttér nélküli és az üres oldalakat a generálás véletlenszerűen tölti ki, a szülő figurái maradnak.
    // US-21: generation fills pages without a background and empty pages randomly; the parent's figures stay.
    public function test_generation_fills_missing_backgrounds_and_empty_pages_randomly()
    {
        $this->completeSteps();
        $this->post(route('stories.create.page.store', 1), $this->pageData(['background_id' => null]));

        $this->post(route('stories.generate'), $this->pageData(['target' => null]))
            ->assertRedirect(route('stories.result', ['page' => 0]));

        $draft = session(StoryDraftService::KEY);
        $this->assertSame(StoryDraftService::STATUS_GENERATED, $draft['status']);
        foreach ($draft['pages'] as $n => $page) {
            $this->assertNotNull($page['background_id'], "page $n");
            $this->assertNotEmpty($page['placements'], "page $n");
        }
        $this->assertSame($this->fox->id, $draft['pages'][1]['placements'][1]['character_id']);
        // Az első és az utolsó oldalon ott a gyerek. / The child is on the first and last page.
        $this->assertSame($this->figure->id, $draft['pages'][7]['placements'][0]['character_id']);
    }

    public function test_surprise_story_fills_every_page_and_generates()
    {
        $this->post(route('stories.create.child.store'), $this->childData());

        $this->post(route('stories.surprise'), ['context' => 'barátság', 'page_count' => 4])
            ->assertRedirect(route('stories.result', ['page' => 0]));

        $draft = session(StoryDraftService::KEY);
        $this->assertCount(4, $draft['pages']);
        $this->assertCount(4, $draft['generated']['pages']);
        foreach ($draft['pages'] as $page) {
            $this->assertNotNull($page['background_id']);
            $this->assertLessThanOrEqual(config('story.random.animals_max') + 1, count($page['placements']));
        }
    }

    public function test_surprise_story_is_validated_like_step_two()
    {
        $this->post(route('stories.create.child.store'), $this->childData());

        $this->post(route('stories.surprise'), ['context' => '', 'page_count' => 3])
            ->assertSessionHasErrors(['context', 'page_count']);
    }

    public function test_valid_ai_answer_is_used()
    {
        $this->useGroq();
        Http::fake(['api.groq.com/*' => Http::response($this->aiAnswer(8))]);

        $this->generateStory()->assertRedirect(route('stories.result', ['page' => 0]));

        $this->assertSame('Panni és a róka', session(StoryDraftService::KEY)['generated']['title']);
        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer test-key')
            && str_contains($request['messages'][1]['content'], 'Panni')
            && str_contains($request['messages'][1]['content'], 'Éjszakai erdő'));
    }

    public function test_invalid_answer_is_retried_once()
    {
        $this->useGroq();
        Http::fakeSequence('api.groq.com/*')
            ->push(['choices' => [['message' => ['content' => 'nem JSON']]]])
            ->push($this->aiAnswer(8));

        $this->generateStory()->assertRedirect(route('stories.result', ['page' => 0]));
        Http::assertSentCount(2);
    }

    public function test_twice_invalid_answer_gives_a_friendly_error_and_keeps_the_pages()
    {
        $this->useGroq();
        Http::fake(['api.groq.com/*' => Http::response($this->aiAnswer(5))]);

        $this->generateStory()
            ->assertRedirect(route('stories.create.page', 8))
            ->assertSessionHas('flash.message', __('wizard.generate.failed'));

        Http::assertSentCount(2);
        $draft = session(StoryDraftService::KEY);
        $this->assertSame(StoryDraftService::STATUS_EDITING, $draft['status']);
        $this->assertSame($this->forest->id, $draft['pages'][8]['background_id']);
    }

    public function test_quota_or_unavailable_provider_gives_a_friendly_error()
    {
        $this->useGroq();
        Http::fake(['api.groq.com/*' => Http::response(['error' => 'rate limit'], 429)]);

        $this->generateStory()
            ->assertRedirect(route('stories.create.page', 8))
            ->assertSessionHas('flash.message', __('wizard.generate.failed'));

        Http::assertSentCount(1);
        $this->assertCount(8, session(StoryDraftService::KEY)['pages']);
    }

    public function test_generation_is_rate_limited_per_ip()
    {
        $this->completeSteps();
        $this->fillAllPages();
        $limit = config('story.rate_limits.generations_per_minute');

        for ($i = 0; $i < $limit; $i++) {
            $this->post(route('stories.generate'), $this->pageData(['target' => null]));
            // Vissza szerkesztésbe, hogy újra generálhasson. / Back to editing, so it can generate again.
            $draft = session(StoryDraftService::KEY);
            $draft['status'] = StoryDraftService::STATUS_EDITING;
            session([StoryDraftService::KEY => $draft]);
        }

        $this->post(route('stories.generate'), $this->pageData(['target' => null]))
            ->assertRedirect(route('stories.create.page', 8))
            ->assertSessionHas('flash.type', 'warning');
    }

    public function test_new_text_overwrites_the_previous_one()
    {
        $this->useGroq();
        Http::fakeSequence('api.groq.com/*')
            ->push($this->aiAnswer(8, 'Első cím'))
            ->push($this->aiAnswer(8, 'Második cím'));

        $this->generateStory();
        $this->post(route('stories.regenerate'))->assertRedirect(route('stories.result', ['page' => 0]));

        $this->assertSame('Második cím', session(StoryDraftService::KEY)['generated']['title']);
    }

    public function test_wizard_steps_are_locked_after_generation()
    {
        $this->generateStory();

        $this->get(route('stories.create.child'))->assertRedirect(route('stories.result'));
        $this->get(route('stories.create.page', 1))->assertRedirect(route('stories.result'));
    }
}

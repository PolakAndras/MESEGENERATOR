<?php

namespace App\Services;

use App\Contracts\StoryTextGenerator;
use App\Exceptions\StoryGenerationException;
use App\Models\Action;
use App\Models\Background;
use App\Models\Character;
use App\Services\Generation\StoryResponseParser;
use Illuminate\Support\Facades\Log;

/**
 * A generálás menete (PRD US-07): a vázlatból összeállítja az AI bemenetét (AC3), egyetlen kéréssel hív (AC2),
 * ellenőrzi a választ (AC4), és érvénytelen válasznál egyszer újrapróbál (AC5). A kontroller így csak egy
 * metódust hív, a szolgáltató pedig az interfész mögött cserélhető (NFR-15).
 *
 * The generation flow (PRD US-07): builds the AI input from the draft (AC3), calls once (AC2), validates the
 * answer (AC4) and retries once on an invalid answer (AC5). The controller calls a single method, and the provider
 * stays swappable behind the interface (NFR-15).
 */
class StoryGenerationService
{
    private StoryTextGenerator $generator;

    private StoryPlanner $planner;

    public function __construct(StoryTextGenerator $generator, StoryPlanner $planner)
    {
        $this->generator = $generator;
        $this->planner = $planner;
    }

    /**
     * @return array ['title' => string, 'pages' => [1 => string, …]]
     *
     * @throws StoryGenerationException
     */
    public function generate(StoryDraftService $drafts): array
    {
        $input = $this->input($drafts);
        $pageCount = count($input['pages']);

        for ($attempt = 1; ; $attempt++) {
            try {
                return StoryResponseParser::parse($this->generator->generate($input), $pageCount);
            } catch (StoryGenerationException $e) {
                // Naplóba csak az ok kerül, gyereknév, kontextus vagy meseszöveg soha (Backend Schema 13.).
                // Only the reason is logged, never the child's name, context or story text (Backend Schema 13.).
                Log::warning('Story generation failed', ['reason' => $e->reason, 'detail' => $e->getMessage(), 'attempt' => $attempt]);

                if ($e->reason !== StoryGenerationException::INVALID_RESPONSE || $attempt >= 2) {
                    throw $e;
                }
            }
        }
    }

    /**
     * Az AI bemenete (US-07 AC3): a gyerek neve, kora, neme, a kontextus, oldalanként a háttér neve,
     * a szereplők neve és cselekvése, és hogy a gyerek szerepel-e és mit csinál. A hajszín nem megy át (Tech Stack 5.2).
     * The AI input (US-07 AC3): child's name, age, gender, the context, and per page the background name, the
     * characters' names and actions, and whether the child appears and what they do. Hair colour is not sent (Tech Stack 5.2).
     */
    public function input(StoryDraftService $drafts): array
    {
        $child = $drafts->child();
        $plan = $this->planner->plan($drafts);
        $backgrounds = Background::pluck('name', 'id');
        $characters = Character::all()->keyBy('id');
        $actions = Action::pluck('name', 'id');
        $pages = [];

        foreach ($drafts->pages() as $number => $page) {
            $childAction = null;
            $animals = [];
            foreach ($page['placements'] as $p) {
                if ($p['character_id'] === $child['character_id']) {
                    $childAction = $actions[$p['action_id']] ?? null;
                } elseif (isset($characters[$p['character_id']])) {
                    $animals[] = ['name' => $characters[$p['character_id']]->name, 'action' => $actions[$p['action_id']] ?? ''];
                }
            }
            $pages[] = [
                'page' => $number,
                'background' => $backgrounds[$page['background_id']] ?? '',
                'child_action' => $childAction,
                'animals' => $animals,
            ];
        }

        return [
            'child' => [
                'name' => $child['name'],
                'age' => $child['age'],
                'gender' => $child['gender'] === 'girl' ? 'lány' : 'fiú',
            ],
            'context' => $drafts->settings()['context'],
            // A mesegyűjteménynek (PRD 1.6 US-24): a téma és a mese terve (cselekmény, oldalankénti jelenet, állattárs),
            // ugyanaz, amiből a véletlen képek készültek. / For the story collection: the theme and the story plan
            // (plot, scene per page, animal companion), the same the random pictures were built from.
            'theme_id' => $drafts->settings()['theme_id'],
            'plot' => $plan['plot'],
            'steps' => $plan['steps'],
            'companion' => optional(Character::find($plan['companion_id']))->name,
            'pages' => $pages,
        ];
    }
}

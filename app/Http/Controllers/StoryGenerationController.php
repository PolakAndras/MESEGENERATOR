<?php

namespace App\Http\Controllers;

use App\Exceptions\StoryGenerationException;
use App\Http\Requests\PageStepRequest;
use App\Http\Requests\SettingsStepRequest;
use App\Models\Story;
use App\Services\StoryDraftService;
use App\Services\StoryGenerationService;
use App\Services\StoryRandomizer;
use App\Support\CanvasState;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Generálás, új szöveg és az eredményoldal (App Flow 4.3, S-10).
 *
 * A kéréskorlátozás (BR-15) itt, a kontrollerben fut, nem throttle middleware-ként: így az utolsó oldal
 * beküldött változásai korlátozáskor is elmentődnek („minden adat megmarad”, App Flow 4.3). Eltérés a Backend
 * Schema 10.6-tól, rögzítve a docs/DECISIONS.md-ben.
 *
 * Generation, new text and the result page (App Flow 4.3, S-10).
 * Rate limiting (BR-15) runs here in the controller, not as throttle middleware: this way the last page's
 * submitted changes are saved even when limited ("all data is kept", App Flow 4.3). A deviation from Backend
 * Schema 10.6, recorded in docs/DECISIONS.md.
 */
class StoryGenerationController extends Controller
{
    private StoryDraftService $drafts;

    public function __construct(StoryDraftService $drafts)
    {
        $this->drafts = $drafts;
    }

    public function generate(PageStepRequest $request, StoryGenerationService $service, StoryRandomizer $randomizer)
    {
        if ($redirect = $this->drafts->guard('page')) {
            return redirect($redirect);
        }

        $last = $this->drafts->pageCount();
        $this->drafts->savePage($last, $request->validated());

        return $this->attempt($request, $service, $randomizer, $last);
    }

    /**
     * „Meglepetés mese” a 2. lépésről (PRD US-21): a beállítások mentése, az oldalak véletlen kitöltése és a
     * generálás egy lépésben. Hiba esetén az 1. oldalra visz, ahol a kitöltött oldalak szerkeszthetők.
     * "Surprise story" from step 2 (PRD US-21): saves the settings, fills the pages randomly and generates in one
     * step. On failure it goes to page 1, where the filled pages can be edited.
     */
    public function surprise(SettingsStepRequest $request, StoryGenerationService $service, StoryRandomizer $randomizer)
    {
        if ($redirect = $this->drafts->guard('settings')) {
            return redirect($redirect);
        }

        $pageCount = (int) $request->input('page_count');
        $lost = $this->drafts->pagesLostOnReduce($pageCount);
        if ($lost !== [] && ! $request->boolean('confirm_reduce')) {
            return redirect()->route('stories.create.settings')
                ->withInput()
                ->with('confirm_reduce', ['from' => min($lost), 'to' => max($lost)]);
        }

        $this->drafts->saveSettings($request->theme(), $pageCount);

        return $this->attempt($request, $service, $randomizer, 1);
    }

    /**
     * A közös generálási lépés: a hiányzó oldalrészek véletlen kitöltése (US-21 – a háttér már nem akadály),
     * kéréskorlát, AI-hívás. Hiba esetén a megadott oldalra tér vissza, minden adat megmarad (App Flow 4.3).
     * The shared generation step: random fill of missing page parts (US-21 – a missing background no longer
     * blocks), rate limit, AI call. On failure it returns to the given page with all data kept (App Flow 4.3).
     */
    private function attempt(Request $request, StoryGenerationService $service, StoryRandomizer $randomizer, int $failPage)
    {
        $randomizer->fillMissing($this->drafts);

        $back = redirect()->route('stories.create.page', ['page' => $failPage]);
        if ($limited = $this->limited($request)) {
            return $back->with('flash', $limited);
        }

        try {
            $result = $service->generate($this->drafts);
        } catch (StoryGenerationException $e) {
            return $back->with('flash', ['type' => 'error', 'message' => __('wizard.generate.failed')]);
        }

        $this->drafts->setGenerated($result['title'], $result['pages']);

        return redirect()->route('stories.result', ['page' => 0]);
    }

    public function regenerate(Request $request, StoryGenerationService $service)
    {
        $back = redirect()->route('stories.result', ['page' => 0]);
        if ($this->drafts->status() !== StoryDraftService::STATUS_GENERATED) {
            return $back;
        }
        if ($limited = $this->limited($request)) {
            return $back->with('flash', $limited);
        }

        try {
            $result = $service->generate($this->drafts);
        } catch (StoryGenerationException $e) {
            return $back->with('flash', ['type' => 'error', 'message' => __('wizard.generate.failed_regenerate')]);
        }

        // Az új szöveg felülírja az előzőt, a régi nem tárolódik (US-09 AC2).
        // The new text overwrites the old one; the old text is not kept (US-09 AC2).
        $this->drafts->setGenerated($result['title'], $result['pages']);

        return $back->with('flash', ['type' => 'success', 'message' => __('wizard.generate.regenerated')]);
    }

    public function result(Request $request)
    {
        if (! $this->drafts->isLocked()) {
            return redirect()->route('stories.create');
        }

        // Mentett állapot, de a mesét közben törölték: a vázlat törlése, új mese (Backend Schema 9.3).
        // Saved state, but the story was deleted meanwhile: drop the draft, start a new story (Backend Schema 9.3).
        $savedId = $this->drafts->savedStoryId();
        if ($savedId !== null && ! Story::whereKey($savedId)->exists()) {
            $this->drafts->forget();

            return redirect()->route('stories.create');
        }

        $generated = $this->drafts->generated();
        $pages = [];
        foreach ($this->drafts->pages() as $n => $page) {
            $pages[$n] = ['canvas' => CanvasState::fromDraftPage($page), 'text' => $generated['pages'][$n] ?? ''];
        }

        return view('stories.result', [
            'title' => $generated['title'],
            'childName' => $this->drafts->child()['name'],
            'pages' => $pages,
            'current' => $this->currentPage($request, count($pages)),
            'saved' => $savedId !== null,
            'savedStoryId' => $savedId,
        ]);
    }

    // Érvénytelen ?page= → címoldal (App Flow K-07). / An invalid ?page= falls back to the title page (App Flow K-07).
    public static function currentPage(Request $request, int $count): int
    {
        $page = filter_var($request->query('page', 0), FILTER_VALIDATE_INT);

        return ($page !== false && $page >= 0 && $page <= $count) ? $page : 0;
    }

    // BR-15: IP-címenként percenként legfeljebb N generálás; az „Új szöveg” is ennek számít (US-09 AC3).
    // BR-15: at most N generations per minute per IP; "New text" counts too (US-09 AC3).
    private function limited(Request $request): ?array
    {
        $key = 'story-generation|'.$request->ip();
        $max = (int) config('story.rate_limits.generations_per_minute');

        if (RateLimiter::tooManyAttempts($key, $max)) {
            return ['type' => 'warning', 'message' => __('wizard.generate.rate_limited', ['seconds' => RateLimiter::availableIn($key)])];
        }
        RateLimiter::hit($key, 60);

        return null;
    }
}

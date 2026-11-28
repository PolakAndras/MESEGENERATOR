<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChildStepRequest;
use App\Http\Requests\PageStepRequest;
use App\Http\Requests\SettingsStepRequest;
use App\Models\Background;
use App\Models\Character;
use App\Models\StoryTheme;
use App\Services\StoryDraftService;
use App\Services\StoryRandomizer;
use App\Support\CanvasState;
use App\Support\PlacementRules;
use App\Support\StoryLimits;
use Illuminate\Http\Request;

/**
 * A varázsló 1–3. lépése és a belépési pont (App Flow 4.2). Vékony kontroller: a munkamenet-logika a
 * StoryDraftService-ben, a validáció a Form Requestekben van.
 * Wizard steps 1–3 and the entry point (App Flow 4.2). A thin controller: session logic lives in
 * StoryDraftService, validation in the Form Requests.
 */
class StoryWizardController extends Controller
{
    private StoryDraftService $drafts;

    public function __construct(StoryDraftService $drafts)
    {
        $this->drafts = $drafts;
    }

    // Belépési pont: elágazás S-06 / S-05 / S-07 felé (App Flow 4.2).
    // Entry point: branches to S-06 / S-05 / S-07 (App Flow 4.2).
    public function create()
    {
        if (! Background::exists()) {
            return view('stories.create.unavailable');
        }

        if ($this->drafts->status() === StoryDraftService::STATUS_SAVED) {
            $this->drafts->forget();  // App Flow D-20
        }

        if ($this->drafts->exists()) {
            return view('stories.create.resume', [
                'childName' => $this->drafts->child()['name'] ?? null,
                'generated' => $this->drafts->status() === StoryDraftService::STATUS_GENERATED,
                'resumeUrl' => $this->drafts->resumeUrl(),
            ]);
        }

        return redirect()->route('stories.create.child');
    }

    public function reset()
    {
        $this->drafts->forget();

        return redirect()->route('stories.create.child');
    }

    public function child()
    {
        if ($redirect = $this->drafts->guard('child')) {
            return redirect($redirect);
        }

        // A gyerekfigura-előnézet (US-01 AC2) a 8 alaphelyzet-képből választ; JS frissíti választáskor.
        // The child figure preview (US-01 AC2) picks from the 8 default images; JS updates it on change.
        $previews = Character::where('type', Character::TYPE_CHILD_FIGURE)->with('defaultAction')->get()
            ->filter(fn ($c) => $c->defaultAction !== null)
            ->mapWithKeys(fn ($c) => [$c->gender.'-'.$c->hair_color => CanvasState::imageUrl($c->defaultAction->image_path)]);

        return view('stories.create.child', [
            'child' => $this->drafts->child(),
            'previews' => $previews,
        ]);
    }

    public function storeChild(ChildStepRequest $request)
    {
        if ($redirect = $this->drafts->guard('child')) {
            return redirect($redirect);
        }

        $swapped = $this->drafts->saveChild($request->validated(), $request->figure());

        $redirect = redirect()->route('stories.create.settings');

        return $swapped ? $redirect->with('flash', ['type' => 'warning', 'message' => __('wizard.child.figure_swapped')]) : $redirect;
    }

    public function settings()
    {
        if ($redirect = $this->drafts->guard('settings')) {
            return redirect($redirect);
        }

        $limits = StoryLimits::current();

        return view('stories.create.settings', [
            'settings' => $this->drafts->settings(),
            'pageOptions' => range($limits['pages_min'], $limits['pages_max']),
            'themes' => StoryTheme::orderBy('sort')->pluck('name', 'id'),
        ]);
    }

    public function storeSettings(SettingsStepRequest $request)
    {
        if ($redirect = $this->drafts->guard('settings')) {
            return redirect($redirect);
        }

        $pageCount = (int) $request->input('page_count');
        $lost = $this->drafts->pagesLostOnReduce($pageCount);

        // Tartalommal bíró oldalak törlése előtt megerősítés kell (App Flow S-08, D-07).
        // Removing pages that have content needs confirmation first (App Flow S-08, D-07).
        if ($lost !== [] && ! $request->boolean('confirm_reduce')) {
            return redirect()->route('stories.create.settings')
                ->withInput()
                ->with('confirm_reduce', ['from' => min($lost), 'to' => max($lost)]);
        }

        $hadPages = $this->drafts->get()['last_step'] === 'page';
        $this->drafts->saveSettings($request->theme(), $pageCount);

        $page = $hadPages ? ($this->drafts->get()['last_page'] ?? 1) : 1;

        return redirect()->route('stories.create.page', ['page' => $page]);
    }

    public function page(Request $request, int $page)
    {
        if ($redirect = $this->drafts->guard('page')) {
            return redirect($redirect);
        }
        if ($page < 1 || $page > $this->drafts->pageCount()) {
            return redirect()->route('stories.create.page', ['page' => 1]);
        }

        // Validációs hiba után a beküldött állapot jelenik meg (App Flow S-09), egyébként a mentett oldal.
        // After a validation error the submitted state is shown (App Flow S-09), otherwise the saved page.
        $draftPage = $request->old('child_enabled') !== null ? $this->pageFromOldInput($request) : $this->drafts->page($page);

        return view('stories.create.page', $this->editorData($page, $draftPage));
    }

    public function storePage(PageStepRequest $request, StoryRandomizer $randomizer, int $page)
    {
        if ($redirect = $this->drafts->guard('page')) {
            return redirect($redirect);
        }
        if ($page < 1 || $page > $this->drafts->pageCount()) {
            return redirect()->route('stories.create.page', ['page' => 1]);
        }

        $this->drafts->savePage($page, $request->validated());

        // „Véletlen oldal” (PRD 1.6 US-21): az aktuális oldalt a mese tervében hozzá tartozó jelenet képére cseréli,
        // és ugyanoda tér vissza. / "Random page" (PRD 1.6 US-21): replaces the current page with the picture of its
        // scene in the story plan and returns to the same page.
        if ($request->input('target') === 'random') {
            $this->drafts->putPage($page, $randomizer->page($this->drafts, $page));

            return redirect()->route('stories.create.page', ['page' => $page])
                ->with('flash', ['type' => 'info', 'message' => __('wizard.pages.randomized')]);
        }

        return redirect($this->targetUrl($request->input('target'), $page));
    }

    private function targetUrl(string $target, int $page): string
    {
        $count = $this->drafts->pageCount();

        switch (true) {
            case $target === 'child':
                return route('stories.create.child');
            case $target === 'settings':
                return route('stories.create.settings');
            case $target === 'prev':
                return route('stories.create.page', ['page' => max(1, $page - 1)]);
            case $target === 'next':
                return route('stories.create.page', ['page' => min($count, $page + 1)]);
            default:
                $n = (int) substr($target, 5);

                return route('stories.create.page', ['page' => ($n >= 1 && $n <= $count) ? $n : $page]);
        }
    }

    private function editorData(int $page, array $draftPage): array
    {
        $child = $this->drafts->child();
        $figure = Character::with('actions')->find($child['character_id']);
        $animals = Character::where('type', Character::TYPE_ANIMAL)->with('actions')->orderBy('name')->get();
        $toActions = fn (Character $c) => $c->actions->sortByDesc('is_default')->values()->map(fn ($a) => [
            'id' => $a->id,
            'name' => $a->name,
            'url' => CanvasState::imageUrl($a->image_path),
            'is_default' => $a->is_default,
        ])->all();

        $canvas = CanvasState::fromDraftPage($draftPage);
        $childPlacement = collect($canvas['placements'])->firstWhere('character_id', $figure->id);

        return [
            'page' => $page,
            'pageCount' => $this->drafts->pageCount(),
            'missingBackgrounds' => collect($this->drafts->pages())->filter(fn ($p) => $p['background_id'] === null)->keys()->all(),
            'canvas' => $canvas,
            'childPlacement' => $childPlacement,
            'animalPlacements' => collect($canvas['placements'])->where('type', Character::TYPE_ANIMAL)
                ->sortBy(fn ($p) => $p['slot'] ?? 0)->values()->all(),
            'backgrounds' => Background::orderBy('name')->get()->map(fn ($b) => [
                'id' => $b->id, 'name' => $b->name, 'url' => CanvasState::imageUrl($b->image_path),
            ])->all(),
            'figure' => ['id' => $figure->id, 'name' => $figure->name, 'actions' => $toActions($figure)],
            'animals' => $animals->map(fn ($a) => ['id' => $a->id, 'name' => $a->name, 'actions' => $toActions($a)])->all(),
            'childName' => $child['name'],
            'limits' => StoryLimits::current(),
            'placementConfig' => config('story.placement') + ['bounds' => PlacementRules::bounds()],
        ];
    }

    // A beküldött, hibás űrlapból vázlat-oldal, hogy a felhasználó ne veszítse el a munkáját.
    // Turns the submitted, invalid form back into a draft page, so the user does not lose their work.
    private function pageFromOldInput(Request $request): array
    {
        $scale = PlacementRules::scale();
        $placements = [];
        $childId = $this->drafts->child()['character_id'];

        if ($request->old('child_enabled')) {
            $placements[] = [
                'character_id' => $childId,
                'action_id' => $this->actionOrDefault($childId, $request->old('child.action_id')),
                'pos_x' => (float) $request->old('child.pos_x'), 'pos_y' => (float) $request->old('child.pos_y'),
                'scale' => $scale, 'slot' => null, 'flipped' => (bool) $request->old('child.flipped'),
            ];
        }
        foreach ((array) $request->old('animals', []) as $animal) {
            $id = (int) ($animal['character_id'] ?? 0);
            $actionId = $this->actionOrDefault($id, $animal['action_id'] ?? null);
            if ($actionId === null) {
                continue;
            }
            $placements[] = [
                'character_id' => $id, 'action_id' => $actionId,
                'pos_x' => (float) ($animal['pos_x'] ?? 0), 'pos_y' => (float) ($animal['pos_y'] ?? 0),
                'scale' => $scale, 'slot' => (int) ($animal['slot'] ?? 0), 'flipped' => ! empty($animal['flipped']),
            ];
        }

        $background = $request->old('background_id');

        return ['background_id' => $background ? (int) $background : null, 'placements' => $placements];
    }

    private function actionOrDefault(int $characterId, $actionId): ?int
    {
        if (! empty($actionId)) {
            return (int) $actionId;
        }

        return optional(optional(Character::find($characterId))->defaultAction)->id;
    }
}

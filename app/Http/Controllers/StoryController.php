<?php

namespace App\Http\Controllers;

use App\Models\Story;
use App\Services\StoryDraftService;
use App\Services\StorySaver;
use App\Support\CanvasState;
use Illuminate\Http\Request;

/**
 * Mentett mesék: mentés, Meséim, megtekintés, törlés (App Flow S-10, S-11, S-12; PRD US-14 – US-16).
 * Más felhasználó meséje 404, hogy a létezése se derüljön ki (App Flow D-21, Backend Schema 10.3).
 * Saved stories: save, My stories, view, delete (App Flow S-10, S-11, S-12; PRD US-14 – US-16).
 * Another user's story gives 404, so even its existence is not revealed (App Flow D-21, Backend Schema 10.3).
 */

function sayHello(){return "Hello";
};

class StoryController extends Controller
{
    public function store(Request $request, StoryDraftService $drafts, StorySaver $saver)
    {
        $story = $saver->save($drafts, $request->user());

        if ($story === null) {
            return redirect()->route('stories.result', ['page' => 0])
                ->with('flash', ['type' => 'error', 'message' => __('stories.save_failed')]);
        }

        return redirect()->route('stories.result', ['page' => 0])
            ->with('flash', ['type' => 'success', 'message' => __('stories.saved')]);
    }

    public function index(Request $request)
    {
        // Az adminnál is csak a saját mesék (Backend Schema 10.3); a többit az admin meselista mutatja.
        // Only own stories, even for the admin (Backend Schema 10.3); the rest are on the admin story list.
        $stories = $request->user()->stories()->latest()->latest('id')->get();

        return view('stories.index', ['stories' => $stories]);
    }

    public function show(Request $request, Story $story)
    {
        abort_unless($request->user()->can('view', $story), 404);

        $story->load(['pages.background', 'pages.placements.character', 'pages.placements.action']);
        $pages = [];
        foreach ($story->pages as $page) {
            $pages[$page->page_number] = ['canvas' => CanvasState::fromStoryPage($page), 'text' => $page->text];
        }

        return view('stories.show', [
            'story' => $story,
            'pages' => $pages,
            'current' => StoryGenerationController::currentPage($request, count($pages)),
        ]);
    }

    public function destroy(Request $request, Story $story)
    {
        abort_unless($request->user()->can('delete', $story), 404);

        // Végleges törlés; az oldalak és elhelyezések az idegen kulcs cascade szabályával törlődnek (US-16 AC2).
        // Permanent delete; pages and placements go with it via the foreign key cascade (US-16 AC2).
        $story->delete();

        return redirect()->route('stories.index')->with('flash', ['type' => 'success', 'message' => __('stories.deleted')]);
    }
}

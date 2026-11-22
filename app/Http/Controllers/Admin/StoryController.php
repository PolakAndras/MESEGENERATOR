<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Story;

/**
 * Admin meselista (Backend Schema 10.5, E-02): minden mentett mese, legújabb elöl. A megnyitás a közös
 * stories.show útvonalon történik (a policy az adminnak engedi).
 * Admin story list (Backend Schema 10.5, E-02): every saved story, newest first. Opening uses the shared
 * stories.show route (the policy allows it for the admin).
 */
class StoryController extends Controller
{
    public function index()
    {
        $stories = Story::with('user:id,name')->latest()->latest('id')->get();

        return view('admin.stories.index', ['stories' => $stories]);
    }

    // Végleges törlés (Backend Schema 4., 10.5). / Permanent delete (Backend Schema 4., 10.5).
    public function destroy(Story $story)
    {
        $story->delete();

        return redirect()->route('admin.stories.index')
            ->with('flash', ['type' => 'success', 'message' => __('stories.deleted')]);
    }
}

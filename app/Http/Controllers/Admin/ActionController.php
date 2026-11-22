<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ActionRequest;
use App\Models\Action;
use App\Models\Character;
use App\Services\CatalogService;

/**
 * Cselekvések (App Flow S-23, S-24, „Alaphelyzetté tesz”). Törlés nincs (Backend Schema E-01).
 * Actions (App Flow S-23, S-24, "Make default"). No deletion (Backend Schema E-01).
 */
class ActionController extends Controller
{
    public function create(Character $character, CatalogService $catalog)
    {
        if ($redirect = $this->limitReached($character, $catalog)) {
            return $redirect;
        }

        return view('admin.actions.create', ['character' => $character]);
    }

    public function store(ActionRequest $request, Character $character, CatalogService $catalog)
    {
        // BR-05, BR-06: a szerver is ellenőrzi a korlátot; túllépéskor S-22 + hibaüzenet (App Flow S-23).
        // BR-05, BR-06: the server checks the limit too; exceeding it returns to S-22 with an error (App Flow S-23).
        if ($redirect = $this->limitReached($character, $catalog)) {
            return $redirect;
        }

        $catalog->createAction($character, $request->input('name'), $request->file('image'));

        return redirect()->route('admin.characters.edit', $character)
            ->with('flash', ['type' => 'success', 'message' => __('admin.actions.created')]);
    }

    public function edit(Action $action)
    {
        return view('admin.actions.edit', ['action' => $action->load('character')]);
    }

    public function update(ActionRequest $request, Action $action, CatalogService $catalog)
    {
        $catalog->updateAction($action, $request->input('name'), $request->file('image'));

        return redirect()->route('admin.characters.edit', $action->character_id)
            ->with('flash', ['type' => 'success', 'message' => __('admin.actions.updated')]);
    }

    public function makeDefault(Action $action, CatalogService $catalog)
    {
        $catalog->makeDefault($action);

        return redirect()->route('admin.characters.edit', $action->character_id)
            ->with('flash', ['type' => 'success', 'message' => __('admin.actions.made_default')]);
    }

    private function limitReached(Character $character, CatalogService $catalog)
    {
        $limit = $catalog->actionLimit($character);
        if ($character->actions()->count() < $limit) {
            return null;
        }

        return redirect()->route('admin.characters.edit', $character)
            ->with('flash', ['type' => 'error', 'message' => __('admin.actions.limit_reached', ['n' => $limit])]);
    }
}

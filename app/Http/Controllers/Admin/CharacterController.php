<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CharacterRequest;
use App\Models\Character;
use App\Services\CatalogService;
use Illuminate\Http\Request;

/**
 * Szereplők (App Flow S-20 – S-22). Törlés és visszaállítás nincs (Backend Schema E-01, D-03).
 * Characters (App Flow S-20 – S-22). No deletion or restore (Backend Schema E-01, D-03).
 */
class CharacterController extends Controller
{
    public function index(Request $request)
    {
        $type = in_array($request->query('type'), [Character::TYPE_ANIMAL, Character::TYPE_CHILD_FIGURE], true)
            ? $request->query('type') : null;

        $characters = Character::with('defaultAction')->withCount('actions')
            ->when($type, fn ($q) => $q->where('type', $type))
            ->orderBy('type')->orderBy('name')->get();

        return view('admin.characters.index', ['characters' => $characters, 'type' => $type]);
    }

    public function create()
    {
        return view('admin.characters.create');
    }

    public function store(CharacterRequest $request, CatalogService $catalog)
    {
        $character = $catalog->createCharacter(
            $request->validated(),
            $request->input('default_action_name'),
            $request->file('default_action_image')
        );

        return redirect()->route('admin.characters.edit', $character)
            ->with('flash', ['type' => 'success', 'message' => __('admin.characters.created')]);
    }

    public function edit(Character $character, CatalogService $catalog)
    {
        $character->load(['actions' => fn ($q) => $q->orderByDesc('is_default')->orderBy('name')]);

        return view('admin.characters.edit', [
            'character' => $character,
            'limit' => $catalog->actionLimit($character),
        ]);
    }

    public function update(CharacterRequest $request, Character $character, CatalogService $catalog)
    {
        $catalog->updateCharacter($character, $request->validated());

        return redirect()->route('admin.characters.edit', $character)
            ->with('flash', ['type' => 'success', 'message' => __('admin.characters.updated')]);
    }
}

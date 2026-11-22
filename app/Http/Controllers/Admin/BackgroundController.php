<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BackgroundRequest;
use App\Models\Background;
use App\Services\CatalogService;

/**
 * Hátterek (App Flow S-25 – S-27). Törlés és visszaállítás nincs (Backend Schema E-01).
 * Backgrounds (App Flow S-25 – S-27). No deletion or restore (Backend Schema E-01).
 */
class BackgroundController extends Controller
{
    public function index()
    {
        return view('admin.backgrounds.index', ['backgrounds' => Background::orderBy('name')->get()]);
    }

    public function create()
    {
        return view('admin.backgrounds.create');
    }

    public function store(BackgroundRequest $request, CatalogService $catalog)
    {
        $catalog->createBackground($request->input('name'), $request->file('image'));

        return redirect()->route('admin.backgrounds.index')
            ->with('flash', ['type' => 'success', 'message' => __('admin.backgrounds.created')]);
    }

    public function edit(Background $background)
    {
        return view('admin.backgrounds.edit', ['background' => $background]);
    }

    public function update(BackgroundRequest $request, Background $background, CatalogService $catalog)
    {
        $catalog->updateBackground($background, $request->input('name'), $request->file('image'));

        return redirect()->route('admin.backgrounds.index')
            ->with('flash', ['type' => 'success', 'message' => __('admin.backgrounds.updated')]);
    }
}

<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\StoryGenerationController;
use App\Http\Controllers\StoryWizardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Az útvonaltérkép az App Flow 6.3 szerint, a Backend Schema 10.5 változásaival (nincs törlés és
| visszaállítás a katalógusban; új admin meselista).
| The route map follows App Flow 6.3 with the Backend Schema 10.5 changes (no catalog deletion or
| restore; new admin story list).
|
*/

// Nyilvános oldalak. Route::view, mert nincs mögöttük logika, így kontroller sem kell.
// Public pages. Route::view because there is no logic behind them, so no controller is needed.
Route::view('/', 'welcome')->name('home');
Route::view('/privacy', 'privacy')->name('privacy');

// Fiók (App Flow S-03, S-04, kilépés). / Account (App Flow S-03, S-04, logout).
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Varázsló (App Flow 4.2). Vendég és bejelentkezett felhasználó egyaránt használhatja (PRD US-10 AC1).
// Wizard (App Flow 4.2). Usable by guests and logged-in users alike (PRD US-10 AC1).
Route::prefix('stories/create')->name('stories.')->group(function () {
    Route::get('/', [StoryWizardController::class, 'create'])->name('create');
    Route::post('/reset', [StoryWizardController::class, 'reset'])->name('create.reset');
    Route::get('/child', [StoryWizardController::class, 'child'])->name('create.child');
    Route::post('/child', [StoryWizardController::class, 'storeChild'])->name('create.child.store');
    Route::get('/settings', [StoryWizardController::class, 'settings'])->name('create.settings');
    Route::post('/settings', [StoryWizardController::class, 'storeSettings'])->name('create.settings.store');
    Route::get('/pages/{page}', [StoryWizardController::class, 'page'])->whereNumber('page')->name('create.page');
    Route::post('/pages/{page}', [StoryWizardController::class, 'storePage'])->whereNumber('page')->name('create.page.store');
    Route::post('/generate', [StoryGenerationController::class, 'generate'])->name('generate');
    Route::post('/surprise', [StoryGenerationController::class, 'surprise'])->name('surprise');
    Route::get('/result', [StoryGenerationController::class, 'result'])->name('result');
    Route::post('/regenerate', [StoryGenerationController::class, 'regenerate'])->name('regenerate');
});

// Mentett mesék (App Flow S-10 mentés, S-11, S-12). / Saved stories (App Flow S-10 save, S-11, S-12).
Route::middleware('auth')->group(function () {
    Route::post('/stories', [StoryController::class, 'store'])->name('stories.store');
    Route::get('/stories', [StoryController::class, 'index'])->name('stories.index');
    Route::get('/stories/{story}', [StoryController::class, 'show'])->whereNumber('story')->name('stories.show');
    Route::delete('/stories/{story}', [StoryController::class, 'destroy'])->whereNumber('story')->name('stories.destroy');

    Route::get('/account', [AccountController::class, 'show'])->name('account.show');
    Route::delete('/account', [AccountController::class, 'destroy'])->name('account.destroy');
});

// Admin (App Flow 4.4, Backend Schema 10.5). / Admin (App Flow 4.4, Backend Schema 10.5).
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::redirect('/', '/admin/characters')->name('home');

    Route::get('/characters', [Admin\CharacterController::class, 'index'])->name('characters.index');
    Route::get('/characters/create', [Admin\CharacterController::class, 'create'])->name('characters.create');
    Route::post('/characters', [Admin\CharacterController::class, 'store'])->name('characters.store');
    Route::get('/characters/{character}/edit', [Admin\CharacterController::class, 'edit'])->name('characters.edit');
    Route::put('/characters/{character}', [Admin\CharacterController::class, 'update'])->name('characters.update');

    Route::get('/characters/{character}/actions/create', [Admin\ActionController::class, 'create'])->name('characters.actions.create');
    Route::post('/characters/{character}/actions', [Admin\ActionController::class, 'store'])->name('characters.actions.store');
    Route::get('/actions/{action}/edit', [Admin\ActionController::class, 'edit'])->name('actions.edit');
    Route::put('/actions/{action}', [Admin\ActionController::class, 'update'])->name('actions.update');
    Route::post('/actions/{action}/make-default', [Admin\ActionController::class, 'makeDefault'])->name('actions.make-default');

    Route::get('/backgrounds', [Admin\BackgroundController::class, 'index'])->name('backgrounds.index');
    Route::get('/backgrounds/create', [Admin\BackgroundController::class, 'create'])->name('backgrounds.create');
    Route::post('/backgrounds', [Admin\BackgroundController::class, 'store'])->name('backgrounds.store');
    Route::get('/backgrounds/{background}/edit', [Admin\BackgroundController::class, 'edit'])->name('backgrounds.edit');
    Route::put('/backgrounds/{background}', [Admin\BackgroundController::class, 'update'])->name('backgrounds.update');

    Route::get('/stories', [Admin\StoryController::class, 'index'])->name('stories.index');
    Route::delete('/stories/{story}', [Admin\StoryController::class, 'destroy'])->name('stories.destroy');
});

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Services\StoryDraftService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Regisztráció, belépés, kilépés saját kontrollerrel, a beépített Auth és Hash eszközökkel, csomag nélkül
 * (Tech Stack 4.1, D-09). Belépéskor a munkamenet-azonosító megújul, a tartalma (a félkész mese) megmarad
 * (Backend Schema 9.5, PRD US-10 AC5).
 *
 * Registration, login and logout with own controllers, built-in Auth and Hash, no package
 * (Tech Stack 4.1, D-09). On login the session id is regenerated but its content (the draft story) is kept
 * (Backend Schema 9.5, PRD US-10 AC5).
 */
class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request, StoryDraftService $drafts)
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            // Általános üzenet, nem árulja el, melyik adat hibás (US-12 AC1); az e-mail megmarad.
            // A generic message that does not reveal which field is wrong (US-12 AC1); the email is kept.
            throw ValidationException::withMessages(['login' => __('auth.failed')]);
        }

        $request->session()->regenerate();

        return redirect($drafts->resumeUrl() ?? route('home'));
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request, StoryDraftService $drafts)
    {
        $user = new User($request->only('name', 'email'));
        $user->password = Hash::make($request->input('password'));
        // A szerepkört és az elfogadás idejét a kód állítja, nem az űrlap (Backend Schema 5., 8.1).
        // Role and acceptance time are set by the code, not the form (Backend Schema 5., 8.1).
        $user->role = User::ROLE_USER;
        $user->privacy_accepted_at = now();
        $user->save();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect($drafts->resumeUrl() ?? route('home'))
            ->with('flash', ['type' => 'success', 'message' => __('account.registered')]);
    }

    // Kilépéskor a munkamenet érvénytelen lesz, a folyamatban lévő mese elvész (App Flow D-22).
    // Logging out invalidates the session; any story in progress is lost (App Flow D-22).
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}

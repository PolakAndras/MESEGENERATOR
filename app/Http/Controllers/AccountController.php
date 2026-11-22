<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteAccountRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Fiókom (App Flow S-13) és a fióktörlés (PRD US-13, Backend Schema 10.4). Az admin fiók nem törölhető.
 * My account (App Flow S-13) and account deletion (PRD US-13, Backend Schema 10.4). The admin cannot be deleted.
 */
class AccountController extends Controller
{
    public function show(Request $request)
    {
        return view('account.show', ['user' => $request->user()]);
    }

    public function destroy(DeleteAccountRequest $request)
    {
        $user = $request->user();
        abort_if($user->isAdmin(), 403);

        // A mesék, oldalak és elhelyezések az idegen kulcsok cascade szabályával törlődnek (US-13 AC2).
        // Stories, pages and placements are removed by the foreign key cascade (US-13 AC2).
        Auth::logout();
        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('flash', ['type' => 'success', 'message' => __('account.deleted')]);
    }
}

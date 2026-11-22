<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Az /admin útvonalak védelme (Backend Schema 10.2): nem admin → 403 (PRD US-17 AC5).
 * Az auth middleware előtte fut, így a vendég a belépő oldalra kerül.
 * Protects the /admin routes (Backend Schema 10.2): non-admin → 403 (PRD US-17 AC5).
 * The auth middleware runs first, so guests are sent to the login page.
 */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless($request->user() && $request->user()->isAdmin(), 403);

        return $next($request);
    }
}

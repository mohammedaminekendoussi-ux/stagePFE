<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPasswordChanged
{
    public function handle(Request $request, Closure $next)
    {
        // Ne pas bloquer la route de changement de mot de passe
        if ($request->routeIs('password.change.form') || $request->routeIs('password.change.update')) {
            return $next($request);
        }

        $user = Auth::user();
        if ($user && is_null($user->password_changed_at)) {
            return redirect()->route('password.change.form');
        }
        return $next($request);
    }
}
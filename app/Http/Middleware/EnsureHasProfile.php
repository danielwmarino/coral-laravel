<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHasProfile
{
    /**
     * Redirect users with no profile assignment to /pending.
     * Applied to all authenticated routes except /pending itself.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && !$user->profile
            && !$request->routeIs('pending')
            && !$request->routeIs('logout')
            && !$request->is('livewire*')
        ) {
            return redirect()->route('pending');
        }

        return $next($request);
    }
}

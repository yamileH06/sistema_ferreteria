<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return to_route('login');
        }

        if (! in_array($user->effectiveRole(), $roles, true)) {
            return to_route($user->panelRoute())
                ->with('error', 'No tienes permiso para acceder a ese módulo.');
        }

        return $next($request);
    }
}

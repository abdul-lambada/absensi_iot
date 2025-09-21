<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Wajib login
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Jika middleware dipasang tanpa parameter role, lewati
        if (empty($roles)) {
            return $next($request);
        }

        // Izinkan hanya jika role user termasuk dalam daftar parameter
        if (in_array($user->role, $roles, true)) {
            return $next($request);
        }

        abort(403, 'Unauthorized');
    }
}
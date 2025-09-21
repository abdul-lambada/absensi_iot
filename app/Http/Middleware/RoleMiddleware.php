<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

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
        // Ambil user dari auth; untuk dev tanpa auth, fallback ke user pertama
        $user = Auth::user() ?? User::first();

        if (!$user) {
            // Jika tidak ada user sama sekali, arahkan ke halaman welcome
            return redirect()->route('welcome');
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
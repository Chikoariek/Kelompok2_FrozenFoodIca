<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * RoleMiddleware
 * 
 * Penjaga gerbang otorisasi multi-role.
 * Memeriksa apakah pengguna yang sedang login memiliki role yang sesuai (contoh: 'admin' atau 'user').
 * Jika tidak berhak, akses langsung ditolak dengan status HTTP 403 Forbidden.
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        $isAdmin = ($user->role === 'admin' || $user->isAdmin());
        $hasRole = ($role === 'admin') ? $isAdmin : ($user->role === $role || $isAdmin);

        if (!$hasRole) {
            if ($role === 'admin') {
                return redirect()->route('home')->with('error', 'Akses ditolak. Halaman ini khusus untuk administrator.');
            }
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    // Tambahkan argumen string $role
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!auth()->check()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki hak akses halaman ini.');
        }

        $user = auth()->user();
        $hasRole = ($role === 'admin') 
            ? ($user->role === 'admin' || $user->isAdmin())
            : ($user->role === $role);

        if (!$hasRole) {
            abort(403, 'Akses ditolak. Anda tidak memiliki hak akses halaman ini.');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware PreventBackHistory
 * 
 * Mencegah browser menyimpan halaman terproteksi ke dalam cache (BFCache / disk cache).
 * Setelah pengguna logout, jika tombol "Back" pada browser ditekan, browser dipaksa
 * mengirim request baru ke server alih-alih menampilkan snapshot lama dari cache.
 * Permintaan tersebut kemudian akan dicegat oleh middleware 'auth' dan dialihkan.
 */
class PreventBackHistory
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Tambahkan header anti-cache pada response
        $response->headers->set('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Sun, 02 Jan 1990 00:00:00 GMT');

        return $response;
    }
}

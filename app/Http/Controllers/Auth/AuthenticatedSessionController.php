<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Controller Autentikasi Pengguna (Login & Logout)
 * Menangani verifikasi kredensial serta pengalihan halaman berbasis multi-role (Admin vs User).
 */
class AuthenticatedSessionController extends Controller
{
    /**
     * Menampilkan antarmuka form login.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Memproses permintaan login masuk.
     * Alur: Verifikasi kredensial -> Regenerasi session token -> Cek role -> Redirect ke dashboard tujuan.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // 1. Verifikasi email & password via Laravel Breeze
        $request->authenticate();

        // 2. Cegah Session Fixation attack dengan regenerasi ID session
        $request->session()->regenerate();

        // 3. Logika Multi-Role: Arahkan ke dashboard yang sesuai hak aksesnya
        if ($request->user()->role === 'admin' || $request->user()->isAdmin()) {
            return redirect()->intended(route('admin.dashboard', absolute: false));
        }

        // Pengguna biasa diarahkan langsung ke homepage (dengan status login & profil aktif di navbar)
        return redirect()->intended(route('home', absolute: false));
    }

    /**
     * Memproses logout (keluar sesi).
     * Alur: Invalidate session web -> Hapus token sesi -> Redirect kembali ke halaman utama.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        if (env('EXAM_MODE', true)) {
            return redirect()->route('login');
        }

        return redirect('/');
    }
}

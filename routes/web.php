<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// --- 1. Rute Publik (Mode Ujian / Normal dikendalikan via EXAM_MODE di .env) ---
Route::get('/', function () {
    if (env('EXAM_MODE', true)) {
        return redirect()->route('login');
    }
    return view('welcome', [
        'user' => Auth::user(),
        'isLoggedIn' => Auth::check(),
        'products' => \App\Models\Product::all(),
        'categories' => \App\Models\Category::all(),
        'orders' => \App\Models\Order::all(),
    ]);
})->name('home');

// --- 2. Rute Pelanggan (Khusus role:user yang sudah login) ---
Route::middleware(['auth', 'verified', 'role:user'])->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route('home');
    })->name('dashboard');

    Route::get('/order', function () {
        return redirect()->route('home');
    })->name('order');
});

// --- 3. Rute Khusus Administrator Toko (role:admin) ---
Route::middleware(['auth', 'role:admin'])->prefix('admin')->as('admin.')->group(function () {
    // Tampilan utama dashboard admin (menampilkan produk, kategori, dan pesanan)
    Route::get('/dashboard', function () {
        $products = \App\Models\Product::all();
        $categories = \App\Models\Category::all();
        $orders = \App\Models\Order::orderBy('created_at', 'desc')->get();
        return view('admin.dashboard', [
            'user' => Auth::user(),
            'products' => $products,
            'categories' => $categories,
            'orders' => $orders,
        ]);
    })->name('dashboard');

    // Fallback URL di bawah /admin/ agar kembali ke dashboard
    Route::get('/{any}', function () {
        return redirect()->route('admin.dashboard');
    })->where('any', '.*')->name('any');
});

// Shortcut URL /admin otomatis mengarah ke /admin/dashboard
Route::get('/admin', function () {
    return redirect()->route('admin.dashboard');
});

// --- 4. Rute Profil Pengguna (Edit data profil & password) ---
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// --- 5. Rute API AJAX (Sinkronisasi database tanpa reload halaman) ---
Route::prefix('api')->group(function () {
    // Ambil data kategori, produk, dan riwayat pesanan
    Route::get('/categories', [\App\Http\Controllers\AdminDataController::class, 'getCategories']);
    Route::get('/products', [\App\Http\Controllers\AdminDataController::class, 'getProducts']);
    Route::get('/orders', [\App\Http\Controllers\AdminDataController::class, 'getOrders']);

    // Simpan pesanan baru & perbarui status pesanan
    Route::post('/orders', [\App\Http\Controllers\AdminDataController::class, 'storeOrder']);
    Route::patch('/orders/{id}/status', [\App\Http\Controllers\AdminDataController::class, 'updateOrderStatus']);

    // Operasi CRUD Kategori Produk
    Route::post('/categories', [\App\Http\Controllers\AdminDataController::class, 'storeCategory']);
    Route::post('/categories/update', [\App\Http\Controllers\AdminDataController::class, 'updateCategory']);
    Route::post('/categories/delete', [\App\Http\Controllers\AdminDataController::class, 'destroyCategory']);
    Route::post('/categories/sync', [\App\Http\Controllers\AdminDataController::class, 'syncCategories']);

    // Operasi CRUD Produk & Stok Freezer
    Route::post('/products', [\App\Http\Controllers\AdminDataController::class, 'storeProduct']);
    Route::post('/products/{id}/stock', [\App\Http\Controllers\AdminDataController::class, 'updateProductStock']);
    Route::delete('/products/{id}', [\App\Http\Controllers\AdminDataController::class, 'destroyProduct']);
    Route::post('/products/sync', [\App\Http\Controllers\AdminDataController::class, 'syncProducts']);
});

// --- 6. Rute Autentikasi Laravel Breeze (Login, Register, Logout) ---
require __DIR__.'/auth.php';

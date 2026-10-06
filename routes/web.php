<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ============================================================
// 1. PUBLIC ROUTES (Dapat diakses siapapun tanpa login)
// ============================================================

// Halaman utama: Menampilkan katalog produk, etalase, dan keranjang belanja
Route::get('/', function () {
    return redirect()->route('login');
})->name('home');


// ============================================================
// 2. USER ROUTES (Khusus Pelanggan yang Sudah Login, role: user)
// ============================================================

Route::middleware(['auth', 'verified', 'role:user'])->group(function () {
    // Pengguna biasa diarahkan langsung ke homepage dengan profil aktif di navbar
    Route::get('/dashboard', function () {
        return redirect()->route('home');
    })->name('dashboard');

    // Rute pemesanan customer diarahkan ke homepage
    Route::get('/order', function () {
        return redirect()->route('home');
    })->name('order');
});


// ============================================================
// 3. ADMIN ROUTES (Khusus Administrator Toko, role: admin)
// Dilindungi ganda: Harus Login ('auth') & Harus Role Admin ('role:admin')
// ============================================================

Route::middleware(['auth', 'role:admin'])->prefix('admin')->as('admin.')->group(function () {
    // Halaman utama dashboard admin: menyuplai data produk, kategori, dan pesanan terbaru
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

    // Menangani redirect fallback untuk URL di bawah prefix /admin/
    Route::get('/{any}', function () {
        return redirect()->route('admin.dashboard');
    })->where('any', '.*')->name('any');
});

// Shortcut redirect otomatis dari URL /admin langsung ke /admin/dashboard
Route::get('/admin', function () {
    return redirect()->route('admin.dashboard');
});


// ============================================================
// 4. PROFILE ROUTES (Laravel Breeze untuk edit data profil & password)
// ============================================================

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


// ============================================================
// 5. DATA & API ROUTES (Endpoint AJAX untuk sinkronisasi Database)
// Menangani pembacaan dan pembaruan data secara asynchronous tanpa reload halaman
// ============================================================

Route::prefix('api')->group(function () {
    // Mengambil data kategori, produk, dan riwayat pesanan
    Route::get('/categories', [\App\Http\Controllers\AdminDataController::class, 'getCategories']);
    Route::get('/products', [\App\Http\Controllers\AdminDataController::class, 'getProducts']);
    Route::get('/orders', [\App\Http\Controllers\AdminDataController::class, 'getOrders']);

    // Proses pesanan: Checkout pelanggan baru & Perubahan status (Diproses/Dikirim/Selesai)
    Route::post('/orders', [\App\Http\Controllers\AdminDataController::class, 'storeOrder']);
    Route::patch('/orders/{id}/status', [\App\Http\Controllers\AdminDataController::class, 'updateOrderStatus']);

    // Manajemen Kategori: Tambah, Ubah Nama, Hapus, dan Sinkronisasi Massal
    Route::post('/categories', [\App\Http\Controllers\AdminDataController::class, 'storeCategory']);
    Route::post('/categories/update', [\App\Http\Controllers\AdminDataController::class, 'updateCategory']);
    Route::post('/categories/delete', [\App\Http\Controllers\AdminDataController::class, 'destroyCategory']);
    Route::post('/categories/sync', [\App\Http\Controllers\AdminDataController::class, 'syncCategories']);

    // Manajemen Produk: Tambah Produk Baru, Update Stok Freezer, Hapus, & Sinkronisasi
    Route::post('/products', [\App\Http\Controllers\AdminDataController::class, 'storeProduct']);
    Route::post('/products/{id}/stock', [\App\Http\Controllers\AdminDataController::class, 'updateProductStock']);
    Route::delete('/products/{id}', [\App\Http\Controllers\AdminDataController::class, 'destroyProduct']);
    Route::post('/products/sync', [\App\Http\Controllers\AdminDataController::class, 'syncProducts']);
});


// ============================================================
// AUTH ROUTES (Breeze)
// ============================================================

require __DIR__.'/auth.php';

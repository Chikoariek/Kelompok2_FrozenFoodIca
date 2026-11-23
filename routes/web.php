<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// --- 1. Rute Publik (Mode Ujian / Normal dikendalikan via EXAM_MODE di .env) ---
Route::get('/', function () {
    if (env('EXAM_MODE', false)) {
        return redirect()->route('login');
    }
    return view('welcome', [
        'user' => Auth::user(),
        'isLoggedIn' => Auth::check(),
        'products' => \App\Models\Product::all(),
        'categories' => \App\Models\Category::all(),
        'orders' => \App\Models\Order::all(),
        'bundles' => \App\Models\Bundle::with('items.product')->where('is_active', true)->get(),
        'storeStatus' => \App\Http\Controllers\AdminDataController::getStoreStatusArray(),
    ]);
})->name('home');

// --- 2. Rute Pelanggan & Dasbor Universal (Pengalihan cerdas berbasis peran pengguna) ---
Route::middleware(['auth', 'prevent-back-history'])->group(function () {
    Route::get('/dashboard', function () {
        if (Auth::user()->role === 'admin' || Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('home');
    })->name('dashboard');

    Route::get('/order', function () {
        return redirect()->route('home');
    })->name('order');
});

// --- 3. Rute Khusus Administrator Toko (role:admin) ---
Route::middleware(['auth', 'role:admin', 'prevent-back-history'])->prefix('admin')->as('admin.')->group(function () {
    // Tampilan utama dashboard admin (menampilkan produk, kategori, dan pesanan)
    Route::get('/dashboard', function () {
        $products = \App\Models\Product::all();
        $categories = \App\Models\Category::all();
        $orders = \App\Models\Order::orderBy('created_at', 'desc')->get();
        return view('admin', [
            'user' => Auth::user(),
            'products' => $products,
            'categories' => $categories,
            'orders' => $orders,
            'bundles' => \App\Models\Bundle::with('items.product')->get(),
            'storeStatus' => \App\Http\Controllers\AdminDataController::getStoreStatusArray(),
        ]);
    })->name('dashboard');

    // Export Data Excel / Spreadsheet
    Route::get('/export/products', [\App\Http\Controllers\AdminDataController::class, 'exportProducts'])->name('export.products');
    Route::get('/export/orders', [\App\Http\Controllers\AdminDataController::class, 'exportOrders'])->name('export.orders');
    Route::get('/export/bundles', [\App\Http\Controllers\AdminDataController::class, 'exportBundles'])->name('export.bundles');
    Route::get('/export/template-products', [\App\Http\Controllers\AdminDataController::class, 'exportProductTemplate'])->name('export.template');

    // Import Data Produk dari Excel / CSV (Kasir Pintar)
    Route::post('/products/import', [\App\Http\Controllers\AdminDataController::class, 'importProducts'])->name('products.import');

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
Route::middleware(['auth', 'prevent-back-history'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// --- 5. Rute API AJAX (Sinkronisasi database tanpa reload halaman) ---
Route::prefix('api')->group(function () {
    // 5.1 Rute Publik & Etalase (Bebas diakses pengunjung & etalase belanja)
    Route::get('/categories', [\App\Http\Controllers\AdminDataController::class, 'getCategories']);
    Route::get('/products', [\App\Http\Controllers\AdminDataController::class, 'getProducts']);
    Route::get('/bundles', [\App\Http\Controllers\AdminDataController::class, 'getBundles']);
    Route::get('/store-status', [\App\Http\Controllers\AdminDataController::class, 'getStoreStatus']);

    // 5.2 Rute Pesanan (Dapat diakses pelanggan terdaftar / checkout)
    Route::post('/orders', [\App\Http\Controllers\AdminDataController::class, 'storeOrder']);
    Route::post('/orders/{id}/payment-proof', [\App\Http\Controllers\AdminDataController::class, 'uploadPaymentProof']);
    Route::get('/orders', [\App\Http\Controllers\AdminDataController::class, 'getOrders']);

    // 5.3 Rute Operasional Khusus Administrator (Wajib Login & Peran Admin)
    Route::middleware(['auth', 'role:admin'])->group(function () {
        // Sakelar Status Toko
        Route::post('/store-status/toggle', [\App\Http\Controllers\AdminDataController::class, 'toggleStoreStatus']);

        // Kelola Pesanan & Verifikasi
        Route::patch('/orders/{id}/status', [\App\Http\Controllers\AdminDataController::class, 'updateOrderStatus']);
        Route::post('/orders/{id}/verify-payment', [\App\Http\Controllers\AdminDataController::class, 'verifyPayment']);

        // CRUD Paket Hemat Bundles
        Route::post('/bundles', [\App\Http\Controllers\AdminDataController::class, 'storeBundle']);
        Route::delete('/bundles/{id}', [\App\Http\Controllers\AdminDataController::class, 'destroyBundle']);
        Route::patch('/bundles/{id}/toggle', [\App\Http\Controllers\AdminDataController::class, 'toggleBundleStatus']);

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
        Route::post('/products/import', [\App\Http\Controllers\AdminDataController::class, 'importProducts']);
    });
});

// --- 6. Rute Autentikasi Laravel Breeze (Login, Register, Logout) ---
require __DIR__.'/auth.php';

// --- 7. Rute Streaming & Fallback untuk File Upload di Shared Hosting ---
Route::get('/order-proof/{id}', [\App\Http\Controllers\AdminDataController::class, 'showPaymentProof'])->name('order.proof');

Route::get('/uploads/proofs/{filename}', function ($filename) {
    $paths = [
        dirname(base_path()) . '/public_html/uploads/proofs/' . $filename,
        public_path('uploads/proofs/' . $filename),
        base_path('public/uploads/proofs/' . $filename),
        dirname(base_path()) . '/icafrozenfood/public/uploads/proofs/' . $filename,
        storage_path('app/public/uploads/proofs/' . $filename),
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            $mime = mime_content_type($path) ?: 'image/jpeg';
            return response()->file($path, [
                'Content-Type' => $mime,
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }
    }

    abort(404);
})->where('filename', '.*');



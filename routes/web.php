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
// PUBLIC ROUTES
// ============================================================

// Mode Demo Ujian Blok: Langsung arahkan root URL ke halaman login
Route::get('/', function () {
    return redirect()->route('login');
})->name('home');


// ============================================================
// USER ROUTES (Biasa) — Harus login & role:user
// ============================================================

Route::middleware(['auth', 'verified', 'role:user'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Customer order route
    Route::get('/order', function () {
        return view('welcome', [
            'user' => Auth::user(),
            'isLoggedIn' => true,
            'initialPage' => 'order',
            'products' => \App\Models\Product::all(),
            'categories' => \App\Models\Category::all(),
        ]);
    })->name('order');
});


// ============================================================
// ADMIN ROUTES — Harus login & role:admin
// ============================================================

Route::middleware(['auth', 'role:admin'])->prefix('admin')->as('admin.')->group(function () {
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

    Route::get('/{any}', function () {
        return redirect()->route('admin.dashboard');
    })->where('any', '.*')->name('any');
});

// Alias redirect /admin -> /admin/dashboard
Route::get('/admin', function () {
    return redirect()->route('admin.dashboard');
});


// ============================================================
// PROFILE ROUTES (Breeze)
// ============================================================

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


// ============================================================
// DATA & API ROUTES (Database Persistence for Products, Categories, Orders)
// ============================================================

Route::prefix('api')->group(function () {
    // Public / Read
    Route::get('/categories', [\App\Http\Controllers\AdminDataController::class, 'getCategories']);
    Route::get('/products', [\App\Http\Controllers\AdminDataController::class, 'getProducts']);
    Route::get('/orders', [\App\Http\Controllers\AdminDataController::class, 'getOrders']);

    // Orders (Customer checkout & POS checkout)
    Route::post('/orders', [\App\Http\Controllers\AdminDataController::class, 'storeOrder']);
    Route::patch('/orders/{id}/status', [\App\Http\Controllers\AdminDataController::class, 'updateOrderStatus']);

    // Admin Only: Category CRUD & Bulk Sync
    Route::post('/categories', [\App\Http\Controllers\AdminDataController::class, 'storeCategory']);
    Route::post('/categories/update', [\App\Http\Controllers\AdminDataController::class, 'updateCategory']);
    Route::post('/categories/delete', [\App\Http\Controllers\AdminDataController::class, 'destroyCategory']);
    Route::post('/categories/sync', [\App\Http\Controllers\AdminDataController::class, 'syncCategories']);

    // Admin Only: Product CRUD & Bulk Sync
    Route::post('/products', [\App\Http\Controllers\AdminDataController::class, 'storeProduct']);
    Route::post('/products/{id}/stock', [\App\Http\Controllers\AdminDataController::class, 'updateProductStock']);
    Route::delete('/products/{id}', [\App\Http\Controllers\AdminDataController::class, 'destroyProduct']);
    Route::post('/products/sync', [\App\Http\Controllers\AdminDataController::class, 'syncProducts']);
});


// ============================================================
// AUTH ROUTES (Breeze)
// ============================================================

require __DIR__.'/auth.php';

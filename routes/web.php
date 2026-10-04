<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Grup Route User Biasa
Route::middleware(['auth', 'verified', 'role:user'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

// Grup Route Khusus Admin (menggunakan parameter role:admin)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->as('admin.')->group(function () {
    Route::get('/dashboard', function () {
        // Pastikan Anda membuat file ini di resources/views/admin/dashboard.blade.php
        return view('admin.dashboard'); 
    })->name('dashboard');
});

require __DIR__.'/auth.php';
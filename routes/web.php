<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

// Halaman Welcome (simple landing)
Route::view('/welcome', 'welcome')->name('welcome');

// Redirect root ke welcome
Route::get('/', function () {
    return redirect()->route('welcome');
});

// Route index dashboard dengan auto-redirect berdasarkan role via controller
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('role:admin,guru,kepala_sekolah')
    ->name('dashboard.index');

// Grup dashboard dengan middleware role spesifik
Route::middleware(['role:admin'])->group(function () {
    Route::get('/dashboard/admin', [DashboardController::class, 'admin'])->name('dashboard.admin');
});

Route::middleware(['role:guru'])->group(function () {
    Route::get('/dashboard/guru', [DashboardController::class, 'guru'])->name('dashboard.guru');
});

Route::middleware(['role:kepala_sekolah'])->group(function () {
    Route::get('/dashboard/kepala-sekolah', [DashboardController::class, 'kepalaSekolah'])->name('dashboard.kepala');
});

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard/admin', [DashboardController::class, 'admin'])->name('dashboard.admin');
Route::get('/dashboard/guru', [DashboardController::class, 'guru'])->name('dashboard.guru');
Route::get('/dashboard/kepala-sekolah', [DashboardController::class, 'kepalaSekolah'])->name('dashboard.kepala');

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\PerangkatController;
use App\Http\Controllers\AbsensiHarianController;
use App\Http\Controllers\KelasSayaController;
use App\Http\Controllers\RekapAbsensiController;

// Halaman Welcome (simple landing)
Route::view('/welcome', 'welcome')->name('welcome');

// Redirect root ke welcome
Route::get('/', function () {
    return redirect()->route('welcome');
});

// Auth routes (SB Admin 2)
Route::get('/login', [AuthController::class, 'showLoginForm'])->middleware('guest')->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest')->name('login.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Route index dashboard dengan auto-redirect berdasarkan role via controller (butuh auth)
Route::middleware(['auth', 'role:admin,guru,kepala_sekolah'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // Grup dashboard dengan middleware role spesifik
    Route::middleware(['role:admin,kepala_sekolah'])->group(function () {
        Route::get('/dashboard/admin', [DashboardController::class, 'admin'])->name('dashboard.admin');

        // CRUD Admin & Kepala Sekolah
        Route::resource('kelas', KelasController::class)->names('kelas');
        Route::resource('siswa', SiswaController::class)->names('siswa');
        Route::resource('perangkat', PerangkatController::class)->names('perangkat');
    });

    // Absensi dapat diakses Admin, Guru, dan Kepala Sekolah
    Route::middleware(['role:admin,guru,kepala_sekolah'])->group(function () {
        Route::resource('absensi-harian', AbsensiHarianController::class)->names('absensi-harian');
        // Rekap Absensi (semua role di grup ini)
        Route::get('/rekap-absensi', [RekapAbsensiController::class, 'index'])->name('rekap.index');
    });

    Route::middleware(['role:guru'])->group(function () {
        Route::get('/dashboard/guru', [DashboardController::class, 'guru'])->name('dashboard.guru');
        // Kelas Saya (hanya guru)
        Route::get('/kelas-saya', [KelasSayaController::class, 'index'])->name('kelas-saya.index');
    });

    Route::middleware(['role:kepala_sekolah'])->group(function () {
        Route::get('/dashboard/kepala-sekolah', [DashboardController::class, 'kepalaSekolah'])->name('dashboard.kepala');
    });
});

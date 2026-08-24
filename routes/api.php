<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BarangController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\KategoriController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\TransaksiController;
use App\Http\Controllers\Api\UlasanController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// --- Publik (tanpa login) ---
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:10,1'); // maks 10 percobaan/menit dari 1 IP
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])
    ->middleware('throttle:5,1');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])
    ->middleware('throttle:5,1');

// --- Butuh login (session Sanctum) ---
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Profil akun sendiri -- terbuka untuk admin maupun pengguna.
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);

    // Dashboard admin: statistik & aktivitas seluruh platform.
    Route::middleware('admin')->group(function () {
        Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
        Route::get('/dashboard/transaksi-per-hari', [DashboardController::class, 'transaksiPerHari']);
        Route::get('/dashboard/barang-per-kategori', [DashboardController::class, 'barangPerKategori']);
        Route::get('/dashboard/recent-activity', [DashboardController::class, 'recentActivity']);
    });

    // Dashboard pribadi pengguna: statistik & aktivitas miliknya sendiri saja.
    Route::get('/dashboard/my-stats', [DashboardController::class, 'myStats']);
    Route::get('/dashboard/my-activity', [DashboardController::class, 'myActivity']);

    // Barang: siapa saja yang login boleh melihat katalog, tapi hanya admin yang boleh mengubahnya.
    Route::apiResource('barang', BarangController::class)->only(['index', 'show']);
    Route::middleware('admin')->group(function () {
        Route::apiResource('barang', BarangController::class)->only(['store', 'update', 'destroy']);
    });

    // Kategori: siapa saja yang login boleh melihat (dipakai juga sebagai filter di halaman
    // Jelajah Barang), tapi hanya admin yang boleh mengubahnya.
    Route::apiResource('kategori', KategoriController::class)->only(['index', 'show']);
    Route::middleware('admin')->group(function () {
        Route::apiResource('kategori', KategoriController::class)->only(['store', 'update', 'destroy']);
    });

    // Pengguna: sepenuhnya admin-only (tidak relevan untuk pengguna biasa).
    Route::middleware('admin')->group(function () {
        Route::apiResource('user', UserController::class);
    });

    // Transaksi & Ulasan: index/show otomatis di-scope ke milik sendiri untuk non-admin
    // (lihat TransaksiController/UlasanController), tapi mengubah data tetap admin-only.
    Route::apiResource('transaksi', TransaksiController::class)->only(['index', 'show']);
    Route::apiResource('ulasan', UlasanController::class)->only(['index', 'show']);
    Route::middleware('admin')->group(function () {
        Route::apiResource('transaksi', TransaksiController::class)->only(['store', 'update', 'destroy']);
        Route::apiResource('ulasan', UlasanController::class)->only(['store', 'update', 'destroy']);
    });

    // Pengguna biasa: beli barang sendiri & beri ulasan setelah transaksinya selesai.
    // Sengaja BUKAN admin-only -- aturan bisnisnya sudah dijaga di dalam masing-masing method.
    Route::post('/barang/{barang}/beli', [TransaksiController::class, 'beli']);
    Route::post('/barang/{barang}/ulasan', [UlasanController::class, 'beriUlasan']);
    Route::post('/transaksi/{transaksi}/batalkan', [TransaksiController::class, 'batalkan']);
});

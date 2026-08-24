<?php

use Illuminate\Support\Facades\Route;

// Semua route non-API diarahkan ke satu shell Blade.
// Vue Router yang menangani "halaman" (/dashboard, /barang, dst) di sisi client.
// Ini WAJIB diletakkan PALING BAWAH agar tidak menabrak /api/* (didaftarkan terpisah di bootstrap/app.php).
Route::get('/{any}', function () {
    return view('app');
})->where('any', '^(?!api).*$')->name('spa');

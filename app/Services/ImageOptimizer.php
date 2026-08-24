<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Resize & kompres foto barang sebelum disimpan, pakai ekstensi GD bawaan PHP
 * (tidak butuh package Composer tambahan). Foto kamera/HP yang aslinya beberapa MB
 * biasanya jadi ratusan KB saja setelah lewat sini, tanpa terlihat jelas bedanya.
 */
class ImageOptimizer
{
    // Sisi terpanjang hasil akhir, dalam pixel. Foto tidak pernah diperbesar,
    // hanya diperkecil kalau lebih besar dari ini.
    private const MAX_DIMENSION = 1200;

    // Kualitas JPEG hasil kompresi (0-100). 80 adalah titik seimbang yang umum
    // dipakai -- hasil masih tajam tapi ukuran file jauh lebih kecil dari aslinya.
    private const JPEG_QUALITY = 80;

    /**
     * @return string Path relatif hasil simpan (mis. "barang/xxxxx.jpg"), untuk
     *                 disimpan ke kolom `foto` -- format yang sama seperti
     *                 UploadedFile::store() supaya BarangController tidak perlu berubah.
     */
    public static function optimizeAndStore(UploadedFile $file, string $directory = 'barang'): string
    {
        // Server tanpa ekstensi GD (jarang, tapi bisa terjadi di hosting minimal) ->
        // simpan file asli saja daripada gagal total.
        if (! extension_loaded('gd')) {
            return $file->store($directory, 'public');
        }

        $source = self::readAsGdImage($file);

        if (! $source) {
            return $file->store($directory, 'public');
        }

        $width = imagesx($source);
        $height = imagesy($source);

        // min(..., 1) supaya foto yang sudah kecil tidak ikut diperbesar.
        $ratio = min(self::MAX_DIMENSION / $width, self::MAX_DIMENSION / $height, 1);
        $newWidth = max(1, (int) round($width * $ratio));
        $newHeight = max(1, (int) round($height * $ratio));

        $resized = imagecreatetruecolor($newWidth, $newHeight);

        // Isi latar putih dulu -- PNG dengan transparansi difoto barang jarang perlu
        // transparansi, dan JPEG (hasil akhir) tidak mendukungnya sama sekali.
        $white = imagecolorallocate($resized, 255, 255, 255);
        imagefill($resized, 0, 0, $white);

        imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        $filename = $directory.'/'.((string) Str::uuid()).'.jpg';
        Storage::disk('public')->makeDirectory($directory);

        imagejpeg($resized, Storage::disk('public')->path($filename), self::JPEG_QUALITY);

        imagedestroy($source);
        imagedestroy($resized);

        return $filename;
    }

    private static function readAsGdImage(UploadedFile $file)
    {
        $path = $file->getRealPath();

        return match ($file->getMimeType()) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => false,
        };
    }
}

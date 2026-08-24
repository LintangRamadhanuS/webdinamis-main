<?php

namespace Database\Seeders;

use App\Models\Ulasan;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $reviews = [
            ['user_id' => 1, 'barang_id' => 3, 'rating' => 5, 'komentar' => 'Barang oke dan sesuai deskripsi, pengiriman juga cepat!'],
            ['user_id' => 2, 'barang_id' => 8, 'rating' => 4, 'komentar' => 'Kualitas bagus, cuma agak lama balas chat penjualnya.'],
            ['user_id' => 3, 'barang_id' => 15, 'rating' => 5, 'komentar' => 'Raketnya masih enak dipakai, senar masih kencang seperti dijelaskan.'],
            ['user_id' => 4, 'barang_id' => 1, 'rating' => 4, 'komentar' => 'Laptop lancar dipakai kuliah online, baterai lumayan awet.'],
            ['user_id' => 5, 'barang_id' => 18, 'rating' => 3, 'komentar' => 'Sesuai foto, tapi jahitannya memang perlu diperbaiki lagi.'],
            ['user_id' => 6, 'barang_id' => 9, 'rating' => 5, 'komentar' => 'Kaosnya adem dan bahannya tebal, worth it banget.'],
            ['user_id' => 7, 'barang_id' => 4, 'rating' => 5, 'komentar' => 'Komik lengkap dan rapi, senang banget nemu seller seperti ini.'],
            ['user_id' => 8, 'barang_id' => 11, 'rating' => 3, 'komentar' => 'Rak sedikit goyang tapi masih bisa dipakai untuk buku ringan.'],
            ['user_id' => 9, 'barang_id' => 3, 'rating' => 4, 'komentar' => 'Powerbank masih ngecas dengan cepat, sesuai deskripsi.'],
            ['user_id' => 1, 'barang_id' => 16, 'rating' => 5, 'komentar' => 'Tas awet dan muat banyak barang, recommended seller-nya.'],
            // Akun demo "Dian Mahasiswa" (id=10, role pengguna) -- supaya halaman Ulasan Saya terisi.
            ['user_id' => 10, 'barang_id' => 7, 'rating' => 5, 'komentar' => 'Sepatunya masih bagus, grip mantap buat main futsal.'],
            ['user_id' => 10, 'barang_id' => 12, 'rating' => 4, 'komentar' => 'Lampunya terang dan hemat listrik, sesuai kebutuhan belajar malam.'],
        ];

        foreach ($reviews as $review) {
            Ulasan::create($review);
        }
    }
}

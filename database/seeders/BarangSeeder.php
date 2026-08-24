<?php

namespace Database\Seeders;

use App\Models\Barang;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class BarangSeeder extends Seeder
{
    public function run(): void
    {
        // migrate:fresh (dan seeder ini) mereset tabelnya, tapi TIDAK menyentuh file fisik
        // di storage -- tanpa ini, foto yang sempat diunggah sebelum reset akan jadi sampah
        // tak-bertuan (tidak direferensikan barang manapun lagi). Bersihkan dulu di sini
        // supaya tiap `migrate:fresh --seed` selalu mulai dari folder foto yang benar-benar bersih.
        Storage::disk('public')->deleteDirectory('barang');

        $items = [
            // Elektronik
            ['nama' => 'Laptop ASUS Vivobook A416', 'deskripsi' => 'Laptop bekas pemakaian kuliah, RAM 8GB, SSD 256GB, baterai masih awet seharian.', 'harga' => 3500000, 'kondisi' => 'bekas - layak pakai', 'status' => 'tersedia', 'kategori_id' => 1],
            ['nama' => 'Headset Gaming Rexus F55', 'deskripsi' => 'Suara jernih, bass mantap, jarang dipakai karena sudah ganti headset baru.', 'harga' => 175000, 'kondisi' => 'bekas - seperti baru', 'status' => 'tersedia', 'kategori_id' => 1],
            ['nama' => 'Powerbank Anker 10000mAh', 'deskripsi' => 'Fast charging, masih menyimpan daya dengan baik, lengkap dengan kabel.', 'harga' => 220000, 'kondisi' => 'bekas - layak pakai', 'status' => 'terjual', 'kategori_id' => 1],

            // Buku & Alat Tulis
            ['nama' => 'Komik Naruto Lengkap Vol 1-20', 'deskripsi' => 'Set komik lengkap 20 volume, kondisi rapi tanpa coretan, cocok buat koleksi.', 'harga' => 250000, 'kondisi' => 'bekas - layak pakai', 'status' => 'tersedia', 'kategori_id' => 2],
            ['nama' => 'Buku Kalkulus Dasar Edisi 5', 'deskripsi' => 'Buku wajib mata kuliah kalkulus, ada highlight di beberapa bab tapi masih jelas terbaca.', 'harga' => 85000, 'kondisi' => 'bekas - layak pakai', 'status' => 'tersedia', 'kategori_id' => 2],
            ['nama' => 'Set Drawing Pen Snowman', 'deskripsi' => 'Isi 6 ukuran mata pena, segel belum dibuka, cocok untuk tugas desain teknik.', 'harga' => 45000, 'kondisi' => 'baru', 'status' => 'tersedia', 'kategori_id' => 2],

            // Pakaian
            ['nama' => 'Jaket Hoodie Oversize Uniqlo', 'deskripsi' => 'Warna abu-abu, bahan tebal hangat, ukuran L, hanya dipakai beberapa kali.', 'harga' => 120000, 'kondisi' => 'bekas - seperti baru', 'status' => 'tersedia', 'kategori_id' => 3],
            ['nama' => 'Kemeja Flanel Kotak-Kotak', 'deskripsi' => 'Ukuran M, adem dipakai harian, sudah dicuci bersih dan siap pakai.', 'harga' => 65000, 'kondisi' => 'bekas - layak pakai', 'status' => 'terjual', 'kategori_id' => 3],
            ['nama' => 'Kaos Polos Cotton Combed (isi 3)', 'deskripsi' => 'Bahan combed 30s, belum pernah dipakai, warna hitam putih navy.', 'harga' => 90000, 'kondisi' => 'baru', 'status' => 'tersedia', 'kategori_id' => 3],

            // Perabot Kos
            ['nama' => 'Kursi Belajar Lipat', 'deskripsi' => 'Praktis dilipat saat tidak dipakai, cocok untuk kamar kos yang sempit.', 'harga' => 150000, 'kondisi' => 'bekas - layak pakai', 'status' => 'tersedia', 'kategori_id' => 4],
            ['nama' => 'Rak Buku Susun 4', 'deskripsi' => 'Salah satu sudut rak sedikit longgar, masih kokoh untuk menyimpan buku.', 'harga' => 95000, 'kondisi' => 'bekas - butuh perbaikan', 'status' => 'ditahan', 'kategori_id' => 4],
            ['nama' => 'Lampu Meja Belajar LED', 'deskripsi' => 'Tiga tingkat kecerahan, hemat listrik, cocok menemani begadang mengerjakan tugas.', 'harga' => 60000, 'kondisi' => 'bekas - seperti baru', 'status' => 'tersedia', 'kategori_id' => 4],

            // Olahraga
            ['nama' => 'Sepatu Futsal Specs Ukuran 42', 'deskripsi' => 'Grip masih bagus, sol belum aus, jarang dipakai karena sudah jarang main futsal.', 'harga' => 180000, 'kondisi' => 'bekas - layak pakai', 'status' => 'tersedia', 'kategori_id' => 5],
            ['nama' => 'Matras Yoga Anti Slip', 'deskripsi' => 'Tebal 6mm, permukaan anti licin, masih dalam plastik pembungkus.', 'harga' => 75000, 'kondisi' => 'baru', 'status' => 'tersedia', 'kategori_id' => 5],
            ['nama' => 'Raket Badminton Yonex', 'deskripsi' => 'Senar masih kencang, grip baru diganti, dilengkapi tas raket.', 'harga' => 210000, 'kondisi' => 'bekas - seperti baru', 'status' => 'terjual', 'kategori_id' => 5],

            // Aksesoris & Tas
            ['nama' => 'Tas Ransel Eiger 25L', 'deskripsi' => 'Muat laptop 14 inci plus buku kuliah, resleting semua berfungsi normal.', 'harga' => 165000, 'kondisi' => 'bekas - layak pakai', 'status' => 'tersedia', 'kategori_id' => 6],
            ['nama' => 'Jam Tangan Digital Casio', 'deskripsi' => 'Water resistant, baterai baru diganti, tali masih kuat dan lentur.', 'harga' => 140000, 'kondisi' => 'bekas - seperti baru', 'status' => 'tersedia', 'kategori_id' => 6],
            ['nama' => 'Dompet Kulit Pria', 'deskripsi' => 'Jahitan di salah satu sisi mulai lepas, masih layak dipakai harian.', 'harga' => 55000, 'kondisi' => 'bekas - butuh perbaikan', 'status' => 'ditahan', 'kategori_id' => 6],
        ];

        foreach ($items as $item) {
            Barang::create($item);
        }
    }
}

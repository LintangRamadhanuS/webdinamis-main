<?php

namespace Database\Seeders;

use App\Models\Kategori;
use Illuminate\Database\Seeder;

class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        collect([
            'Elektronik',
            'Buku & Alat Tulis',
            'Pakaian',
            'Perabot Kos',
            'Olahraga',
            'Aksesoris & Tas',
        ])->each(fn ($nama) => Kategori::create(['nama_kategori' => $nama]));
    }
}

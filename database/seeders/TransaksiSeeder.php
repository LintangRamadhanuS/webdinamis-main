<?php

namespace Database\Seeders;

use App\Models\Transaksi;
use Illuminate\Database\Seeder;

class TransaksiSeeder extends Seeder
{
    public function run(): void
    {
        // Status dibobotkan supaya "selesai" lebih dominan, sisanya bervariasi.
        $statuses = ['selesai', 'selesai', 'selesai', 'diproses', 'pending', 'dibatalkan'];

        // [user_id, barang_id] — mengacu ke data UserSeeder (1-9) & BarangSeeder (1-18).
        // 3 entri terakhir sengaja untuk user_id=10 (akun demo "Dian Mahasiswa" / role pengguna)
        // supaya dashboard pribadinya langsung terisi saat dicoba, bukan kosong.
        $pairs = [
            [1, 3], [2, 8], [3, 15], [4, 1], [5, 18], [6, 9],
            [7, 4], [8, 11], [9, 3], [1, 15], [3, 8], [5, 1],
            [2, 18], [6, 4],
            [10, 7], [10, 12], [10, 16],
        ];

        foreach ($pairs as $i => [$userId, $barangId]) {
            Transaksi::create([
                'user_id' => $userId,
                'barang_id' => $barangId,
                'status' => $statuses[$i % count($statuses)],
                'tanggal' => now()->subDays(random_int(0, 29))->subHours(random_int(0, 23)),
            ]);
        }
    }
}

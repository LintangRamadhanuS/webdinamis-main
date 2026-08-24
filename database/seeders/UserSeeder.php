<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Akun tetap supaya selalu ada kredensial yang pasti bisa dipakai untuk login.
        User::create([
            'name' => 'Admin Bekas',
            'nim' => '1029300001',
            'email' => 'admin@bekas.test',
            'email_verified_at' => now(),
            'fakultas' => 'Teknik',
            'role' => UserRole::Admin,
            'password' => Hash::make('password'),
        ]);

        // Beberapa akun acak tambahan (password sama: "password"), otomatis role 'pengguna'
        // lewat default kolom di migration -- agar daftar user terasa hidup.
        User::factory()->count(8)->create();

        // Akun pengguna biasa yang juga tetap/dikenal, supaya pengalaman role "pengguna"
        // bisa langsung dicoba tanpa perlu intip database (id=10, setelah 8 user factory di atas).
        User::create([
            'name' => 'Dian Mahasiswa',
            'nim' => '1029300099',
            'email' => 'mahasiswa@bekas.test',
            'email_verified_at' => now(),
            'fakultas' => 'Ekonomi',
            'role' => UserRole::Pengguna,
            'password' => Hash::make('password'),
        ]);
    }
}

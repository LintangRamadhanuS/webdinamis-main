<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Default 'pengguna' supaya akun yang sudah ada (dan seeder factory) otomatis
            // bukan admin -- akun admin ditetapkan eksplisit lewat UserSeeder.
            $table->string('role')->default('pengguna')->after('fakultas');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Update profil akun sendiri. Sengaja TIDAK menerima nim/role -- nim itu identitas
     * tetap (mirip NIM asli), dan role hanya boleh diubah admin lewat UserController
     * (dan admin pun tidak bisa mengubah role dirinya sendiri, lihat UserController::update).
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'fakultas' => ['nullable', 'string', 'max:255'],
        ]);

        $user->update($data);

        return $user;
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        $request->user()->update(['password' => bcrypt($data['password'])]);

        return response()->json(['message' => 'Password berhasil diubah.']);
    }
}

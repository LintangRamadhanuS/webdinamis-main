<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        return User::query()
            ->when($request->q, fn ($q, $v) => $q->where('name', 'like', "%{$v}%")->orWhere('email', 'like', "%{$v}%"))
            ->select('id', 'name', 'nim', 'email', 'fakultas', 'role', 'created_at')
            ->orderBy('name')
            ->paginate($request->integer('per_page', 10));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nim' => ['required', 'string', 'max:50', 'unique:users,nim'],
            'email' => ['required', 'email', 'unique:users,email'],
            'fakultas' => ['nullable', 'string', 'max:255'],
            'role' => ['required', Rule::enum(UserRole::class)],
            'password' => ['required', Password::min(8)->mixedCase()->numbers()],
        ]);

        $data['password'] = bcrypt($data['password']);

        return response()->json(User::create($data), 201);
    }

    public function show(User $user)
    {
        return $user->only(['id', 'name', 'nim', 'email', 'fakultas', 'role', 'created_at']);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'nim' => ['sometimes', 'required', 'string', 'max:50', 'unique:users,nim,' . $user->id],
            'email' => ['sometimes', 'required', 'email', 'unique:users,email,' . $user->id],
            'fakultas' => ['nullable', 'string', 'max:255'],
            // Tidak boleh mengubah role diri sendiri (cegah admin tidak sengaja mendemosi dirinya
            // sendiri sampai terkunci) -- lihat juga UserFormModal.vue yang menyembunyikan field ini.
            'role' => [Rule::excludeIf($user->id === $request->user()->id), 'sometimes', 'required', Rule::enum(UserRole::class)],
        ]);

        $user->update($data);

        return $user;
    }

    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(['message' => 'User berhasil dihapus.']);
    }
}

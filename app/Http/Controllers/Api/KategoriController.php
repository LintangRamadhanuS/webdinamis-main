<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    public function index()
    {
        return Kategori::withCount('barangs')->orderBy('nama_kategori')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:255', 'unique:kategoris,nama_kategori'],
        ]);

        return response()->json(Kategori::create($data), 201);
    }

    public function show(Kategori $kategori)
    {
        return $kategori->loadCount('barangs');
    }

    public function update(Request $request, Kategori $kategori)
    {
        $data = $request->validate([
            'nama_kategori' => ['sometimes', 'required', 'string', 'max:255', 'unique:kategoris,nama_kategori,' . $kategori->id],
        ]);

        $kategori->update($data);

        return $kategori;
    }

    public function destroy(Kategori $kategori)
    {
        if ($kategori->barangs()->exists()) {
            return response()->json([
                'message' => 'Kategori tidak bisa dihapus karena masih memiliki barang terkait.',
            ], 422);
        }

        $kategori->delete();

        return response()->json(['message' => 'Kategori berhasil dihapus.']);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBarangRequest;
use App\Http\Requests\UpdateBarangRequest;
use App\Http\Resources\BarangResource;
use App\Models\Barang;
use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BarangController extends Controller
{
    /**
     * GET /api/barang
     * Query params: q (search), kategori_id, status, kondisi,
     *               sort (harga_asc|harga_desc|terbaru), per_page
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'kategori_id' => ['nullable', 'integer', 'exists:kategoris,id'],
            'status' => ['nullable', 'string'],
            'kondisi' => ['nullable', 'string'],
            'sort' => ['nullable', 'in:harga_asc,harga_desc,terbaru,terlama'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $barang = Barang::query()
            ->with('kategori')
            ->when($validated['q'] ?? null, function ($query, $q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nama', 'like', "%{$q}%")
                        ->orWhere('deskripsi', 'like', "%{$q}%");
                });
            })
            ->when($validated['kategori_id'] ?? null, fn ($query, $v) => $query->where('kategori_id', $v))
            ->when($validated['status'] ?? null, fn ($query, $v) => $query->where('status', $v))
            ->when($validated['kondisi'] ?? null, fn ($query, $v) => $query->where('kondisi', $v))
            ->when(($validated['sort'] ?? null) === 'harga_asc', fn ($query) => $query->orderBy('harga', 'asc'))
            ->when(($validated['sort'] ?? null) === 'harga_desc', fn ($query) => $query->orderBy('harga', 'desc'))
            ->when(($validated['sort'] ?? null) === 'terlama', fn ($query) => $query->orderBy('created_at', 'asc'))
            ->when(!isset($validated['sort']) || $validated['sort'] === 'terbaru', fn ($query) => $query->orderBy('created_at', 'desc'))
            ->paginate($validated['per_page'] ?? 12)
            ->withQueryString();

        return BarangResource::collection($barang);
    }

    public function store(StoreBarangRequest $request)
    {
        $data = $request->safe()->except('foto');

        if ($request->hasFile('foto')) {
            $data['foto'] = ImageOptimizer::optimizeAndStore($request->file('foto'));
        }

        $barang = Barang::create($data);

        return (new BarangResource($barang->load('kategori')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Barang $barang)
    {
        return new BarangResource($barang->load(['kategori', 'ulasan']));
    }

    public function update(UpdateBarangRequest $request, Barang $barang)
    {
        $data = $request->safe()->except('foto');

        if ($request->hasFile('foto')) {
            if ($barang->foto) {
                Storage::disk('public')->delete($barang->foto);
            }
            $data['foto'] = ImageOptimizer::optimizeAndStore($request->file('foto'));
        }

        $barang->update($data);

        return new BarangResource($barang->fresh('kategori'));
    }

    public function destroy(Barang $barang)
    {
        if ($barang->foto) {
            Storage::disk('public')->delete($barang->foto);
        }
        $barang->delete();

        return response()->json(['message' => 'Barang berhasil dihapus.'], 200);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Transaksi;
use App\Models\Ulasan;
use Illuminate\Http\Request;

class UlasanController extends Controller
{
    public function index(Request $request)
    {
        return Ulasan::with(['user:id,name', 'barang:id,nama'])
            // Ulasan untuk barang tertentu (mis. dilihat calon pembeli sebelum beli) selalu
            // publik untuk siapa saja yang login. Tanpa filter barang_id (daftar "Ulasan Saya"
            // atau moderasi admin), non-admin hanya melihat ulasan miliknya sendiri.
            ->when(!$request->user()->isAdmin() && !$request->barang_id, fn ($q) => $q->where('user_id', $request->user()->id))
            ->when($request->barang_id, fn ($q, $v) => $q->where('barang_id', $v))
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 10));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'barang_id' => ['required', 'exists:barangs,id'],
            'komentar' => ['required', 'string', 'max:1000'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        return response()->json(Ulasan::create($data)->load(['user', 'barang']), 201);
    }

    public function show(Request $request, Ulasan $ulasan)
    {
        if (!$request->user()->isAdmin() && $ulasan->user_id !== $request->user()->id) {
            abort(403, 'Anda tidak dapat melihat ulasan milik pengguna lain.');
        }

        return $ulasan->load(['user', 'barang']);
    }

    public function update(Request $request, Ulasan $ulasan)
    {
        $data = $request->validate([
            'komentar' => ['sometimes', 'required', 'string', 'max:1000'],
            'rating' => ['sometimes', 'required', 'integer', 'min:1', 'max:5'],
        ]);

        $ulasan->update($data);

        return $ulasan->load(['user', 'barang']);
    }

    public function destroy(Ulasan $ulasan)
    {
        $ulasan->delete();

        return response()->json(['message' => 'Ulasan berhasil dihapus.']);
    }

    /**
     * Pengguna biasa memberi ulasan untuk barang yang transaksinya sudah "selesai".
     * Route: POST /api/barang/{barang}/ulasan — lihat routes/api.php.
     */
    public function beriUlasan(Request $request, Barang $barang)
    {
        $sudahSelesai = Transaksi::where('user_id', $request->user()->id)
            ->where('barang_id', $barang->id)
            ->where('status', 'selesai')
            ->exists();

        if (!$sudahSelesai) {
            abort(403, 'Anda hanya bisa memberi ulasan untuk barang yang transaksinya sudah selesai.');
        }

        $sudahUlasan = Ulasan::where('user_id', $request->user()->id)
            ->where('barang_id', $barang->id)
            ->exists();

        if ($sudahUlasan) {
            abort(422, 'Anda sudah memberi ulasan untuk barang ini.');
        }

        $data = $request->validate([
            'komentar' => ['required', 'string', 'max:1000'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        $ulasan = Ulasan::create([
            'user_id' => $request->user()->id,
            'barang_id' => $barang->id,
            ...$data,
        ]);

        return response()->json($ulasan->load(['user', 'barang']), 201);
    }
}

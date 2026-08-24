<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Transaksi;
use Illuminate\Http\Request;

class TransaksiController extends Controller
{
    public function index(Request $request)
    {
        $transaksi = Transaksi::with(['user:id,name,email', 'barang:id,nama,harga'])
            // Non-admin hanya melihat transaksi miliknya sendiri.
            ->when(!$request->user()->isAdmin(), fn ($q) => $q->where('user_id', $request->user()->id))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->orderByDesc('tanggal')
            ->paginate($request->integer('per_page', 10));

        return $transaksi;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'barang_id' => ['required', 'exists:barangs,id'],
            'status' => ['required', 'in:pending,diproses,selesai,dibatalkan'],
            'tanggal' => ['required', 'date'],
        ]);

        $transaksi = Transaksi::create($data);
        $this->syncBarangStatus($transaksi);

        return response()->json($transaksi->load(['user', 'barang']), 201);
    }

    public function show(Request $request, Transaksi $transaksi)
    {
        if (!$request->user()->isAdmin() && $transaksi->user_id !== $request->user()->id) {
            abort(403, 'Anda tidak dapat melihat transaksi milik pengguna lain.');
        }

        return $transaksi->load(['user', 'barang']);
    }

    public function update(Request $request, Transaksi $transaksi)
    {
        $data = $request->validate([
            'status' => ['sometimes', 'required', 'in:pending,diproses,selesai,dibatalkan'],
            'tanggal' => ['sometimes', 'required', 'date'],
        ]);

        $transaksi->update($data);

        if (array_key_exists('status', $data)) {
            $this->syncBarangStatus($transaksi);
        }

        return $transaksi->load(['user', 'barang']);
    }

    public function destroy(Transaksi $transaksi)
    {
        $transaksi->delete();

        return response()->json(['message' => 'Transaksi berhasil dihapus.']);
    }

    /**
     * Pengguna biasa "membeli" barang sendiri (bukan dicatatkan admin).
     * Route: POST /api/barang/{barang}/beli — lihat routes/api.php.
     */
    public function beli(Request $request, Barang $barang)
    {
        if ($barang->status !== 'tersedia') {
            abort(422, 'Barang ini sudah tidak tersedia.');
        }

        $transaksi = Transaksi::create([
            'user_id' => $request->user()->id,
            'barang_id' => $barang->id,
            'status' => 'pending',
            'tanggal' => now(),
        ]);

        $this->syncBarangStatus($transaksi);

        return response()->json($transaksi->load(['user', 'barang']), 201);
    }

    /**
     * Pengguna membatalkan transaksi miliknya sendiri (belum final -- masih pending/diproses).
     * Route: POST /api/transaksi/{transaksi}/batalkan — lihat routes/api.php.
     * Admin sebenarnya sudah punya cara lain (dropdown status di TransaksiIndex.vue), tapi
     * endpoint ini tetap diperiksa kepemilikannya supaya konsisten & aman dipakai admin juga.
     */
    public function batalkan(Request $request, Transaksi $transaksi)
    {
        if (!$request->user()->isAdmin() && $transaksi->user_id !== $request->user()->id) {
            abort(403, 'Anda tidak dapat membatalkan transaksi milik pengguna lain.');
        }

        if (!in_array($transaksi->status, ['pending', 'diproses'], true)) {
            abort(422, 'Transaksi ini sudah tidak bisa dibatalkan.');
        }

        $transaksi->update(['status' => 'dibatalkan']);
        $this->syncBarangStatus($transaksi);

        return $transaksi->load(['user', 'barang']);
    }

    /**
     * Samakan status barang dengan status transaksi terbarunya, supaya barang yang sedang
     * dibeli tidak bisa "dibeli" dua kali oleh pengguna lain sekaligus:
     * pending/diproses -> ditahan, selesai -> terjual, dibatalkan -> tersedia lagi.
     */
    private function syncBarangStatus(Transaksi $transaksi): void
    {
        $barangStatus = match ($transaksi->status) {
            'selesai' => 'terjual',
            'dibatalkan' => 'tersedia',
            default => 'ditahan',
        };

        $transaksi->barang()->update(['status' => $barangStatus]);
    }
}

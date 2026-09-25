<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Transaksi;
use App\Models\Ulasan;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stats()
    {
        return response()->json([
            'totals' => [
                'users' => User::count(),
                'barang' => Barang::count(),
                'kategori' => Kategori::count(),
                'transaksi' => Transaksi::count(),
                'ulasan' => Ulasan::count(),
                'barang_tersedia' => Barang::where('status', 'tersedia')->count(),
                'barang_terjual' => Barang::where('status', 'terjual')->count(),
            ],
        ]);
    }

    /**
     * Statistik pribadi untuk dashboard pengguna (bukan admin) -- hanya menghitung
     * transaksi & ulasan miliknya sendiri.
     */
    public function myStats(Request $request)
    {
        $userId = $request->user()->id;

        return response()->json([
            'totals' => [
                'transaksi' => Transaksi::where('user_id', $userId)->count(),
                'transaksi_selesai' => Transaksi::where('user_id', $userId)->where('status', 'selesai')->count(),
                'transaksi_berjalan' => Transaksi::where('user_id', $userId)->whereIn('status', ['pending', 'diproses'])->count(),
                'ulasan' => Ulasan::where('user_id', $userId)->count(),
            ],
        ]);
    }

    /**
     * Versi pribadi dari recentActivity() -- hanya transaksi & ulasan milik user yang login.
     */
    public function myActivity(Request $request)
    {
        $userId = $request->user()->id;

        $transaksi = Transaksi::with('barang:id,nama')
            ->where('user_id', $userId)
            ->latest('tanggal')
            ->take(6)
            ->get()
            ->map(fn ($t) => [
                'type' => 'transaksi',
                'id' => $t->id,
                'title' => $t->barang?->nama ?? 'Barang telah dihapus',
                'meta' => $t->status,
                'at' => $t->tanggal,
                'sort_at' => strtotime($t->tanggal),
            ]);

        $ulasan = Ulasan::with('barang:id,nama')
            ->where('user_id', $userId)
            ->latest()
            ->take(6)
            ->get()
            ->map(fn ($u) => [
                'type' => 'ulasan',
                'id' => $u->id,
                'title' => $u->barang?->nama ?? 'Barang telah dihapus',
                'meta' => $u->rating,
                'at' => $u->created_at,
                'sort_at' => $u->created_at->timestamp,
            ]);

        $activity = $transaksi->concat($ulasan)
            ->sortByDesc('sort_at')
            ->take(6)
            ->values()
            ->map(fn ($item) => collect($item)->except('sort_at'));

        return response()->json(['data' => $activity]);
    }

    /**
     * 6 transaksi + ulasan terbaru untuk widget "Aktivitas Terbaru" di dashboard.
     */
    public function recentActivity()
    {
        $transaksi = Transaksi::with(['user:id,name', 'barang:id,nama'])
            ->latest('tanggal')
            ->take(6)
            ->get()
            ->map(fn ($t) => [
                'type' => 'transaksi',
                'id' => $t->id,
                'title' => $t->barang?->nama ?? 'Barang telah dihapus',
                'subtitle' => $t->user?->name ?? 'Pengguna telah dihapus',
                'meta' => $t->status,
                'at' => $t->tanggal,
                // 'tanggal' bukan Carbon (tidak di-cast di model), sedangkan created_at ulasan
                // di bawah otomatis Carbon — samakan dulu ke timestamp integer sebelum dibandingkan,
                // karena membandingkan string mentah dengan objek Carbon lewat sortByDesc tidak akurat.
                'sort_at' => strtotime($t->tanggal),
            ]);

        $ulasan = Ulasan::with(['user:id,name', 'barang:id,nama'])
            ->latest()
            ->take(6)
            ->get()
            ->map(fn ($u) => [
                'type' => 'ulasan',
                'id' => $u->id,
                'title' => $u->barang?->nama ?? 'Barang telah dihapus',
                'subtitle' => $u->user?->name ?? 'Pengguna telah dihapus',
                'meta' => $u->rating,
                'at' => $u->created_at,
                'sort_at' => $u->created_at->timestamp,
            ]);

        $activity = $transaksi->concat($ulasan)
            ->sortByDesc('sort_at')
            ->take(6)
            ->values()
            ->map(fn ($item) => collect($item)->except('sort_at'));

        return response()->json(['data' => $activity]);
    }

    /**
     * Transaksi 30 hari terakhir, dikelompokkan per tanggal — untuk line chart.
     */
    public function transaksiPerHari()
    {
        // Ambil lewat Eloquent (bukan selectRaw) supaya 'tanggal' otomatis jadi Carbon
        // (ikut cast di model), lalu grouping per tanggal LOKAL dilakukan di PHP —
        // supaya transaksi dini hari WIB tidak nyasar ke hari sebelumnya seperti
        // kalau dikelompokkan langsung dari nilai UTC di database.
        $grouped = Transaksi::query()
            ->where('tanggal', '>=', now()->subDays(30))
            ->get(['tanggal'])
            ->groupBy(fn ($t) => $t->tanggal->timezone('Asia/Jakarta')->toDateString())
            ->map->count()
            ->sortKeys();

        return response()->json([
            'labels' => $grouped->keys()->values(),
            'values' => $grouped->values(),
        ]);
    }

    /**
     * Jumlah barang per kategori — untuk bar/doughnut chart.
     */
    public function barangPerKategori()
    {
        $data = Barang::query()
            ->join('kategoris', 'barangs.kategori_id', '=', 'kategoris.id')
            ->selectRaw('kategoris.nama_kategori as label, COUNT(*) as total')
            ->groupBy('kategoris.nama_kategori')
            ->orderByDesc('total')
            ->get();

        return response()->json([
            'labels' => $data->pluck('label'),
            'values' => $data->pluck('total'),
        ]);
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BarangResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'deskripsi' => $this->deskripsi,
            'harga' => (float) $this->harga,
            'harga_format' => 'Rp ' . number_format($this->harga, 0, ',', '.'),
            'kondisi' => $this->kondisi,
            'foto' => $this->foto,
            'foto_url' => $this->foto ? asset('storage/' . $this->foto) : null,
            'status' => $this->status,
            'kategori_id' => $this->kategori_id,
            'kategori' => $this->whenLoaded('kategori', fn () => [
                'id' => $this->kategori->id,
                'nama_kategori' => $this->kategori->nama_kategori,
            ]),
            'rata_rata_rating' => $this->when(
                $this->relationLoaded('ulasan'),
                fn () => round($this->ulasan->avg('rating') ?? 0, 1)
            ),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBarangRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Ganti dengan Gate/Policy sesuai kebutuhan role Anda, misal:
        // return $this->user()->can('create', Barang::class);
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'deskripsi' => ['required', 'string', 'max:2000'],
            'harga' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'kondisi' => ['required', 'string', 'in:baru,bekas - seperti baru,bekas - layak pakai,bekas - butuh perbaikan'],
            'status' => ['required', 'string', 'in:tersedia,terjual,ditahan'],
            'kategori_id' => ['required', 'integer', 'exists:kategoris,id'],
            // 8MB -- cukup longgar untuk foto HP asli, karena ukuran akhir yang tersimpan
            // sudah dikompres jauh lebih kecil oleh ImageOptimizer (lihat BarangController).
            // Dibatasi ke jpeg/png/webp saja (bukan gif/bmp/svg bawaan rule `image`) --
            // sesuai untuk foto barang, dan menghindari risiko SVG (bisa memuat script).
            'foto' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama barang wajib diisi.',
            'harga.numeric' => 'Harga harus berupa angka.',
            'harga.min' => 'Harga tidak boleh kurang dari 0.',
            'kategori_id.exists' => 'Kategori yang dipilih tidak valid.',
            'foto.image' => 'File yang diunggah harus berupa gambar.',
            'foto.mimes' => 'Format foto harus JPG, PNG, atau WEBP.',
            'foto.max' => 'Ukuran foto maksimal 8MB.',
        ];
    }
}

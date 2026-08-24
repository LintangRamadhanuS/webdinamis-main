<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBarangRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'nama' => ['sometimes', 'required', 'string', 'max:255'],
            'deskripsi' => ['sometimes', 'required', 'string', 'max:2000'],
            'harga' => ['sometimes', 'required', 'numeric', 'min:0'],
            'kondisi' => ['sometimes', 'required', 'string', 'in:baru,bekas - seperti baru,bekas - layak pakai,bekas - butuh perbaikan'],
            'status' => ['sometimes', 'required', 'string', 'in:tersedia,terjual,ditahan'],
            'kategori_id' => ['sometimes', 'required', 'integer', 'exists:kategoris,id'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
        ];
    }

    public function messages(): array
    {
        return [
            'foto.image' => 'File yang diunggah harus berupa gambar.',
            'foto.mimes' => 'Format foto harus JPG, PNG, atau WEBP.',
            'foto.max' => 'Ukuran foto maksimal 8MB.',
        ];
    }
}

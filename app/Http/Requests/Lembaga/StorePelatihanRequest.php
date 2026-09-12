<?php

namespace App\Http\Requests\Lembaga;

use Illuminate\Foundation\Http\FormRequest;

class StorePelatihanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('lembaga');
    }

    public function rules(): array
    {
        return [
            'lembaga_pelatihan_id' => ['required', 'uuid', 'exists:lembaga_pelatihan,id'],
            'nama' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'jumlah_jam' => ['nullable', 'integer', 'min:1'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'kuota' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'in:dibuka,ditutup'],
        ];
    }
}

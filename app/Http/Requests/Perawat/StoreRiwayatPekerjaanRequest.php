<?php

namespace App\Http\Requests\Perawat;

use Illuminate\Foundation\Http\FormRequest;

class StoreRiwayatPekerjaanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('perawat');
    }

    public function rules(): array
    {
        return [
            'mitra_pengguna_id' => ['required', 'uuid', 'exists:mitra_pengguna,id'],
            'posisi' => ['required', 'string', 'max:100'],
            'unit' => ['nullable', 'string', 'max:100'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required_if:status_pekerjaan,selesai,resign', 'nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'status_pekerjaan' => ['required', 'string', 'in:aktif,selesai,resign'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ];
    }
}

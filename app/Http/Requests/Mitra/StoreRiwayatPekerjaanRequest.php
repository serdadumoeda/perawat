<?php

namespace App\Http\Requests\Mitra;

use Illuminate\Foundation\Http\FormRequest;

class StoreRiwayatPekerjaanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole(['mitra', 'faskes', 'headhunter']);
    }

    public function rules(): array
    {
        return [
            'perawat_nik' => ['nullable', 'string', 'digits:16', 'required_without_all:perawat_id,nik', 'exists:perawat,nik'],
            'nik' => ['nullable', 'string', 'digits:16', 'required_without_all:perawat_id,perawat_nik', 'exists:perawat,nik'],
            'perawat_id' => ['nullable', 'uuid', 'required_without_all:perawat_nik,nik', 'exists:perawat,id'],
            'mitra_pengguna_id' => ['required', 'uuid', 'exists:mitra_pengguna,id'],
            'posisi' => ['nullable', 'string', 'max:100'],
            'unit' => ['nullable', 'string', 'max:100'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required_if:status_pekerjaan,selesai,resign', 'nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'status_pekerjaan' => ['nullable', 'string', 'in:aktif,selesai,resign'],
            'penilaian_kinerja' => ['nullable', 'string', 'max:100'],
            'kinerja_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'kinerja_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'is_verified' => ['prohibited'],
        ];
    }
}

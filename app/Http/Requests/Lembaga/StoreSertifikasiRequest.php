<?php

namespace App\Http\Requests\Lembaga;

use Illuminate\Foundation\Http\FormRequest;

class StoreSertifikasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('lembaga');
    }

    public function rules(): array
    {
        return [
            'perawat_nik' => ['nullable', 'string', 'digits:16', 'required_without_all:perawat_id,nik', 'exists:perawat,nik'],
            'nik' => ['nullable', 'string', 'digits:16', 'required_without_all:perawat_id,perawat_nik', 'exists:perawat,nik'],
            'perawat_id' => ['nullable', 'uuid', 'required_without_all:perawat_nik,nik', 'exists:perawat,id'],
            'lembaga_pelatihan_id' => ['required', 'uuid', 'exists:lembaga_pelatihan,id'],
            'nama_sertifikat' => ['required', 'string', 'max:255'],
            'jumlah_jam' => ['nullable', 'integer', 'min:1'],
            'nilai_kompetensi' => ['nullable', 'numeric', 'between:0,100'],
            'nomor_sertifikat' => ['nullable', 'string', 'max:100'],
            'tanggal_terbit' => ['nullable', 'date'],
            'tanggal_kadaluarsa' => ['nullable', 'date', 'after_or_equal:tanggal_terbit'],
            'status_verifikasi' => ['nullable', 'in:pending,verified,rejected,valid'],
            'file_sertifikat' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }
}

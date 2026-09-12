<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreSertifikasiApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data' => ['required', 'array', 'min:1', 'max:500'],
            'data.*.nik' => ['required', 'digits:16'],
            'data.*.nama_sertifikat' => ['required', 'string', 'max:255'],
            'data.*.nomor_sertifikat' => ['nullable', 'string', 'max:100'],
            'data.*.tanggal_terbit' => ['nullable', 'date'],
            'data.*.tanggal_kadaluarsa' => ['nullable', 'date'],
            'data.*.status_verifikasi' => ['nullable', 'in:pending,verified,rejected,valid'],
        ];
    }
}

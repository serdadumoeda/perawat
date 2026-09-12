<?php

namespace App\Http\Requests\Mitra;

use Illuminate\Foundation\Http\FormRequest;

class LockKandidatRequest extends FormRequest
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
            'lowongan_id' => ['nullable', 'uuid', 'exists:lowongan,id'],
        ];
    }
}

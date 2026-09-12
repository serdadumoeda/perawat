<?php

namespace App\Http\Requests\Mitra;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VerifyRiwayatPekerjaanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole(['mitra', 'faskes', 'headhunter']);
    }

    public function rules(): array
    {
        return [
            'penilaian_kinerja' => ['nullable', 'string', 'max:100'],
            'keterangan' => [
                'nullable',
                'string',
                'max:1000',
                Rule::requiredIf(fn () => $this->routeIs('mitra.riwayat_pekerjaan.reject')),
            ],
        ];
    }
}

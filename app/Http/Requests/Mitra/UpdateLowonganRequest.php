<?php

namespace App\Http\Requests\Mitra;

use App\Models\Lowongan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLowonganRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole(['mitra', 'faskes', 'headhunter']);
    }

    public function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'tipe_penempatan' => ['required', 'string', Rule::in(Lowongan::PENEMPATAN_OPTIONS)],
            'negara_penempatan' => ['nullable', 'string', 'max:255', 'required_if:tipe_penempatan,'.Lowongan::PENEMPATAN_LUAR_NEGERI],
            'provinsi_penempatan' => ['nullable', 'string', 'max:255'],
            'kota_penempatan' => ['nullable', 'string', 'max:255'],
            'kriteria' => ['nullable', 'array'],
            'kriteria.status_str' => ['nullable', 'in:aktif,hold,expired'],
            'kriteria.sertifikat' => ['nullable', 'string', 'max:255'],
        ];
    }
}

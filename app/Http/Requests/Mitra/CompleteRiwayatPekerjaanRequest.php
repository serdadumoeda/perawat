<?php

namespace App\Http\Requests\Mitra;

use Illuminate\Foundation\Http\FormRequest;

class CompleteRiwayatPekerjaanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole(['mitra', 'faskes', 'headhunter']);
    }

    public function rules(): array
    {
        return [
            'status_pekerjaan' => ['required', 'in:selesai,resign'],
            'tanggal_selesai' => ['required', 'date'],
            'penilaian_kinerja' => ['nullable', 'string', 'max:255'],
            'kinerja_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'kinerja_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'keterangan' => ['nullable', 'string', 'max:500'],
        ];
    }
}

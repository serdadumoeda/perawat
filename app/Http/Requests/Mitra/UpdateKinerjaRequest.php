<?php

namespace App\Http\Requests\Mitra;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKinerjaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole(['mitra', 'faskes', 'headhunter']);
    }

    public function rules(): array
    {
        return [
            'penilaian_kinerja' => ['nullable', 'string', 'max:255'],
            'kinerja_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'kinerja_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'keterangan' => ['nullable', 'string', 'max:500'],
        ];
    }
}

<?php

namespace App\Http\Requests\Mitra;

use Illuminate\Foundation\Http\FormRequest;

class LamaranActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole(['mitra', 'faskes', 'headhunter']);
    }

    public function rules(): array
    {
        return [
            'lamaran_id' => ['required', 'uuid', 'exists:lamaran,id'],
            'mitra_pengguna_id' => ['required', 'uuid', 'exists:mitra_pengguna,id'],
        ];
    }
}

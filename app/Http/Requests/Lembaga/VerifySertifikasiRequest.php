<?php

namespace App\Http\Requests\Lembaga;

use Illuminate\Foundation\Http\FormRequest;

class VerifySertifikasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('lembaga');
    }

    public function rules(): array
    {
        return [
            'catatan' => ['nullable', 'string', 'max:500'],
        ];
    }
}

<?php

namespace App\Http\Requests\Perawat;

use Illuminate\Foundation\Http\FormRequest;

class ApplyLowonganRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('perawat');
    }

    public function rules(): array
    {
        return [
            'lowongan_id' => ['required', 'uuid', 'exists:lowongan,id'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ];
    }
}

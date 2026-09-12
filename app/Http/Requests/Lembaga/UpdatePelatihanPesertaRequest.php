<?php

namespace App\Http\Requests\Lembaga;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePelatihanPesertaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('lembaga');
    }

    public function rules(): array
    {
        return [
            'nilai_kompetensi' => ['nullable', 'numeric', 'between:0,100'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ];
    }
}

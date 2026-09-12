<?php

namespace App\Http\Requests\Perawat;

use Illuminate\Foundation\Http\FormRequest;

class DaftarPelatihanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('perawat');
    }

    public function rules(): array
    {
        return [
            'pelatihan_id' => ['required', 'uuid', 'exists:pelatihan,id'],
        ];
    }
}

<?php

namespace App\Http\Requests\Perawat;

use Illuminate\Foundation\Http\FormRequest;

class StoreStrSyncRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('perawat');
    }

    public function rules(): array
    {
        return [
            'nomor_str' => ['required', 'string', 'max:100'],
            'nomor_izin_luar_negeri' => ['nullable', 'string', 'max:100'],
            'str_document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'izin_luar_negeri_document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'catatan' => ['nullable', 'string'],
        ];
    }
}

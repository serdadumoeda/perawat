<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRegulatorOrganisasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'kode' => ['nullable', 'string', 'max:100', Rule::unique('regulator_organisasi', 'kode')],
            'nama' => ['required', 'string', 'max:255'],
            'tingkat' => ['nullable', 'string', 'max:100'],
            'wilayah' => ['nullable', 'string', 'max:150'],
            'alamat' => ['nullable', 'string'],
            'email' => ['nullable', 'email', 'max:255'],
            'telepon' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}

<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreApiKeyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'owner_type' => ['required', 'string', 'in:institusi,lembaga,regulator,regulator_org'],
            'owner_id' => ['required', 'uuid'],
            'scopes' => ['required', 'array', 'min:1'],
            'scopes.*' => ['string', 'in:lulusan:write,sertifikasi:write,legalitas:write'],
            'require_signature' => ['nullable', 'boolean'],
        ];
    }
}

<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrganisasiUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'uuid', 'exists:users,id'],
            'organisasi_type' => ['required', Rule::in(['institusi', 'lembaga', 'mitra', 'regulator'])],
            'organisasi_id' => ['required', 'uuid'],
        ];
    }
}

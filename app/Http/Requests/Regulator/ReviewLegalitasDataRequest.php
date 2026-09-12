<?php

namespace App\Http\Requests\Regulator;

use Illuminate\Foundation\Http\FormRequest;

class ReviewLegalitasDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('regulator');
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'string', 'in:approve,reject'],
            'note' => ['nullable', 'string', 'max:255', 'required_if:action,reject'],
        ];
    }
}

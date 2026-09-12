<?php

namespace App\Http\Requests\Regulator;

use Illuminate\Foundation\Http\FormRequest;

class ReopenLegalitasDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('regulator');
    }

    public function rules(): array
    {
        return [
            'note' => ['required', 'string', 'max:255'],
        ];
    }
}


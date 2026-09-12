<?php

namespace App\Http\Requests\Regulator;

use Illuminate\Foundation\Http\FormRequest;

class ReopenLegalitasDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('regulator');
    }

    public function rules(): array
    {
        return [
            'document_type' => ['required', 'string', 'in:str,izin_luar_negeri'],
            'note' => ['required', 'string', 'max:255'],
        ];
    }
}


<?php

namespace App\Http\Requests\Regulator;

use Illuminate\Foundation\Http\FormRequest;

class ReviewLegalitasDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('regulator');
    }

    public function rules(): array
    {
        return [
            'document_type' => ['required', 'string', 'in:str,izin_luar_negeri'],
            'action' => ['required', 'string', 'in:approve,reject'],
            'note' => ['nullable', 'string', 'max:255', 'required_if:action,reject'],
        ];
    }
}

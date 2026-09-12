<?php

namespace App\Http\Requests\Kampus;

use Illuminate\Foundation\Http\FormRequest;

class ImportLulusanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('kampus');
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'in:preview,import'],
            'institusi_pendidikan_id' => ['required', 'uuid', 'exists:institusi_pendidikan,id'],
            'file' => [$this->isPreviewAction() ? 'required' : 'nullable', 'file', 'mimes:csv,txt', 'max:10240'],
            'temp_file' => [$this->isImportAction() ? 'required' : 'nullable', 'string', 'max:255'],
            'original_filename' => ['nullable', 'string', 'max:255'],
            'mapping' => [$this->isImportAction() ? 'required' : 'nullable', 'array'],
            'mapping.*' => ['nullable', 'string', 'max:100'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('action')) {
            $this->merge([
                'action' => $this->hasFile('file') ? 'preview' : 'import',
            ]);
        }
    }

    private function isPreviewAction(): bool
    {
        return $this->input('action') === 'preview';
    }

    private function isImportAction(): bool
    {
        return $this->input('action') === 'import';
    }
}

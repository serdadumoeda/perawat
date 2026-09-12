<?php

namespace App\Http\Requests\Kampus;

use App\Models\TracingAlumni;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertTracingAlumniRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('kampus');
    }

    public function rules(): array
    {
        return [
            'institusi_pendidikan_id' => ['required', 'uuid', 'exists:institusi_pendidikan,id'],
            'status' => ['required', 'string', Rule::in(TracingAlumni::STATUSES)],
            'instansi' => ['nullable', 'string', 'max:255'],
            'posisi' => ['nullable', 'string', 'max:255'],
            'kota' => ['nullable', 'string', 'max:255'],
            'provinsi' => ['nullable', 'string', 'max:255'],
            'tanggal_mulai' => ['nullable', 'date'],
            'catatan' => ['nullable', 'string'],
        ];
    }
}

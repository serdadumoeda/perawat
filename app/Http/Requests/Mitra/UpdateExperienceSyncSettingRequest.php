<?php

namespace App\Http\Requests\Mitra;

use App\Models\ExperienceSyncSetting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateExperienceSyncSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole(['mitra', 'faskes', 'headhunter']);
    }

    public function rules(): array
    {
        return [
            'mitra_pengguna_id' => ['required', 'uuid', 'exists:mitra_pengguna,id'],
            'base_url' => ['nullable', 'url'],
            'api_key' => ['nullable', 'string', 'max:255'],
            'client_id' => ['nullable', 'string', 'max:255'],
            'client_secret' => ['nullable', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'sync_mode' => ['required', 'string', Rule::in(ExperienceSyncSetting::MODES)],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ];
    }
}

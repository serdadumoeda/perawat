<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreLegalitasApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data' => ['required', 'array', 'min:1', 'max:500'],
            'data.*.nik' => ['required', 'digits:16'],
            'data.*.nomor_str' => ['nullable', 'string', 'max:100'],
            'data.*.status_str' => ['nullable', 'in:aktif,hold,expired'],
            'data.*.str_berlaku_mulai' => ['nullable', 'date'],
            'data.*.str_berlaku_sampai' => ['nullable', 'date'],
            'data.*.nomor_izin_luar_negeri' => ['nullable', 'string', 'max:100'],
            'data.*.status_izin_luar_negeri' => ['nullable', 'string', 'max:50'],
            'data.*.sumber_validasi' => ['nullable', 'string', 'max:100'],
        ];
    }
}

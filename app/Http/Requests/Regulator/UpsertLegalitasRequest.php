<?php

namespace App\Http\Requests\Regulator;

use Illuminate\Foundation\Http\FormRequest;

class UpsertLegalitasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('regulator');
    }

    public function rules(): array
    {
        return [
            'perawat_nik' => ['nullable', 'string', 'digits:16', 'required_without_all:perawat_id,nik', 'exists:perawat,nik'],
            'nik' => ['nullable', 'string', 'digits:16', 'required_without_all:perawat_id,perawat_nik', 'exists:perawat,nik'],
            'perawat_id' => ['nullable', 'uuid', 'required_without_all:perawat_nik,nik', 'exists:perawat,id'],
            'nomor_str' => ['nullable', 'string', 'max:100'],
            'nomor_sipp' => ['nullable', 'string', 'max:100'],
            'status_str' => ['nullable', 'string', 'in:aktif,hold,expired'],
            'str_berlaku_mulai' => ['nullable', 'date'],
            'str_berlaku_sampai' => ['nullable', 'date', 'after_or_equal:str_berlaku_mulai'],
            'nomor_izin_luar_negeri' => ['nullable', 'string', 'max:100'],
            'status_izin_luar_negeri' => ['nullable', 'string', 'max:50'],
            'sumber_validasi' => ['nullable', 'string', 'max:100'],
        ];
    }
}

<?php

namespace App\Http\Requests\Regulator;

use App\Models\DataLegalitas;
use Illuminate\Foundation\Http\FormRequest;

class SyncStrRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('regulator');
    }

    public function rules(): array
    {
        return [
            'status_str' => ['required', 'string', 'in:'.implode(',', [
                DataLegalitas::STATUS_STR_AKTIF,
                DataLegalitas::STATUS_STR_HOLD,
                DataLegalitas::STATUS_STR_EXPIRED,
            ])],
            'str_berlaku_mulai' => ['required', 'date'],
            'str_berlaku_sampai' => ['required', 'date', 'after_or_equal:str_berlaku_mulai'],
            'nomor_sipp' => ['nullable', 'string', 'max:100'],
            'nomor_izin_luar_negeri' => ['nullable', 'string', 'max:100'],
            'status_izin_luar_negeri' => ['nullable', 'string', 'max:50'],
            'response_note' => ['nullable', 'string'],
        ];
    }
}

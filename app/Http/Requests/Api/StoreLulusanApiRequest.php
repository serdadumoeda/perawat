<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreLulusanApiRequest extends FormRequest
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
            'data.*.nama' => ['required', 'string', 'max:255'],
            'data.*.email' => ['nullable', 'email', 'max:255'],
            'data.*.no_hp' => ['nullable', 'string', 'max:30'],
            'data.*.jenis_kelamin' => ['nullable', 'in:L,P'],
            'data.*.tempat_lahir' => ['nullable', 'string', 'max:255'],
            'data.*.tanggal_lahir' => ['nullable', 'date'],
            'data.*.alamat' => ['nullable', 'string'],
            'data.*.jenjang' => ['nullable', 'string', 'max:50'],
            'data.*.program_studi' => ['nullable', 'string', 'max:255'],
            'data.*.nomor_ijazah' => ['nullable', 'string', 'max:100'],
            'data.*.tanggal_ijazah' => ['nullable', 'date'],
            'data.*.tahun_masuk' => ['nullable', 'digits:4'],
            'data.*.tahun_lulus' => ['nullable', 'digits:4'],
            'data.*.ipk' => ['nullable', 'numeric', 'between:0,4.00'],
        ];
    }
}

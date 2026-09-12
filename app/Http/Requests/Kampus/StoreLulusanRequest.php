<?php

namespace App\Http\Requests\Kampus;

use Illuminate\Foundation\Http\FormRequest;

class StoreLulusanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('kampus');
    }

    public function rules(): array
    {
        return [
            'nik' => ['nullable', 'string', 'max:20'],
            'nim' => ['nullable', 'string', 'max:50'],
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:30'],
            'jenis_kelamin' => ['nullable', 'in:L,P'],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'alamat' => ['nullable', 'string'],

            'institusi_pendidikan_id' => ['required', 'uuid', 'exists:institusi_pendidikan,id'],
            'jenjang' => ['nullable', 'string', 'max:50'],
            'program_studi' => ['nullable', 'string', 'max:255'],
            'nomor_ijazah' => ['nullable', 'string', 'max:100'],
            'tanggal_ijazah' => ['nullable', 'date'],
            'tahun_masuk' => ['nullable', 'integer', 'digits:4'],
            'tahun_lulus' => ['nullable', 'integer', 'digits:4'],
            'ipk' => ['nullable', 'numeric', 'between:0,4.00'],
            'transkrip' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'ijazah' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }
}

<?php

namespace App\Http\Requests\Survey;

use Illuminate\Foundation\Http\FormRequest;

class RespondenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama'          => ['nullable', 'string', 'max:255'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'usia'          => ['required', 'integer', 'min:1', 'max:120'],
            'pendidikan'    => ['required', 'string', 'max:255'],
            'pekerjaan'     => ['required', 'string', 'max:255'],
            'no_hp'         => ['nullable', 'string', 'regex:/^08[0-9]{7,13}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'jenis_kelamin.required' => 'Pilih jenis kelamin.',
            'usia.required'          => 'Usia wajib diisi.',
            'usia.min'               => 'Usia minimal 1 tahun.',
            'usia.max'               => 'Usia maksimal 120 tahun.',
            'pendidikan.required'    => 'Pilih pendidikan terakhir.',
            'pekerjaan.required'     => 'Pilih pekerjaan.',
            'no_hp.regex'            => 'Format nomor HP tidak valid (contoh: 08123456789).',
        ];
    }
}
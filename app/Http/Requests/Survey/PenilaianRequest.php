<?php

namespace App\Http\Requests\Survey;

use App\Models\UnsurPelayanan;
use Illuminate\Foundation\Http\FormRequest;

class PenilaianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = ['jawaban' => ['required', 'array']];

        foreach (UnsurPelayanan::where('status', 'active')->pluck('id') as $id) {
            $rules['jawaban.' . $id] = ['required', 'integer', 'between:1,5'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'jawaban.required'   => 'Seluruh pertanyaan wajib dinilai.',
            'jawaban.*.required' => 'Pertanyaan ini belum dinilai.',
            'jawaban.*.between'  => 'Nilai harus antara 1 sampai 5.',
        ];
    }
}
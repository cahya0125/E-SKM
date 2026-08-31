<?php

namespace App\Http\Requests\Survey;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StartSurveyRequest extends FormRequest
{
    // Konstanta internal — dipakai oleh controller juga
    public const JENIS_LAYANAN = [
        'Pelayanan Kesekretariatan',
        'Pelayanan Pencegahan dan Kesiapsiagaan',
        'Pelayanan Kedaruratan dan Logistik',
        'Pelayanan Rehabilitasi dan Rekonstruksi',
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jenis_layanan' => ['required', 'string', Rule::in(self::JENIS_LAYANAN)],
        ];
    }

    public function messages(): array
    {
        return [
            'jenis_layanan.required' => 'Silakan pilih jenis layanan terlebih dahulu.',
            'jenis_layanan.in'       => 'Jenis layanan yang dipilih tidak valid.',
        ];
    }
}
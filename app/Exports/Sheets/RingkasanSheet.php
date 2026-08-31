<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class RingkasanSheet implements FromArray, WithTitle
{
    public function __construct(private array $hasil)
    {
    }

    public function array(): array
    {
        return [
            ['Nilai IKM', $this->hasil['nilai_ikm']],
            ['Nilai SKM', $this->hasil['nilai_skm']],
            ['Mutu Pelayanan', $this->hasil['mutu_pelayanan']],
            ['Kinerja Pelayanan', $this->hasil['kinerja_pelayanan']],
            ['Jumlah Responden', $this->hasil['jumlah_responden']],
        ];
    }

    public function title(): string
    {
        return 'Ringkasan';
    }
}
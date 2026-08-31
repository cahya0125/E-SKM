<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class DetailUnsurSheet implements FromArray, WithHeadings, WithTitle
{
    public function __construct(private array $hasil)
    {
    }

    public function array(): array
    {
        return collect($this->hasil['details'])->map(fn ($d) => [
            $d['kode'],
            $d['nama_unsur'],
            $d['jumlah_responden'],
            $d['nilai_rata_rata'],
            $d['bobot_nilai'],
            $d['nrr_tertimbang'],
            $d['mutu_unsur'],
        ])->toArray();
    }

    public function headings(): array
    {
        return ['Kode', 'Unsur Pelayanan', 'Responden', 'Nilai Rata-rata', 'Bobot', 'Nilai Tertimbang', 'Mutu'];
    }

    public function title(): string
    {
        return 'Detail Unsur';
    }
}
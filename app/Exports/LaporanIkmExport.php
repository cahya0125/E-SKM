<?php

namespace App\Exports;

use App\Exports\Sheets\DetailUnsurSheet;
use App\Exports\Sheets\PengolahanSheet;
use App\Exports\Sheets\RingkasanSheet;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LaporanIkmExport implements Export, WithMultipleSheets
{
    public function __construct(private array $hasil)
    {
    }

    public function sheets(): array
    {
        return [
            new PengolahanSheet($this->hasil),
            new RingkasanSheet($this->hasil),
            new DetailUnsurSheet($this->hasil),
        ];
    }
}
<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PengolahanSheet implements FromArray, WithColumnWidths, WithEvents, WithStyles, WithTitle
{
    public function __construct(private array $hasil)
    {
    }

    public function array(): array
    {
        $details = $this->hasil['details'];
        $surveiIds = $this->hasil['survei_ids'] ?? [];
        $jawaban = DB::table('jawaban_surveis')
            ->whereIn('survei_id', $surveiIds)
            ->get(['survei_id', 'unsur_pelayanan_id', 'nilai'])
            ->groupBy('survei_id');

        $rows = [
            ['PENGOLAHAN INDEKS KEPUASAN PER RESPONDEN DAN UNSUR PELAYANAN'],
            ['Jumlah Responden', $this->hasil['jumlah_responden']],
            ['No. Urut Responden', 'Parameter'],
            ['', ...collect($details)->map(fn ($detail, $index) => 'P' . ($index + 1))->all()],
        ];

        foreach ($surveiIds as $index => $surveiId) {
            $nilaiByUnsur = $jawaban->get($surveiId, collect())->keyBy('unsur_pelayanan_id');
            $rows[] = array_merge(
                [$index + 1],
                collect($details)->map(fn ($detail) => (int) ($nilaiByUnsur->get($detail['unsur_pelayanan_id'])->nilai ?? 0))->all()
            );
        }

        $rows[] = array_merge(['Nilai Per Parameter'], collect($details)->map(fn ($detail) =>
            $jawaban->flatten(1)->where('unsur_pelayanan_id', $detail['unsur_pelayanan_id'])->sum('nilai')
        )->all());
        $rows[] = array_merge(['Nilai Rata-rata (NRR)'], collect($details)->pluck('nilai_rata_rata')->all());
        $rows[] = array_merge(['BOBOT'], collect($details)->pluck('bobot_nilai')->all());
        $rows[] = array_merge(['Survei Kepuasan Masyarakat (SKM)'], collect($details)->pluck('nrr_tertimbang')->all());
        $rows[] = ['', $this->hasil['nilai_skm']];
        $rows[] = ['Indeks Kepuasan Masyarakat (IKM)', $this->hasil['nilai_ikm']];
        $rows[] = ['MUTU PELAYANAN', $this->hasil['mutu_pelayanan']];
        $rows[] = ['Kategori Penilaian Kepuasan Pelayanan', $this->hasil['kinerja_pelayanan']];

        return $rows;
    }

    public function title(): string
    {
        return 'Pengolahan';
    }

    public function columnWidths(): array
    {
        $widths = ['A' => 32];

        for ($column = 2; $column <= count($this->hasil['details']) + 1; $column++) {
            $widths[$this->columnName($column)] = 14;
        }

        return $widths;
    }

    public function styles(Worksheet $sheet): array
    {
        $lastColumn = $this->columnName(count($this->hasil['details']) + 1);
        $lastRow = $sheet->getHighestRow();

        $sheet->getStyle("A1:{$lastColumn}{$lastRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("A3:{$lastColumn}" . (4 + $this->hasil['jumlah_responden']))
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A3:{$lastColumn}4")->getFont()->setBold(true);
        $sheet->getStyle("A3:{$lastColumn}4")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9EAD3');
        $sheet->getStyle("A1:{$lastColumn}1")->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle("A1:{$lastColumn}1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A3:{$lastColumn}{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $summaryStartRow = 5 + $this->hasil['jumlah_responden'];
        $sheet->getStyle("A{$summaryStartRow}:{$lastColumn}{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$summaryStartRow}:A{$lastRow}")->getFont()->setBold(true);

        $nrrRow = 6 + $this->hasil['jumlah_responden'];
        $bobotRow = 7 + $this->hasil['jumlah_responden'];
        $skmRow = 8 + $this->hasil['jumlah_responden'];
        $totalSkmRow = 9 + $this->hasil['jumlah_responden'];
        $ikmRow = 10 + $this->hasil['jumlah_responden'];
        $sheet->getStyle("B{$nrrRow}:{$lastColumn}{$nrrRow}")->getNumberFormat()->setFormatCode('0.000');
        $sheet->getStyle("B{$bobotRow}:{$lastColumn}{$bobotRow}")->getNumberFormat()->setFormatCode('0.000');
        $sheet->getStyle("B{$skmRow}:{$lastColumn}{$skmRow}")->getNumberFormat()->setFormatCode('0.000');
        $sheet->getStyle("B{$totalSkmRow}")->getNumberFormat()->setFormatCode('0.000');
        $sheet->getStyle("B{$ikmRow}:{$lastColumn}{$ikmRow}")->getNumberFormat()->setFormatCode('0.00');

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $lastColumn = $this->columnName(count($this->hasil['details']) + 1);
                $event->sheet->mergeCells("A1:{$lastColumn}1");
                $event->sheet->mergeCells('B3:' . $lastColumn . '3');

                $summaryStartRow = 5 + $this->hasil['jumlah_responden'];
                $lastRow = $summaryStartRow + 7;
                $skmRow = $summaryStartRow + 3;
                $totalSkmRow = $summaryStartRow + 4;
                $event->sheet->mergeCells("A{$skmRow}:A{$totalSkmRow}");
                for ($row = $totalSkmRow; $row <= $lastRow; $row++) {
                    $event->sheet->mergeCells("B{$row}:{$lastColumn}{$row}");
                }

                $event->sheet->getDelegate()->freezePane('B5');
            },
        ];
    }

    private function columnName(int $number): string
    {
        $name = '';
        while ($number > 0) {
            $remainder = ($number - 1) % 26;
            $name = chr(65 + $remainder) . $name;
            $number = intdiv($number - 1, 26);
        }

        return $name;
    }
}
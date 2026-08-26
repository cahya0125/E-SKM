<?php

namespace App\Http\Controllers\Laporan;

use App\Exports\LaporanIkmExport;
use App\Http\Controllers\Controller;
use App\Models\Survei;
use App\Traits\HitungIkm;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html as PhpWordHtml;

class LaporanController extends Controller
{
    use HitungIkm;

    private array $bulanList = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
        7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
    ];

    public function index(Request $request)
    {
        $tahunOptions = Survei::selectRaw('YEAR(created_at) as tahun')
            ->distinct()
            ->orderByDesc('tahun')
            ->pluck('tahun');

        $filter = $this->resolveLaporanFilter($request, $tahunOptions);
        $hasil = $this->hitungIkm($filter['tipe'], $filter['tahun'], $filter['bulan'], $filter['triwulan']);
        $konten = view('laporan.content', $this->buildKontenData($filter, $hasil))->render();

        return view('laporan.index', array_merge($filter, [
            'tahunOptions' => $tahunOptions,
            'bulanList' => $this->bulanList,
            'hasil' => $hasil,
            'konten' => $konten,
            'labelPeriode' => $this->labelPeriode($filter),
        ]));
    }

    public function exportPdf(Request $request)
    {
        $konten = $request->input('konten') ?: $this->regenerateKonten($request);

        $pdf = Pdf::loadView('laporan.pdf', ['konten' => $konten])->setPaper('a4', 'portrait');

        return $pdf->download($this->namaFile($request, 'pdf'));
    }

    public function exportWord(Request $request)
    {
        $konten = $request->input('konten') ?: $this->regenerateKonten($request);

        $phpWord = new PhpWord();
        $section = $phpWord->addSection();
        PhpWordHtml::addHtml($section, $this->normalizeWordHtml($konten), false, false);

        $filename = $this->namaFile($request, 'docx');

        return response()->streamDownload(function () use ($phpWord) {
            $writer = IOFactory::createWriter($phpWord, 'Word2007');
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }

    public function exportExcel(Request $request)
    {
        $filter = $this->resolveLaporanFilter($request, collect());
        $hasil = $this->hitungIkm($filter['tipe'], $filter['tahun'], $filter['bulan'], $filter['triwulan']);

        return Excel::download(new LaporanIkmExport($hasil), $this->namaFile($request, 'xlsx'));
    }

    private function regenerateKonten(Request $request): string
    {
        $filter = $this->resolveLaporanFilter($request, collect());
        $hasil = $this->hitungIkm($filter['tipe'], $filter['tahun'], $filter['bulan'], $filter['triwulan']);

        return view('laporan.content', $this->buildKontenData($filter, $hasil))->render();
    }

    private function normalizeWordHtml(string $konten): string
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $previousUseInternalErrors = libxml_use_internal_errors(true);

        $dom->loadHTML(
            '<?xml encoding="UTF-8"><!DOCTYPE html><html><body><div id="word-content">' . $konten . '</div></body></html>',
            LIBXML_HTML_NODEFDTD
        );

        $container = $dom->getElementById('word-content');
        $normalized = '';

        if ($container) {
            foreach ($container->childNodes as $child) {
                $normalized .= $dom->saveHTML($child);
            }
        }

        libxml_clear_errors();
        libxml_use_internal_errors($previousUseInternalErrors);

        return preg_replace('/<(br|hr)([^>]*)>/i', '<$1$2 />', $normalized) ?? $normalized;
    }

    private function buildKontenData(array $filter, array $hasil): array
    {
        $detailsWithPersen = collect($hasil['details'])->map(function ($detail) {
            $detail['nilai_persen'] = round($detail['nilai_rata_rata'] * 25, 2);

            return $detail;
        });

        $terisi = $detailsWithPersen->where('jumlah_responden', '>', 0);

        return [
            'hasil' => array_merge($hasil, ['details' => $detailsWithPersen]),
            'labelPeriode' => $this->labelPeriode($filter),
            'jenisLabel' => match ($filter['tipe']) {
                'bulanan' => 'Bulanan',
                'triwulanan' => 'Triwulanan',
                default => 'Tahunan',
            },
            'unsurTertinggi' => $terisi->sortByDesc('nilai_rata_rata')->first(),
            'unsurTerendah' => $terisi->sortBy('nilai_rata_rata')->first(),
            'rekomendasi' => $this->rekomendasi($terisi->sortBy('nilai_rata_rata')->first()),
            'tanggalCetak' => now()->translatedFormat('d F Y'),
        ];
    }

    private function rekomendasi($unsurTerendah): array
    {
        $items = [];

        if ($unsurTerendah) {
            $items[] = 'Meningkatkan kualitas ' . strtolower($unsurTerendah['nama_unsur']) . ' yang masih perlu ditingkatkan.';
        }

        $items[] = 'Mempertahankan dan meningkatkan kompetensi petugas melalui pelatihan rutin.';
        $items[] = 'Mempercepat waktu respons pelayanan terutama pada kejadian bencana besar.';
        $items[] = 'Meningkatkan transparansi proses dan hasil pelayanan kepada masyarakat.';

        return $items;
    }

    private function labelPeriode(array $filter): string
    {
        return match ($filter['tipe']) {
            'bulanan' => Carbon::create($filter['tahun'], $filter['bulan'], 1)->translatedFormat('F Y'),
            'triwulanan' => 'Triwulan ' . $this->romawi($filter['triwulan']) . ' ' . $filter['tahun'],
            default => 'Tahun ' . $filter['tahun'],
        };
    }

    private function namaFile(Request $request, string $ext): string
    {
        $filter = $this->resolveLaporanFilter($request, collect());

        $label = match ($filter['tipe']) {
            'bulanan' => 'Bulan-' . str_pad((string) $filter['bulan'], 2, '0', STR_PAD_LEFT) . '-' . $filter['tahun'],
            'triwulanan' => 'Triwulan-' . $this->romawi($filter['triwulan']) . '-' . $filter['tahun'],
            default => 'Tahun-' . $filter['tahun'],
        };

        return "Laporan-SKM-BPBD-{$label}.{$ext}";
    }

    private function romawi(int $angka): string
    {
        return ['I', 'II', 'III', 'IV'][$angka - 1] ?? (string) $angka;
    }

    private function resolveLaporanFilter(Request $request, $tahunOptions): array
    {
        $jenis = $request->get('jenis', 'tahun');

        $tipe = match ($jenis) {
            'triwulan' => 'triwulanan',
            'bulan' => 'bulanan',
            default => 'tahunan',
        };

        return [
            'jenis' => $jenis,
            'tipe' => $tipe,
            'tahun' => $request->integer('tahun') ?: ($tahunOptions->first() ?? now()->year),
            'bulan' => $request->integer('bulan') ?: now()->month,
            'triwulan' => $request->integer('triwulan') ?: 1,
        ];
    }
}
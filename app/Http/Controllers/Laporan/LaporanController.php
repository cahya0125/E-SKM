<?php

namespace App\Http\Controllers\Laporan;

use App\Exports\LaporanIkmExport;
use App\Http\Controllers\Controller;
use App\Models\Survei;
use App\Models\JawabanSurvei;
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
        1 => 'Jan',
        2 => 'Feb',
        3 => 'Mar',
        4 => 'Apr',
        5 => 'Mei',
        6 => 'Jun',
        7 => 'Jul',
        8 => 'Agu',
        9 => 'Sep',
        10 => 'Okt',
        11 => 'Nov',
        12 => 'Des',
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

        // pastikan margin section standar biar konsisten dgn PDF
        $section = $phpWord->addSection([
            'marginLeft'   => 1417, // ~2.5cm dlm twips
            'marginRight'  => 1417,
            'marginTop'    => 1417,
            'marginBottom' => 1417,
        ]);

        // PhpWord\Shared\Html TIDAK mendukung CSS "page-break-before".
        // Di Blade, pindah halaman ditandai dengan:
        //   <div style="page-break-before:always;"></div>
        // Untuk PDF (DomPDF) ini otomatis jalan karena DomPDF paham CSS.
        // Untuk Word kita harus pecah HTML manual di titik itu dan
        // panggil addPageBreak() sendiri.
        $potongan = preg_split(
            '/<div\s+style="page-break-before:\s*always;?\s*"\s*>\s*<\/div>/i',
            $konten
        );

        foreach ($potongan as $index => $bagian) {
            if ($index > 0) {
                $section->addPageBreak();
            }

            if (trim($bagian) === '') {
                continue;
            }

            PhpWordHtml::addHtml($section, $this->normalizeWordHtml($bagian), false, false);
        }

        $this->forceTableFullWidth($section);   // <-- fix "offside", sekarang cuma tabel top-level
        $this->forceThinTableBorders($section);

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
                // saveXML (bukan saveHTML) otomatis nutup semua void element (img, br, hr, dst)
                // jadi valid XML — inilah yang dibutuhkan PhpWord\Shared\Html::addHtml().
                $normalized .= $dom->saveXML($child);
            }
        }

        libxml_clear_errors();
        libxml_use_internal_errors($previousUseInternalErrors);

        return $normalized;
    }

    private function buildKontenData(array $filter, array $hasil): array
    {
        $detailsWithPersen = collect($hasil['details'])->map(function ($detail) {
            $detail['nilai_persen'] = round($detail['nilai_rata_rata'] * 25, 2);

            return $detail;
        });

        $terisi = $detailsWithPersen->where('jumlah_responden', '>', 0);
        $matriksResponden = $this->buildMatriksResponden($hasil, $detailsWithPersen);

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
            'chartImageUrl' => $this->buildChartUrl($detailsWithPersen),
            'matriksResponden' => $matriksResponden,
        ];
    }

    private function buildMatriksResponden(array $hasil, $details): array
    {
        $surveiIds = collect($hasil['survei_ids'] ?? [])->values();
        $unsurIds = collect($details)->pluck('unsur_pelayanan_id')->values();
        $jumlahResponden = $surveiIds->count();

        if ($surveiIds->isEmpty() || $unsurIds->isEmpty()) {
            return [
                'rows' => [],
                'total_per_unsur' => [],
                'nrr_per_unsur' => [],
                'bobot_per_unsur' => [],
                'skm_per_unsur' => [],
            ];
        }

        $jawaban = JawabanSurvei::query()
            ->whereIn('survei_id', $surveiIds)
            ->whereIn('unsur_pelayanan_id', $unsurIds)
            ->get(['survei_id', 'unsur_pelayanan_id', 'nilai']);

        $nilaiMap = [];

        foreach ($jawaban as $item) {
            $nilaiMap[$item->survei_id][$item->unsur_pelayanan_id] = (float) $item->nilai;
        }

        $rows = [];
        $totalPerUnsur = array_fill(0, $unsurIds->count(), 0.0);

        foreach ($surveiIds->values() as $index => $surveiId) {
            $nilaiPerUnsur = [];

            foreach ($unsurIds as $unsurIndex => $unsurId) {
                $nilai = $nilaiMap[$surveiId][$unsurId] ?? null;
                $nilaiPerUnsur[] = $nilai;

                if ($nilai !== null) {
                    $totalPerUnsur[$unsurIndex] += $nilai;
                }
            }

            $rows[] = [
                'nomor' => $index + 1,
                'nilai' => $nilaiPerUnsur,
            ];
        }

        $nrrPerUnsur = [];
        $bobotPerUnsur = [];
        $skmPerUnsur = [];

        foreach ($totalPerUnsur as $idx => $total) {
            $nrr = $jumlahResponden > 0 ? round($total / $jumlahResponden, 3) : 0;
            $bobot = (float) ($details[$idx]['bobot_nilai'] ?? 0);
            $skm = round($nrr * $bobot, 3);

            $nrrPerUnsur[] = $nrr;
            $bobotPerUnsur[] = $bobot;
            $skmPerUnsur[] = $skm;
        }

        return [
            'rows' => $rows,
            'total_per_unsur' => $totalPerUnsur,
            'nrr_per_unsur' => $nrrPerUnsur,
            'bobot_per_unsur' => $bobotPerUnsur,
            'skm_per_unsur' => $skmPerUnsur,
        ];
    }

    private function buildChartUrl($details): ?string
    {
        $config = [
            'type' => 'bar',
            'data' => [
                'labels' => $details->pluck('kode')->values()->all(),
                'datasets' => [[
                    'label' => 'Nilai Persepsi (%)',
                    'data' => $details->pluck('nilai_persen')->values()->all(),
                    'backgroundColor' => '#4472C4',
                ]],
            ],
            'options' => [
                'plugins' => [
                    'legend' => ['display' => false],
                    'datalabels' => [
                        'anchor' => 'end',
                        'align' => 'top',
                        'formatter' => "function(value) { return value + '%'; }",
                    ],
                ],
                'scales' => [
                    'y' => ['min' => 0, 'max' => 100],
                ],
            ],
        ];

        $chartUrl = 'https://quickchart.io/chart?w=500&h=300&backgroundColor=white&c=' . urlencode(json_encode($config));

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(8)->get($chartUrl);

            if (! $response->successful()) {
                return null;
            }

            $mime = $response->header('Content-Type') ?: 'image/png';

            return 'data:' . $mime . ';base64,' . base64_encode($response->body());
        } catch (\Throwable $e) {
            // Kalau quickchart lagi nggak bisa diakses, laporan tetap jalan tanpa chart
            report($e);

            return null;
        }
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

    private function forceThinTableBorders(\PhpOffice\PhpWord\Element\AbstractContainer $container): void
    {
        foreach ($container->getElements() as $element) {
            if ($element instanceof \PhpOffice\PhpWord\Element\Table) {
                foreach ($element->getRows() as $row) {
                    foreach ($row->getCells() as $cell) {
                        $style = $cell->getStyle();
                        $style->setBorderSize(4);       // 4 = 0.5pt (satuannya per-8 poin)
                        $style->setBorderColor('000000');
                    }
                }
            }

            if (method_exists($element, 'getElements')) {
                $this->forceThinTableBorders($element);
            }
        }
    }

    /**
     * Paksa tabel jadi lebar penuh (100%) — TAPI hanya untuk tabel top-level
     * (langsung anak dari section). Tabel yang bersarang di dalam sebuah cell
     * (mis. tabel trik untuk nge-center kotak rumus SKM, atau kotak "IKM x 20")
     * SENGAJA tidak disentuh, karena kalau ikut dipaksa 100% dia melebar
     * mengikuti section, bukan mengikuti cell induknya — itu yang bikin
     * layout-nya berantakan di Word walau di PDF rapi.
     */
    private function forceTableFullWidth(\PhpOffice\PhpWord\Element\AbstractContainer $container, bool $isTopLevel = true): void
    {
        foreach ($container->getElements() as $element) {
            if ($element instanceof \PhpOffice\PhpWord\Element\Table) {
                if ($isTopLevel) {
                    $style = $element->getStyle();

                    // 5000 = 100% (PhpWord pakai satuan 1/50 persen)
                    $style->setUnit(\PhpOffice\PhpWord\SimpleType\TblWidth::PERCENT);
                    $style->setWidth(5000);
                }

                foreach ($element->getRows() as $row) {
                    foreach ($row->getCells() as $cell) {
                        // masuk ke dalam cell buat handle nested table,
                        // tapi tabel nested TIDAK dipaksa full width lagi
                        $this->forceTableFullWidth($cell, false);
                    }
                }
            } elseif (method_exists($element, 'getElements')) {
                $this->forceTableFullWidth($element, $isTopLevel);
            }
        }
    }
}
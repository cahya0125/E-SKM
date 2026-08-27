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
        try {
            $filter = $this->resolveLaporanFilter($request, collect());
            $hasil = $this->hitungIkm($filter['tipe'], $filter['tahun'], $filter['bulan'], $filter['triwulan']);
            $data = $this->buildKontenData($filter, $hasil);
            $temporaryFile = tempnam(storage_path('app'), 'laporan-word-');
            $this->fillWordTemplate($temporaryFile, $data);
            $filename = $this->namaFile($request, 'docx');

            $archive = new \ZipArchive();
            if ($archive->open($temporaryFile) !== true || $archive->locateName('word/document.xml') === false) {
                @unlink($temporaryFile);
                throw new \RuntimeException('Template Word menghasilkan dokumen yang tidak valid.');
            }
            $archive->close();

            return response()->download($temporaryFile, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ])->deleteFileAfterSend(true);
            
        } catch (\Throwable $e) {
            \Log::error('Export Word failed: ' . $e->getMessage());
            
            // Fallback: download PDF jika Word gagal
            return redirect()->back()->with('error', 'Export Word gagal. Silakan coba export PDF.');
        }
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

    private function fillWordTemplate(string $outputPath, array $data): void
    {
        $templatePath = storage_path('app/templates/Template_Laporan_SKM.docx');

        if (! is_file($templatePath)) {
            throw new \RuntimeException('Template Word tidak ditemukan.');
        }

        copy($templatePath, $outputPath);
        $archive = new \ZipArchive();

        if ($archive->open($outputPath) !== true) {
            throw new \RuntimeException('Template Word tidak dapat dibaca.');
        }

        $xml = $archive->getFromName('word/document.xml');
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->loadXML($xml);
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        $this->replaceWordLabeledParagraph($xpath, 'Tujuan', 'Dalam rangka meningkatkan mutu pelayanan publik, sebagai acuan mengukur tingkat kepuasan masyarakat sebagai pengguna layanan dan meningkatkan kualitas penyelenggaraan pelayanan publik yang dilakukan oleh unit pelayanan instansi pemerintah dalam melaksanakan pelayanan kepada masyarakat.');
        $this->replaceWordLabeledParagraph($xpath, 'Periode', $data['labelPeriode']);
        $this->replaceWordLabeledParagraph($xpath, 'Tanggal', ($data['hasil']['tanggal_mulai'] ?? '-') . ' s.d. ' . ($data['hasil']['tanggal_selesai'] ?? '-'));
        $this->replaceWordLabeledParagraph($xpath, 'Survei', 'Per Responden Per Parameter Survei');
        $this->replaceWordLabeledParagraph($xpath, 'Metode', 'Peraturan Menteri Pendayagunaan Aparatur Negara dan Reformasi Birokrasi Nomor 14 Tahun 2017 tentang Pedoman Penyusunan Survei Kepuasan Masyarakat Unit Pelayanan Instansi Pemerintah.');
        $this->replaceWordLabeledParagraph($xpath, 'Jumlah Responden', number_format($data['hasil']['jumlah_responden']) . ' orang');
        $this->replaceWordLabeledParagraph($xpath, 'Jumlah Parameter', count($data['hasil']['details']) . ' Parameter');
        $this->forceWordPageBreakBefore($xpath, 'PENGOLAHAN INDEKS KEPUASAN PERRESPONDEN');

        $rows = $xpath->query('//w:tr');
        $matrixStart = null;

        foreach ($rows as $index => $row) {
            $text = $this->wordNodeText($xpath, $row);
            if (str_contains($text, 'No. Urut Responden')) {
                $matrixStart = $index + 1;
                break;
            }
        }

        if ($matrixStart !== null) {
            $matrixRows = $data['matriksResponden']['rows'];
            $templateRows = [];
            $summaryRow = null;

            for ($index = $matrixStart; $index < $rows->length; $index++) {
                $row = $rows->item($index);
                $text = $this->wordNodeText($xpath, $row);
                if (str_contains($text, 'Nilai Per Parameter') || str_contains($text, 'Nilai Rer') || str_contains($text, 'Nilai Rata-rata')) {
                    $summaryRow = $row;
                    break;
                }

                $templateRows[] = $row;
            }

            if ($summaryRow && count($matrixRows) > count($templateRows)) {
                $lastRow = end($templateRows);
                while (count($templateRows) < count($matrixRows) && $lastRow) {
                    $newRow = $lastRow->cloneNode(true);
                    $summaryRow->parentNode->insertBefore($newRow, $summaryRow);
                    $templateRows[] = $newRow;
                }
            }

            foreach ($matrixRows as $index => $matrixRow) {
                $row = $templateRows[$index] ?? null;
                if (! $row) {
                    continue;
                }

                $cells = $xpath->query('./w:tc', $row);
                $this->setWordCellText($xpath, $cells->item(0), $matrixRow['nomor'] . '.');
                foreach ($matrixRow['nilai'] as $cellIndex => $value) {
                    $this->setWordCellText($xpath, $cells->item($cellIndex + 1), $this->formatTemplateValue($value));
                }
            }

            foreach (array_slice($templateRows, count($matrixRows)) as $row) {
                $row->parentNode->removeChild($row);
            }
        }

        if ($matrixStart !== null) {
            $countRow = $rows->item($matrixStart - 2);
            $cells = $countRow ? $xpath->query('./w:tc', $countRow) : null;
            $this->setWordCellText($xpath, $cells?->item(1), (string) $data['hasil']['jumlah_responden']);
        }

        foreach ($xpath->query('//w:tr') as $row) {
            $text = $this->wordNodeText($xpath, $row);
            $values = null;

            if (str_contains($text, 'Nilai Per Parameter') || str_contains($text, 'Nilai Rer Parameter')) {
                $values = $data['matriksResponden']['total_per_unsur'];
            } elseif (str_contains($text, 'Nilai Rata-rata')) {
                $values = $data['matriksResponden']['nrr_per_unsur'];
            } elseif (trim($text) === 'BOBOT') {
                $values = $data['matriksResponden']['bobot_per_unsur'];
            } elseif (str_contains($text, 'Survey Kepuasan')) {
                $values = $data['matriksResponden']['skm_per_unsur'];
            }

            if ($values !== null) {
                $cells = $xpath->query('./w:tc', $row);
                foreach ($values as $index => $value) {
                    $this->setWordCellText($xpath, $cells->item($index + 1), number_format((float) $value, 3, '.', ''));
                }
            } elseif (str_contains($text, 'Indeks Kepuasan Masyarakat')) {
                $cells = $xpath->query('./w:tc', $row);
                $this->setWordCellText($xpath, $cells->item(1), number_format((float) $data['hasil']['nilai_ikm'], 2, '.', ''));
            } elseif (str_contains($text, 'MUTU PELAYANAN')) {
                $cells = $xpath->query('./w:tc', $row);
                $this->setWordCellText($xpath, $cells->item(1), $data['hasil']['mutu_pelayanan']);
            } elseif (str_contains($text, 'Kategori Penilaian Kepuasan')) {
                $cells = $xpath->query('./w:tc', $row);
                $this->setWordCellText($xpath, $cells->item(1), $data['hasil']['kinerja_pelayanan']);
            }
        }

        $afterConclusion = false;
        foreach ($xpath->query('//w:p') as $paragraph) {
            $text = $this->wordNodeText($xpath, $paragraph);
            if (str_contains(strtoupper($text), 'KESIMPULAN')) {
                $afterConclusion = true;
                continue;
            }

            if ($afterConclusion && in_array(strtolower($text), ['sangat baik', 'baik', 'cukup baik', 'kurang baik'], true)) {
                $this->setWordParagraphText($xpath, $paragraph, strtoupper($data['hasil']['kinerja_pelayanan']));
            }
        }

        $archive->addFromString('word/document.xml', $dom->saveXML());
        $archive->close();
    }

    private function replaceWordParagraph(\DOMXPath $xpath, string $needle, string $replacement): void
    {
        foreach ($xpath->query('//w:p') as $paragraph) {
            if (str_contains($this->wordNodeText($xpath, $paragraph), $needle)) {
                $texts = $xpath->query('.//w:t', $paragraph);
                if ($texts->length > 0) {
                    $texts->item(0)->textContent = $replacement;
                    for ($index = 1; $index < $texts->length; $index++) {
                        $texts->item($index)->textContent = '';
                    }
                }
                return;
            }
        }
    }

    private function replaceWordLabeledParagraph(\DOMXPath $xpath, string $label, string $replacement): void
    {
        foreach ($xpath->query('//w:p') as $paragraph) {
            $textNodes = $xpath->query('.//w:t', $paragraph);
            $paragraphText = $this->wordNodeText($xpath, $paragraph);

            if (! str_starts_with(str_replace(' ', '', $paragraphText), str_replace(' ', '', $label) . ':')) {
                continue;
            }

            $colonIndex = null;
            foreach ($textNodes as $index => $textNode) {
                if (str_contains($textNode->textContent, ':')) {
                    $colonIndex = $index;
                    break;
                }
            }

            if ($colonIndex === null || ! isset($textNodes[$colonIndex + 1])) {
                return;
            }

            $textNodes->item($colonIndex + 1)->textContent = ' ' . $replacement;
            for ($index = $colonIndex + 2; $index < $textNodes->length; $index++) {
                $textNodes->item($index)->textContent = '';
            }

            return;
        }
    }

    private function forceWordPageBreakBefore(\DOMXPath $xpath, string $needle): void
    {
        $namespace = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

        foreach ($xpath->query('//w:p') as $paragraph) {
            if (! str_contains($this->wordNodeText($xpath, $paragraph), $needle)) {
                continue;
            }

            $properties = $xpath->query('./w:pPr', $paragraph)->item(0);
            if (! $properties) {
                $properties = $paragraph->ownerDocument->createElementNS($namespace, 'w:pPr');
                $paragraph->insertBefore($properties, $paragraph->firstChild);
            }

            if ($xpath->query('./w:pageBreakBefore', $properties)->length === 0) {
                $properties->appendChild($paragraph->ownerDocument->createElementNS($namespace, 'w:pageBreakBefore'));
            }

            return;
        }
    }

    private function wordNodeText(\DOMXPath $xpath, \DOMNode $node): string
    {
        $text = '';
        foreach ($xpath->query('.//w:t', $node) as $textNode) {
            $text .= $textNode->nodeValue;
        }

        return trim($text);
    }

    private function setWordCellText(\DOMXPath $xpath, ?\DOMNode $cell, string $value): void
    {
        if (! $cell) {
            return;
        }

        $texts = $xpath->query('.//w:t', $cell);
        if ($texts->length === 0) {
            return;
        }

        $texts->item(0)->textContent = $value;
        for ($index = 1; $index < $texts->length; $index++) {
            $texts->item($index)->textContent = '';
        }
    }

    private function setWordParagraphText(\DOMXPath $xpath, \DOMNode $paragraph, string $value): void
    {
        $texts = $xpath->query('.//w:t', $paragraph);
        if ($texts->length === 0) {
            return;
        }

        $texts->item(0)->textContent = $value;
        for ($index = 1; $index < $texts->length; $index++) {
            $texts->item($index)->textContent = '';
        }
    }

    private function removeWordNumbering(\DOMXPath $xpath, \DOMNode $row): void
    {
        $firstCell = $xpath->query('./w:tc', $row)->item(0);

        if (! $firstCell) {
            return;
        }

        foreach ($xpath->query('.//w:numPr', $firstCell) as $numbering) {
            $numbering->parentNode?->removeChild($numbering);
        }
    }

    private function formatTemplateValue($value): string
    {
        if ($value === null) {
            return '-';
        }

        return ((float) $value === (float) (int) $value) ? (string) (int) $value : number_format((float) $value, 3, '.', '');
    }

    private function normalizeWordHtml(string $konten): string
    {
        $konten = preg_replace('/<!--\[if.*?\]>.*?<!\[endif\]-->/is', '', $konten);

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $previousErrors = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8"><!DOCTYPE html><html><body><div id="word-content">' . $konten . '</div></body></html>', LIBXML_HTML_NODEFDTD);
        $container = $dom->getElementById('word-content');
        $normalized = '';

        if ($container) {
            foreach ($container->childNodes as $child) {
                $normalized .= $dom->saveXML($child);
            }
        }

        libxml_clear_errors();
        libxml_use_internal_errors($previousErrors);

        return $normalized ?: $konten;
    }

    private function prepareWordImages(string $konten, array &$temporaryImages): string
    {
        return preg_replace_callback(
            '/src=["\']data:(image\/(?:png|jpe?g|gif));base64,([^"\']+)["\']/i',
            function (array $matches) use (&$temporaryImages): string {
                $extension = strtolower($matches[1]) === 'image/gif' ? 'gif' : (strtolower($matches[1]) === 'image/png' ? 'png' : 'jpg');
                $temporaryImage = tempnam(sys_get_temp_dir(), 'eskm-word-') . '.' . $extension;
                $image = base64_decode($matches[2], true);

                if ($image === false || file_put_contents($temporaryImage, $image) === false) {
                    return $matches[0];
                }

                $temporaryImages[] = $temporaryImage;

                return 'src="' . str_replace('\\', '/', $temporaryImage) . '"';
            },
            $konten
        ) ?? $konten;
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
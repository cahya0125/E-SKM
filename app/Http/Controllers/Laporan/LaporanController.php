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
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

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
            Log::error('Export Word failed: ' . $e->getMessage());

            return redirect()->back()->with(
                'error',
                'Export Word gagal. Silakan coba export PDF.'
            );
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

                // Cek apakah ini baris "Nilai Per Parameter"
                $isNilaiPerParameter = str_contains($text, 'Nilai Per Parameter')
                    || str_contains($text, 'Nilai Rer Parameter');

                foreach ($values as $index => $value) {
                    $formatted = $isNilaiPerParameter
                        ? (string) (int) round((float) $value)   // → 234, 236, 265, dst.
                        : number_format((float) $value, 3, '.', ''); // NRR/BOBOT/SKM tetap 3 desimal

                    $this->setWordCellText($xpath, $cells->item($index + 1), $formatted);
                }
            } elseif (str_contains($text, 'Indeks Kepuasan Masyarakat (IKM)')) {
                $cells = $xpath->query('./w:tc', $row);
                $this->setWordCellText($xpath, $cells->item(1), number_format((float) $data['hasil']['nilai_ikm'], 2, '.', ''));
            } elseif (str_contains(strtoupper($text), 'MUTU PELAYANAN')) {
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

        $this->fillWordKesimpulanUnsur($dom, $xpath, $data);
        $this->insertWordChartImage($archive, $dom, $xpath, $data);

        // Isi nama pimpinan & NIP pada blok tanda tangan
        $this->fillWordSignatureBlock($dom, $xpath);

        $archive->addFromString('word/document.xml', $dom->saveXML());
        $archive->close();
    }

    /**
     * Mengisi nama pejabat dan NIP pada blok tanda tangan
     * (paragraf "KEPALA PELAKSANA BADAN ..." lalu baris nama, lalu "NIP.").
     * Format: nama dicetak tebal + garis bawah, rata tengah (menyambung judul di atasnya),
     * baris NIP di bawahnya rata tengah juga.
     */
    private function fillWordSignatureBlock(\DOMDocument $dom, \DOMXPath $xpath): void
    {
        $this->pullWordSignatureBlockUp($dom, $xpath);
        $pimpinan = $this->pimpinanInfo();

        $paragraphList = iterator_to_array($xpath->query('//w:p'));
        $nipIndex = null;
        $titleIndex = null;

        foreach ($paragraphList as $index => $paragraph) {
            $text = trim($this->wordNodeText($xpath, $paragraph));

            if ($titleIndex === null && str_contains(strtoupper($text), 'KEPALA PELAKSANA')) {
                $titleIndex = $index;
            }

            if (str_starts_with($text, 'NIP')) {
                $nipIndex = $index;
                break;
            }
        }

        if ($nipIndex === null) {
            return;
        }

        $nipParagraph = $paragraphList[$nipIndex];

        $titleIndent = $titleIndex !== null
            ? $this->getWordParagraphIndent($xpath, $paragraphList[$titleIndex])
            : null;

        // baris NIP
        $this->resetWordParagraphRuns($dom, $nipParagraph, 'NIP. ' . $pimpinan['nip'], bold: true, fontSize: 20);
        $this->setWordParagraphAlignment($dom, $xpath, $nipParagraph, 'center');
        $this->applyWordParagraphIndent($dom, $xpath, $nipParagraph, $titleIndent);

        $maxBlankLines = 4;
        $nameIndex = $nipIndex - 1;

        if ($titleIndex !== null && $nameIndex > $titleIndex + 1) {
            $blankCandidates = [];
            for ($i = $nameIndex - 1; $i > $titleIndex; $i--) {
                $text = trim($this->wordNodeText($xpath, $paragraphList[$i]));
                if ($text === '') {
                    $blankCandidates[] = $paragraphList[$i];
                }
            }

            $toRemove = array_slice(array_reverse($blankCandidates), $maxBlankLines);
            foreach ($toRemove as $p) {
                $p->parentNode->removeChild($p);
            }
        }

        for ($i = $nipIndex + 1; $i < count($paragraphList); $i++) {
            $candidate = $paragraphList[$i];
            $candidateText = trim($this->wordNodeText($xpath, $candidate));

            if ($candidateText === '' || ! preg_match('/^[0-9xX\s]+$/', $candidateText)) {
                break;
            }

            $candidate->parentNode->removeChild($candidate);
        }

        for ($i = $nipIndex - 1; $i >= 0; $i--) {
            $candidate = $paragraphList[$i];
            // baris nama
            $this->resetWordParagraphRuns($dom, $candidate, $pimpinan['nama'], bold: true, underline: true, fontSize: 20);
            $this->setWordParagraphAlignment($dom, $xpath, $candidate, 'center');
            $this->applyWordParagraphIndent($dom, $xpath, $candidate, $titleIndent);

            // Template aslinya cuma punya 2 baris (nama + NIP), nggak ada baris
            // pangkat sama sekali. Jadi baris pangkat harus DISISIPKAN BARU di
            // antara nama dan NIP, bukan diisi ke paragraf yang sudah ada.
            // baris pangkat
            $pangkatParagraph = $this->createWordParagraph($dom, $pimpinan['pangkat'], fontSize: 20);
            $this->setWordParagraphAlignment($dom, $xpath, $pangkatParagraph, 'center');
            $this->applyWordParagraphIndent($dom, $xpath, $pangkatParagraph, $titleIndent);
            $nipParagraph->parentNode->insertBefore($pangkatParagraph, $nipParagraph);

            break;
        }

        $this->keepWordSignatureBlockTogether($dom, $xpath);
    }

    /**
     * Membuat satu paragraf <w:p> baru berisi teks $text (satu run, tanpa format khusus).
     * Dipakai buat nyisipin baris baru (mis. baris pangkat) yang emang belum ada
     * placeholder-nya di template asli.
     */
    private function createWordParagraph(\DOMDocument $dom, string $text, bool $bold = true, int $fontSize = 22): \DOMElement
    {
        $namespace = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

        $paragraph = $dom->createElementNS($namespace, 'w:p');
        $run = $dom->createElementNS($namespace, 'w:r');
        $rPr = $dom->createElementNS($namespace, 'w:rPr');

        $rFonts = $dom->createElementNS($namespace, 'w:rFonts');
        $rFonts->setAttribute('w:ascii', 'Bookman Old Style');
        $rFonts->setAttribute('w:hAnsi', 'Bookman Old Style');
        $rFonts->setAttribute('w:cs', 'Bookman Old Style');
        $rPr->appendChild($rFonts);

        if ($bold) {
            $rPr->appendChild($dom->createElementNS($namespace, 'w:b'));
        }

        $sz = $dom->createElementNS($namespace, 'w:sz');
        $sz->setAttribute('w:val', (string) $fontSize);
        $rPr->appendChild($sz);

        $run->appendChild($rPr);

        $t = $dom->createElementNS($namespace, 'w:t');
        $t->setAttribute('xml:space', 'preserve');
        $t->appendChild($dom->createTextNode($text));
        $run->appendChild($t);
        $paragraph->appendChild($run);

        return $paragraph;
    }
    /**
     * Ambil nilai w:left / w:right dari <w:ind> sebuah paragraf (kalau ada).
     */
    private function getWordParagraphIndent(\DOMXPath $xpath, \DOMNode $paragraph): ?array
    {
        $ind = $xpath->query('./w:pPr/w:ind', $paragraph)->item(0);

        if (! $ind instanceof \DOMElement) {
            return null;
        }

        return [
            'left' => $ind->getAttribute('w:left'),
            'right' => $ind->getAttribute('w:right'),
        ];
    }

    /**
     * Terapkan indentasi kiri/kanan yang sama seperti $indent ke sebuah paragraf,
     * supaya sejajar horizontal dengan paragraf lain (mis. baris nama/NIP disejajarkan
     * dengan judul "KEPALA PELAKSANA BADAN..." di atasnya). Kalau $indent null,
     * indentasi lama dihapus (fallback, biasanya nggak kepakai lagi).
     */
    private function applyWordParagraphIndent(\DOMDocument $dom, \DOMXPath $xpath, \DOMNode $paragraph, ?array $indent): void
    {
        $pPr = $xpath->query('./w:pPr', $paragraph)->item(0);

        if (! $pPr instanceof \DOMElement) {
            $pPr = $dom->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'w:pPr');
            $paragraph->insertBefore($pPr, $paragraph->firstChild);
        }

        $ind = $xpath->query('./w:ind', $pPr)->item(0);

        if (! $indent) {
            if ($ind) {
                $pPr->removeChild($ind);
            }
            return;
        }

        if (! $ind instanceof \DOMElement) {
            $ind = $dom->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'w:ind');
            $pPr->appendChild($ind);
        }

        $ind->setAttribute('w:left', $indent['left']);
        $ind->setAttribute('w:right', $indent['right']);
    }

    /**
     * Menghapus <w:ind> (indentasi kiri/kanan custom) dari sebuah paragraf, kalau ada.
     * Dipakai buat paragraf yang indentasi bawaannya kelewat sempit sehingga bikin
     * teks pendek pun ke-wrap jadi banyak baris.
     */
    private function removeWordParagraphIndent(\DOMXPath $xpath, \DOMNode $paragraph): void
    {
        $pPr = $xpath->query('./w:pPr', $paragraph)->item(0);

        if (! $pPr) {
            return;
        }

        $ind = $xpath->query('./w:ind', $pPr)->item(0);

        if ($ind) {
            $pPr->removeChild($ind);
        }
    }

    /**
     * Menghapus semua run (w:r) di dalam sebuah paragraf lalu menulis satu run baru
     * berisi $value. Ini membersihkan isi lama sepenuhnya (termasuk w:br / run kosong sisa
     * placeholder template) sehingga hasilnya selalu satu baris yang bersih.
     */
    private function resetWordParagraphRuns(\DOMDocument $dom, \DOMNode $paragraph, string $value, bool $bold = false, bool $underline = false, int $fontSize = 22): void
    {
        $namespace = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

        foreach (iterator_to_array($paragraph->childNodes) as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE && $child->nodeName === 'w:r') {
                $paragraph->removeChild($child);
            }
        }

        $run = $dom->createElementNS($namespace, 'w:r');
        $rPr = $dom->createElementNS($namespace, 'w:rPr');

        $rFonts = $dom->createElementNS($namespace, 'w:rFonts');
        $rFonts->setAttribute('w:ascii', 'Bookman Old Style');
        $rFonts->setAttribute('w:hAnsi', 'Bookman Old Style');
        $rFonts->setAttribute('w:cs', 'Bookman Old Style');
        $rPr->appendChild($rFonts);

        if ($bold) {
            $rPr->appendChild($dom->createElementNS($namespace, 'w:b'));
        }

        if ($underline) {
            $u = $dom->createElementNS($namespace, 'w:u');
            $u->setAttribute('w:val', 'single');
            $rPr->appendChild($u);
        }

        $sz = $dom->createElementNS($namespace, 'w:sz');
        $sz->setAttribute('w:val', (string) $fontSize); // half-points: 20 = 10pt
        $rPr->appendChild($sz);

        $run->appendChild($rPr);

        $textElement = $dom->createElementNS($namespace, 'w:t');
        $textElement->setAttribute('xml:space', 'preserve');
        $textElement->appendChild($dom->createTextNode($value));
        $run->appendChild($textElement);

        $paragraph->appendChild($run);
    }

    /**
     * Mengatur perataan (rata kiri/tengah/kanan) sebuah paragraf.
     */
    private function setWordParagraphAlignment(\DOMDocument $dom, \DOMXPath $xpath, \DOMNode $paragraph, string $alignment): void
    {
        $namespace = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';
        $pPr = $xpath->query('./w:pPr', $paragraph)->item(0);

        if (! $pPr instanceof \DOMElement) {
            $pPr = $dom->createElementNS($namespace, 'w:pPr');
            $paragraph->insertBefore($pPr, $paragraph->firstChild);
        }

        $jc = $xpath->query('./w:jc', $pPr)->item(0);
        if (! $jc instanceof \DOMElement) {
            $jc = $dom->createElementNS($namespace, 'w:jc');
            $pPr->appendChild($jc);
        }

        $jc->setAttribute('w:val', $alignment);
    }

    /**
     * Nama & NIP pimpinan yang menandatangani laporan.
     * Diambil dari config/laporan.php (bisa di-override lewat .env: LAPORAN_PIMPINAN_NAMA, LAPORAN_PIMPINAN_NIP).
     */
    private function pimpinanInfo(): array
    {
        return [
            'nama' => config('laporan.pimpinan_nama', 'Nama Pimpinan Belum Diisi'),
            'pangkat' => config('laporan.pimpinan_pangkat', 'Pangkat Belum Diisi'),
            'nip' => config('laporan.pimpinan_nip', '-'),
        ];
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


    private function formatTemplateValue($value): string
    {
        if ($value === null) {
            return '-';
        }

        return ((float) $value === (float) (int) $value) ? (string) (int) $value : number_format((float) $value, 3, '.', '');
    }

    private function buildKontenData(array $filter, array $hasil): array
    {
        $periode = $this->computePeriodeTanggal($filter);
        $hasil['tanggal_mulai'] = $periode['mulai']->locale('id')->translatedFormat('d F Y');
        $hasil['tanggal_selesai'] = $periode['selesai']->locale('id')->translatedFormat('d F Y');

        $detailsWithPersen = collect($hasil['details'])->map(function ($detail) {
            $detail['nilai_persen'] = round($detail['nilai_rata_rata'] * 20, 2);

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
            'tanggalCetak' => now()->locale('id')->translatedFormat('d F Y'),
            'chartImageUrl' => $this->buildChartUrl($detailsWithPersen),
            'matriksResponden' => $matriksResponden,
            'pimpinan' => $this->pimpinanInfo(),
        ];
    }

    /**
     * Menghitung rentang tanggal awal-akhir periode laporan berdasarkan jenis filter:
     * - tahunan: 1 Januari s.d. 31 Desember tahun terpilih
     * - triwulanan: 3 bulan sesuai triwulan (I: Jan-Mar, II: Apr-Jun, III: Jul-Sep, IV: Okt-Des)
     * - bulanan: tanggal 1 s.d. akhir bulan (otomatis menyesuaikan 28/29/30/31 hari)
     */
    private function computePeriodeTanggal(array $filter): array
    {
        return match ($filter['tipe']) {
            'bulanan' => [
                'mulai' => Carbon::create($filter['tahun'], $filter['bulan'], 1)->startOfMonth(),
                'selesai' => Carbon::create($filter['tahun'], $filter['bulan'], 1)->endOfMonth(),
            ],
            'triwulanan' => (function () use ($filter) {
                $bulanAwal = ($filter['triwulan'] - 1) * 3 + 1;
                $mulai = Carbon::create($filter['tahun'], $bulanAwal, 1)->startOfMonth();

                return [
                    'mulai' => $mulai,
                    'selesai' => $mulai->copy()->addMonths(2)->endOfMonth(),
                ];
            })(),
            default => [
                'mulai' => Carbon::create($filter['tahun'], 1, 1)->startOfYear(),
                'selesai' => Carbon::create($filter['tahun'], 12, 31)->endOfYear(),
            ],
        };
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
        $labels = $details->pluck('kode')->values()->all();
        $values = $details->pluck('nilai_persen')
            ->map(fn($value) => (float) $value)
            ->values()
            ->all();

        if (empty($values)) {
            return null;
        }

        // ==============================
        // UKURAN GRAFIK
        // ==============================
        $width = 500;
        $height = 260;

        $paddingLeft = 45;
        $paddingRight = 15;
        $paddingTop = 30;
        $paddingBottom = 35;

        // ==============================
        // BUAT IMAGE
        // ==============================
        $image = imagecreatetruecolor($width, $height);

        $white = imagecolorallocate($image, 255, 255, 255);
        $gray = imagecolorallocate($image, 226, 232, 240);
        $textGray = imagecolorallocate($image, 100, 116, 139);
        $barColor = imagecolorallocate($image, 68, 114, 196);
        $black = imagecolorallocate($image, 0, 0, 0);

        imagefill($image, 0, 0, $white);

        $chartWidth = $width - $paddingLeft - $paddingRight;
        $chartHeight = $height - $paddingTop - $paddingBottom;

        // ==============================
        // AUTO SCALE SEPERTI EXCEL
        // ==============================

        $dataMin = min($values);
        $dataMax = max($values);

        /*
    |--------------------------------------------------------------------------
    | Tentukan interval sumbu Y
    |--------------------------------------------------------------------------
    |
    | Contoh:
    | Data: 87.54 - 100
    |
    | Hasil:
    | Min = 80
    | Max = 100
    | Interval = 5
    |
    */

        $range = $dataMax - $dataMin;

        if ($range <= 5) {
            $step = 1;
        } elseif ($range <= 10) {
            $step = 2;
        } elseif ($range <= 20) {
            $step = 5;
        } else {
            $step = 10;
        }

        // Bulatkan nilai minimum ke bawah
        $minValue = floor($dataMin / $step) * $step;

        // Bulatkan nilai maximum ke atas
        $maxValue = ceil($dataMax / $step) * $step;

        /*
    |--------------------------------------------------------------------------
    | Pastikan skala tidak terlalu sempit
    |--------------------------------------------------------------------------
    */

        if ($maxValue === $minValue) {
            $minValue -= $step * 2;
            $maxValue += $step * 2;
        }

        // Jangan sampai minimum kurang dari 0
        $minValue = max(0, $minValue);

        /*
    |--------------------------------------------------------------------------
    | Hitung jumlah garis/grid
    |--------------------------------------------------------------------------
    */

        $stepCount = (int) (($maxValue - $minValue) / $step);

        // Minimal 4 interval agar grafik enak dilihat
        if ($stepCount < 4) {
            $stepCount = 4;
            $maxValue = $minValue + ($step * $stepCount);
        }

        // ==============================
        // GAMBAR GARIS DAN ANGKA SUMBU Y
        // ==============================

        for ($i = 0; $i <= $stepCount; $i++) {

            $value = $minValue + ($step * $i);

            $y = $paddingTop
                + $chartHeight
                - (($value - $minValue) / ($maxValue - $minValue))
                * $chartHeight;

            // Garis horizontal
            imageline(
                $image,
                $paddingLeft,
                (int) $y,
                $width - $paddingRight,
                (int) $y,
                $gray
            );

            // Label sumbu Y
            imagestring(
                $image,
                2,
                5,
                (int) $y - 6,
                (string) $value,
                $textGray
            );
        }

        // ==============================
        // PENGATURAN BAR
        // ==============================

        $barCount = max(count($values), 1);

        $gap = 10;

        $barWidth = (
            $chartWidth - ($gap * ($barCount + 1))
        ) / $barCount;

        // ==============================
        // GAMBAR BAR
        // ==============================

        foreach ($values as $index => $value) {

            /*
        |--------------------------------------------------------------------------
        | Hitung tinggi bar berdasarkan min-max axis
        |--------------------------------------------------------------------------
        */

            $normalizedValue = (
                ($value - $minValue)
                / ($maxValue - $minValue)
            );

            $barHeight = $normalizedValue * $chartHeight;

            $x1 = $paddingLeft
                + $gap
                + $index * ($barWidth + $gap);

            $y1 = $paddingTop
                + $chartHeight
                - $barHeight;

            $x2 = $x1 + $barWidth;

            $y2 = $paddingTop + $chartHeight;

            // Gambar batang
            imagefilledrectangle(
                $image,
                (int) $x1,
                (int) $y1,
                (int) $x2,
                (int) $y2,
                $barColor
            );

            // ==============================
            // LABEL PERSENTASE DI ATAS BAR
            // ==============================

            $label = number_format($value, 2) . '%';

            $labelWidth = imagefontwidth(2) * strlen($label);

            imagestring(
                $image,
                2,
                (int) (
                    $x1
                    + ($barWidth / 2)
                    - ($labelWidth / 2)
                ),
                (int) $y1 - 14,
                $label,
                $black
            );

            // ==============================
            // LABEL U1, U2, U3, DST
            // ==============================

            $xLabel = $labels[$index] ?? '';

            $xLabelWidth = imagefontwidth(2)
                * strlen($xLabel);

            imagestring(
                $image,
                2,
                (int) (
                    $x1
                    + ($barWidth / 2)
                    - ($xLabelWidth / 2)
                ),
                $height - $paddingBottom + 6,
                $xLabel,
                $textGray
            );
        }

        // ==============================
        // SIMPAN IMAGE KE BASE64
        // ==============================

        ob_start();

        imagepng($image);

        $binary = ob_get_clean();

        imagedestroy($image);

        return 'data:image/png;base64,'
            . base64_encode($binary);
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
            'bulanan' => Carbon::create($filter['tahun'], $filter['bulan'], 1)->locale('id')->translatedFormat('F Y'),
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

    /**
     * Isi ulang paragraf "Persepsi tertinggi ... adalah X, sedangkan ... adalah Y."
     * dengan nama unsur tertinggi/terendah hasil hitung asli (bukan contoh statis
     * bawaan template), tetap mempertahankan format bold di nama unsurnya.
     */
    private function fillWordKesimpulanUnsur(\DOMDocument $dom, \DOMXPath $xpath, array $data): void
    {
        $unsurTertinggi = $data['unsurTertinggi']['nama_unsur'] ?? '-';
        $unsurTerendah = $data['unsurTerendah']['nama_unsur'] ?? '-';

        foreach ($xpath->query('//w:p') as $paragraph) {
            $text = $this->wordNodeText($xpath, $paragraph);

            if (! str_contains($text, 'Persepsi tertinggi')) {
                continue;
            }

            $namespace = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

            foreach (iterator_to_array($paragraph->childNodes) as $child) {
                if ($child->nodeType === XML_ELEMENT_NODE && $child->nodeName === 'w:r') {
                    $paragraph->removeChild($child);
                }
            }

            $addRun = function (string $value, bool $bold = false) use ($dom, $paragraph, $namespace) {
                $run = $dom->createElementNS($namespace, 'w:r');
                $rPr = $dom->createElementNS($namespace, 'w:rPr');

                $rFonts = $dom->createElementNS($namespace, 'w:rFonts');
                $rFonts->setAttribute('w:ascii', 'Bookman Old Style');
                $rFonts->setAttribute('w:hAnsi', 'Bookman Old Style');
                $rFonts->setAttribute('w:cs', 'Bookman Old Style');
                $rPr->appendChild($rFonts);

                if ($bold) {
                    $rPr->appendChild($dom->createElementNS($namespace, 'w:b'));
                }

                $sz = $dom->createElementNS($namespace, 'w:sz');
                $sz->setAttribute('w:val', '17'); // 17 half-points = 8.5pt
                $rPr->appendChild($sz);

                $run->appendChild($rPr);

                $t = $dom->createElementNS($namespace, 'w:t');
                $t->setAttribute('xml:space', 'preserve');
                $t->appendChild($dom->createTextNode($value));
                $run->appendChild($t);
                $paragraph->appendChild($run);
            };

            $addRun('Persepsi tertinggi terhadap kepuasan pelayanan adalah ');
            $addRun($unsurTertinggi, bold: true);
            $addRun(', sedangkan persepsi terendah terhadap kepuasan pelayanan adalah ');
            $addRun($unsurTerendah, bold: true);
            $addRun('.');

            return;
        }
    }

    /**
     * Sisipkan gambar chart (base64 data URI dari quickchart.io) ke dokumen Word,
     * ditaruh tepat setelah paragraf "Persepsi tertinggi ... terendah ...".
     */
    private function insertWordChartImage(\ZipArchive $archive, \DOMDocument $dom, \DOMXPath $xpath, array $data): void
    {
        $chartImageUrl = $data['chartImageUrl'] ?? null;

        if (! $chartImageUrl || ! str_starts_with($chartImageUrl, 'data:')) {
            return;
        }

        [$meta, $base64] = explode(',', $chartImageUrl, 2);
        $binary = base64_decode($base64);

        if ($binary === false) {
            return;
        }

        // ---------- 1. Tambahkan file gambar ke dalam paket docx ----------
        $mediaPath = 'word/media/chart_ikm.png';
        $archive->addFromString($mediaPath, $binary);

        // ---------- 2. Pastikan [Content_Types].xml kenal ekstensi .png ----------
        $contentTypes = $archive->getFromName('[Content_Types].xml');
        if ($contentTypes !== false && ! str_contains($contentTypes, 'Extension="png"')) {
            $contentTypes = str_replace(
                '</Types>',
                '<Default Extension="png" ContentType="image/png"/></Types>',
                $contentTypes
            );
            $archive->addFromString('[Content_Types].xml', $contentTypes);
        }

        // ---------- 3. Daftarkan relationship id buat gambar ini ----------
        $relsPath = 'word/_rels/document.xml.rels';
        $rels = $archive->getFromName($relsPath);
        $relationshipId = 'rIdChartIkm';

        if ($rels !== false && ! str_contains($rels, $relationshipId)) {
            $rels = str_replace(
                '</Relationships>',
                '<Relationship Id="' . $relationshipId . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/chart_ikm.png"/></Relationships>',
                $rels
            );
            $archive->addFromString($relsPath, $rels);
        }

        // ---------- 4. Bangun elemen <w:drawing> lalu sisipkan ke document.xml ----------
        $widthEmu = 3429000;   // ~360px @ 96dpi
        $heightEmu = 2057400;  // ~216px @ 96dpi
        $drawingParagraph = $this->buildWordDrawingParagraph($dom, $relationshipId, $widthEmu, $heightEmu);

        foreach ($xpath->query('//w:p') as $paragraph) {
            $text = $this->wordNodeText($xpath, $paragraph);

            if (! str_contains($text, 'Persepsi tertinggi')) {
                continue;
            }

            $paragraph->parentNode->insertBefore($drawingParagraph, $paragraph->nextSibling);
            return;
        }
    }

    private function buildWordDrawingParagraph(\DOMDocument $dom, string $relationshipId, int $widthEmu, int $heightEmu): \DOMElement
    {
        $w = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';
        $wp = 'http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing';
        $a = 'http://schemas.openxmlformats.org/drawingml/2006/main';
        $pic = 'http://schemas.openxmlformats.org/drawingml/2006/picture';
        $r = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';

        $paragraph = $dom->createElementNS($w, 'w:p');
        $pPr = $dom->createElementNS($w, 'w:pPr');
        $jc = $dom->createElementNS($w, 'w:jc');
        $jc->setAttribute('w:val', 'center');
        $pPr->appendChild($jc);
        $paragraph->appendChild($pPr);

        $run = $dom->createElementNS($w, 'w:r');
        $drawing = $dom->createElementNS($w, 'w:drawing');

        $inline = $dom->createElementNS($wp, 'wp:inline');
        $inline->setAttribute('distT', '0');
        $inline->setAttribute('distB', '0');
        $inline->setAttribute('distL', '0');
        $inline->setAttribute('distR', '0');

        $extent = $dom->createElementNS($wp, 'wp:extent');
        $extent->setAttribute('cx', (string) $widthEmu);
        $extent->setAttribute('cy', (string) $heightEmu);
        $inline->appendChild($extent);

        $docPr = $dom->createElementNS($wp, 'wp:docPr');
        $docPr->setAttribute('id', '1');
        $docPr->setAttribute('name', 'Chart IKM');
        $inline->appendChild($docPr);

        $graphic = $dom->createElementNS($a, 'a:graphic');
        $graphicData = $dom->createElementNS($a, 'a:graphicData');
        $graphicData->setAttribute('uri', 'http://schemas.openxmlformats.org/drawingml/2006/picture');

        $picElement = $dom->createElementNS($pic, 'pic:pic');

        $nvPicPr = $dom->createElementNS($pic, 'pic:nvPicPr');
        $cNvPr = $dom->createElementNS($pic, 'pic:cNvPr');
        $cNvPr->setAttribute('id', '0');
        $cNvPr->setAttribute('name', 'Chart IKM');
        $nvPicPr->appendChild($cNvPr);
        $nvPicPr->appendChild($dom->createElementNS($pic, 'pic:cNvPicPr'));
        $picElement->appendChild($nvPicPr);

        $blipFill = $dom->createElementNS($pic, 'pic:blipFill');
        $blip = $dom->createElementNS($a, 'a:blip');
        $blip->setAttributeNS($r, 'r:embed', $relationshipId);
        $blipFill->appendChild($blip);
        $stretch = $dom->createElementNS($a, 'a:stretch');
        $stretch->appendChild($dom->createElementNS($a, 'a:fillRect'));
        $blipFill->appendChild($stretch);
        $picElement->appendChild($blipFill);

        $spPr = $dom->createElementNS($pic, 'pic:spPr');
        $xfrm = $dom->createElementNS($a, 'a:xfrm');
        $off = $dom->createElementNS($a, 'a:off');
        $off->setAttribute('x', '0');
        $off->setAttribute('y', '0');
        $xfrm->appendChild($off);
        $ext = $dom->createElementNS($a, 'a:ext');
        $ext->setAttribute('cx', (string) $widthEmu);
        $ext->setAttribute('cy', (string) $heightEmu);
        $xfrm->appendChild($ext);
        $spPr->appendChild($xfrm);
        $prstGeom = $dom->createElementNS($a, 'a:prstGeom');
        $prstGeom->setAttribute('prst', 'rect');
        $prstGeom->appendChild($dom->createElementNS($a, 'a:avLst'));
        $spPr->appendChild($prstGeom);
        $picElement->appendChild($spPr);

        $graphicData->appendChild($picElement);
        $graphic->appendChild($graphicData);
        $inline->appendChild($graphic);
        $drawing->appendChild($inline);
        $run->appendChild($drawing);
        $paragraph->appendChild($run);

        return $paragraph;
    }

    /**
     * Menarik blok tanda tangan ke halaman sebelumnya (halaman chart) dengan
     * membuang page break eksplisit (manual break, pageBreakBefore, sectPr
     * "next page") serta paragraf kosong tepat sebelum judul, lalu menyisipkan
     * 2 baris kosong sebagai jarak antara chart dan blok tanda tangan.
     */
    private function pullWordSignatureBlockUp(\DOMDocument $dom, \DOMXPath $xpath): void
    {
        $titleParagraph = null;

        foreach ($xpath->query('//w:p') as $paragraph) {
            $text = trim($this->wordNodeText($xpath, $paragraph));

            if (str_contains(strtoupper($text), 'KEPALA PELAKSANA')) {
                $titleParagraph = $paragraph;
                break;
            }
        }

        if (! $titleParagraph) {
            return;
        }

        // 1) Buang page break yang menempel di paragraf judul itu sendiri.
        $this->stripWordPageBreak($dom, $xpath, $titleParagraph);

        // 2) Mundur ke atas: hapus SEMUA paragraf kosong di antara chart dan judul
        //    (termasuk yang memuat page break/sectPr) sampai mentok konten sebelumnya.
        $current = $titleParagraph->previousSibling;

        while ($current !== null) {
            $previous = $current->previousSibling;

            if ($current->nodeType === XML_ELEMENT_NODE) {
                if ($current->nodeName !== 'w:p') {
                    break; // tabel / elemen lain
                }

                $isEmptyText = trim($this->wordNodeText($xpath, $current)) === '';
                $hasDrawing = $xpath->query('.//w:drawing', $current)->length > 0;

                if (! $isEmptyText || $hasDrawing) {
                    break; // mentok konten (gambar chart / teks kesimpulan)
                }

                $current->parentNode->removeChild($current);
            }

            $current = $previous;
        }

        // 3) Sisipkan tepat 2 baris kosong (setara 2x enter) sebagai jarak
        //    antara gambar chart dan blok tanda tangan.
        $this->insertWordBlankParagraphs($dom, $titleParagraph, 2);
    }

    /**
     * Sisipkan sejumlah paragraf kosong tepat sebelum paragraf referensi.
     * Dipakai buat memberi jarak (setara enter) antara gambar chart dan
     * blok tanda tangan "KEPALA PELAKSANA ...".
     */
    private function insertWordBlankParagraphs(\DOMDocument $dom, \DOMNode $referenceParagraph, int $count): void
    {
        $namespace = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

        for ($i = 0; $i < $count; $i++) {
            $blank = $dom->createElementNS($namespace, 'w:p');
            $referenceParagraph->parentNode->insertBefore($blank, $referenceParagraph);
        }
    }

    /** Buang manual page break, pageBreakBefore, dan ubah sectPr jadi "continuous". */
    private function stripWordPageBreak(\DOMDocument $dom, \DOMXPath $xpath, \DOMNode $paragraph): void
    {
        $namespace = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';
        $pPr = $xpath->query('./w:pPr', $paragraph)->item(0);

        if ($pPr) {
            foreach (iterator_to_array($xpath->query('./w:pageBreakBefore', $pPr)) as $node) {
                $pPr->removeChild($node);
            }

            $sectPr = $xpath->query('./w:sectPr', $pPr)->item(0);
            if ($sectPr) {
                $type = $xpath->query('./w:type', $sectPr)->item(0);
                if (! $type instanceof \DOMElement) {
                    $type = $dom->createElementNS($namespace, 'w:type');
                    $sectPr->insertBefore($type, $sectPr->firstChild);
                }
                $type->setAttribute('w:val', 'continuous');
            }
        }

        foreach (iterator_to_array($xpath->query('.//w:br', $paragraph)) as $br) {
            if ($br instanceof \DOMElement && $br->getAttribute('w:type') === 'page') {
                $br->parentNode->removeChild($br);
            }
        }
    }

    /** Set keepNext/keepLines agar blok judul–nama–NIP tidak pecah antar halaman. */
    private function keepWordSignatureBlockTogether(\DOMDocument $dom, \DOMXPath $xpath): void
    {
        $paragraphList = iterator_to_array($xpath->query('//w:p'));
        $titleIndex = null;
        $nipIndex = null;

        foreach ($paragraphList as $index => $paragraph) {
            $text = trim($this->wordNodeText($xpath, $paragraph));

            if ($titleIndex === null && str_contains(strtoupper($text), 'KEPALA PELAKSANA')) {
                $titleIndex = $index;
                continue;
            }

            if ($titleIndex !== null && str_starts_with($text, 'NIP')) {
                $nipIndex = $index;
                break;
            }
        }

        if ($titleIndex === null || $nipIndex === null) {
            return;
        }

        for ($index = $titleIndex; $index <= $nipIndex; $index++) {
            $this->setWordParagraphKeepTogether($dom, $xpath, $paragraphList[$index], $index < $nipIndex);
        }
    }

    private function setWordParagraphKeepTogether(\DOMDocument $dom, \DOMXPath $xpath, \DOMNode $paragraph, bool $keepWithNext): void
    {
        $namespace = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

        $pPr = $xpath->query('./w:pPr', $paragraph)->item(0);
        if (! $pPr instanceof \DOMElement) {
            $pPr = $dom->createElementNS($namespace, 'w:pPr');
            $paragraph->insertBefore($pPr, $paragraph->firstChild);
        }

        // keepNext/keepLines harus berada di awal pPr (tepat setelah pStyle) sesuai skema OOXML.
        $insertAfter = function (\DOMElement $newNode) use ($pPr, $xpath) {
            $pStyle = $xpath->query('./w:pStyle', $pPr)->item(0);
            if ($pStyle && $pStyle->nextSibling) {
                $pPr->insertBefore($newNode, $pStyle->nextSibling);
            } elseif ($pStyle) {
                $pPr->appendChild($newNode);
            } elseif ($pPr->firstChild) {
                $pPr->insertBefore($newNode, $pPr->firstChild);
            } else {
                $pPr->appendChild($newNode);
            }
        };

        if ($keepWithNext && $xpath->query('./w:keepNext', $pPr)->length === 0) {
            $insertAfter($dom->createElementNS($namespace, 'w:keepNext'));
        }

        if ($xpath->query('./w:keepLines', $pPr)->length === 0) {
            $insertAfter($dom->createElementNS($namespace, 'w:keepLines'));
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HasilIkm;
use App\Models\JawabanSurvei;
use App\Models\Survei;
use App\Models\UnsurPelayanan;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class HasilIkmController extends Controller
{
    public function index(Request $request)
    {
        $tahunOptions = Survei::selectRaw('YEAR(created_at) as tahun')
            ->distinct()
            ->orderByDesc('tahun')
            ->pluck('tahun');

        $filter = $this->resolveFilter($request, $tahunOptions);

        $hasil = $this->hitung($filter['tipe'], $filter['tahun'], $filter['bulan'], $filter['triwulan']);
        if ($hasil['jumlah_responden'] > 0) {
            $this->simpan($hasil);
        }

        return view('admin.hasilIkm', array_merge($filter, [
            'tahunOptions' => $tahunOptions,
            'hasil' => $hasil,
        ]));
    }

    public function hitungUlang(Request $request)
    {
        $filter = $this->resolveFilter($request, collect());
        $hasil = $this->hitung($filter['tipe'], $filter['tahun'], $filter['bulan'], $filter['triwulan']);

        if ($hasil['jumlah_responden'] === 0) {
            return back()->with('error', 'Tidak ada data survei pada periode ini, snapshot tidak disimpan.');
        }

        $this->simpan($hasil);

        return back()->with('success', 'Snapshot Hasil IKM periode ini berhasil disimpan.');
    }

    public function downloadPdf(Request $request)
    {
        $filter = $this->resolveFilter($request, collect());
        $hasil = $this->hitung($filter['tipe'], $filter['tahun'], $filter['bulan'], $filter['triwulan']);

        $pdf = Pdf::loadView('components.admin.pdfHasilIkm', ['hasil' => $hasil])->setPaper('a4', 'portrait');

        return $pdf->download("hasil-ikm-{$filter['tipe']}-{$filter['tahun']}.pdf");
    }

    private function resolveFilter(Request $request, Collection $tahunOptions): array
    {
        return [
            'tipe' => $request->get('tipe', 'tahunan'),
            'tahun' => $request->integer('tahun') ?: ($tahunOptions->first() ?? now()->year),
            'bulan' => $request->integer('bulan') ?: now()->month,
            'triwulan' => $request->integer('triwulan') ?: 1,
        ];
    }

    /**
     * Hitung Nilai IKM untuk satu rentang periode (tahunan/bulanan/triwulanan),
     * berdasarkan created_at pada tabel surveis.
     *
     * Skala penilaian jawaban yang dipakai aplikasi ini: 1 - 5.
     * Nilai penimbang (pengali akhir) untuk skala 1-5 adalah 20
     * (rumus umum: 100 / jumlah skala = 100 / 5 = 20).
     */
    private function hitung(string $tipe, int $tahun, ?int $bulan = null, ?int $triwulan = null): array
    {
        [$mulai, $selesai] = $this->rentangTanggal($tipe, $tahun, $bulan, $triwulan);

        $surveiQuery = Survei::whereDate('created_at', '>=', $mulai)
            ->whereDate('created_at', '<=', $selesai);

        $surveiIds = $surveiQuery->pluck('id');
        $surveiIdTerakhir = $surveiIds->max();

        $unsurAktif = UnsurPelayanan::where('status', 'active')->orderBy('id')->get();
        $jumlahUnsur = max($unsurAktif->count(), 1);
        $bobotNilai = 1 / $jumlahUnsur;

        $details = [];
        $totalNrrTertimbang = 0;

        foreach ($unsurAktif as $index => $unsur) {
            $jawabanQuery = JawabanSurvei::whereIn('survei_id', $surveiIds)
                ->where('unsur_pelayanan_id', $unsur->id);

            $jumlahResponden = (clone $jawabanQuery)->count();
            $nilaiRataRata = $jumlahResponden > 0 ? (float) (clone $jawabanQuery)->avg('nilai') : 0;
            $nrrTertimbang = round($nilaiRataRata * $bobotNilai, 4);
            $totalNrrTertimbang += $nrrTertimbang;

            $details[] = [
                'kode' => 'U' . ($index + 1),
                'unsur_pelayanan_id' => $unsur->id,
                'nama_unsur' => $unsur->nama_unsur,
                'jumlah_responden' => $jumlahResponden,
                'nilai_rata_rata' => $nilaiRataRata,
                'bobot_nilai' => $bobotNilai,
                'nrr_tertimbang' => $nrrTertimbang,
                'mutu_unsur' => $this->mutuDariNrr($nilaiRataRata),
            ];
        }

        $nilaiSkm = round($totalNrrTertimbang, 4);

        // Skala 1-5 -> pengali 20 (bukan 25, yang itu khusus skala 1-4)
        $nilaiIkm = round($nilaiSkm * 20, 2);

        $mutuPelayanan = $this->mutuDariNilaiIkm($nilaiIkm);

        return [
            'tipe' => $tipe,
            'tahun' => $tahun,
            'bulan' => $bulan,
            'triwulan' => $triwulan,
            'mulai' => $mulai,
            'selesai' => $selesai,
            'survei_id_terakhir' => $surveiIdTerakhir,
            'jumlah_responden' => $surveiIds->count(),
            'nilai_skm' => $nilaiSkm,
            'nilai_ikm' => $nilaiIkm,
            'mutu_pelayanan' => $mutuPelayanan,
            'kinerja_pelayanan' => $this->kinerjaDariMutu($mutuPelayanan),
            'details' => $details,
        ];
    }

    /**
     * Simpan snapshot hasil hitung ke hasil_ikms & hasil_ikm_details.
     * survei_id cuma penanda (survei terakhir dalam rentang itu), bukan kunci pencarian.
     */
    private function simpan(array $hasil): ?HasilIkm
    {
        if (! $hasil['survei_id_terakhir']) {
            return null;
        }

        return DB::transaction(function () use ($hasil) {
            HasilIkm::where('survei_id', $hasil['survei_id_terakhir'])->delete();
            $hasilIkm = HasilIkm::create([
                'survei_id' => $hasil['survei_id_terakhir'],
                'nilai_skm' => $hasil['nilai_skm'],
                'nilai_ikm' => $hasil['nilai_ikm'],
                'mutu_pelayanan' => $hasil['mutu_pelayanan'],
                'kinerja_pelayanan' => $hasil['kinerja_pelayanan'],
            ]);

            foreach ($hasil['details'] as $detail) {
                $hasilIkm->details()->create([
                    'unsur_pelayanan_id' => $detail['unsur_pelayanan_id'],
                    'jumlah_responden' => $detail['jumlah_responden'],
                    'nilai_rata_rata' => $detail['nilai_rata_rata'],
                    'bobot_nilai' => $detail['bobot_nilai'],
                    'nrr_tertimbang' => $detail['nrr_tertimbang'],
                    'mutu_unsur' => $detail['mutu_unsur'],
                ]);
            }

            return $hasilIkm;
        });
    }

    private function rentangTanggal(string $tipe, int $tahun, ?int $bulan, ?int $triwulan): array
    {
        return match ($tipe) {
            'bulanan' => [
                Carbon::create($tahun, $bulan ?? 1, 1)->startOfMonth(),
                Carbon::create($tahun, $bulan ?? 1, 1)->endOfMonth(),
            ],
            'triwulanan' => $this->rentangTriwulan($tahun, $triwulan ?? 1),
            default => [
                Carbon::create($tahun, 1, 1)->startOfYear(),
                Carbon::create($tahun, 12, 31)->endOfYear(),
            ],
        };
    }

    private function rentangTriwulan(int $tahun, int $triwulan): array
    {
        $bulanMulai = (($triwulan - 1) * 3) + 1;
        $mulai = Carbon::create($tahun, $bulanMulai, 1)->startOfMonth();
        $selesai = (clone $mulai)->addMonths(2)->endOfMonth();

        return [$mulai, $selesai];
    }

    /**
     * Mutu per unsur, berdasarkan NRR (nilai rata-rata jawaban, skala 1-5).
     * Interval dibagi rata pada rentang 1.00 - 5.00.
     */
    private function mutuDariNrr(float $nrr): string
    {
        return match (true) {
            $nrr >= 4.20 => 'A', // Sangat Baik
            $nrr >= 3.40 => 'B', // Baik
            $nrr >= 2.60 => 'C', // Kurang Baik
            default => 'D',       // Tidak Baik
        };
    }

    /**
     * Mutu pelayanan keseluruhan, berdasarkan Nilai IKM (hasil x 20, skala 0-100).
     */
    private function mutuDariNilaiIkm(float $nilaiIkm): string
    {
        return match (true) {
            $nilaiIkm >= 84 => 'A', // Sangat Baik
            $nilaiIkm >= 68 => 'B', // Baik
            $nilaiIkm >= 52 => 'C', // Kurang Baik
            default => 'D',          // Tidak Baik
        };
    }

    private function kinerjaDariMutu(string $mutu): string
    {
        return [
            'A' => 'Sangat Baik',
            'B' => 'Baik',
            'C' => 'Kurang Baik',
            'D' => 'Tidak Baik',
        ][$mutu] ?? 'Tidak Baik';
    }
}
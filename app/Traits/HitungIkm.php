<?php

namespace App\Traits;

use App\Models\HasilIkm;
use App\Models\JawabanSurvei;
use App\Models\Survei;
use App\Models\UnsurPelayanan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait HitungIkm
{
    /**
     * Hitung Nilai IKM untuk satu rentang periode (tahunan/bulanan/triwulanan),
     * berdasarkan created_at pada tabel surveis.
     */
    protected function hitungIkm(string $tipe, int $tahun, ?int $bulan = null, ?int $triwulan = null): array
    {
        [$mulai, $selesai] = $this->rentangTanggalIkm($tipe, $tahun, $bulan, $triwulan);

        $surveiQuery = Survei::whereDate('created_at', '>=', $mulai)
            ->whereDate('created_at', '<=', $selesai);

        $surveiIds = $surveiQuery->pluck('id');
        $surveiIdTerakhir = $surveiIds->max();

        $unsurAktif = UnsurPelayanan::where('status', 'active')->orderBy('id')->get();
        $jumlahUnsur = max($unsurAktif->count(), 1);
        $bobotNilai = 1 / $jumlahUnsur;
        $bobotNilaiDisplay = round($bobotNilai, 4);

        $details = [];
        $totalNrrTertimbang = 0;

        foreach ($unsurAktif as $index => $unsur) {
            $jawabanQuery = JawabanSurvei::whereIn('survei_id', $surveiIds)
                ->where('unsur_pelayanan_id', $unsur->id);

            $jumlahResponden = (clone $jawabanQuery)->count();
            $nilaiRataRataAsli = $jumlahResponden > 0 ? (float) ((clone $jawabanQuery)->avg('nilai')) : 0;
            $nilaiRataRata = round($nilaiRataRataAsli, 3);
            $nrrTertimbangAsli = $nilaiRataRataAsli * $bobotNilai;
            $nrrTertimbang = round($nrrTertimbangAsli, 4);
            $totalNrrTertimbang += $nrrTertimbangAsli;

            $details[] = [
                'kode' => 'U' . ($index + 1),
                'unsur_pelayanan_id' => $unsur->id,
                'nama_unsur' => $unsur->nama_unsur,
                'jumlah_responden' => $jumlahResponden,
                'nilai_rata_rata' => $nilaiRataRata,
                'bobot_nilai' => $bobotNilaiDisplay,
                'nrr_tertimbang' => $nrrTertimbang,
                'mutu_unsur' => $this->mutuDariNrr($nilaiRataRata),
            ];
        }

        $nilaiSkm = round($totalNrrTertimbang, 3);
        $nilaiIkm = (float) number_format($totalNrrTertimbang * 20, 2, '.', '');
        $mutuPelayanan = $this->mutuDariNilaiIkm($nilaiIkm);

        return [
            'tipe' => $tipe,
            'tahun' => $tahun,
            'bulan' => $bulan,
            'triwulan' => $triwulan,
            'mulai' => $mulai,
            'selesai' => $selesai,
            'survei_id_terakhir' => $surveiIdTerakhir,
            'survei_ids' => $surveiIds->values()->all(),
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
    protected function simpanSnapshotIkm(array $hasil): ?HasilIkm
    {
        if (! $hasil['survei_id_terakhir']) {
            return null;
        }

        return DB::transaction(function () use ($hasil) {
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

    /**
     * Ambil & normalkan filter tipe/tahun/bulan/triwulan dari query string.
     */
    protected function resolveIkmFilter(Request $request, Collection $tahunOptions): array
    {
        return [
            'tipe' => $request->get('tipe', 'tahunan'),
            'tahun' => $request->integer('tahun') ?: ($tahunOptions->first() ?? now()->year),
            'bulan' => $request->integer('bulan') ?: now()->month,
            'triwulan' => $request->integer('triwulan') ?: 1,
        ];
    }

    private function rentangTanggalIkm(string $tipe, int $tahun, ?int $bulan, ?int $triwulan): array
    {
        return match ($tipe) {
            'bulanan' => [
                Carbon::create($tahun, $bulan ?? 1, 1)->startOfMonth(),
                Carbon::create($tahun, $bulan ?? 1, 1)->endOfMonth(),
            ],
            'triwulanan' => $this->rentangTriwulanIkm($tahun, $triwulan ?? 1),
            default => [
                Carbon::create($tahun, 1, 1)->startOfYear(),
                Carbon::create($tahun, 12, 31)->endOfYear(),
            ],
        };
    }

    private function rentangTriwulanIkm(int $tahun, int $triwulan): array
    {
        $bulanMulai = (($triwulan - 1) * 3) + 1;
        $mulai = Carbon::create($tahun, $bulanMulai, 1)->startOfMonth();
        $selesai = (clone $mulai)->addMonths(2)->endOfMonth();

        return [$mulai, $selesai];
    }

    private function mutuDariNrr(float $nrr): string
    {
        return match (true) {
            $nrr >= 3.26 => 'A',
            $nrr >= 2.51 => 'B',
            $nrr >= 1.76 => 'C',
            default => 'D',
        };
    }

    private function mutuDariNilaiIkm(float $nilaiIkm): string
    {
        return match (true) {
            $nilaiIkm >= 88.31 => 'A',
            $nilaiIkm >= 76.61 => 'B',
            $nilaiIkm >= 65.00 => 'C',
            default => 'D',
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
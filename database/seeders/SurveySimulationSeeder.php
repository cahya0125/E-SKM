<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SurveySimulationSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $layananIds = DB::table('jenis_layanans')->pluck('id')->all();
        $periodeIds = DB::table('priode_surveis')->pluck('id')->all();
        $unsurIds = DB::table('unsur_pelayanans')->pluck('id')->all();
        $respondens = DB::table('respondens')->get();
        $saran = ['Pelayanan sudah baik dan informatif.', 'Mohon waktu pelayanan dapat dibuat lebih cepat.', 'Fasilitas ruang tunggu perlu ditingkatkan.'];

        foreach ($periodeIds as $periodIndex => $periodeId) {
            $hasilId = DB::table('hasil_ikms')->insertGetId([
                'priode_survei_id' => $periodeId,
                'nilai_skm' => 0,
                'nilai_ikm' => 0,
                'mutu_pelayanan' => 'D',
                'kinerja_pelayanan' => 'Tidak Baik',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $nilaiPerUnsur = [];
            $totalNilai = 0;
            $jumlahJawaban = 0;

            foreach ($respondens as $respondenIndex => $responden) {
                $surveiId = DB::table('surveis')->insertGetId([
                    'priode_survei_id' => $periodeId,
                    'jenis_layanan_id' => $layananIds[($respondenIndex + $periodIndex) % count($layananIds)],
                    'responden_id' => $responden->id,
                    'nama_responden' => $responden->nama,
                    'alamat_responden' => 'Kota Bandung',
                    'created_at' => $now->copy()->subDays($respondenIndex),
                    'updated_at' => $now,
                ]);

                foreach ($unsurIds as $unsurIndex => $unsurId) {
                    $nilai = 3 + (($respondenIndex + $unsurIndex + $periodIndex) % 3);
                    DB::table('jawaban_surveis')->insert([
                        'survei_id' => $surveiId,
                        'unsur_pelayanan_id' => $unsurId,
                        'nilai' => $nilai,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    $nilaiPerUnsur[$unsurIndex] = ($nilaiPerUnsur[$unsurIndex] ?? 0) + $nilai;
                    $totalNilai += $nilai;
                    $jumlahJawaban++;
                }

                if ($respondenIndex % 4 === 0) {
                    DB::table('saran_kritiks')->insert([
                        'survei_id' => $surveiId,
                        'saran' => $saran[$respondenIndex % count($saran)],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            $totalRataRata = $totalNilai / $jumlahJawaban;
            $nilaiIkm = round($totalRataRata * 20, 2);
            [$mutu, $kinerja] = $this->mutu($nilaiIkm);
            DB::table('hasil_ikms')->where('id', $hasilId)->update([
                'nilai_skm' => round($totalRataRata, 2),
                'nilai_ikm' => $nilaiIkm,
                'mutu_pelayanan' => $mutu,
                'kinerja_pelayanan' => $kinerja,
                'updated_at' => $now,
            ]);

            foreach ($unsurIds as $unsurIndex => $unsurId) {
                $rataRata = $nilaiPerUnsur[$unsurIndex] / $respondens->count();
                [$mutuUnsur] = $this->mutu(round($rataRata * 20, 2));
                DB::table('hasil_ikm_details')->insert([
                    'unsur_pelayanan_id' => $unsurId,
                    'hasil_ikm_id' => $hasilId,
                    'jumlah_responden' => $respondens->count(),
                    'nilai_rata_rata' => round($rataRata, 2),
                    'bobot_nilai' => round(1 / count($unsurIds), 4),
                    'nrr_tertimbang' => round($rataRata * (1 / count($unsurIds)), 4),
                    'mutu_unsur' => $mutuUnsur,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    private function mutu(float $nilaiIkm): array
    {
        return match (true) {
            $nilaiIkm >= 88.31 => ['A', 'Sangat Baik'],
            $nilaiIkm >= 76.61 => ['B', 'Baik'],
            $nilaiIkm >= 65.00 => ['C', 'Kurang Baik'],
            default => ['D', 'Tidak Baik'],
        };
    }
}

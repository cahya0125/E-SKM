<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        foreach ([
            ['nama_unsur' => 'Persyaratan', 'pertanyaan' => 'Bagaimana pendapat Anda tentang kesesuaian persyaratan pelayanan?'],
            ['nama_unsur' => 'Prosedur', 'pertanyaan' => 'Bagaimana pemahaman Anda tentang prosedur pelayanan?'],
            ['nama_unsur' => 'Waktu Pelayanan', 'pertanyaan' => 'Bagaimana pendapat Anda tentang kecepatan waktu pelayanan?'],
            ['nama_unsur' => 'Biaya/Tarif', 'pertanyaan' => 'Bagaimana kewajaran biaya atau tarif pelayanan?'],
            ['nama_unsur' => 'Produk Layanan', 'pertanyaan' => 'Bagaimana kesesuaian produk layanan yang diterima?'],
            ['nama_unsur' => 'Kompetensi Petugas', 'pertanyaan' => 'Bagaimana kompetensi petugas dalam memberikan pelayanan?'],
            ['nama_unsur' => 'Perilaku Petugas', 'pertanyaan' => 'Bagaimana perilaku petugas dalam memberikan pelayanan?'],
            ['nama_unsur' => 'Penanganan Pengaduan', 'pertanyaan' => 'Bagaimana kualitas penanganan pengaduan dan saran?'],
            ['nama_unsur' => 'Sarana dan Prasarana', 'pertanyaan' => 'Bagaimana kualitas sarana dan prasarana pelayanan?'],
        ] as $item) {
            DB::table('unsur_pelayanans')->insert($item + ['status' => 'active', 'created_at' => $now, 'updated_at' => $now]);
        }

        $pendidikan = ['SMA', 'D3', 'S1', 'S2'];
        $pekerjaan = ['ASN', 'Swasta', 'Wirausaha', 'Pelajar/Mahasiswa'];

        foreach (range(1, 53) as $index) {
            DB::table('respondens')->insert([
            'nama' => 'Responden '.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
            'jenis_kelamin' => $index % 2 === 1 ? 'L' : 'P',
            'usia' => (string) (21 + (($index - 1) * 3) % 39),
            'pendidikan' => $pendidikan[($index - 1) % count($pendidikan)],
            'pekerjaan' => $pekerjaan[($index - 1) % count($pekerjaan)],
            'no_hp' => '0812'.str_pad((string) $index, 8, '0', STR_PAD_LEFT),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}

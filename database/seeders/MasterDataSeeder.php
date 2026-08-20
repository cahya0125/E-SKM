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
            ['nama_layanan' => 'Penanganan Darurat Bencana', 'deskripsi' => 'Layanan informasi dan penanganan kedaruratan bencana.', 'status' => 'active'],
            ['nama_layanan' => 'Pelatihan Kebencanaan', 'deskripsi' => 'Pelatihan kesiapsiagaan dan mitigasi bencana.', 'status' => 'active'],
            ['nama_layanan' => 'Permohonan Informasi Publik', 'deskripsi' => 'Layanan informasi publik BPBD Kota Bandung.', 'status' => 'active'],
        ] as $item) {
            DB::table('jenis_layanans')->insert($item + ['created_at' => $now, 'updated_at' => $now]);
        }

        foreach ([
            ['nama_priode' => 'Survei Semester I 2026', 'tanggal_mulai' => '2026-01-01', 'tanggal_selesai' => '2026-06-30', 'status' => 'inactive'],
            ['nama_priode' => 'Survei Semester II 2026', 'tanggal_mulai' => '2026-07-01', 'tanggal_selesai' => '2026-12-31', 'status' => 'active'],
        ] as $item) {
            DB::table('priode_surveis')->insert($item + ['created_at' => $now, 'updated_at' => $now]);
        }

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

        $nama = ['Andi Setiawan', 'Budi Hartono', 'Citra Lestari', 'Dewi Anggraini', 'Eko Prasetyo', 'Fitri Nuraini', 'Galih Ramadhan', 'Hana Mulyani', 'Irfan Maulana', 'Joko Susanto', 'Kiki Amelia', 'Lina Marlina', 'Maman Suherman', 'Nia Kurniasih', 'Oki Firmansyah'];
        $pendidikan = ['SMA', 'Diploma', 'S1', 'S2'];
        $pekerjaan = ['PNS', 'Karyawan Swasta', 'Wiraswasta', 'Pelajar/Mahasiswa'];

        foreach ($nama as $index => $namaResponden) {
            DB::table('respondens')->insert([
                'nama' => $namaResponden,
                'jenis_kelamin' => $index % 2 === 0 ? 'L' : 'P',
                'usia' => (string) (21 + ($index * 3) % 39),
                'pendidikan' => $pendidikan[$index % count($pendidikan)],
                'pekerjaan' => $pekerjaan[$index % count($pekerjaan)],
                'no_hp' => '0812'.str_pad((string) ($index + 1), 8, '0', STR_PAD_LEFT),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}

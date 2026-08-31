<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class GrafikController extends Controller
{
    public function index()
    {
        /*
         * Tren IKM per tahun.
         * CATATAN: tabel priode_surveis sudah dihapus sesuai keputusan tim,
         * dan hasil_ikms tidak lagi memiliki priode_survei_id/survei_id.
         * Maka tren dihitung langsung dari jawaban survei:
         * IKM = rata-rata nilai (1–5) × 20, dikelompokkan per tahun.
         */
        $trend = DB::table('jawaban_surveis')
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('ROUND(AVG(nilai) * 20, 2) as value')
            )
            ->groupBy('year')
            ->orderBy('year')
            ->get();

        // Rata-rata nilai per unsur (skala 100)
        $elements = DB::table('jawaban_surveis')
            ->join('unsur_pelayanans', 'unsur_pelayanans.id', '=', 'jawaban_surveis.unsur_pelayanan_id')
            ->select('unsur_pelayanans.nama_unsur', DB::raw('AVG(jawaban_surveis.nilai * 20) as nilai'))
            ->groupBy('unsur_pelayanans.id', 'unsur_pelayanans.nama_unsur')
            ->orderBy('unsur_pelayanans.id')
            ->get();

        // Distribusi jenis kelamin responden
        $gender = DB::table('respondens')
            ->select('jenis_kelamin', DB::raw('COUNT(*) as total'))
            ->groupBy('jenis_kelamin')
            ->orderBy('jenis_kelamin')
            ->get();

        // Distribusi pekerjaan responden
        $jobs = DB::table('respondens')
            ->select('pekerjaan', DB::raw('COUNT(*) as total'))
            ->groupBy('pekerjaan')
            ->orderByDesc('total')
            ->get();

        $data = [
            'trend' => [
                'labels' => $trend->pluck('year')->values(),
                'values' => $trend->pluck('value')->map(fn ($v) => (float) $v)->values(),
            ],
            'elements' => [
                'labels' => $elements->pluck('nama_unsur')->values(),
                'values' => $elements->pluck('nilai')->map(fn ($v) => round((float) $v, 2))->values(),
            ],
            'gender' => [
                'labels' => $gender->pluck('jenis_kelamin')
                    ->map(fn ($v) => $v === 'L' ? 'Laki-laki' : 'Perempuan')->values(),
                'values' => $gender->pluck('total')->map(fn ($v) => (int) $v)->values(),
            ],
            'jobs' => [
                'labels' => $jobs->pluck('pekerjaan')->values(),
                'values' => $jobs->pluck('total')->map(fn ($v) => (int) $v)->values(),
            ],
        ];

        return view('admin.grafik', compact('data'));
    }
}
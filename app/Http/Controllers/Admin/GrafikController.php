<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class GrafikController extends Controller
{
    public function index()
    {
        $trend = DB::table('hasil_ikms')
            ->leftJoin('surveis', 'surveis.id', '=', 'hasil_ikms.survei_id')
            ->leftJoin('priode_surveis', 'priode_surveis.id', '=', 'hasil_ikms.priode_survei_id')
            ->where(function ($query) {
                $query->whereNotNull('surveis.created_at')
                    ->orWhereNotNull('priode_surveis.tanggal_mulai');
            })
            ->orderByRaw('COALESCE(surveis.created_at, priode_surveis.tanggal_mulai)')
            ->get([
                DB::raw('COALESCE(surveis.created_at, priode_surveis.tanggal_mulai) as tanggal'),
                'hasil_ikms.nilai_ikm',
            ])
            ->groupBy(fn ($item) => substr($item->tanggal, 0, 4))
            ->map(fn ($items, $year) => ['year' => $year, 'value' => round((float) $items->avg('nilai_ikm'), 2)])
            ->values();

        $elements = DB::table('jawaban_surveis')
            ->join('unsur_pelayanans', 'unsur_pelayanans.id', '=', 'jawaban_surveis.unsur_pelayanan_id')
            ->select('unsur_pelayanans.nama_unsur', DB::raw('AVG(jawaban_surveis.nilai * 20) as nilai'))
            ->groupBy('unsur_pelayanans.id', 'unsur_pelayanans.nama_unsur')
            ->orderBy('unsur_pelayanans.id')->get();

        $gender = DB::table('respondens')
            ->select('jenis_kelamin', DB::raw('COUNT(*) as total'))
            ->groupBy('jenis_kelamin')->orderBy('jenis_kelamin')->get();

        $jobs = DB::table('respondens')
            ->select('pekerjaan', DB::raw('COUNT(*) as total'))
            ->groupBy('pekerjaan')->orderByDesc('total')->get();

        $data = [
            'trend' => ['labels' => $trend->pluck('year')->values(), 'values' => $trend->pluck('value')->values()],
            'elements' => ['labels' => $elements->pluck('nama_unsur')->values(), 'values' => $elements->pluck('nilai')->map(fn ($value) => round((float) $value, 2))->values()],
            'gender' => ['labels' => $gender->pluck('jenis_kelamin')->map(fn ($value) => $value === 'L' ? 'Laki-laki' : 'Perempuan')->values(), 'values' => $gender->pluck('total')->map(fn ($value) => (int) $value)->values()],
            'jobs' => ['labels' => $jobs->pluck('pekerjaan')->values(), 'values' => $jobs->pluck('total')->map(fn ($value) => (int) $value)->values()],
        ];

        return view('admin.grafik', compact('data'));
    }
}
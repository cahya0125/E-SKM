<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Survei;
use App\Traits\HitungIkm;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class HasilIkmController extends Controller
{
    use HitungIkm;

    public function index(Request $request)
    {
        $tahunOptions = Survei::selectRaw('YEAR(created_at) as tahun')
            ->distinct()
            ->orderByDesc('tahun')
            ->pluck('tahun');

        $filter = $this->resolveIkmFilter($request, $tahunOptions);

        $hasil = $this->hitungIkm($filter['tipe'], $filter['tahun'], $filter['bulan'], $filter['triwulan']);

        return view('admin.hasilIkm', array_merge($filter, [
            'tahunOptions' => $tahunOptions,
            'hasil' => $hasil,
        ]));
    }

    public function hitungUlang(Request $request)
    {
        $filter = $this->resolveIkmFilter($request, collect());
        $hasil = $this->hitungIkm($filter['tipe'], $filter['tahun'], $filter['bulan'], $filter['triwulan']);

        if ($hasil['jumlah_responden'] === 0) {
            return back()->with('error', 'Tidak ada data survei pada periode ini, snapshot tidak disimpan.');
        }

        $this->simpanSnapshotIkm($hasil);

        return back()->with('success', 'Snapshot Hasil IKM periode ini berhasil disimpan.');
    }

    public function downloadPdf(Request $request)
    {
        $filter = $this->resolveIkmFilter($request, collect());
        $hasil = $this->hitungIkm($filter['tipe'], $filter['tahun'], $filter['bulan'], $filter['triwulan']);

        $pdf = Pdf::loadView('admin.hasil-ikm.pdf', ['hasil' => $hasil])->setPaper('a4', 'portrait');

        return $pdf->download("hasil-ikm-{$filter['tipe']}-{$filter['tahun']}.pdf");
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Respondens;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RespondensController extends Controller
{
    public function index()
    {
        $respondens = Respondens::query()
            ->leftJoin('surveis', function ($join) {
                $join->on('surveis.responden_id', '=', 'respondens.id')
                    ->whereRaw('surveis.id = (select max(id) from surveis as latest_survei where latest_survei.responden_id = respondens.id)');
            })
            ->select('respondens.*', 'surveis.id as survei_id', 'surveis.jenis_layanan', 'surveis.created_at as survei_created_at')
            ->latest('respondens.id')->get()->map(fn ($responden) => $this->present($responden))->values();

        if (request()->expectsJson()) {
            return response()->json($respondens);
        }

        return view('admin.respondens', compact('respondens'));
    }

    public function store(Request $request)
    {
        $responden = Respondens::create($this->validated($request));

        return response()->json($this->present($responden), 201);
    }

    public function update(Request $request, Respondens $respondens)
    {
        $respondens->update($this->validated($request));

        $survei = DB::table('surveis')->where('responden_id', $respondens->id)->latest('id')->first();
        if ($survei) {
            if ($request->filled('jenis_layanan')) {
                DB::table('surveis')->where('id', $survei->id)->update([
                    'jenis_layanan' => $request->input('jenis_layanan'),
                    'updated_at' => now(),
                ]);
            }

            foreach ($request->input('ratings', []) as $rating) {
                $unsurId = DB::table('unsur_pelayanans')->where('nama_unsur', $rating['label'] ?? '')->value('id');
                if ($unsurId) {
                    DB::table('jawaban_surveis')->where('survei_id', $survei->id)->where('unsur_pelayanan_id', $unsurId)->update([
                        'nilai' => (int) ($rating['value'] ?? 0),
                        'updated_at' => now(),
                    ]);
                }
            }

            if ($request->has('saran')) {
                DB::table('saran_kritiks')->updateOrInsert(
                    ['survei_id' => $survei->id],
                    ['saran' => $request->input('saran'), 'updated_at' => now(), 'created_at' => now()],
                );
            }
        }

        return response()->json($this->present($respondens->refresh()));
    }

    public function destroy(Respondens $respondens)
    {
        $respondens->delete();

        return response()->json(['message' => 'Responden berhasil dihapus.']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'usia' => ['required', 'string', 'max:30'],
            'pendidikan' => ['required', 'string', 'max:255'],
            'pekerjaan' => ['required', 'string', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:30'],
        ]);
    }

    private function present($responden): array
    {
        $ratings = collect();
        $saran = null;

        if ($responden->survei_id) {
            $ratings = DB::table('jawaban_surveis')
                ->join('unsur_pelayanans', 'unsur_pelayanans.id', '=', 'jawaban_surveis.unsur_pelayanan_id')
                ->where('jawaban_surveis.survei_id', $responden->survei_id)
                ->orderBy('unsur_pelayanans.id')
                ->get(['unsur_pelayanans.nama_unsur', 'jawaban_surveis.nilai'])
                ->map(fn ($rating) => [
                    'label' => $rating->nama_unsur,
                    'value' => (int) $rating->nilai,
                ])->values();
            $saran = DB::table('saran_kritiks')->where('survei_id', $responden->survei_id)->value('saran');
        }

        $averageRating = $ratings->avg('value');

        return [
            'id' => $responden->id,
            'nama' => $responden->nama,
            'inisial' => strtoupper(substr($responden->nama, 0, 1)),
            'jenisKelamin' => $responden->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan',
            'jenisKelaminValue' => $responden->jenis_kelamin,
            'usia' => $responden->usia,
            'pendidikan' => $responden->pendidikan,
            'pekerjaan' => $responden->pekerjaan,
            'noHp' => $responden->no_hp,
            'jenisLayanan' => $responden->jenis_layanan ?? '-',
            'tanggal' => $responden->survei_created_at ? date('d M Y', strtotime($responden->survei_created_at)) : '-',
            'survei' => $responden->survei_id ? 'Survei #'.str_pad((string) $responden->survei_id, 4, '0', STR_PAD_LEFT) : 'Belum ada survei',
            'ikm' => $averageRating ? number_format($averageRating * 20, 2, ',', '.') : '-',
            'impact' => $averageRating && $averageRating >= 3 ? 'Terdampak Langsung' : '-',
            'ratings' => $ratings,
            'saran' => $saran,
        ];
    }
}
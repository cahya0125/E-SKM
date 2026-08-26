<?php

namespace App\Http\Controllers\Survey;

use App\Http\Controllers\Controller;
use App\Http\Requests\Survey\PenilaianRequest;
use App\Http\Requests\Survey\RespondenRequest;
use App\Models\JawabanSurvei;
use App\Models\Respondens;
use App\Models\SaranKritik;
use App\Models\Survei;
use App\Models\UnsurPelayanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Survey\StartSurveyRequest;

class SurveyController extends Controller
{
    protected function activeUnsurs()
    {
        return UnsurPelayanan::where('status', 'active')->orderBy('id')->get();
    }

    /** Halaman "Mulai Survei". */
    public function mulai()
    {
        return view('survey.mulai', [
            'jenisLayanans' => StartSurveyRequest::JENIS_LAYANAN,
        ]);
    }

    public function start(StartSurveyRequest $request)
    {
        session(['survey.jenis_layanan' => $request->jenis_layanan]);

        return redirect()->route('survey.responden');
    }

    // Guard tetap sama
    protected function ensureStarted()
    {
        if (! session('survey.jenis_layanan')) {
            return redirect()->route('survey.mulai');
        }
        return null;
    }

    /* --------------------------------------------------------------
     | STEP 1 — DATA RESPONDEN
     |------------------------------------------------------------- */
    public function responden()
    {
        return view('survey.responden');
    }

    public function saveResponden(RespondenRequest $request)
    {
        $data = $request->validated();

        // Normalisasi: string kosong → null (nama & no_hp opsional)
        $data['nama']  = trim($data['nama'] ?? '') !== '' ? $data['nama'] : null;
        $data['no_hp'] = trim($data['no_hp'] ?? '') !== '' ? $data['no_hp'] : null;

        session(['survey.responden' => $data]);

        return redirect()->route('survey.penilaian');
    }

    /* --------------------------------------------------------------
     | STEP 2 — PENILAIAN (9 unsur, satu pertanyaan per halaman)
     |------------------------------------------------------------- */
    public function penilaian()
    {
        if (! session('survey.responden')) {
            return redirect()->route('survey.responden');
        }

        $unsurs = $this->activeUnsurs();

        if ($unsurs->isEmpty()) {
            return redirect()->route('survey.mulai')
                ->with('error', 'Unsur pelayanan belum tersedia. Hubungi admin.');
        }

        return view('survey.penilaian', [
            'unsurs'  => $unsurs,
            'jawaban' => old('jawaban', session('survey.jawaban', [])),
        ]);
    }

    public function savePenilaian(PenilaianRequest $request)
    {
        session(['survey.jawaban' => $request->validated()['jawaban']]);

        return redirect()->route('survey.saran');
    }

    /* --------------------------------------------------------------
     | STEP 3 — KRITIK & SARAN (opsional)
     |------------------------------------------------------------- */
    public function saran()
    {
        if (! session('survey.responden')) {
            return redirect()->route('survey.responden');
        }

        return view('survey.saran', [
            'saran' => old('isi_saran', session('survey.saran', '')),
        ]);
    }

    public function saveSaran(Request $request)
    {
        $data = $request->validate([
            'saran' => ['nullable', 'string', 'max:2000'],
        ]);

        session(['survey.saran' => trim($data['saran'] ?? '')]);

        return redirect()->route('survey.review');
    }

    /* --------------------------------------------------------------
     | REVIEW — TINJAU JAWABAN
     |------------------------------------------------------------- */
    public function review()
    {
        if (! session('survey.responden')) {
            return redirect()->route('survey.responden');
        }

        if (! session('survey.jawaban')) {
            return redirect()->route('survey.penilaian');
        }

        return view('survey.review', [
            'unsurs'    => $this->activeUnsurs(),
            'responden' => session('survey.responden'),
            'jawaban'   => session('survey.jawaban', []),
            'saran'     => session('survey.saran', ''),
        ]);
    }

    /* --------------------------------------------------------------
     | SUBMIT — SIMPAN KE DATABASE (satu transaksi)
     |------------------------------------------------------------- */
    public function submit(Request $request)
    {
        $request->validate(
            ['persetujuan' => ['required', 'accepted']],
            ['persetujuan.required' => 'Centang pernyataan terlebih dahulu.']
        );

        $dataResponden = session('survey.responden');
        $jawaban       = session('survey.jawaban', []);
        $saran         = session('survey.saran', '');

        if (! $dataResponden) {
            return redirect()->route('survey.responden');
        }

        // Pastikan seluruh unsur aktif sudah dinilai.
        foreach ($this->activeUnsurs() as $unsur) {
            if (! isset($jawaban[$unsur->id])) {
                return redirect()->route('survey.penilaian')
                    ->with('error', 'Lengkapi seluruh penilaian terlebih dahulu.');
            }
        }

        DB::transaction(function () use ($dataResponden, $jawaban, $saran) {
            $responden = Respondens::create([
                'nama'          => $dataResponden['nama'],          // opsional (nullable)
                'jenis_kelamin' => $dataResponden['jenis_kelamin'],
                'usia'          => (string) $dataResponden['usia'],
                'pendidikan'    => $dataResponden['pendidikan'],
                'pekerjaan'     => $dataResponden['pekerjaan'],
                'no_hp'         => $dataResponden['no_hp'],         // opsional (nullable)
            ]);

            $survei = Survei::create([
                'responden_id'  => $responden->id,
                'jenis_layanan' => session('survey.jenis_layanan'),
            ]);
            
            foreach ($jawaban as $unsurId => $nilai) {
                JawabanSurvei::create([
                    'survei_id'          => $survei->id,
                    'unsur_pelayanan_id' => $unsurId,
                    'nilai'              => $nilai,
                ]);
            }

            if (trim($saran) !== '') {
                SaranKritik::create([
                    'survei_id' => $survei->id,
                    'saran' => $saran,
                ]);
            }
        });

        session()->forget(['survey.responden', 'survey.jawaban', 'survey.saran']);
        session(['survey_selesai' => true]);

        return redirect()
            ->route('survey.selesai')
            ->with('success', 'Survei Kepuasan Masyarakat Anda berhasil dikirim.');
    }

    /* --------------------------------------------------------------
     | STEP 4 — SELESAI
     |------------------------------------------------------------- */
    public function selesai()
    {
        if (! session('survey_selesai')) {
            return redirect()->route('survey.mulai');
        }

        session()->forget('survey_selesai');

        return view('survey.selesai');
    }
}
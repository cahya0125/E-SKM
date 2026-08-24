<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SaranKritik;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SaranKritiksController extends Controller
{
    public function index()
    {
        $items = SaranKritik::with('survei.responden')
            ->latest('id')
            ->get()
            ->map(fn (SaranKritik $item) => $this->transform($item));

        return view('admin.sarankritik', ['items' => $items]);
    }

    public function updateStatus(Request $request, SaranKritik $saranKritik)
    {
        $validated = $request->validate([
            'status' => 'required|in:baru,ditinjau,selesai',
        ]);

        $saranKritik->update($validated);

        return response()->json($this->transform($saranKritik->fresh('survei.responden')));
    }

    public function destroy(SaranKritik $saranKritik)
    {
        $saranKritik->delete();

        return response()->json(['message' => 'Kritik & saran berhasil dihapus.']);
    }

    private function transform(SaranKritik $item): array
    {
        return [
            'id' => $item->id,
            'title' => $item->survei?->jenis_layanan ?? '-',
            'nama' => $item->survei?->responden?->nama ?? '-',
            'tanggal' => $item->survei?->created_at
                ? Carbon::parse($item->survei->created_at)->locale('id')->translatedFormat('d M Y')
                : '-',
            'saran' => $item->saran,
            'status' => $item->status,
        ];
    }
}
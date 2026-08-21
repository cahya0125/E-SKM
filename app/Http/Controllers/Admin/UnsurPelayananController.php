<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UnsurPelayanan;
use Illuminate\Http\Request;

class UnsurPelayananController extends Controller
{
    public function index()
    {
        $items = $this->transformedItems();

        if (request()->wantsJson()) {
            return response()->json($items);
        }

        return view('admin.unsur', ['items' => $items]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_unsur' => 'required|string|max:255',
            'pertanyaan' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        UnsurPelayanan::create($validated);

        return response()->json($this->transformedItems());
    }

    public function update(Request $request, UnsurPelayanan $unsurPelayanan)
    {
        $validated = $request->validate([
            'nama_unsur' => 'required|string|max:255',
            'pertanyaan' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $unsurPelayanan->update($validated);

        return response()->json($this->transformedItems());
    }

    public function destroy(UnsurPelayanan $unsurPelayanan)
    {
        $unsurPelayanan->delete();

        return response()->json($this->transformedItems());
    }

    private function transformedItems()
    {
        return UnsurPelayanan::orderBy('id')
            ->get()
            ->values()
            ->map(fn (UnsurPelayanan $item, int $index) => $this->transform($item, $index + 1));
    }

    private function transform(UnsurPelayanan $item, int $position): array
    {
        return [
            'id' => $item->id,
            'kode' => 'U' . $position,
            'nama_unsur' => $item->nama_unsur,
            'pertanyaan' => $item->pertanyaan,
            'status' => $item->status,
        ];
    }
}
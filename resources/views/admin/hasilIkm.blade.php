@extends('layouts.admin')

@section('title', 'Hasil IKM · e-SKM BPBD Kota Bandung')

@section('content')
@php
    $bulanList = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    $labelPeriode = match ($tipe) {
        'bulanan' => ($bulanList[$bulan] ?? '-') . ' ' . $tahun,
        'triwulanan' => 'Triwulan ' . $triwulan . ' ' . $tahun,
        default => 'Tahun ' . $tahun,
    };
@endphp

<div x-data="{ tipe: '{{ $tipe }}' }" class="space-y-5">

    {{-- ================= FILTER ================= --}}
    <form method="GET" class="flex flex-wrap items-end gap-3">
        <div>
            <label class="mb-1 block text-[10px] font-semibold text-slate-400">Tampilan</label>
            <select
                name="tipe"
                x-model="tipe"
                class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600 outline-0 focus:border-[#c43b2d]"
                onchange="this.form.submit()"
            >
                <option value="tahunan" @selected($tipe === 'tahunan')>Tahunan</option>
                <option value="bulanan" @selected($tipe === 'bulanan')>Bulanan</option>
                <option value="triwulanan" @selected($tipe === 'triwulanan')>Triwulanan</option>
            </select>
        </div>

        <div>
            <label class="mb-1 block text-[10px] font-semibold text-slate-400">Tahun</label>
            <select
                name="tahun"
                class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600 outline-0 focus:border-[#c43b2d]"
                onchange="this.form.submit()"
            >
                @forelse ($tahunOptions as $opt)
                    <option value="{{ $opt }}" @selected($tahun == $opt)>{{ $opt }}</option>
                @empty
                    <option value="{{ $tahun }}">{{ $tahun }}</option>
                @endforelse
            </select>
        </div>

        <div x-show="tipe === 'bulanan'" x-cloak>
            <label class="mb-1 block text-[10px] font-semibold text-slate-400">Bulan</label>
            <select
                name="bulan"
                class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600 outline-0 focus:border-[#c43b2d]"
                onchange="this.form.submit()"
            >
                @foreach ($bulanList as $num => $label)
                    <option value="{{ $num }}" @selected($bulan == $num)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div x-show="tipe === 'triwulanan'" x-cloak>
            <label class="mb-1 block text-[10px] font-semibold text-slate-400">Triwulan</label>
            <select
                name="triwulan"
                class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600 outline-0 focus:border-[#c43b2d]"
                onchange="this.form.submit()"
            >
                @for ($i = 1; $i <= 4; $i++)
                    <option value="{{ $i }}" @selected($triwulan == $i)>Triwulan {{ $i }}</option>
                @endfor
            </select>
        </div>
    </form>

    @if ($hasil['jumlah_responden'] === 0)
        <div class="rounded-xl border border-slate-200 bg-white p-10 text-center text-sm text-slate-400 shadow-sm">
            Belum ada data survei untuk periode <strong>{{ $labelPeriode }}</strong>.
        </div>
    @else
        {{-- ================= KPI CARDS ================= --}}
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <article class="rounded-xl border border-slate-200 bg-white p-5 text-center shadow-sm">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Nilai IKM</p>
                <p class="mt-2 text-3xl font-bold text-[#c43b2d]">{{ number_format($hasil['nilai_ikm'], 2) }}</p>
                <span class="mt-2 inline-block rounded-full bg-blue-50 px-3 py-1 text-[10px] font-bold text-blue-600">
                    {{ strtoupper($hasil['kinerja_pelayanan']) }}
                </span>
            </article>

            <article class="rounded-xl border border-slate-200 bg-white p-5 text-center shadow-sm">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Mutu Pelayanan</p>
                <p class="mt-2 text-3xl font-bold text-slate-800">{{ $hasil['mutu_pelayanan'] }}</p>
                <p class="mt-2 text-[11px] text-slate-400">Kategori {{ $hasil['kinerja_pelayanan'] }}</p>
            </article>

            <article class="rounded-xl border border-slate-200 bg-white p-5 text-center shadow-sm">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Kinerja Pelayanan</p>
                <p class="mt-2 text-3xl font-bold text-slate-800">{{ $hasil['kinerja_pelayanan'] }}</p>
                <p class="mt-2 text-[11px] text-slate-400">NRR × 25</p>
            </article>

            <article class="rounded-xl border border-slate-200 bg-white p-5 text-center shadow-sm">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Jumlah Responden</p>
                <p class="mt-2 text-3xl font-bold text-slate-800">{{ number_format($hasil['jumlah_responden']) }}</p>
                <p class="mt-2 text-[11px] text-slate-400">{{ $labelPeriode }}</p>
            </article>
        </section>

        {{-- ================= TABEL DETAIL ================= --}}
        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm font-semibold text-slate-800">Detail Nilai Per Unsur Pelayanan · {{ $labelPeriode }}</p>

                <div class="flex gap-2">
                    <a
                        href="{{ route('admin.hasil-ikm.pdf', ['tipe' => $tipe, 'tahun' => $tahun, 'bulan' => $bulan, 'triwulan' => $triwulan]) }}"
                        class="rounded-lg bg-[#c43b2d] px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-[#a83227]"
                    >
                        Download PDF
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-245 w-full border-collapse text-left text-xs text-slate-500">
                    <thead>
                        <tr class="bg-slate-50 text-[10px] uppercase text-slate-500">
                            <th class="h-14 px-3">Kode</th>
                            <th class="h-14 px-3">Unsur Pelayanan</th>
                            <th class="h-14 px-3">Responden</th>
                            <th class="h-14 px-3">Nilai Rata-rata</th>
                            <th class="h-14 px-3">Bobot</th>
                            <th class="h-14 px-3">Nilai Tertimbang</th>
                            <th class="h-14 px-3">Mutu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($hasil['details'] as $detail)
                            <tr class="border-t border-slate-100">
                                <td class="h-14 whitespace-nowrap px-3">
                                    <strong class="text-[#c43b2d]">{{ $detail['kode'] }}</strong>
                                </td>
                                <td class="h-14 px-3">
                                    <strong class="text-slate-800">{{ $detail['nama_unsur'] }}</strong>
                                </td>
                                <td class="h-14 whitespace-nowrap px-3 text-slate-500">{{ number_format($detail['jumlah_responden']) }}</td>
                                <td class="h-14 whitespace-nowrap px-3 font-semibold text-slate-700">{{ number_format($detail['nilai_rata_rata'], 3) }}</td>
                                <td class="h-14 whitespace-nowrap px-3 text-slate-400">{{ number_format($detail['bobot_nilai'], 3) }}</td>
                                <td class="h-14 whitespace-nowrap px-3 font-semibold text-slate-700">{{ number_format($detail['nrr_tertimbang'], 3) }}</td>
                                <td class="h-14 whitespace-nowrap px-3">
                                    <strong class="{{ ['A' => 'text-emerald-600', 'B' => 'text-blue-600', 'C' => 'text-amber-600', 'D' => 'text-red-600'][$detail['mutu_unsur']] }}">
                                        {{ $detail['mutu_unsur'] }}
                                    </strong>
                                </td>
                            </tr>
                        @endforeach

                        <tr class="border-t border-slate-200 bg-slate-50">
                            <td colspan="5" class="h-14 px-3 text-right text-xs font-bold text-slate-600">Total IKM</td>
                            <td class="h-14 px-3">
                                <strong class="text-sm text-[#c43b2d]">{{ number_format($hasil['nilai_ikm'], 2) }}</strong>
                            </td>
                            <td class="h-14 px-3">
                                <span class="rounded-full bg-blue-50 px-2.5 py-1 text-[10px] font-bold text-blue-600">
                                    {{ $hasil['mutu_pelayanan'] }} / {{ strtoupper($hasil['kinerja_pelayanan']) }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    @endif
</div>
@endsection
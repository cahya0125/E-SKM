@extends('layouts.admin')

@section('title', 'Laporan · e-SKM BPBD Kota Bandung')

@section('content')
<div
    x-data="{
        jenis: '{{ $jenis }}',
        tahun: {{ $tahun }},
        bulan: {{ $bulan }},
        triwulan: {{ $triwulan }},
        bulanList: @js($bulanList),
        tahunOptions: @js($tahunOptions),
    }"
    class="space-y-5"
>
    {{-- ================= FILTER ================= --}}
    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="GET" id="filter-form">
            <input type="hidden" name="jenis" :value="jenis">
            <input type="hidden" name="tahun" :value="tahun">
            <input type="hidden" name="bulan" :value="bulan">
            <input type="hidden" name="triwulan" :value="triwulan">
        </form>

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="mb-2 text-[10px] font-semibold uppercase tracking-wide text-slate-400">Jenis Laporan</p>
                <div class="inline-flex rounded-lg border border-slate-200 p-1">
                    <button
                        type="button"
                        class="rounded-md px-4 py-2 text-xs font-semibold transition"
                        :class="jenis === 'tahun' ? 'bg-[#102342] text-white' : 'text-slate-500 hover:bg-slate-50'"
                        @click="jenis = 'tahun'"
                    >
                        Per Tahun
                    </button>
                    <button
                        type="button"
                        class="rounded-md px-4 py-2 text-xs font-semibold transition"
                        :class="jenis === 'triwulan' ? 'bg-[#102342] text-white' : 'text-slate-500 hover:bg-slate-50'"
                        @click="jenis = 'triwulan'"
                    >
                        Per Triwulan
                    </button>
                    <button
                        type="button"
                        class="rounded-md px-4 py-2 text-xs font-semibold transition"
                        :class="jenis === 'bulan' ? 'bg-[#102342] text-white' : 'text-slate-500 hover:bg-slate-50'"
                        @click="jenis = 'bulan'"
                    >
                        Per Bulan
                    </button>
                </div>
            </div>

            <div>
                <span class="inline-block rounded-full bg-rose-50 px-3 py-1.5 text-[11px] font-semibold text-rose-600">
                    {{ $labelPeriode }}
                </span>
            </div>
        </div>

        <div class="mt-4">
            {{-- Per Tahun --}}
            <div x-show="jenis === 'tahun'">
                <p class="mb-2 text-[10px] font-semibold uppercase tracking-wide text-slate-400">Tahun</p>
                <div class="flex flex-wrap gap-2">
                    <template x-for="opt in tahunOptions" :key="opt">
                        <button
                            type="button"
                            class="rounded-lg border px-4 py-2 text-xs font-semibold transition"
                            :class="tahun === opt ? 'border-[#c43b2d] bg-[#c43b2d] text-white' : 'border-slate-200 text-slate-600 hover:bg-slate-50'"
                            @click="tahun = opt"
                            x-text="opt"
                        ></button>
                    </template>
                </div>
            </div>

            {{-- Per Triwulan --}}
            <div x-show="jenis === 'triwulan'" x-cloak>
                <div class="flex flex-wrap items-end gap-4">
                    <div>
                        <p class="mb-2 text-[10px] font-semibold uppercase tracking-wide text-slate-400">Triwulan</p>
                        <div class="flex gap-2">
                            <template x-for="i in [1, 2, 3, 4]" :key="i">
                                <button
                                    type="button"
                                    class="rounded-lg border px-4 py-2 text-xs font-semibold transition"
                                    :class="triwulan === i ? 'border-[#c43b2d] bg-[#c43b2d] text-white' : 'border-slate-200 text-slate-600 hover:bg-slate-50'"
                                    @click="triwulan = i"
                                    x-text="'TW ' + i"
                                ></button>
                            </template>
                        </div>
                    </div>

                    <div>
                        <p class="mb-2 text-[10px] font-semibold uppercase tracking-wide text-slate-400">Tahun</p>
                        <select x-model.number="tahun" class="h-10 rounded-lg border border-slate-200 px-3 text-xs font-semibold text-slate-600 outline-0 focus:border-[#c43b2d]">
                            <template x-for="opt in tahunOptions" :key="opt">
                                <option :value="opt" x-text="opt"></option>
                            </template>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Per Bulan --}}
            <div x-show="jenis === 'bulan'" x-cloak>
                <div class="flex flex-wrap items-end gap-4">
                    <div>
                        <p class="mb-2 text-[10px] font-semibold uppercase tracking-wide text-slate-400">Bulan</p>
                        <div class="grid grid-cols-4 gap-2 sm:grid-cols-6">
                            <template x-for="(label, num) in bulanList" :key="num">
                                <button
                                    type="button"
                                    class="rounded-lg border px-3 py-2 text-xs font-semibold transition"
                                    :class="bulan == num ? 'border-[#c43b2d] bg-[#c43b2d] text-white' : 'border-slate-200 text-slate-600 hover:bg-slate-50'"
                                    @click="bulan = parseInt(num)"
                                    x-text="label"
                                ></button>
                            </template>
                        </div>
                    </div>

                    <div>
                        <p class="mb-2 text-[10px] font-semibold uppercase tracking-wide text-slate-400">Tahun</p>
                        <select x-model.number="tahun" class="h-10 rounded-lg border border-slate-200 px-3 text-xs font-semibold text-slate-600 outline-0 focus:border-[#c43b2d]">
                            <template x-for="opt in tahunOptions" :key="opt">
                                <option :value="opt" x-text="opt"></option>
                            </template>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5">
            <button
                type="button"
                class="rounded-lg bg-[#c43b2d] px-5 py-2.5 text-xs font-semibold text-white transition hover:bg-[#a83227]"
                @click="document.getElementById('filter-form').submit()"
            >
                Generate Laporan
            </button>
        </div>
    </section>

    {{-- ================= EXPORT & EDITOR ================= --}}
    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-4">
            <div class="flex flex-wrap gap-2">
                <form method="POST" action="{{ route('admin.laporan.word') }}" class="export-form" data-format="word">
                    @csrf
                    <input type="hidden" name="jenis" value="{{ $jenis }}">
                    <input type="hidden" name="tahun" value="{{ $tahun }}">
                    <input type="hidden" name="bulan" value="{{ $bulan }}">
                    <input type="hidden" name="triwulan" value="{{ $triwulan }}">
                    <input type="hidden" name="konten">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-blue-50 px-4 py-2.5 text-xs font-semibold text-blue-600 transition hover:bg-blue-100">
                        <i class="fa-solid fa-file-word" aria-hidden="true"></i> Word (.docx)
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.laporan.excel') }}" class="export-form" data-format="excel">
                    @csrf
                    <input type="hidden" name="jenis" value="{{ $jenis }}">
                    <input type="hidden" name="tahun" value="{{ $tahun }}">
                    <input type="hidden" name="bulan" value="{{ $bulan }}">
                    <input type="hidden" name="triwulan" value="{{ $triwulan }}">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-emerald-50 px-4 py-2.5 text-xs font-semibold text-emerald-600 transition hover:bg-emerald-100">
                        <i class="fa-solid fa-file-excel" aria-hidden="true"></i> Excel (.xlsx)
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.laporan.pdf') }}" class="export-form" data-format="pdf">
                    @csrf
                    <input type="hidden" name="jenis" value="{{ $jenis }}">
                    <input type="hidden" name="tahun" value="{{ $tahun }}">
                    <input type="hidden" name="bulan" value="{{ $bulan }}">
                    <input type="hidden" name="triwulan" value="{{ $triwulan }}">
                    <input type="hidden" name="konten">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-red-50 px-4 py-2.5 text-xs font-semibold text-red-600 transition hover:bg-red-100">
                        <i class="fa-solid fa-file-pdf" aria-hidden="true"></i> PDF
                    </button>
                </form>
            </div>
        </div>

        {{-- Toolbar editor --}}
        <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 bg-slate-50 p-3">
            <select onchange="document.execCommand('formatBlock', false, this.value)" class="h-9 rounded-md border border-slate-200 bg-white px-2 text-xs">
                <option value="p">Normal</option>
                <option value="h1">Heading 1</option>
                <option value="h2">Heading 2</option>
                <option value="h3">Heading 3</option>
            </select>

            <select onchange="document.execCommand('fontSize', false, this.value)" class="h-9 rounded-md border border-slate-200 bg-white px-2 text-xs">
                <option value="2">8pt</option>
                <option value="3" selected>12pt</option>
                <option value="4">14pt</option>
                <option value="5">18pt</option>
                <option value="6">24pt</option>
                <option value="7">36pt</option>
            </select>

            <div class="mx-1 h-6 w-px bg-slate-200"></div>

            <button type="button" onclick="document.execCommand('bold')" class="grid h-9 w-9 place-content-center rounded-md text-xs font-bold text-slate-600 hover:bg-slate-100">B</button>
            <button type="button" onclick="document.execCommand('italic')" class="grid h-9 w-9 place-content-center rounded-md text-xs italic text-slate-600 hover:bg-slate-100">I</button>
            <button type="button" onclick="document.execCommand('underline')" class="grid h-9 w-9 place-content-center rounded-md text-xs underline text-slate-600 hover:bg-slate-100">U</button>

            <div class="mx-1 h-6 w-px bg-slate-200"></div>

            <button type="button" onclick="document.execCommand('justifyLeft')" class="grid h-9 w-9 place-content-center rounded-md text-slate-600 hover:bg-slate-100"><i class="fa-solid fa-align-left text-xs" aria-hidden="true"></i></button>
            <button type="button" onclick="document.execCommand('justifyCenter')" class="grid h-9 w-9 place-content-center rounded-md text-slate-600 hover:bg-slate-100"><i class="fa-solid fa-align-center text-xs" aria-hidden="true"></i></button>
            <button type="button" onclick="document.execCommand('justifyRight')" class="grid h-9 w-9 place-content-center rounded-md text-slate-600 hover:bg-slate-100"><i class="fa-solid fa-align-right text-xs" aria-hidden="true"></i></button>

            <div class="mx-1 h-6 w-px bg-slate-200"></div>

            <button type="button" onclick="document.execCommand('insertUnorderedList')" class="grid h-9 w-9 place-content-center rounded-md text-slate-600 hover:bg-slate-100"><i class="fa-solid fa-list-ul text-xs" aria-hidden="true"></i></button>
            <button type="button" onclick="document.execCommand('insertOrderedList')" class="grid h-9 w-9 place-content-center rounded-md text-slate-600 hover:bg-slate-100"><i class="fa-solid fa-list-ol text-xs" aria-hidden="true"></i></button>

            <div class="ml-auto">
                <button type="button" onclick="resetLaporanContent()" class="inline-flex items-center gap-1.5 rounded-md border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-500 hover:bg-slate-50">
                    <i class="fa-solid fa-rotate-left text-[10px]" aria-hidden="true"></i> Reset
                </button>
            </div>
        </div>

        {{-- Area editor --}}
        <div class="bg-slate-100 p-6">
            <div
                id="laporan-content"
                contenteditable="true"
                class="mx-auto max-w-3xl rounded-lg border border-slate-200 bg-white p-10 text-sm leading-relaxed text-slate-700 shadow-sm outline-none"
            >{!! $konten !!}</div>
        </div>
    </section>
</div>

<script>
    const laporanEl = document.getElementById('laporan-content');
    const originalKonten = laporanEl.innerHTML;

    function resetLaporanContent() {
        laporanEl.innerHTML = originalKonten;
    }

    document.querySelectorAll('form.export-form').forEach((form) => {
        form.addEventListener('submit', () => {
            const kontenInput = form.querySelector('input[name="konten"]');
            if (kontenInput) {
                kontenInput.value = laporanEl.innerHTML;
            }
        });
    });
</script>
@endsection
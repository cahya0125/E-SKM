@extends('layouts.survey')

@section('content')
@php
    $defaultOptions = ['Sangat Tidak Sesuai', 'Tidak Sesuai', 'Cukup Sesuai', 'Sesuai', 'Sangat Sesuai'];
    $row     = 'flex items-center justify-between px-4 py-3 text-sm odd:bg-slate-50';
@endphp

<div class="mx-auto max-w-3xl px-4 py-10">
    <x-survey.stepper :current="3" />

    <form method="POST" action="{{ route('survey.submit') }}"
          class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-10"
          x-data="{ agreed: false }">
        @csrf

        <h1 class="text-2xl font-extrabold text-slate-800">Tinjau Jawaban</h1>
        <p class="mt-2 text-slate-500">Periksa kembali jawaban Anda sebelum mengirim survei.</p>

        {{-- Data responden (tanpa alamat & status dampak) --}}
        <p class="mt-8 text-xs font-semibold uppercase tracking-wider text-slate-400">Data Responden</p>
        <div class="mt-2 divide-y divide-slate-200 overflow-hidden rounded-xl border border-slate-200">
            <div class="{{ $row }}"><span class="text-slate-500">Nama</span><span class="font-semibold text-slate-800">{{ $responden['nama'] ?: '-' }}</span></div>
            <div class="{{ $row }}"><span class="text-slate-500">Jenis Kelamin</span><span class="font-semibold text-slate-800">{{ ($responden['jenis_kelamin'] ?? '') === 'L' ? 'Laki-laki' : 'Perempuan' }}</span></div>
            <div class="{{ $row }}"><span class="text-slate-500">Usia</span><span class="font-semibold text-slate-800">{{ $responden['usia'] }} tahun</span></div>
            <div class="{{ $row }}"><span class="text-slate-500">Pendidikan</span><span class="font-semibold text-slate-800">{{ $responden['pendidikan'] }}</span></div>
            <div class="{{ $row }}"><span class="text-slate-500">Pekerjaan</span><span class="font-semibold text-slate-800">{{ $responden['pekerjaan'] }}</span></div>
            <div class="{{ $row }}"><span class="text-slate-500">Nomor HP</span><span class="font-semibold text-slate-800">{{ $responden['no_hp'] ?: '-' }}</span></div>
        </div>

        {{-- Penilaian --}}
        <p class="mt-8 text-xs font-semibold uppercase tracking-wider text-slate-400">Penilaian</p>
        <div class="mt-2 divide-y divide-slate-200 overflow-hidden rounded-xl border border-slate-200">
            @foreach ($unsurs as $i => $unsur)
                @php
                    $options = array_combine(range(1, 5), array_values($unsur->opsi_jawaban ?: $defaultOptions));
                @endphp
                <div class="{{ $row }}">
                    <span class="text-slate-600">U{{ $i + 1 }}. {{ $unsur->nama_unsur }}</span>
                    @if (isset($jawaban[$unsur->id]))
                        <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                            {{ $jawaban[$unsur->id] }} – {{ $options[$jawaban[$unsur->id]] }}
                        </span>
                    @else
                        <span class="text-xs font-semibold text-orange-600">Belum dijawab</span>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Kritik & saran --}}
        <p class="mt-8 text-xs font-semibold uppercase tracking-wider text-slate-400">Kritik &amp; Saran</p>
        <div class="mt-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-700">
            {{ $saran ?: '-' }}
        </div>

        {{-- Pernyataan --}}
        <label class="mt-8 flex cursor-pointer items-start gap-3 rounded-xl border border-amber-300 bg-amber-50 px-4 py-4">
            <input type="checkbox" name="persetujuan" value="1" x-model="agreed"
                   class="mt-0.5 h-4 w-4 rounded accent-[#b3402a]">
            <span class="text-sm text-amber-800">
                Saya telah memeriksa jawaban saya dan menyatakan bahwa data yang diberikan sudah sesuai dan benar.
            </span>
        </label>
        @error('persetujuan') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror

        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
            <a href="{{ route('survey.saran') }}"
               class="rounded-xl border border-slate-200 bg-white px-6 py-3 text-center text-sm font-semibold text-slate-600 hover:bg-slate-50">← Kembali</a>
            <button type="submit" :disabled="!agreed"
                    class="flex-1 rounded-xl bg-[#b3402a] px-6 py-3 font-bold text-white hover:bg-[#9c3521] disabled:cursor-not-allowed disabled:bg-slate-300 disabled:text-slate-500">
                Kirim Survei ✓
            </button>
        </div>
    </form>
</div>
@endsection
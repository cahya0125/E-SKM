@extends('layouts.survey')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-10">
    <x-survey.stepper :current="3" />

    <form method="POST" action="{{ route('survey.saran.save') }}"
          class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-10"
          x-data="{ isi: @js($saran) }">
        @csrf

        <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-red-50 text-2xl text-[#b3402a]">
            <i class="fa-regular fa-comment"></i>
        </div>

        <h1 class="mt-6 text-2xl font-extrabold text-slate-800">Kritik &amp; Saran</h1>
        <p class="mt-3 text-slate-500">
            Sampaikan kritik, saran, atau masukan Anda untuk membantu meningkatkan kualitas pelayanan
            BPBD Kota Bandung. Kolom ini bersifat opsional.
        </p>

        <textarea name="saran" rows="6" x-model="isi"
                placeholder="Tulis kritik atau saran Anda di sini... Misalnya: peningkatan waktu respons, ketersediaan alat, keramahan petugas, dll."
                class="mt-6 w-full rounded-xl border border-slate-300 px-4 py-3 text-slate-700 focus:border-[#b3402a] focus:outline-none focus:ring-1 focus:ring-[#b3402a]"></textarea>
        <p class="mt-2 text-right text-sm text-slate-400" x-text="isi.length + ' karakter'"></p>
        @error('saran') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror

        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
            <a href="{{ route('survey.penilaian') }}"
               class="rounded-xl border border-slate-200 bg-white px-6 py-3 text-center text-sm font-semibold text-slate-600 hover:bg-slate-50">← Kembali</a>
            <button type="submit"
                    class="flex-1 rounded-xl bg-[#b3402a] px-6 py-3 font-bold text-white hover:bg-[#9c3521]">Review Jawaban →</button>
        </div>
    </form>
</div>
@endsection
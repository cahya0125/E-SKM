@extends('layouts.survey')

@section('title', 'Mulai Survei · e-SKM BPBD Kota Bandung')

{{-- Sembunyikan footer pada alur pengisian survei (bila layout mendukung) --}}
@section('footer')@endsection

@section('content')
<div class="mx-auto max-w-2xl px-4 py-10">

    {{-- ============================= --}}
    {{-- BREADCRUMB --}}
    {{-- ============================= --}}
    <nav class="text-sm text-slate-400" aria-label="Breadcrumb">
        <a href="{{ route('home') }}" class="transition hover:text-slate-600">Beranda</a>
        <span class="mx-2">›</span>
        <span class="font-medium text-[#b3402a]">Mulai Survei</span>
    </nav>

    {{-- ============================= --}}
    {{-- CARD MULAI SURVEI --}}
    {{-- ============================= --}}
    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-10"
         x-data="{ dipilih: @js(old('jenis_layanan', '')) }">

        {{-- Ikon --}}
        <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-red-50 text-2xl text-[#b3402a]">
            <i class="fa-regular fa-clipboard" aria-hidden="true"></i>
        </div>

        {{-- Judul & deskripsi --}}
        <h1 class="mt-6 text-3xl font-extrabold text-slate-800">Mulai Survei</h1>
        <p class="mt-3 text-slate-500">
            Pilih jenis layanan BPBD yang pernah Anda gunakan untuk memulai survei
            kepuasan masyarakat.
        </p>

        {{-- ============================= --}}
        {{-- PENGANTAR / KETERANGAN BPBD --}}
        {{-- ============================= --}}
        <div class="mt-6 space-y-3 rounded-xl border border-slate-200 bg-slate-50 px-5 py-4 text-justify text-sm leading-relaxed text-slate-600">
            <p>
                <span class="font-semibold text-slate-700">Badan Penanggulangan Bencana Daerah</span>
                adalah Organisasi Perangkat Daerah (OPD) di lingkungan Pemerintah Kota Bandung,
                urusan pemerintahan bidang Penanggulangan Bencana serta perlindungan masyarakat
                terkait kesiapsiagaan dan penanganan bencana di daerah.
            </p>
            <p>
                Guna menjalankan tugas dan fungsi di atas, kami membutuhkan informasi unit pelayanan
                instansi kami, untuk itu kami berupaya menyajikan Indeks Kepuasan Masyarakat secara
                rutin yang diharapkan mampu memberikan gambaran mengenai kualitas pelayanan di
                instansi kami kepada masyarakat, indeks tersebut diperoleh dari pengukuran atas
                pendapat masyarakat yang dikumpulkan melalui survey ini, hasil pengukurannya akan
                kami gunakan sebagai bahan evaluasi dalam meningkatkan kinerja birokrasi dinas kami
                pada khususnya dan Pemerintah Kota Bandung pada umumnya.
            </p>
            <p>
                Kami berharap Bapak/Ibu/Sdr. berkenan untuk meluangkan waktu demi kemajuan
                Pemerintah Kota Bandung. Atas perhatian dan partisipasinya diucapkan terima kasih.
            </p>
        </div>

        {{-- ============================= --}}
        {{-- FORM PILIH JENIS LAYANAN --}}
        {{-- ============================= --}}
        <form method="POST" action="{{ route('survey.start') }}" class="mt-6">
            @csrf

            <label for="jenis_layanan" class="text-sm font-semibold text-slate-700">
                Jenis Layanan <span class="text-[#b3402a]">*</span>
            </label>

            <select id="jenis_layanan"
                    name="jenis_layanan"
                    x-model="dipilih"
                    class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-700 focus:border-[#b3402a] focus:outline-none focus:ring-1 focus:ring-[#b3402a]">
                <option value="">— Pilih jenis layanan —</option>
                @foreach ($jenisLayanans as $layanan)
                    <option value="{{ $layanan }}">{{ $layanan }}</option>
                @endforeach
            </select>

            @error('jenis_layanan')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror

            {{-- Tombol disabled sampai ada pilihan --}}
            <button type="submit"
                    :disabled="!dipilih"
                    class="mt-6 w-full rounded-xl bg-[#b3402a] px-5 py-3.5 font-bold text-white transition hover:bg-[#9c3521] disabled:cursor-not-allowed disabled:bg-slate-300 disabled:text-slate-500">
                Mulai Survei →
            </button>
        </form>
    </div>

    {{-- Catatan privasi --}}
    <p class="mt-6 text-center text-sm text-slate-400">
        Data yang Anda berikan dijaga kerahasiaannya dan digunakan semata-mata untuk
        peningkatan layanan.
    </p>
</div>
@endsection
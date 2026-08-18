@extends('layouts.app')

@section('title', 'e-SKM · BPBD Kota Bandung')

@section('content')

    {{-- ============================= --}}
    {{-- HERO SECTION --}}
    {{-- ============================= --}}
    <section class="relative overflow-hidden bg-[#101a34]">
        {{-- Foto latar kota Bandung --}}
        <img src="{{ asset('assets/img/Bandung_city_centre,_July_2014.jpg') }}" alt="Kota Bandung"
            class="absolute inset-0 h-full w-full object-cover opacity-85">

        {{-- Overlay gradasi supaya teks tetap terbaca di atas foto latar --}}
        <div
            class="absolute inset-0 bg-gradient-to-r from-[#101a34] via-[#101a34]/60 to-[#101a34]/40 sm:via-[#101a34]/50 sm:to-[#101a34]/30">
        </div>

        <div class="relative mx-auto max-w-7xl px-4 py-14 sm:px-6 sm:py-20 lg:py-28">

            {{-- Badge status survei --}}
            <span
                class="inline-flex items-center gap-2 rounded-full bg-[#dc4b3f]/15 px-3 py-1.5 text-[11px] font-semibold text-[#ff8a7a] sm:px-4 sm:text-xs">
                <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-[#ff8a7a]"></span>
                Survei Kepuasan Masyarakat {{ date('Y') }} · Aktif
            </span>

            <h1 class="mt-5 max-w-2xl text-[28px] font-extrabold leading-tight text-white sm:mt-6 sm:text-4xl md:text-5xl">
                Suara Anda Membantu Meningkatkan Pelayanan
                <span class="text-[#ff7a68]">BPBD Kota Bandung</span>
            </h1>

            <p class="mt-4 max-w-xl text-sm text-slate-300 sm:mt-6 sm:text-base">
                Berikan penilaian dan masukan terhadap pelayanan BPBD Kota Bandung melalui
                Survei Kepuasan Masyarakat secara online, mudah, dan terpercaya.
            </p>

            <div class="mt-6 flex flex-col gap-3 sm:mt-8 sm:flex-row sm:flex-wrap sm:gap-4">
                <a href="#"
                    class="rounded-lg bg-[#dc4b3f] px-6 py-3 text-center text-sm font-semibold text-white shadow hover:bg-[#c9392e] transition">
                    Mulai Survei &rarr;
                </a>
                <a href="#tentang-sistem"
                    class="rounded-lg border border-white/25 px-6 py-3 text-center text-sm font-semibold text-white hover:bg-white/10 transition">
                    Tentang e-SKM
                </a>
            </div>

            {{-- Statistik singkat --}}
            <div class="mt-10 flex flex-wrap gap-6 sm:mt-12 sm:gap-10">
                <div>
                    <p class="text-2xl font-bold text-white sm:text-3xl">
                        {{ number_format($totalResponden ?? 1248, 0, ',', '.') }}</p>
                    <p class="text-xs text-slate-400 sm:text-sm">Responden</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-white sm:text-3xl">
                        {{ number_format($nilaiIkm ?? 87.42, 2, ',', '.') }}</p>
                    <p class="text-xs text-slate-400 sm:text-sm">Nilai IKM</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-white sm:text-3xl">{{ $mutuPelayanan ?? 'B' }}</p>
                    <p class="text-xs text-slate-400 sm:text-sm">Mutu Pelayanan</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================= --}}
    {{-- TENTANG SISTEM --}}
    {{-- ============================= --}}
    <section id="tentang-sistem" class="mx-auto max-w-5xl px-4 py-14 text-center sm:px-6 sm:py-20">
        <p class="text-xs font-semibold uppercase tracking-widest text-[#dc4b3f] sm:text-sm">
            Tentang Sistem
        </p>
        <h2 class="mt-3 text-2xl font-extrabold text-[#101a34] sm:text-3xl md:text-4xl">
            Apa itu e-SKM?
        </h2>
        <p class="mx-auto mt-4 max-w-2xl text-sm text-slate-500 sm:text-base">
            e-SKM adalah sistem survei elektronik yang digunakan untuk mengukur tingkat
            kepuasan masyarakat terhadap pelayanan BPBD Kota Bandung secara digital dan transparan.
        </p>

        {{-- 3 kartu keunggulan --}}
        <div class="mt-10 grid gap-5 sm:mt-12 sm:grid-cols-3 sm:gap-6">
            @php
                $keunggulan = [
                    [
                        'judul' => 'Mudah',
                        'deskripsi' => 'Isi survei secara online dari mana saja menggunakan ponsel atau komputer Anda.',
                        'icon' =>
                            'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
                    ],
                    [
                        'judul' => 'Cepat',
                        'deskripsi' => 'Survei dapat diselesaikan hanya dalam beberapa menit tanpa proses yang rumit.',
                        'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                    ],
                    [
                        'judul' => 'Bermanfaat',
                        'deskripsi' =>
                            'Masukan Anda membantu BPBD Kota Bandung meningkatkan kualitas pelayanan kepada masyarakat.',
                        'icon' => 'M5 13l4 4L19 7',
                    ],
                ];
            @endphp

            @foreach ($keunggulan as $item)
                <div class="rounded-2xl border border-slate-100 p-6 text-left shadow-sm sm:p-8">
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#dc4b3f]/10">
                        <svg class="h-6 w-6 text-[#dc4b3f]" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                        </svg>
                    </span>
                    <h3 class="mt-5 text-lg font-bold text-[#101a34]">{{ $item['judul'] }}</h3>
                    <p class="mt-2 text-sm text-slate-500">{{ $item['deskripsi'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ============================= --}}
    {{-- CARA KERJA --}}
    {{-- ============================= --}}
    <section class="bg-[#1B2B4B] px-4 py-14 text-center sm:px-6 sm:py-20">
        <p class="text-xs font-semibold uppercase tracking-widest text-[#dc4b3f] sm:text-sm">
            Cara Kerja
        </p>
        <h2 class="mt-3 text-2xl font-extrabold text-white sm:text-3xl md:text-4xl">
            Cara Mengisi Survei
        </h2>

        <div class="mx-auto mt-10 grid max-w-5xl grid-cols-2 gap-8 sm:mt-14 sm:grid-cols-4 sm:gap-10">
            @php
                $langkah = [
                    [
                        'no' => '01',
                        'judul' => 'Pilih Layanan',
                        'deskripsi' => 'Pilih jenis layanan BPBD yang pernah Anda gunakan',
                    ],
                    ['no' => '02', 'judul' => 'Isi Data', 'deskripsi' => 'Lengkapi informasi dasar sebagai responden'],
                    [
                        'no' => '03',
                        'judul' => 'Berikan Penilaian',
                        'deskripsi' => 'Jawab pertanyaan seputar kepuasan layanan',
                    ],
                    ['no' => '04', 'judul' => 'Kirim Survei', 'deskripsi' => 'Tinjau jawaban dan kirimkan survei Anda'],
                ];
            @endphp

            @foreach ($langkah as $step)
                <div>
                    <span
                        class="mx-auto flex h-14 w-14 items-center justify-center rounded-full border-2 border-[#dc4b3f]/60 text-base font-bold text-[#ff8a7a] sm:h-16 sm:w-16 sm:text-lg">
                        {{ $step['no'] }}
                    </span>
                    <h3 class="mt-3 text-sm font-bold text-white sm:mt-4 sm:text-base">{{ $step['judul'] }}</h3>
                    <p class="mt-2 text-xs text-slate-400 sm:text-sm">{{ $step['deskripsi'] }}</p>
                </div>
            @endforeach
        </div>

        <a href="#"
            class="mt-10 inline-block rounded-lg bg-[#dc4b3f] px-6 py-3 text-sm font-semibold text-white shadow hover:bg-[#c9392e] transition sm:mt-14 sm:px-8">
            Mulai Survei Sekarang
        </a>
    </section>

@endsection

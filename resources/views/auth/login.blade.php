<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk ke Dashboard · e-SKM BPBD Kota Bandung</title>
    <link rel="shortcut icon" href="{{ asset('assets/img/bpbd-logo.png') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

@php
    $errorMessage = $errors->first();
    $lockoutSeconds = 0;

    if (preg_match('/Coba lagi dalam (\d+) detik/', $errorMessage, $matches)) {
        $lockoutSeconds = (int) $matches[1];
    }

    $isLockout = $lockoutSeconds > 0;
@endphp

<body x-data="{ lockoutSeconds: {{ $lockoutSeconds }} }"
    x-init="if (lockoutSeconds > 0) { const timer = setInterval(() => { lockoutSeconds--; if (lockoutSeconds <= 0) clearInterval(timer) }, 1000) }"
    class="min-h-screen bg-white text-slate-800 antialiased">

    <div class="grid min-h-screen lg:grid-cols-2">

        {{-- ============================= --}}
        {{-- PANEL KIRI — Branding (disembunyikan di mobile) --}}
        {{-- ============================= --}}
        <div
            class="relative hidden overflow-hidden bg-[#101a34] lg:flex lg:flex-col lg:justify-between lg:px-12 lg:py-14">

            {{-- Bentuk lingkaran dekoratif transparan --}}
            <div class="pointer-events-none absolute -right-24 top-16 h-80 w-80 rounded-full bg-[#dc4b3f]/25 blur-3xl">
            </div>
            <div
                class="pointer-events-none absolute -left-24 bottom-10 h-72 w-72 rounded-full bg-[#dc4b3f]/10 blur-3xl">
            </div>

            {{-- Logo + Nama Instansi --}}
            <div class="relative flex items-center gap-3">
                <img src="{{ asset('assets/img/bpbd-logo.png') }}" alt="Logo BPBD Kota Bandung"
                    class="h-12 w-12 rounded-xl object-cover ring-1 ring-white/20">
                <div class="leading-tight">
                    <p class="text-sm font-bold tracking-wide text-white">BPBD KOTA BANDUNG</p>
                    <p class="text-xs text-slate-400">Badan Penanggulangan Bencana Daerah</p>
                </div>
            </div>

            {{-- Judul besar + deskripsi --}}
            <div class="relative mt-10">
                <h1 class="text-5xl font-extrabold text-[#ff7a68]">e-SKM</h1>
                <p class="mt-4 max-w-sm text-lg text-slate-300">
                    Sistem Elektronik Survei Kepuasan Masyarakat BPBD Kota Bandung
                </p>
            </div>

            {{-- Daftar fitur --}}
            <div class="relative mt-auto space-y-6 pt-16">
                @php
                    $fitur = [
                        [
                            'judul' => 'Kelola data survei',
                            'deskripsi' => 'Manajemen responden dan hasil survei secara terpusat',
                        ],
                        [
                            'judul' => 'Analisis IKM',
                            'deskripsi' => 'Hitung dan pantau Indeks Kepuasan Masyarakat secara real-time',
                        ],
                        [
                            'judul' => 'Laporan otomatis',
                            'deskripsi' => 'Generate laporan PDF dan Excel dengan satu klik',
                        ],
                    ];
                @endphp

                @foreach ($fitur as $item)
                    <div class="flex gap-3">
                        <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-[#dc4b3f]"></span>
                        <div>
                            <p class="text-sm font-bold text-white">{{ $item['judul'] }}</p>
                            <p class="mt-1 text-sm text-slate-400">{{ $item['deskripsi'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ============================= --}}
        {{-- PANEL KANAN — Form Login --}}
        {{-- ============================= --}}
        <div class="flex items-center justify-center px-6 py-14 sm:px-10">
            <div class="w-full max-w-md">

                {{-- Logo tampil di sini kalau layar kecil (panel kiri disembunyikan) --}}
                <div class="mb-8 flex items-center gap-3 lg:hidden">
                    <img src="{{ asset('assets/img/bpbd-logo.png') }}" alt="Logo BPBD Kota Bandung"
                        class="h-11 w-11 rounded-xl object-cover">
                    <div class="leading-tight">
                        <p class="text-sm font-bold text-[#101a34]">BPBD KOTA BANDUNG</p>
                        <p class="text-xs text-slate-500">e-SKM</p>
                    </div>
                </div>

                <h2 class="text-2xl font-extrabold text-[#101a34] sm:text-3xl">
                    Masuk ke Dashboard
                </h2>
                <p class="mt-2 text-sm text-slate-500">
                    Login menggunakan akun administrator yang telah terdaftar.
                </p>

                @if ($errors->any())
                    @if ($isLockout)
                        <div x-show="lockoutSeconds > 0"
                            class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
                            Terlalu banyak percobaan. Coba lagi dalam
                            <strong x-text="lockoutSeconds"></strong> detik.
                        </div>
                    @else
                        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
                            {{ $errorMessage }}
                        </div>
                    @endif
                @endif

                <form method="POST" action="{{ route('admin.login.attempt') }}" class="mt-8 space-y-5">
                    @csrf

                    {{-- Username --}}
                    <div>
                        <label for="username" class="mb-1.5 block text-sm font-semibold text-[#101a34]">
                            Username
                        </label>
                        <input type="text" id="username" name="username" value="{{ old('username') }}"
                            placeholder="Masukkan username" autocomplete="username" required
                            class="w-full rounded-lg border border-slate-200 px-4 py-3 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[#dc4b3f] focus:outline-none focus:ring-2 focus:ring-[#dc4b3f]/20">
                    </div>

                    {{-- Password --}}
                    <div x-data="{ show: false }">
                        <label for="password" class="mb-1.5 block text-sm font-semibold text-[#101a34]">
                            Password
                        </label>
                        <div class="relative">
                            <input :type="show ? 'text' : 'password'" id="password" name="password"
                                placeholder="Masukkan password" autocomplete="current-password" required
                                class="w-full rounded-lg border border-slate-200 px-4 py-3 pr-11 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[#dc4b3f] focus:outline-none focus:ring-2 focus:ring-[#dc4b3f]/20">
                            <button type="button" @click="show = !show"
                                class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-slate-400 hover:text-slate-600"
                                :aria-label="show ? 'Sembunyikan password' : 'Tampilkan password'">
                                {{-- Ikon mata (tampil) --}}
                                <svg x-show="!show" class="h-5 w-5" fill="none" stroke="currentColor"
                                    stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                {{-- Ikon mata dicoret (sembunyi) --}}
                                <svg x-show="show" x-cloak class="h-5 w-5" fill="none" stroke="currentColor"
                                    stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3.98 8.223A10.477 10.477 0 001.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" :disabled="lockoutSeconds > 0"
                        class="w-full rounded-lg bg-[#dc4b3f] px-4 py-3.5 text-sm font-semibold text-white shadow transition hover:bg-[#c9392e] disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:bg-[#dc4b3f]">
                        Masuk
                    </button>
                </form>

                <div class="mt-6 space-y-2 text-center text-sm">
                    <p class="text-slate-500">
                        Lupa password? Hubungi administrator sistem.
                    </p>
                    <a href="{{ route('home') }}" class="inline-block font-medium text-[#dc4b3f] hover:text-[#c9392e]">
                        &larr; Kembali ke Beranda
                    </a>
                </div>
            </div>
        </div>
    </div>

</body>

</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('code', 'Error') — e-SKM · BPBD Kota Bandung</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Hapus baris CDN ini bila Font Awesome sudah dibundle via npm --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="relative flex min-h-screen flex-col overflow-hidden bg-[#101a34] text-white antialiased">

    <style>
        .error-page-grid {
            background-image: linear-gradient(rgba(255,255,255,.035) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.035) 1px, transparent 1px);
            background-size: 42px 42px;
            mask-image: linear-gradient(to bottom, rgba(0,0,0,.9), transparent 78%);
        }

        .error-panel {
            animation: error-panel-in .65s ease-out both;
        }

        .error-logo {
            animation: error-logo-in .7s ease-out both;
        }

        @keyframes error-panel-in {
            from { opacity: 0; transform: translateY(14px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes error-logo-in {
            from { opacity: 0; transform: scale(.88); }
            to { opacity: 1; transform: scale(1); }
        }

        @media (max-width: 640px) {
            .error-panel { padding: 2rem 1.25rem; }
        }
    </style>

    <div class="error-page-grid pointer-events-none absolute inset-0"></div>

    {{-- ========== DEKORASI BACKGROUND (glow halus) ========== --}}
    <div class="pointer-events-none absolute left-1/2 top-1/2 h-[36rem] w-[36rem] -translate-x-1/2 -translate-y-1/2 rounded-full bg-[#dc4b3f]/10 blur-3xl"></div>
    <div class="pointer-events-none absolute -right-24 -top-24 h-80 w-80 rounded-full bg-[#4c6fff]/10 blur-3xl"></div>

    {{-- ========== KONTEN SATU KOLOM DI TENGAH ========== --}}
    <main class="relative z-10 mx-auto flex w-full max-w-3xl flex-1 flex-col items-center justify-center px-5 pb-14 pt-6 text-center sm:px-8 sm:pb-20">

        <div class="error-panel w-full max-w-xl rounded-2xl border border-white/10 bg-[#162243]/75 px-6 py-10 shadow-2xl shadow-black/20 backdrop-blur-sm sm:px-12 sm:py-12">
            {{-- Logo brand dengan glow --}}
            <div class="error-logo relative mx-auto w-fit">
                <div class="absolute inset-0 -m-5 rounded-full bg-[#dc4b3f]/25 blur-2xl"></div>
                <img src="{{ asset('assets/img/bpbd-logo.png') }}" alt="Logo BPBD Kota Bandung"
                     class="relative h-20 w-20 rounded-full object-cover shadow-xl shadow-black/40 ring-4 ring-white/10 sm:h-24 sm:w-24">
            </div>

            {{-- Kode error --}}
            <p class="mt-7 whitespace-nowrap text-[2.65rem] font-extrabold leading-none tracking-[-0.04em] sm:mt-8 sm:text-7xl">
                @yield('code', 'Error')<span class="text-[#dc4b3f]">-error</span>
            </p>

            {{-- Judul --}}
            <h1 class="mt-4 text-lg font-extrabold uppercase leading-tight tracking-[0.12em] text-slate-100 sm:text-2xl sm:tracking-[0.16em]">
                @yield('title', 'Terjadi Kesalahan')
            </h1>

            {{-- Pesan --}}
            <p class="mx-auto mt-4 max-w-lg text-[13px] leading-6 text-slate-400 sm:text-sm sm:leading-7">
                @yield('message')
            </p>

            <a href="{{ url('/') }}"
               class="mt-8 inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-[#dc4b3f] px-6 py-3 text-[13px] font-bold text-white shadow-lg shadow-[#dc4b3f]/25 transition hover:-translate-y-0.5 hover:bg-[#c9392e] focus:outline-none focus:ring-2 focus:ring-[#dc4b3f] focus:ring-offset-2 focus:ring-offset-[#162243] sm:px-7">
                <i class="fa-solid fa-house text-xs" aria-hidden="true"></i>
                Kembali ke Beranda
            </a>
        </div>
    </main>

    {{-- ========== FOOTER KECIL ========== --}}
    <footer class="relative z-10 mx-auto w-full max-w-6xl px-6 pb-6 sm:px-8 sm:pb-8">
        <p class="text-center text-[10px] leading-4 text-slate-500 sm:text-[11px]">
            © {{ date('Y') }} BPBD Kota Bandung — e-SKM · Elektronik Survei Kepuasan Masyarakat
        </p>
    </footer>
</body>
</html>
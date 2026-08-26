@php
    $currentPage = request()->routeIs('admin.users') ? 'pengguna'
        : (request()->routeIs('admin.respondens') ? 'responden'
        : (request()->routeIs('admin.kritik-saran') ? 'kritik-saran' 
        : (request()->routeIs('admin.unsur-pelayanan') ? 'unsur-pelayanan'
        : (request()->routeIs('admin.hasil-ikm') ? 'hasil-ikm'
        : (request()->routeIs('admin.dashboard') ? 'dashboard'
        : (request()->routeIs('admin.laporan') ? 'laporan'
        : (request()->routeIs('admin.grafik') ? 'grafik'
        : request('page', 'dashboard'))))))));
@endphp

<aside class="fixed inset-y-0 left-0 z-10 flex w-14 flex-col bg-[#102342] text-slate-400 lg:w-48">

    {{-- ================= LOGO ================= --}}
    <div class="flex h-16 items-center justify-center gap-2 px-3 lg:justify-start">
        <div class="grid h-9 w-9 shrink-0 place-content-center overflow-hidden rounded-full border-2 border-white bg-white">
            <img
                class="h-full w-full object-cover"
                src="{{ asset('assets/img/bpbd-logo.png') }}"
                alt="Logo BPBD Kota Bandung"
            >
        </div>
        <div class="hidden min-w-0 lg:block">
            <strong class="block text-xs text-white">e-SKM</strong>
            <span class="mt-0.5 block text-[9px] text-slate-500">BPBD Kota Bandung</span>
        </div>
    </div>

    {{-- ================= MENU NAVIGASI ================= --}}
    @php
        $hiddenForPetugas = ['pengguna'];

        $menus = [
            ['dashboard', 'Dashboard', 'fa-gauge-high'],
            ['pengguna', 'Pengguna', 'fa-users'],
            // ['periode-survei', 'Periode Survei', 'fa-calendar-days'],
            ['unsur-pelayanan', 'Unsur Pelayanan', 'fa-list-check'],
            ['responden', 'Responden', 'fa-user-group'],
            // ['hasil-survei', 'Hasil Survei', 'fa-square-poll-vertical'],
            ['kritik-saran', 'Kritik & Saran', 'fa-comments'],
            ['hasil-ikm', 'Hasil IKM', 'fa-chart-line'],
            ['grafik', 'Grafik', 'fa-chart-column'],
            ['laporan', 'Laporan', 'fa-file-lines'],
        ];
    @endphp

    <nav class="grid gap-1 p-2" aria-label="Navigasi admin">
        @foreach ($menus as [$slug, $label, $icon])
            @if (auth()->user()->role === 'petugas' && in_array($slug, $hiddenForPetugas))
                @continue
            @endif

            @php
                $href = match ($slug) {
                    'pengguna' => route('admin.users'),
                    'responden' => route('admin.respondens'),
                    'kritik-saran' => route('admin.kritik-saran'),
                    'unsur-pelayanan' => route('admin.unsur-pelayanan'),
                    'hasil-ikm' => route('admin.hasil-ikm'),
                    // 'grafik' => route('admin.grafik'),
                    'laporan' => route('admin.laporan'),
                    default => route('admin.dashboard', ['page' => $slug]),
                };
            @endphp

            <a
                href="{{ $href }}"
                class="flex h-9 cursor-pointer items-center justify-center gap-2 rounded-md px-2 text-xs transition-colors duration-150 hover:bg-white/10 hover:text-white lg:justify-start {{ $currentPage === $slug ? 'bg-[#c43b2d] font-semibold text-white' : '' }}"
            >
                <i class="fa-solid {{ $icon }} grid w-4 place-content-center text-sm"></i>
                <span class="hidden lg:inline">{{ $label }}</span>
            </a>
        @endforeach
    </nav>

    {{-- ================= PROFIL & LOGOUT ================= --}}
    <div class="mt-auto border-t border-white/10 p-3">
        <div class="mb-4 flex items-center justify-center gap-2 lg:justify-start">
            <span class="grid h-7 w-7 shrink-0 place-content-center rounded-full bg-[#c43b2d] text-xs font-semibold text-white">
                {{ strtoupper(Str::substr(auth()->user()->name, 0, 1)) }}
            </span>
            <div class="hidden lg:block">
                <strong class="block text-[10px] text-slate-200">{{ auth()->user()->name }}</strong>
                <span class="mt-0.5 block text-[9px] text-slate-500">{{ auth()->user()->role }}</span>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button
                type="submit"
                class="flex w-full cursor-pointer items-center justify-center gap-1.5 rounded-md p-3 text-xs font-semibold text-[#e44b3d] transition-colors duration-150 hover:bg-white/10 lg:justify-start"
            >
                <i class="fa-solid fa-right-from-bracket text-sm" aria-hidden="true"></i>
                <span class="hidden lg:inline">Keluar</span>
            </button>
        </form>
    </div>
</aside>
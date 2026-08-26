<header x-data="{ scrolled: false, open: false }" x-init="scrolled = window.scrollY > 10;
window.addEventListener('scroll', () => { scrolled = window.scrollY > 10 })"
    :class="scrolled
        ?
        'bg-[#101a34]/70 backdrop-blur-md shadow-lg shadow-black/20' :
        'bg-[#101a34] backdrop-blur-0'"
    class="sticky top-0 z-50 border-b border-white/10 transition-all duration-300">
    <nav class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 sm:py-4">

        {{-- Logo + Nama Instansi --}}
        <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-2.5 sm:gap-3">
            <img src="{{ asset('assets/img/bpbd-logo.png') }}" alt="Logo BPBD Kota Bandung"
                class="h-9 w-9 shrink-0 rounded-full object-cover sm:h-10 sm:w-10">
            <span class="min-w-0 leading-tight">
                <span class="block truncate text-xs font-bold tracking-wide text-white sm:text-sm">
                    BPBD KOTA BANDUNG
                </span>
                <span class="hidden truncate text-xs text-slate-300 sm:block">
                    e-SKM · Elektronik Survei Kepuasan Masyarakat
                </span>
            </span>
        </a>

        {{-- Menu Navigasi — versi desktop/tablet --}}
        <div class="hidden items-center gap-3 sm:flex">
            <a href="{{ route('home') }}" class="rounded-lg justify-center text-sm font-medium text-slate-200 shadow hover:text-white transition">
                Beranda
            </a>

            <a href=""
                class="rounded-lg bg-[#dc4b3f] px-4 py-2 text-sm font-semibold text-white shadow hover:bg-[#c9392e] transition">
                Mulai Survei
            </a>

            <a href="{{ auth()->check() ? route('admin.dashboard') : route('admin.login') }}"
                class="rounded-lg border border-white/20 px-4 py-2 text-sm font-semibold text-white hover:bg-white/10 transition">
                {{ auth()->check() ? 'Dashboard' : 'Admin' }}
            </a>
        </div>

        {{-- Tombol hamburger — hanya tampil di mobile --}}
        <button type="button" @click="open = !open"
            class="flex h-9 w-9 items-center justify-center rounded-lg border border-white/20 text-white sm:hidden"
            :aria-expanded="open" aria-label="Buka menu navigasi">
            <svg x-show="!open" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg x-show="open" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </nav>

    {{-- Panel menu mobile — dropdown di bawah navbar --}}
    <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2" @click.outside="open = false"
        class="border-t border-white/10 bg-[#101a34] px-4 pb-4 pt-2 sm:hidden">
        <a href="{{ route('home') }}"
            class="block rounded-lg px-3 py-2.5 text-center text-sm font-medium text-slate-200 hover:bg-white/5 hover:text-white">
            Beranda
        </a>

        <a href=""
            class="mt-2 block rounded-lg bg-[#dc4b3f] px-3 py-2.5 text-center text-sm font-semibold text-white shadow hover:bg-[#c9392e] transition">
            Mulai Survei
        </a>

        <a href="{{ auth()->check() ? route('admin.dashboard') : route('admin.login') }}"
            class="mt-2 block rounded-lg border border-white/20 px-3 py-2.5 text-center text-sm font-semibold text-white hover:bg-white/10 transition">
            {{ auth()->check() ? 'Dashboard' : 'Admin' }}
        </a>
    </div>
</header>

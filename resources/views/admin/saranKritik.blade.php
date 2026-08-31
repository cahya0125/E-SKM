@extends('layouts.admin')

@section('title', 'Kritik & Saran · e-SKM BPBD Kota Bandung')

@section('content')
<div x-data="saranKritikManagement()" x-cloak class="space-y-5">

    {{-- ================= TABS STATUS ================= --}}
    <div class="flex flex-wrap gap-2">
        <template x-for="tab in tabs" :key="tab.key">
            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-xs font-semibold transition"
                :class="activeTab === tab.key
                    ? 'bg-[#102342] text-white'
                    : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50'"
                @click="activeTab = tab.key; page = 1"
            >
                <span x-text="tab.label"></span>
                <span
                    class="grid h-4.5 min-w-4.5 place-content-center rounded-full px-1.5 text-[10px]"
                    :class="activeTab === tab.key ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500'"
                    x-text="tab.count"
                ></span>
            </button>
        </template>
    </div>

    {{-- ================= GRID CARD ================= --}}
    <section class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
        <template x-for="item in paginatedItems" :key="item.id">
            <article class="flex flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                {{-- Header --}}
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <h3 class="text-sm font-bold text-slate-800 leading-snug" x-text="item.title"></h3>
                        <p class="mt-1 text-[11px] text-slate-400" x-text="`${item.nama} · ${item.tanggal}`"></p>
                    </div>
                    <span
                        class="shrink-0 rounded-full px-3 py-1 text-[10px] font-semibold"
                        :class="statusBadgeClass(item.status)"
                        x-text="statusLabel(item.status)"
                    ></span>
                </div>

                {{-- Quote --}}
                <p class="mt-3 text-xs italic leading-relaxed text-slate-500" x-text="`&quot;${item.saran}&quot;`"></p>

                {{-- Actions --}}
                <div class="mt-auto flex items-center gap-2 pt-4">
                    <button
                        type="button"
                        class="rounded-full border border-blue-200 bg-blue-50 px-4 py-1.5 text-[11px] font-semibold text-blue-600 transition hover:bg-blue-100"
                        @click="openDetail(item)"
                    >
                        Detail
                    </button>

                    <button
                        type="button"
                        class="flex-1 rounded-full px-3 py-1.5 text-[11px] font-semibold transition"
                        :class="nextActionClass(item.status)"
                        :disabled="!nextStatus(item.status)"
                        @click="nextStatus(item.status) && setStatus(item, nextStatus(item.status))"
                        x-text="item.status === 'selesai' ? '✓ Selesai' : nextActionLabel(item.status)"
                    ></button>

                    <button
                        type="button"
                        class="rounded-full border border-rose-200 bg-rose-50 px-4 py-1.5 text-[11px] font-semibold text-rose-500 transition hover:bg-rose-100"
                        @click="remove(item)"
                    >
                        Hapus
                    </button>
                </div>
            </article>
        </template>

        <p class="col-span-full py-10 text-center text-xs text-slate-400" x-show="filteredItems.length === 0">
            Belum ada data kritik & saran untuk kategori ini.
        </p>
    </section>

    <div class="flex flex-col items-center justify-between gap-3 border-t border-slate-100 pt-3 text-[10px] text-slate-400 sm:flex-row" x-show="totalPages > 1">
        <span x-text="`Menampilkan ${paginatedItems.length} dari ${filteredItems.length} data`"></span>
        <div class="flex items-center gap-1">
            <button
                type="button"
                class="grid h-7 w-7 place-content-center rounded-md border border-slate-200 text-slate-400 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
                :disabled="page === 1"
                @click="page = Math.max(1, page - 1)"
                aria-label="Halaman sebelumnya"
            >
                <i class="fa-solid fa-chevron-left text-[10px]" aria-hidden="true"></i>
            </button>

            <template x-for="(item, idx) in pageList" :key="idx">
                <button
                    type="button"
                    class="grid h-7 w-7 place-content-center rounded-md text-[10px] font-semibold transition"
                    :class="item === page ? 'bg-[#102342] text-white' : (item === '...' ? 'cursor-default text-slate-400' : 'border border-slate-200 text-slate-500 hover:bg-slate-50')"
                    :disabled="item === '...'"
                    @click="item !== '...' && (page = item)"
                    x-text="item"
                ></button>
            </template>

            <button
                type="button"
                class="grid h-7 w-7 place-content-center rounded-md border border-slate-200 text-slate-400 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
                :disabled="page === totalPages"
                @click="page = Math.min(totalPages, page + 1)"
                aria-label="Halaman berikutnya"
            >
                <i class="fa-solid fa-chevron-right text-[10px]" aria-hidden="true"></i>
            </button>
        </div>
    </div>

    {{-- ================= MODAL DETAIL ================= --}}
    <div
        class="fixed inset-0 z-30 grid place-items-center bg-slate-900/50 p-5"
        x-show="detailOpen"
        x-transition.opacity
        @keydown.escape.window="detailOpen = false"
    >
        <section
            class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl"
            @click.outside="detailOpen = false"
            role="dialog"
            aria-modal="true"
            aria-labelledby="detail-modal-title"
            x-show="detailItem"
        >
            <div class="relative bg-[#102342] p-5 pr-10 text-white">
                <button
                    type="button"
                    class="absolute right-4 top-4 cursor-pointer text-xl leading-none text-slate-300 hover:text-white"
                    @click="detailOpen = false"
                >
                    ×
                </button>
                <span
                    class="absolute right-12 top-5 rounded-full px-2.5 py-1 text-[10px] font-semibold"
                    :class="statusBadgeClass(detailItem?.status)"
                    x-text="statusLabel(detailItem?.status)"
                ></span>
                <h2 id="detail-modal-title" class="text-base font-bold" x-text="detailItem?.title"></h2>
                <p class="mt-1 text-[11px] text-slate-300" x-text="`${detailItem?.nama} · ${detailItem?.tanggal}`"></p>
            </div>

            <div class="p-5">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Kritik &amp; Saran</p>
                <blockquote class="mt-2 rounded-r-lg border-l-4 border-amber-400 bg-amber-50 p-4 text-xs italic leading-relaxed text-slate-700" x-text="`&quot;${detailItem?.saran}&quot;`"></blockquote>

                <p class="mt-5 text-[10px] font-semibold uppercase tracking-wide text-slate-400">Ubah Status</p>
                <div class="mt-2 grid grid-cols-3 gap-2">
                    <template x-for="option in ['baru', 'ditinjau', 'selesai']" :key="option">
                        <button
                            type="button"
                            class="rounded-lg border py-2.5 text-xs font-semibold transition"
                            :class="detailItem?.status === option ? statusActiveClass(option) : 'border-slate-200 text-slate-500 hover:bg-slate-50'"
                            @click="setStatus(detailItem, option)"
                            x-text="statusLabel(option)"
                        ></button>
                    </template>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button
                        type="button"
                        class="rounded-lg border border-rose-200 px-4 py-2.5 text-xs font-semibold text-rose-500 transition hover:bg-rose-50"
                        @click="remove(detailItem)"
                    >
                        Hapus
                    </button>
                    <button
                        type="button"
                        class="rounded-lg bg-[#102342] px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-[#0b1a33]"
                        @click="detailOpen = false"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
    function saranKritikManagement() {
        return {
            items: @json($items),
            activeTab: 'semua',
            detailOpen: false,
            detailItem: null,
            page: 1,
            perPage: 9,

            get tabs() {
                return [
                    { key: 'semua', label: 'Semua', count: this.items.length },
                    { key: 'baru', label: 'Baru', count: this.items.filter(i => i.status === 'baru').length },
                    { key: 'ditinjau', label: 'Ditinjau', count: this.items.filter(i => i.status === 'ditinjau').length },
                    { key: 'selesai', label: 'Selesai', count: this.items.filter(i => i.status === 'selesai').length },
                ];
            },

            get filteredItems() {
                const items = this.activeTab === 'semua'
                    ? this.items
                    : this.items.filter(item => item.status === this.activeTab);

                const statusPriority = { baru: 1, ditinjau: 2, selesai: 3 };

                return [...items].sort((a, b) => {
                    const statusDiff = (statusPriority[a.status] ?? 99) - (statusPriority[b.status] ?? 99);
                    if (statusDiff !== 0) return statusDiff;
                    return (b.id ?? 0) - (a.id ?? 0);
                });
            },

            get totalPages() {
                return Math.max(1, Math.ceil(this.filteredItems.length / this.perPage));
            },

            get paginatedItems() {
                if (this.page > this.totalPages) this.page = this.totalPages;
                const start = (this.page - 1) * this.perPage;
                return this.filteredItems.slice(start, start + this.perPage);
            },

            get pageList() {
                const total = this.totalPages;
                const current = Math.min(this.page, total);
                const delta = 1;
                const range = [];

                for (let i = Math.max(2, current - delta); i <= Math.min(total - 1, current + delta); i++) {
                    range.push(i);
                }

                if (current - delta > 2) range.unshift('...');
                if (current + delta < total - 1) range.push('...');

                range.unshift(1);
                if (total > 1) range.push(total);

                return range;
            },

            statusLabel(status) {
                return { baru: 'Baru', ditinjau: 'Ditinjau', selesai: 'Selesai' }[status] ?? '-';
            },

            statusBadgeClass(status) {
                return {
                    baru: 'bg-red-50 text-red-500 border border-red-100',
                    ditinjau: 'bg-amber-50 text-amber-600 border border-amber-100',
                    selesai: 'bg-green-50 text-green-600 border border-green-100',
                }[status] ?? 'bg-slate-100 text-slate-500';
            },

            statusActiveClass(status) {
                return {
                    baru: 'border-red-500 bg-red-50 text-red-500',
                    ditinjau: 'border-amber-500 bg-amber-400 text-white',
                    selesai: 'border-green-500 bg-green-500 text-white',
                }[status] ?? '';
            },

            nextStatus(status) {
                return { baru: 'ditinjau', ditinjau: 'selesai' }[status] ?? null;
            },

            nextActionLabel(status) {
                return { baru: 'Tinjau', ditinjau: 'Tandai Selesai' }[status] ?? '';
            },

            nextActionClass(status) {
                if (status === 'baru') return 'bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100';
                if (status === 'ditinjau') return 'bg-green-50 text-green-700 border border-green-200 hover:bg-green-100';
                return 'cursor-default bg-slate-50 text-slate-400 border border-slate-200';
            },

            async request(url, options = {}) {
                const response = await fetch(url, {
                    ...options,
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        ...options.headers,
                    },
                });

                const payload = await response.json();

                if (!response.ok) {
                    throw new Error(payload.message ?? 'Terjadi kesalahan.');
                }

                return payload;
            },

            openDetail(item) {
                this.detailItem = item;
                this.detailOpen = true;
            },

            async setStatus(item, status) {
                if (!item || item.status === status) return;

                try {
                    const updated = await this.request(`/admin/saran-kritik/${item.id}/status`, {
                        method: 'PATCH',
                        body: JSON.stringify({ status }),
                    });

                    const index = this.items.findIndex(i => i.id === item.id);
                    if (index !== -1) this.items[index] = updated;
                    if (this.detailItem?.id === item.id) this.detailItem = updated;

                    window.showAlert('Status berhasil diperbarui.', 'success');
                } catch (error) {
                    window.showAlert(error.message, 'error');
                }
            },

            async remove(item) {
                if (!item) return;

                const confirmed = await window.confirmAction(`Hapus kritik & saran dari ${item.nama}?`, {
                    title: 'Hapus Kritik & Saran',
                    confirmText: 'Ya, hapus',
                });

                if (!confirmed) return;

                try {
                    await this.request(`/admin/saran-kritik/${item.id}`, { method: 'DELETE' });
                    this.items = this.items.filter(i => i.id !== item.id);
                    this.detailOpen = false;
                    window.showAlert('Kritik & saran berhasil dihapus.', 'success');
                } catch (error) {
                    window.showAlert(error.message, 'error');
                }
            },
        };
    }
</script>
@endsection
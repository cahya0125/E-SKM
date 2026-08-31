@extends('layouts.admin')

@section('title', 'Responden · e-SKM BPBD Kota Bandung')

@section('content')
<div x-data="respondenManagement()" x-cloak class="space-y-5">
    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-slate-100 p-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-col gap-2 sm:flex-row">
                <label class="flex h-9 w-full items-center gap-2 rounded-lg border border-slate-200 px-3 text-slate-400 sm:w-56"><i class="fa-solid fa-magnifying-glass text-[11px]" aria-hidden="true"></i><input class="w-full border-0 text-xs text-slate-600 outline-0" type="search" x-model="search" placeholder="Cari responden..." aria-label="Cari responden"></label>
                <select class="h-9 rounded-lg border border-slate-200 bg-white px-3 text-xs text-slate-600 outline-0" x-model="selectedService" aria-label="Filter layanan"><option value="">Semua Layanan</option><template x-for="service in services" :key="service"><option :value="service" x-text="service"></option></template></select>
            </div>
            <button type="button" class="inline-flex h-9 items-center justify-center gap-2 rounded-lg bg-[#102342] px-4 text-xs font-semibold text-white transition hover:bg-[#1b355f]" title="Export Excel"><i class="fa-solid fa-file-excel" aria-hidden="true"></i> Export Excel</button>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-280 w-full border-collapse text-left text-xs text-slate-600">
                <thead><tr class="bg-slate-50 text-[10px] uppercase text-slate-500"><th class="h-10 w-12 px-3 text-center">No</th><th class="h-10 px-3">Nama</th><th class="h-10 px-3">JK</th><th class="h-10 px-3">Usia</th><th class="h-10 px-3">Pendidikan</th><th class="h-10 px-3">Pekerjaan</th><th class="h-10 px-3">Jenis Layanan</th><th class="h-10 px-3">Tanggal</th><th class="h-10 px-3">Aksi</th></tr></thead>
                <tbody>
                    <template x-for="(responden, index) in paginatedRespondens" :key="responden.id">
                        <tr class="border-t border-slate-100 transition hover:bg-slate-50">
                            <td class="h-11 px-3 text-center text-slate-400" x-text="(page - 1) * perPage + index + 1"></td>
                            <td class="h-11 whitespace-nowrap px-3"><div class="flex items-center gap-2 text-slate-800"><span class="grid h-6 w-6 place-content-center rounded-full bg-[#182c50] text-[10px] font-semibold text-white" x-text="responden.inisial"></span><strong x-text="responden.nama"></strong></div></td>
                            <td class="h-11 whitespace-nowrap px-3"><span class="rounded-full px-2 py-1 text-[10px] font-semibold" :class="responden.jenisKelaminValue === 'L' ? 'bg-blue-50 text-blue-600' : 'bg-pink-50 text-pink-600'" x-text="responden.jenisKelamin"></span></td>
                            <td class="h-11 whitespace-nowrap px-3" x-text="responden.usia + ' thn'"></td><td class="h-11 whitespace-nowrap px-3" x-text="responden.pendidikan"></td><td class="h-11 whitespace-nowrap px-3" x-text="responden.pekerjaan"></td><td class="h-11 max-w-48 truncate px-3" x-text="responden.jenisLayanan"></td><td class="h-11 whitespace-nowrap px-3 text-slate-400" x-text="responden.tanggal"></td>
                            <td class="h-11 whitespace-nowrap px-3">
                                <div class="flex items-center gap-1.5">
                                    <button type="button" class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-1 text-[10px] font-semibold text-blue-600 transition hover:bg-blue-100" title="Lihat detail" aria-label="Lihat detail" @click="openDetail(responden)"><i class="fa-solid fa-eye text-[9px]" aria-hidden="true"></i> Detail</button>
                                    <button type="button" class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-semibold text-amber-600 transition hover:bg-amber-100" title="Edit responden" aria-label="Edit responden" @click="openEdit(responden)"><i class="fa-solid fa-pen text-[9px]" aria-hidden="true"></i> Edit</button>
                                    <button type="button" class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2.5 py-1 text-[10px] font-semibold text-red-600 transition hover:bg-red-100" title="Hapus responden" aria-label="Hapus responden" @click="remove(responden)"><i class="fa-solid fa-trash text-[9px]" aria-hidden="true"></i> Hapus</button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="filteredRespondens.length === 0"><td colspan="9" class="h-20 text-center text-slate-400">Data responden tidak ditemukan.</td></tr>
                </tbody>
            </table>
        </div>
        <div class="flex flex-col items-center justify-between gap-3 border-t border-slate-100 p-4 text-[10px] text-slate-400 sm:flex-row">
            <span x-text="`Menampilkan ${paginatedRespondens.length} dari ${filteredRespondens.length} responden`"></span>
            <div class="flex items-center gap-1" x-show="totalPages > 1">
                <button type="button" class="grid h-7 w-7 place-content-center rounded-md border border-slate-200 text-slate-400 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40" :disabled="page === 1" @click="page = Math.max(1, page - 1)" aria-label="Halaman sebelumnya"><i class="fa-solid fa-chevron-left text-[10px]" aria-hidden="true"></i></button>
                <template x-for="(item, idx) in pageList" :key="idx">
                    <button type="button" class="grid h-7 w-7 place-content-center rounded-md text-[10px] font-semibold transition" :class="item === page ? 'bg-[#c43b2d] text-white' : (item === '...' ? 'cursor-default text-slate-400' : 'border border-slate-200 text-slate-500 hover:bg-slate-50')" :disabled="item === '...'" @click="item !== '...' && (page = item)" x-text="item"></button>
                </template>
                <button type="button" class="grid h-7 w-7 place-content-center rounded-md border border-slate-200 text-slate-400 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40" :disabled="page === totalPages" @click="page = Math.min(totalPages, page + 1)" aria-label="Halaman berikutnya"><i class="fa-solid fa-chevron-right text-[10px]" aria-hidden="true"></i></button>
            </div>
        </div>
    </section>

    <div class="fixed inset-0 z-30 grid place-items-center bg-slate-900/50 p-3 sm:p-5" x-show="modalOpen" x-transition.opacity @keydown.escape.window="modalOpen = false">
        <section class="max-h-[calc(100vh-24px)] w-full max-w-xl overflow-hidden rounded-xl bg-white shadow-2xl" @click.outside="modalOpen = false" role="dialog" aria-modal="true">
            <header class="flex items-center justify-between px-4 py-3 text-white" :class="detailMode ? 'bg-[#243d70]' : 'bg-[#c86a08]'">
                <div class="flex items-center gap-3">
                    <span class="grid h-9 w-9 place-content-center rounded-full bg-[#c43b2d] text-sm font-bold text-white" x-show="detailMode" x-text="activeResponden?.inisial ?? ''"></span>
                    <span class="grid h-9 w-9 place-content-center rounded-full bg-white/20" x-show="!detailMode"><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i></span>
                    <div><h2 class="text-sm font-bold" x-text="detailMode ? (activeResponden?.nama ?? 'Detail Responden') : 'Edit Responden'"></h2><p class="text-[10px] text-white/75" x-text="`${activeResponden?.survei ?? ''} · ${activeResponden?.tanggal ?? ''}`"></p></div>
                </div>
                <button type="button" class="text-xl text-white/80 hover:text-white" @click="modalOpen = false" aria-label="Tutup">&times;</button>
            </header>
            <div class="max-h-[calc(100vh-100px)] overflow-y-auto p-4 sm:p-5">
                <div x-show="detailMode" class="space-y-4 text-xs">
                    <div class="grid grid-cols-1 gap-3 rounded-lg border border-red-200 bg-red-50/70 p-3 sm:grid-cols-[1fr_auto]"><div><span class="block text-[9px] uppercase text-slate-400">Nilai IKM Responden</span><strong class="text-2xl leading-none text-[#c43b2d]" x-text="activeResponden?.ikm ?? '-' "></strong></div><div><span class="block text-[9px] text-slate-400">Jenis Layanan</span><strong x-text="activeResponden?.jenisLayanan ?? '-' "></strong></div></div>
                    <div><h3 class="mb-2 font-semibold text-slate-700"><i class="fa-regular fa-user text-red-500" aria-hidden="true"></i> Data Responden</h3><div class="grid grid-cols-2 gap-1.5 sm:grid-cols-3"><template x-for="item in detailItems" :key="item.label"><div class="rounded-lg bg-slate-50 p-2"><span class="block text-[8px] uppercase text-slate-400" x-text="item.label"></span><strong class="mt-1 block text-[11px] text-slate-700" x-text="item.value || '-' "></strong></div></template></div></div>
                    <div><h3 class="mb-2 font-semibold text-slate-700"><i class="fa-regular fa-star text-red-500" aria-hidden="true"></i> Penilaian Unsur Pelayanan</h3><div class="space-y-1.5"><template x-for="(rating, index) in (activeResponden?.ratings ?? [])" :key="rating.label"><div class="flex items-center gap-2 rounded-lg bg-slate-50 px-2 py-2"><span class="grid h-5 w-5 shrink-0 place-content-center rounded-full bg-[#182c50] text-[8px] font-semibold text-white" x-text="'U' + (index + 1)"></span><span class="min-w-0 flex-1 truncate text-[10px]" x-text="rating.label"></span><span class="flex w-24 shrink-0 items-center justify-start gap-1.5"><template x-for="star in 5" :key="star"><span class="h-4 w-1.5 shrink-0 rounded-sm" :class="star <= rating.value ? ratingSolidClass(rating.value) : 'bg-slate-200'"></span></template></span><span class="w-24 shrink-0 rounded-full px-2 py-0.5 text-center text-[9px] font-semibold" :class="ratingBadgeClass(rating.value)" x-text="rating.value + ' - ' + ratingText(rating.value)"></span></div></template><p x-show="!activeResponden?.ratings?.length" class="rounded-lg bg-slate-50 p-3 text-center text-slate-400">Belum ada penilaian survei.</p></div></div>
                    <div><h3 class="mb-2 font-semibold text-slate-700"><i class="fa-regular fa-flag text-red-500" aria-hidden="true"></i> Kritik & Saran</h3><blockquote class="rounded-lg border border-amber-300 border-l-4 bg-amber-50 p-3 text-[11px] italic text-slate-600" x-text="activeResponden?.saran ? `&quot;${activeResponden.saran}&quot;` : 'Belum ada kritik atau saran.'"></blockquote></div>
                </div>
                <form x-show="!detailMode" class="space-y-4 text-[10px] font-semibold text-slate-700" @submit.prevent="save">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <label>Nama Lengkap<input class="mt-1 block h-8 w-full rounded-md border border-slate-200 px-2 text-xs font-normal outline-0 focus:border-[#c86a08]" x-model="form.nama" required></label>
                        <div>Jenis Kelamin<div class="mt-1 flex h-8 items-center gap-4 text-xs font-normal text-slate-600"><label class="inline-flex items-center gap-1.5"><input type="radio" name="jenis_kelamin" value="L" x-model="form.jenis_kelamin" class="h-3.5 w-3.5 accent-[#c86a08]"> Laki-laki</label><label class="inline-flex items-center gap-1.5"><input type="radio" name="jenis_kelamin" value="P" x-model="form.jenis_kelamin" class="h-3.5 w-3.5 accent-[#c86a08]"> Perempuan</label></div></div>
                        <label>Usia<input class="mt-1 block h-8 w-full rounded-md border border-slate-200 px-2 text-xs font-normal outline-0" x-model="form.usia" required></label>
                        <label>Pendidikan<input class="mt-1 block h-8 w-full rounded-md border border-slate-200 px-2 text-xs font-normal outline-0" x-model="form.pendidikan" required></label>
                        <label>Pekerjaan<select class="mt-1 block h-8 w-full rounded-md border border-slate-200 bg-white px-2 text-xs font-normal outline-0" x-model="form.pekerjaan" required><option value="" disabled>Pilih pekerjaan</option><template x-for="option in pekerjaanOptions" :key="option"><option :value="option" x-text="option"></option></template></select></label>
                        <label>No. HP<input class="mt-1 block h-8 w-full rounded-md border border-slate-200 px-2 text-xs font-normal outline-0" x-model="form.no_hp"></label>
                    </div>
                    <label class="block uppercase text-slate-500">Jenis Layanan<select class="mt-1 block h-8 w-full rounded-md border border-slate-200 bg-white px-2 text-xs font-normal outline-0" x-model="form.jenis_layanan"><template x-for="service in services" :key="service"><option :value="service" x-text="service"></option></template></select></label>
                    <div><h3 class="mb-2 text-[10px] uppercase text-slate-500">Penilaian Unsur Pelayanan</h3><div class="space-y-1.5"><template x-for="(rating, index) in (activeResponden?.ratings ?? [])" :key="rating.label"><div class="flex items-center gap-2 rounded-lg bg-slate-50 px-2 py-1.5"><span class="grid h-5 w-5 shrink-0 place-content-center rounded-full bg-[#182c50] text-[8px] font-semibold text-white" x-text="'U' + (index + 1)"></span><span class="min-w-0 flex-1 truncate text-[10px] font-normal" x-text="rating.label"></span><div class="flex gap-0.5"><template x-for="score in 5" :key="score"><button type="button" class="grid h-5 w-5 place-content-center rounded text-[9px]" :class="score === rating.value ? ratingSolidClass(score) : 'bg-slate-200 text-slate-400'" @click="rating.value = score" x-text="score"></button></template></div><span class="w-20 rounded px-1 py-1 text-center text-[9px]" :class="ratingBadgeClass(rating.value)" x-text="ratingText(rating.value)"></span></div></template><p x-show="!activeResponden?.ratings?.length" class="rounded-lg bg-slate-50 p-3 text-center font-normal text-slate-400">Belum ada penilaian survei.</p></div></div>
                    <label class="block uppercase text-slate-500">Kritik & Saran<textarea class="mt-1 block min-h-16 w-full resize-y rounded-md border border-slate-200 p-2 text-xs font-normal outline-0 focus:border-[#c86a08]" x-model="form.saran"></textarea></label>
                    <p class="text-xs font-normal text-red-500" x-show="error" x-text="error"></p>
                    <div class="flex justify-end gap-2 border-t border-slate-100 pt-3"><button type="button" class="rounded-md border border-slate-200 px-4 py-2 text-xs text-slate-600" @click="modalOpen = false">Batal</button><button type="submit" class="rounded-md bg-[#c86a08] px-4 py-2 text-xs font-semibold text-white">Simpan Perubahan</button></div>
                </form>
            </div>
            <footer x-show="detailMode" class="flex justify-end gap-2 border-t border-slate-100 px-4 py-3 sm:px-5"><button type="button" class="rounded-md border border-slate-200 px-4 py-2 text-xs text-slate-600" @click="window.print()">Cetak</button><button type="button" class="rounded-md bg-[#182c50] px-4 py-2 text-xs font-semibold text-white" @click="modalOpen = false">Tutup</button></footer>
        </section>
    </div>
</div>

<script>
function respondenManagement() {
    return {
        search: '', selectedService: '', modalOpen: false, detailMode: false, editingId: null, activeResponden: null, error: '', form: {},
        page: 1, perPage: 8,
        pekerjaanOptions: ['Pelajar / Mahasiswa', 'PNS / TNI / Polri', 'Pegawai Swasta', 'Wirausaha / Wiraswasta', 'Petani / Nelayan', 'Pensiunan', 'Tidak Bekerja', 'Lainnya'],
        respondens: @json($respondens),
        init() {
            this.$watch('search', () => { this.page = 1; });
            this.$watch('selectedService', () => { this.page = 1; });
        },
        get services() { return [...new Set(this.respondens.map(item => item.jenisLayanan).filter(item => item !== '-'))]; },
        get filteredRespondens() { const term = this.search.toLowerCase(); return this.respondens.filter(item => `${item.nama} ${item.pendidikan} ${item.pekerjaan}`.toLowerCase().includes(term) && (!this.selectedService || item.jenisLayanan === this.selectedService)); },
        get totalPages() { return Math.max(1, Math.ceil(this.filteredRespondens.length / this.perPage)); },
        get paginatedRespondens() { if (this.page > this.totalPages) this.page = this.totalPages; const start = (this.page - 1) * this.perPage; return this.filteredRespondens.slice(start, start + this.perPage); },
        get pageList() { const total = this.totalPages; const current = Math.min(this.page, total); const delta = 1; const range = []; for (let i = Math.max(2, current - delta); i <= Math.min(total - 1, current + delta); i++) { range.push(i); } if (current - delta > 2) { range.unshift('...'); } if (current + delta < total - 1) { range.push('...'); } range.unshift(1); if (total > 1) { range.push(total); } return range; },
        openDetail(responden) { this.detailMode = true; this.activeResponden = responden; this.modalOpen = true; },
        openEdit(responden) { this.detailMode = false; this.editingId = responden.id; this.activeResponden = responden; this.form = { nama: responden.nama, jenis_kelamin: responden.jenisKelaminValue, usia: responden.usia, pendidikan: responden.pendidikan, pekerjaan: responden.pekerjaan, no_hp: responden.noHp ?? '', jenis_layanan: responden.jenisLayanan ?? '', saran: responden.saran ?? '' }; this.error = ''; this.modalOpen = true; },
        ratingText(value) { return { 1: 'Sangat Tidak Sesuai', 2: 'Tidak Sesuai', 3: 'Cukup Sesuai', 4: 'Sesuai', 5: 'Sangat Sesuai' }[value] ?? 'Belum dinilai'; },
        ratingSolidClass(value) { return { 1: 'bg-red-500 text-white', 2: 'bg-orange-500 text-white', 3: 'bg-amber-500 text-white', 4: 'bg-blue-600 text-white', 5: 'bg-green-600 text-white' }[value] ?? 'bg-slate-200'; },
        ratingBadgeClass(value) { return { 1: 'bg-red-100 text-red-600', 2: 'bg-orange-100 text-orange-600', 3: 'bg-amber-100 text-amber-600', 4: 'bg-blue-100 text-blue-600', 5: 'bg-green-100 text-green-600' }[value] ?? 'bg-slate-100 text-slate-400'; },
        get detailItems() { const item = this.activeResponden ?? {}; return [{ label: 'Jenis Kelamin', value: item.jenisKelamin }, { label: 'Usia', value: item.usia ? `${item.usia} tahun` : '-' }, { label: 'Pendidikan', value: item.pendidikan }, { label: 'Pekerjaan', value: item.pekerjaan }, { label: 'No. HP', value: item.noHp }]; },
        async request(url, options = {}) { const response = await fetch(url, { ...options, headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, ...options.headers } }); const payload = await response.json(); if (!response.ok) throw new Error(Object.values(payload.errors ?? {})[0]?.[0] ?? payload.message ?? 'Terjadi kesalahan.'); return payload; },
        async save() { this.error = ''; try { const editing = Boolean(this.editingId); const payload = editing ? { ...this.form, ratings: this.activeResponden?.ratings ?? [] } : this.form; await this.request(editing ? `/admin/respondens/${this.editingId}` : '{{ route('admin.respondens.store') }}', { method: editing ? 'PATCH' : 'POST', body: JSON.stringify(payload) }); this.respondens = await this.request('{{ route('admin.respondens') }}'); this.modalOpen = false; window.showAlert(editing ? 'Data responden berhasil diperbarui.' : 'Responden berhasil ditambahkan.', 'success'); } catch (error) { this.error = error.message; window.showAlert(error.message, 'error'); } },
        async remove(responden) { if (!await window.confirmAction(`Hapus data ${responden.nama}?`, { title: 'Hapus Responden', confirmText: 'Ya, hapus' })) return; try { await this.request(`/admin/respondens/${responden.id}`, { method: 'DELETE' }); this.respondens = this.respondens.filter(item => item.id !== responden.id); window.showAlert('Responden berhasil dihapus.', 'success'); } catch (error) { window.showAlert(error.message, 'error'); } }
    };
}
</script>
@endsection
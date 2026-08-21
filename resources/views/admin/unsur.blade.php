@extends('layouts.admin')

@section('title', 'Unsur Pelayanan · e-SKM BPBD Kota Bandung')

@section('content')
<div x-data="unsurPelayananManagement()" x-cloak class="space-y-5">

    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs text-slate-500">
                <span x-text="items.length"></span> unsur terdaftar 
            </p>

            <button
                type="button"
                class="cursor-pointer rounded-lg bg-[#c43b2d] px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-[#a83227]"
                @click="openCreate"
            >
                + Tambah Unsur
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-245 w-full border-collapse text-left text-xs text-slate-500">
                <thead>
                    <tr class="bg-slate-50 text-[10px] uppercase text-slate-500">
                        <th class="h-14 px-3">Kode</th>
                        <th class="h-14 px-3">Unsur Pelayanan</th>
                        <th class="h-14 px-3">Pertanyaan</th>
                        <th class="h-14 px-3">Status</th>
                        <th class="h-14 px-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(item, index) in items" :key="item.id">
                        <tr class="border-t border-slate-100">
                            <td class="h-14 whitespace-nowrap px-3">
                                <strong class="text-xs text-[#c43b2d]" x-text="item.kode"></strong>
                            </td>
                            <td class="h-14 px-3">
                                <strong class="text-xs text-slate-800" x-text="item.nama_unsur"></strong>
                            </td>
                            <td class="h-14 max-w-xs px-3 text-slate-500" x-text="item.pertanyaan"></td>
                            <td class="h-14 whitespace-nowrap px-3">
                                <span
                                    class="rounded-full px-2.5 py-1 text-[10px] font-semibold"
                                    :class="item.status === 'active' ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-500'"
                                    x-text="item.status === 'active' ? 'Aktif' : 'Tidak Aktif'"
                                ></span>
                            </td>
                            <td class="h-14 whitespace-nowrap px-3">
                                <div class="flex items-center gap-1.5">
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1.5 text-[11px] font-semibold text-amber-500 transition hover:bg-amber-100"
                                        @click="openEdit(item)"
                                    >
                                        <i class="fa-solid fa-pen-to-square text-[10px]" aria-hidden="true"></i>
                                        Edit
                                    </button>

                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-3 py-1.5 text-[11px] font-semibold text-red-500 transition hover:bg-red-100"
                                        @click="remove(item)"
                                    >
                                        <i class="fa-solid fa-trash-can text-[10px]" aria-hidden="true"></i>
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <tr x-show="items.length === 0">
                        <td colspan="5" class="h-20 text-center text-slate-400">Belum ada unsur pelayanan.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    {{-- ================= MODAL TAMBAH / EDIT ================= --}}
    <div
        class="fixed inset-0 z-30 grid place-items-center bg-slate-900/50 p-5"
        x-show="modalOpen"
        x-transition.opacity
        @keydown.escape.window="modalOpen = false"
    >
        <section
            class="max-h-[calc(100vh-40px)] w-full max-w-md overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl"
            @click.outside="modalOpen = false"
            role="dialog"
            aria-modal="true"
            aria-labelledby="unsur-modal-title"
        >
            <div class="mb-5 flex justify-between">
                <div>
                    <h2 id="unsur-modal-title" class="text-lg font-bold text-slate-800" x-text="editingId ? 'Edit Unsur Pelayanan' : 'Tambah Unsur Pelayanan'"></h2>
                    <p class="mt-1 text-xs text-slate-400">Tambahkan unsur penilaian baru ke dalam survei.</p>
                </div>
                <button type="button" class="cursor-pointer text-2xl leading-none text-slate-400" @click="modalOpen = false">×</button>
            </div>

            <form class="grid gap-4 text-xs font-semibold text-slate-700" @submit.prevent="save">
                <div class="grid grid-cols-2 gap-3">
                    <label>
                        Kode
                        <input
                            class="mt-1.5 block h-10 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 font-normal text-slate-500 outline-0"
                            :value="displayKode"
                            disabled
                        >
                    </label>

                    <div>
                        Status
                        <div class="mt-1.5 grid grid-cols-2 gap-2">
                            <button
                                type="button"
                                class="h-10 rounded-lg border border-slate-200 text-slate-500"
                                :class="{ 'border-emerald-500 bg-emerald-50 text-emerald-600': form.status === 'active' }"
                                @click="form.status = 'active'"
                            >
                                Aktif
                            </button>
                            <button
                                type="button"
                                class="h-10 rounded-lg border border-slate-200 text-slate-500"
                                :class="{ 'border-slate-500 bg-slate-100': form.status === 'inactive' }"
                                @click="form.status = 'inactive'"
                            >
                                Tidak Aktif
                            </button>
                        </div>
                    </div>
                </div>

                <label>
                    Nama Unsur Pelayanan <b class="text-[#c43b2d]">*</b>
                    <input
                        class="mt-1.5 block h-10 w-full rounded-lg border border-slate-200 px-3 font-normal outline-0 focus:border-[#c43b2d]"
                        x-model="form.nama_unsur"
                        placeholder="Contoh: Persyaratan pelayanan"
                        required
                    >
                </label>

                <label>
                    Pertanyaan <b class="text-[#c43b2d]">*</b>
                    <textarea
                        class="mt-1.5 block w-full rounded-lg border border-slate-200 px-3 py-2 font-normal outline-0 focus:border-[#c43b2d]"
                        x-model="form.pertanyaan"
                        rows="3"
                        placeholder="Contoh: Bagaimana pendapat Anda tentang kesesuaian persyaratan pelayanan?"
                        required
                    ></textarea>
                </label>

                <p class="text-xs font-normal text-red-500" x-show="error" x-text="error"></p>

                <div class="flex justify-end gap-3">
                    <button type="button" class="rounded-lg border border-slate-200 px-4 py-2.5 text-xs text-slate-600" @click="modalOpen = false">Batal</button>
                    <button type="submit" class="rounded-lg bg-[#c43b2d] px-4 py-2.5 text-xs font-semibold text-white" x-text="editingId ? 'Simpan Perubahan' : 'Tambah'"></button>
                </div>
            </form>
        </section>
    </div>
</div>

<script>
    function unsurPelayananManagement() {
        return {
            items: @json($items),
            modalOpen: false,
            editingId: null,
            form: { nama_unsur: '', pertanyaan: '', status: 'active' },
            error: '',

            get displayKode() {
                if (this.editingId) {
                    return this.items.find(i => i.id === this.editingId)?.kode ?? '';
                }
                return `U${this.items.length + 1}`;
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
                    throw new Error(Object.values(payload.errors ?? {})[0]?.[0] ?? payload.message ?? 'Terjadi kesalahan.');
                }

                return payload;
            },

            openCreate() {
                this.editingId = null;
                this.form = { nama_unsur: '', pertanyaan: '', status: 'active' };
                this.error = '';
                this.modalOpen = true;
            },

            openEdit(item) {
                this.editingId = item.id;
                this.form = {
                    nama_unsur: item.nama_unsur,
                    pertanyaan: item.pertanyaan,
                    status: item.status,
                };
                this.error = '';
                this.modalOpen = true;
            },

            async save() {
                this.error = '';
                try {
                    const isEditing = Boolean(this.editingId);
                    const url = isEditing
                        ? `/admin/unsur-pelayanan/${this.editingId}`
                        : '{{ route('admin.unsur-pelayanan.store') }}';
                    const method = isEditing ? 'PATCH' : 'POST';

                    this.items = await this.request(url, { method, body: JSON.stringify(this.form) });
                    this.modalOpen = false;
                    window.showAlert(isEditing ? 'Unsur pelayanan berhasil diperbarui.' : 'Unsur pelayanan berhasil ditambahkan.', 'success');
                } catch (error) {
                    this.error = error.message;
                    window.showAlert(error.message, 'error');
                }
            },

            async remove(item) {
                const confirmed = await window.confirmAction(`Hapus unsur "${item.nama_unsur}"?`, {
                    title: 'Hapus Unsur Pelayanan',
                    confirmText: 'Ya, hapus',
                });

                if (!confirmed) return;

                try {
                    this.items = await this.request(`/admin/unsur-pelayanan/${item.id}`, { method: 'DELETE' });
                    window.showAlert('Unsur pelayanan berhasil dihapus.', 'success');
                } catch (error) {
                    window.showAlert(error.message, 'error');
                }
            },
        };
    }
</script>
@endsection
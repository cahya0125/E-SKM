@extends('layouts.admin')

@section('title', 'Pengguna · e-SKM BPBD Kota Bandung')

@section('content')
<div x-data="userManagement()" x-cloak class="space-y-5">

    {{-- ================= STATISTIK RINGKAS ================= --}}
    <section class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <article class="grid min-h-24 place-content-center rounded-xl border border-slate-200 bg-white text-center shadow-sm">
            <strong class="text-3xl leading-none text-slate-800" x-text="users.length"></strong>
            <span class="mt-2 text-xs text-slate-400">Total Admin</span>
        </article>

        <article class="grid min-h-24 place-content-center rounded-xl border border-slate-200 bg-white text-center shadow-sm">
            <strong class="text-3xl leading-none text-emerald-600" x-text="users.filter(user => user.status === 'Aktif').length"></strong>
            <span class="mt-2 text-xs text-slate-400">Aktif</span>
        </article>

        <article class="grid min-h-24 place-content-center rounded-xl border border-slate-200 bg-white text-center shadow-sm">
            <strong class="text-3xl leading-none text-slate-400" x-text="users.filter(user => user.status === 'Tidak Aktif').length"></strong>
            <span class="mt-2 text-xs text-slate-400">Tidak Aktif</span>
        </article>
    </section>

    {{-- ================= TABEL PENGGUNA ================= --}}
    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
            <label class="flex h-10 w-full items-center gap-2 rounded-lg border border-slate-200 px-3 text-slate-400 sm:w-56">
                <i class="fa-solid fa-magnifying-glass text-[11px]" aria-hidden="true"></i>
                <input
                    class="w-full border-0 text-xs text-slate-600 outline-0"
                    type="search"
                    x-model="search"
                    placeholder="Cari nama atau username..."
                    aria-label="Cari pengguna"
                >
            </label>

            <button
                type="button"
                class="cursor-pointer rounded-lg bg-[#c43b2d] px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-[#a83227]"
                @click="openCreate"
            >
                + Tambah Akun Admin
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-245 w-full border-collapse text-left text-xs text-slate-500">
                <thead>
                    <tr class="bg-slate-50 text-[10px] uppercase text-slate-500">
                        <th class="h-14 px-3">Pengguna</th>
                        <th class="h-14 px-3">Username</th>
                        <th class="h-14 px-3">Email</th>
                        <th class="h-14 px-3">Role</th>
                        <th class="h-14 px-3">Status</th>
                        <th class="h-14 px-3">Login Terakhir</th>
                        <th class="h-14 px-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="user in paginatedUsers" :key="user.id">
                        <tr class="border-t border-slate-100">
                            <td class="h-14 whitespace-nowrap px-3">
                                <div class="flex items-center gap-2.5 text-slate-800">
                                    <span
                                        class="grid h-8 w-8 place-content-center rounded-full border text-xs"
                                        :class="{
                                            'border-red-200 bg-red-50 text-red-500': user.color === 'red',
                                            'border-blue-200 bg-blue-50 text-blue-500': user.color === 'blue',
                                            'border-emerald-200 bg-emerald-50 text-emerald-500': user.color === 'green'
                                        }"
                                        x-text="user.name.charAt(0)"
                                    ></span>
                                    <strong class="text-xs" x-text="user.name"></strong>
                                </div>
                            </td>

                            <td class="h-14 whitespace-nowrap px-3" x-text="user.username"></td>
                            <td class="h-14 whitespace-nowrap px-3" x-text="user.email"></td>

                            <td class="h-14 whitespace-nowrap px-3">
                                <strong
                                    :class="{
                                        'text-red-500': user.color === 'red',
                                        'text-blue-500': user.color === 'blue',
                                        'text-emerald-600': user.color === 'green'
                                    }"
                                    x-text="user.role"
                                ></strong>
                            </td>

                            <td class="h-14 whitespace-nowrap px-3">
                                <span
                                    class="rounded-full px-2.5 py-1 text-[10px] font-semibold"
                                    :class="user.status === 'Aktif' ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-500'"
                                    x-text="user.status"
                                ></span>
                            </td>

                            <td class="h-14 whitespace-nowrap px-3" x-text="user.lastLogin"></td>

                            <td class="h-14 whitespace-nowrap px-3">
                                <div class="flex items-center gap-1.5">
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1.5 text-[11px] font-semibold text-blue-500 transition hover:bg-blue-100"
                                        title="Edit pengguna"
                                        aria-label="Edit pengguna"
                                        @click="openEdit(user)"
                                    >
                                        <i class="fa-solid fa-pen-to-square text-[10px]" aria-hidden="true"></i>
                                        Edit
                                    </button>

                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1.5 text-[11px] font-semibold text-amber-500 transition hover:bg-amber-100"
                                        title="Reset password"
                                        aria-label="Reset password"
                                        @click="openReset(user)"
                                    >
                                        <i class="fa-solid fa-key text-[10px]" aria-hidden="true"></i>
                                        Reset
                                    </button>

                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-3 py-1.5 text-[11px] font-semibold text-red-500 transition hover:bg-red-100"
                                        title="Hapus pengguna"
                                        aria-label="Hapus pengguna"
                                        @click="remove(user)"
                                        x-show="user.id !== {{ Auth::id() }}"
                                    >
                                        <i class="fa-solid fa-trash-can text-[10px]" aria-hidden="true"></i>
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <tr x-show="filteredUsers.length === 0">
                        <td colspan="7" class="h-20 text-center text-slate-400">Data pengguna tidak ditemukan.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-end gap-2 border-t border-slate-100 p-4" x-show="totalPages > 1">
            <button
                type="button"
                class="grid h-8 w-8 place-content-center rounded-lg border border-slate-200 text-slate-400 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
                :disabled="page === 1"
                @click="page--"
                aria-label="Halaman sebelumnya"
            >
                <i class="fa-solid fa-chevron-left text-[10px]" aria-hidden="true"></i>
            </button>

            <template x-for="p in totalPages" :key="p">
                <button
                    type="button"
                    class="grid h-8 w-8 place-content-center rounded-lg border text-xs font-semibold transition"
                    :class="p === page ? 'border-[#c43b2d] bg-[#c43b2d] text-white' : 'border-slate-200 text-slate-500 hover:bg-slate-50'"
                    @click="page = p"
                    x-text="p"
                ></button>
            </template>

            <button
                type="button"
                class="grid h-8 w-8 place-content-center rounded-lg border border-slate-200 text-slate-400 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
                :disabled="page === totalPages"
                @click="page++"
                aria-label="Halaman berikutnya"
            >
                <i class="fa-solid fa-chevron-right text-[10px]" aria-hidden="true"></i>
            </button>
        </div>
    </section>

    {{-- ================= MODAL TAMBAH / EDIT PENGGUNA ================= --}}
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
            aria-labelledby="user-modal-title"
        >
            <div class="mb-5 flex justify-between">
                <div>
                    <h2 id="user-modal-title" class="text-lg font-bold text-slate-800" x-text="editingId ? 'Edit Pengguna' : 'Tambah Akun Admin'"></h2>
                    <p class="mt-1 text-xs text-slate-400">Buat akun baru untuk petugas admin atau operator.</p>
                </div>
                <button type="button" class="cursor-pointer text-2xl leading-none text-slate-400" @click="modalOpen = false">×</button>
            </div>

            <form class="grid gap-4 text-xs font-semibold text-slate-700" @submit.prevent="save">
                <label>
                    Nama Lengkap <b class="text-[#c43b2d]">*</b>
                    <input
                        class="mt-1.5 block h-10 w-full rounded-lg border border-slate-200 px-3 font-normal outline-0 focus:border-[#c43b2d]"
                        x-model="form.name"
                        placeholder="Nama petugas"
                        required
                    >
                </label>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <label>
                        Username <b class="text-[#c43b2d]">*</b>
                        <input
                            class="mt-1.5 block h-10 w-full rounded-lg border border-slate-200 px-3 font-normal outline-0 focus:border-[#c43b2d]"
                            x-model="form.username"
                            placeholder="username"
                            required
                        >
                    </label>

                    <label>
                        Role
                        <select class="mt-1.5 block h-10 w-full rounded-lg border border-slate-200 px-3 font-normal outline-0 focus:border-[#c43b2d]" x-model="form.role">
                            <option value="petugas">Operator</option>
                            <option value="admin">Admin</option>
                        </select>
                    </label>
                </div>

                <label>
                    Email
                    <input
                        class="mt-1.5 block h-10 w-full rounded-lg border border-slate-200 px-3 font-normal outline-0 focus:border-[#c43b2d]"
                        type="email"
                        x-model="form.email"
                        placeholder="email@bpbd-bandung.go.id"
                    >
                </label>

                <label>
                    Password <b class="text-[#c43b2d]">*</b> <small class="font-normal text-slate-400">(min. 6 karakter)</small>
                    <input
                        class="mt-1.5 block h-10 w-full rounded-lg border border-slate-200 px-3 font-normal outline-0 focus:border-[#c43b2d]"
                        type="password"
                        x-model="form.password"
                        placeholder="Buat password"
                        minlength="6"
                        :required="!editingId"
                    >
                </label>

                <label>
                    Konfirmasi Password <b class="text-[#c43b2d]">*</b>
                    <input
                        class="mt-1.5 block h-10 w-full rounded-lg border border-slate-200 px-3 font-normal outline-0 focus:border-[#c43b2d]"
                        type="password"
                        x-model="form.password_confirmation"
                        placeholder="Ulangi password"
                        minlength="6"
                        :required="!editingId"
                    >
                </label>

                <fieldset>
                    <legend class="mb-2">Status</legend>
                    <p class="mb-2 text-[10px] font-normal text-slate-400" x-show="editingId === {{ Auth::id() }}">
                        Status akun sendiri tidak dapat diubah.
                    </p>
                    <div class="grid grid-cols-2 gap-3">
                        <button
                            type="button"
                            class="h-10 rounded-lg border border-slate-200 text-slate-500 disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="editingId === {{ Auth::id() }}"
                            :class="{ 'border-emerald-500 bg-emerald-50 text-emerald-600': form.status === 'active' }"
                            @click="form.status = 'active'"
                        >
                            Aktif
                        </button>
                        <button
                            type="button"
                            class="h-10 rounded-lg border border-slate-200 text-slate-500 disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="editingId === {{ Auth::id() }}"
                            :class="{ 'border-slate-500 bg-slate-100': form.status === 'inactive' }"
                            @click="form.status = 'inactive'"
                        >
                            Tidak Aktif
                        </button>
                    </div>
                </fieldset>

                <p class="text-xs font-normal text-red-500" x-show="error" x-text="error"></p>

                <div class="flex justify-end gap-3">
                    <button type="button" class="rounded-lg border border-slate-200 px-4 py-2.5 text-xs text-slate-600" @click="modalOpen = false">
                        Batal
                    </button>
                    <button type="submit" class="rounded-lg bg-[#c43b2d] px-4 py-2.5 text-xs font-semibold text-white" x-text="editingId ? 'Simpan Perubahan' : 'Buat Akun'"></button>
                </div>
            </form>
        </section>
    </div>

    {{-- ================= MODAL RESET PASSWORD ================= --}}
    <div
        class="fixed inset-0 z-30 grid place-items-center bg-slate-900/50 p-5"
        x-show="resetModalOpen"
        x-transition.opacity
        @keydown.escape.window="resetModalOpen = false"
    >
        <section
            class="max-h-[calc(100vh-40px)] w-full max-w-md overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl"
            @click.outside="resetModalOpen = false"
            role="dialog"
            aria-modal="true"
            aria-labelledby="reset-password-title"
        >
            <div class="mb-5 flex justify-between">
                <div>
                    <h2 id="reset-password-title" class="text-lg font-bold text-slate-800">Reset Password</h2>
                    <p class="mt-1 text-xs text-slate-400">Buat password baru untuk akun <strong x-text="resetUser?.username"></strong>.</p>
                </div>
                <button type="button" class="cursor-pointer text-2xl leading-none text-slate-400" @click="resetModalOpen = false">×</button>
            </div>

            <form class="grid gap-4 text-xs font-semibold text-slate-700" @submit.prevent="resetPassword">
                <label>
                    Password Baru
                    <input
                        class="mt-1.5 block h-10 w-full rounded-lg border border-slate-200 px-3 font-normal outline-0 focus:border-[#c43b2d]"
                        type="password"
                        x-model="resetForm.password"
                        placeholder="Minimal 6 karakter"
                        minlength="6"
                        required
                    >
                </label>

                <label>
                    Konfirmasi Password
                    <input
                        class="mt-1.5 block h-10 w-full rounded-lg border border-slate-200 px-3 font-normal outline-0 focus:border-[#c43b2d]"
                        type="password"
                        x-model="resetForm.password_confirmation"
                        placeholder="Ulangi password baru"
                        minlength="6"
                        required
                    >
                </label>

                <p class="text-xs font-normal text-red-500" x-show="resetError" x-text="resetError"></p>

                <div class="flex justify-end gap-3">
                    <button type="button" class="rounded-lg border border-slate-200 px-4 py-2.5 text-xs text-slate-600" @click="resetModalOpen = false">
                        Batal
                    </button>
                    <button type="submit" class="rounded-lg bg-[#c43b2d] px-4 py-2.5 text-xs font-semibold text-white">
                        Reset Password
                    </button>
                </div>
            </form>
        </section>
    </div>
</div>

<script>
    function userManagement() {
        return {
            search: '',
            modalOpen: false,
            resetModalOpen: false,
            editingId: null,
            resetUser: null,
            error: '',
            resetError: '',
            resetForm: {},
            users: @json($users),
            page: 1,
            perPage: 6,
            form: {
                name: '',
                username: '',
                email: '',
                role: 'petugas',
                status: 'active',
                password: '',
                password_confirmation: '',
            },

            init() {
                this.$watch('search', () => { this.page = 1; });
            },

            get filteredUsers() {
                const term = this.search.toLowerCase();
                return this.users.filter(user =>
                    `${user.name} ${user.username} ${user.email ?? ''}`.toLowerCase().includes(term)
                );
            },

            get totalPages() {
                return Math.max(1, Math.ceil(this.filteredUsers.length / this.perPage));
            },

            get paginatedUsers() {
                if (this.page > this.totalPages) this.page = this.totalPages;
                const start = (this.page - 1) * this.perPage;
                return this.filteredUsers.slice(start, start + this.perPage);
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
                this.form = {
                    name: '',
                    username: '',
                    email: '',
                    role: 'petugas',
                    status: 'active',
                    password: '',
                    password_confirmation: '',
                };
                this.error = '';
                this.modalOpen = true;
            },

            openEdit(user) {
                this.editingId = user.id;
                this.form = {
                    name: user.name,
                    username: user.username,
                    email: user.email ?? '',
                    role: user.roleValue,
                    status: user.statusValue,
                    password: '',
                    password_confirmation: '',
                };
                this.error = '';
                this.modalOpen = true;
            },

            openReset(user) {
                this.resetUser = user;
                this.resetForm = { password: '', password_confirmation: '' };
                this.resetError = '';
                this.resetModalOpen = true;
            },

            async save() {
                this.error = '';
                try {
                    const isEditing = Boolean(this.editingId);
                    const url = isEditing ? `/admin/users/${this.editingId}` : '{{ route('admin.users.store') }}';
                    const method = isEditing ? 'PATCH' : 'POST';

                    await this.request(url, { method, body: JSON.stringify(this.form) });

                    this.users = await this.request('{{ route('admin.users') }}');
                    this.modalOpen = false;
                    window.showAlert(isEditing ? 'Data pengguna berhasil diperbarui.' : 'Akun pengguna berhasil dibuat.', 'success');
                } catch (error) {
                    this.error = error.message;
                    window.showAlert(error.message, 'error');
                }
            },

            async resetPassword() {
                this.resetError = '';
                try {
                    await this.request(`/admin/users/${this.resetUser.id}/reset-password`, {
                        method: 'POST',
                        body: JSON.stringify(this.resetForm),
                    });

                    this.resetModalOpen = false;
                    window.showAlert('Password berhasil direset.', 'success');
                } catch (error) {
                    this.resetError = error.message;
                    window.showAlert(error.message, 'error');
                }
            },

            async remove(user) {
                const confirmed = await window.confirmAction(`Hapus akun ${user.name}?`, {
                    title: 'Hapus Pengguna',
                    confirmText: 'Ya, hapus',
                });

                if (!confirmed) return;

                try {
                    await this.request(`/admin/users/${user.id}`, { method: 'DELETE' });
                    this.users = this.users.filter(item => item.id !== user.id);
                    window.showAlert('Akun pengguna berhasil dihapus.', 'success');
                } catch (error) {
                    window.showAlert(error.message, 'error');
                }
            },
        };
    }
</script>
@endsection
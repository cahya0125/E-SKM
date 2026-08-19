@extends('layouts.admin')

@section('title', 'Pengguna · e-SKM BPBD Kota Bandung')

@section('content')
<div x-data="userManagement()" x-cloak>
    <section class="user-summary-grid">
        <article class="user-summary-card"><strong x-text="users.length"></strong><span>Total Admin</span></article>
        <article class="user-summary-card is-green"><strong x-text="users.filter(user => user.status === 'Aktif').length"></strong><span>Aktif</span></article>
        <article class="user-summary-card is-muted"><strong x-text="users.filter(user => user.status === 'Tidak Aktif').length"></strong><span>Tidak Aktif</span></article>
    </section>

    <section class="user-table-panel">
        <div class="user-toolbar">
            <label class="user-search"><span>⌕</span><input type="search" x-model="search" placeholder="Cari nama atau username..." aria-label="Cari pengguna"></label>
            <button type="button" class="user-add-button" @click="openCreate">+ Tambah Akun Admin</button>
        </div>
        <div class="user-table-wrap"><table class="user-table"><thead><tr><th>Pengguna</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Login Terakhir</th><th>Aksi</th></tr></thead><tbody>
            <template x-for="user in filteredUsers" :key="user.id"><tr><td><div class="user-name"><span class="user-row-avatar" :class="user.color" x-text="user.name.charAt(0)"></span><strong x-text="user.name"></strong></div></td><td x-text="user.username"></td><td x-text="user.email"></td><td><strong class="user-role" :class="user.color" x-text="user.role"></strong></td><td><span class="user-status" :class="user.status === 'Aktif' ? 'active' : 'inactive'" x-text="user.status"></span></td><td x-text="user.lastLogin"></td><td><div class="user-actions"><button type="button" @click="openEdit(user)">Edit</button><button type="button" @click="openReset(user)">Reset PW</button><button type="button" @click="remove(user)" x-show="user.name !== 'Admin BPBD'">Hapus</button></div></td></tr></template>
            <tr x-show="filteredUsers.length === 0"><td colspan="7" class="user-empty">Data pengguna tidak ditemukan.</td></tr>
        </tbody></table></div>
    </section>

    <div class="user-modal-backdrop" x-show="modalOpen" x-transition.opacity @keydown.escape.window="modalOpen = false">
        <section class="user-modal" @click.outside="modalOpen = false" role="dialog" aria-modal="true" aria-labelledby="user-modal-title">
            <div class="user-modal-heading"><div><h2 id="user-modal-title" x-text="editingId ? 'Edit Pengguna' : 'Tambah Akun Admin'"></h2><p>Buat akun baru untuk petugas admin atau operator.</p></div><button type="button" class="user-modal-close" @click="modalOpen = false">×</button></div>
            <form @submit.prevent="save"><label>Nama Lengkap <b>*</b><input x-model="form.name" placeholder="Nama petugas" required></label><div class="user-modal-row"><label>Username <b>*</b><input x-model="form.username" placeholder="username" required></label><label>Role<select x-model="form.role"><option>Operator</option><option>Admin</option></select></label></div><label>Email<input type="email" x-model="form.email" placeholder="email@bpbd-bandung.go.id"></label><label>Password <b>*</b> <small>(min. 6 karakter)</small><div class="password-field"><input type="password" x-model="form.password" placeholder="Buat password" minlength="6" :required="!editingId"></div></label><label>Konfirmasi Password <b>*</b><div class="password-field"><input type="password" x-model="form.confirmation" placeholder="Ulangi password" minlength="6" :required="!editingId"></div></label><fieldset><legend>Status</legend><div class="user-status-options"><button type="button" :class="{selected: form.status === 'Aktif'}" @click="form.status = 'Aktif'">Aktif</button><button type="button" :class="{selected: form.status === 'Tidak Aktif'}" @click="form.status = 'Tidak Aktif'">Tidak Aktif</button></div></fieldset><p class="user-form-error" x-show="error" x-text="error"></p><div class="user-modal-actions"><button type="button" class="user-cancel-button" @click="modalOpen = false">Batal</button><button type="submit" class="user-save-button" x-text="editingId ? 'Simpan Perubahan' : 'Buat Akun'"></button></div></form>
        </section>
    </div>

    <div class="user-modal-backdrop" x-show="resetModalOpen" x-transition.opacity @keydown.escape.window="resetModalOpen = false">
        <section class="user-modal reset-password-modal" @click.outside="resetModalOpen = false" role="dialog" aria-modal="true" aria-labelledby="reset-password-title">
            <div class="user-modal-heading"><div><h2 id="reset-password-title">Reset Password</h2><p>Buat password baru untuk akun <strong x-text="resetUser?.username"></strong>.</p></div><button type="button" class="user-modal-close" @click="resetModalOpen = false">×</button></div>
            <form @submit.prevent="resetPassword"><label>Password Baru<input type="password" x-model="resetForm.password" placeholder="Minimal 6 karakter" minlength="6" required></label><label>Konfirmasi Password<input type="password" x-model="resetForm.confirmation" placeholder="Ulangi password baru" minlength="6" required></label><p class="user-form-error" x-show="resetError" x-text="resetError"></p><div class="user-modal-actions"><button type="button" class="user-cancel-button" @click="resetModalOpen = false">Batal</button><button type="submit" class="user-reset-button">Reset Password</button></div></form>
        </section>
    </div>
</div>

<script>
function userManagement() { return { search: '', modalOpen: false, resetModalOpen: false, editingId: null, resetUser: null, showPassword: false, showConfirmation: false, error: '', resetError: '', resetForm: {}, users: [
    { id: 1, name: 'Admin BPBD', username: 'admin', email: 'admin@bpbd-bandung.go.id', role: 'Superadmin', status: 'Aktif', lastLogin: '12 Agu 2026, 08:42', color: 'red' }, { id: 2, name: 'Rini Widiastuti', username: 'rini.w', email: 'rini.w@bpbd-bandung.go.id', role: 'Admin', status: 'Aktif', lastLogin: '11 Agu 2026, 14:15', color: 'blue' }, { id: 3, name: 'Dedi Kurniawan', username: 'dedi.k', email: 'dedi.k@bpbd-bandung.go.id', role: 'Operator', status: 'Aktif', lastLogin: '10 Agu 2026, 09:30', color: 'green' }, { id: 4, name: 'Sari Puspita', username: 'sari.p', email: 'sari.p@bpbd-bandung.go.id', role: 'Operator', status: 'Tidak Aktif', lastLogin: '02 Jul 2026, 11:00', color: 'green' }
], form: {}, get filteredUsers() { const term = this.search.toLowerCase(); return this.users.filter(user => `${user.name} ${user.username} ${user.email}`.toLowerCase().includes(term)); }, openCreate() { this.editingId = null; this.form = { name: '', username: '', email: '', role: 'Operator', status: 'Aktif', password: '', confirmation: '' }; this.error = ''; this.modalOpen = true; }, openEdit(user) { this.editingId = user.id; this.form = { ...user, password: '', confirmation: '' }; this.error = ''; this.modalOpen = true; }, openReset(user) { this.resetUser = user; this.resetForm = { password: '', confirmation: '' }; this.resetError = ''; this.resetModalOpen = true; }, save() { if (this.form.password !== this.form.confirmation) { this.error = 'Konfirmasi password tidak sama.'; return; } if (this.editingId) { const index = this.users.findIndex(user => user.id === this.editingId); this.users[index] = { ...this.users[index], ...this.form, color: this.form.role === 'Admin' ? 'blue' : 'green' }; } else { this.users.push({ ...this.form, id: Date.now(), lastLogin: '-', color: this.form.role === 'Admin' ? 'blue' : 'green' }); } this.modalOpen = false; }, resetPassword() { if (this.resetForm.password !== this.resetForm.confirmation) { this.resetError = 'Konfirmasi password tidak sama.'; return; } this.resetModalOpen = false; }, remove(user) { if (confirm(`Hapus akun ${user.name}?`)) this.users = this.users.filter(item => item.id !== user.id); } }; }
</script>
+@endsection

# Alert Reusable

Komponen alert global untuk notifikasi singkat di aplikasi e-SKM. Komponen menggunakan Blade, Alpine.js, Tailwind CSS, dan Font Awesome.

## Pemasangan

Alert sudah dipasang satu kali di layout admin:

```blade
<x-alert />
```

Untuk layout baru, letakkan komponen setelah elemen `<body>` dibuka. Komponen tidak perlu dipasang berulang pada setiap halaman.

## Dari Controller

Gunakan flash session lalu redirect:

```php
return redirect()
    ->route('admin.users')
    ->with('success', 'Data berhasil disimpan.');
```

Varian yang tersedia:

- `success`: operasi berhasil
- `error`: operasi gagal
- `warning`: peringatan
- `info`: informasi umum

Contoh:

```php
return back()->with('error', 'Data tidak dapat diproses.');
```

Komponen akan membaca key `success`, `error`, `warning`, dan `info` dari session secara otomatis.

## Dari Alpine atau JavaScript

Gunakan fungsi global `showAlert`:

```js
window.showAlert('Akun berhasil dibuat.', 'success');
```

Dengan judul custom:

```js
window.showAlert('Perubahan membutuhkan perhatian.', 'warning', 'Perhatian');
```

Signature:

```js
showAlert(message, type = 'info', title = '')
```

Contoh di Alpine:

```js
async save() {
    try {
        await this.request('/admin/items', { method: 'POST' });
        window.showAlert('Data berhasil disimpan.', 'success');
    } catch (error) {
        window.showAlert(error.message, 'error');
    }
}
```

## Event Langsung

Untuk kebutuhan yang tidak memakai Alpine component, kirim event `app-alert`:

```js
window.dispatchEvent(new CustomEvent('app-alert', {
    detail: {
        message: 'Sinkronisasi selesai.',
        type: 'info',
        title: 'Informasi',
        duration: 6000,
    },
}));
```

`duration` memakai milidetik dan default-nya 4500 ms. Tombol close tetap tersedia untuk menutup alert secara manual.

## Dialog Konfirmasi

Untuk aksi berisiko seperti hapus data, gunakan dialog konfirmasi reusable:

```js
const confirmed = await window.confirmAction('Hapus data ini?', {
    title: 'Hapus Data',
    confirmText: 'Ya, hapus',
    cancelText: 'Batal',
});

if (!confirmed) return;
```

Dialog mengembalikan `true` jika pengguna memilih tombol konfirmasi dan `false` jika membatalkan, menekan `Escape`, atau mengeklik area di luar dialog.

## Catatan Desain

- Alert tampil di kanan atas desktop dan melebar penuh secara aman di mobile.
- Setiap varian memakai warna tema yang berbeda dan icon Font Awesome.
- `aria-live="polite"` dan `role="alert"` membantu pembaca layar menerima notifikasi.
- Hindari memanggil `<x-alert />` lebih dari satu kali dalam layout yang sama agar notifikasi tidak tampil ganda.

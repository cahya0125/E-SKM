# e-SKM — Sistem Elektronik Survei Kepuasan Masyarakat

Repository ini berisi project **e-SKM (Elektronik Survei Kepuasan Masyarakat)** yang dikembangkan secara tim menggunakan Laravel 12.

Dokumen ini menjadi **panduan utama pengerjaan project** agar seluruh anggota tim menggunakan struktur, workflow Git, dan standar coding yang sama.

---

## 1. Teknologi yang Digunakan

| Teknologi | Kegunaan |
|---|---|
| Laravel 12 | Backend dan framework utama |
| PHP | Bahasa pemrograman |
| Blade | Template/frontend |
| Tailwind CSS | Styling/UI |
| Alpine.js | Interaksi frontend sederhana |
| MySQL | Database |
| Vite | Build asset frontend |
| Git | Version control |
| GitHub | Repository dan kolaborasi |

---

# 2. Tujuan Sistem

Sistem e-SKM digunakan untuk membantu proses:

1. Pengelolaan akun admin.
2. Pengelolaan jenis layanan.
3. Pengelolaan periode survei.
4. Pengelolaan unsur pelayanan.
5. Pengisian data responden.
6. Pengisian survei oleh masyarakat.
7. Penyimpanan jawaban survei.
8. Penyimpanan kritik dan saran.
9. Perhitungan nilai IKM/SKM.
10. Penyajian hasil survei.
11. Penyajian grafik dan laporan.
12. Export/cetak laporan jika fitur tersebut sudah diterapkan.

---

# 3. Gambaran Alur Sistem

```text
                         ADMIN
                           |
             +-------------+-------------+
             |             |             |
             v             v             v
       Jenis Layanan   Periode Survei   Unsur Pelayanan
             |             |             |
             +-------------+-------------+
                           |
                           v
                    SISTEM SIAP
                           |
                           v
                       MASYARAKAT
                           |
                           v
                    Isi Data Responden
                           |
                           v
                    Pilih Jenis Layanan
                           |
                           v
                      Isi Survei
                           |
                           v
                    Jawaban Survei
                           |
                           v
                    Kritik & Saran
                           |
                           v
                         Submit
                           |
                           v
                    Data Tersimpan
                           |
                           v
                    Hitung Nilai IKM
                           |
                 +---------+---------+
                 |                   |
                 v                   v
             hasil_ikm        hasil_ikm_detail
                 |                   |
                 +---------+---------+
                           |
                           v
                     DASHBOARD
                           |
              +------------+------------+
              |            |            |
              v            v            v
           Grafik        Rekap       Laporan
```

---

# 4. Struktur Folder Project

```text
e-skm/
│
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   ├── Admin/
│   │   │   ├── Survey/
│   │   │   └── Laporan/
│   │   │
│   │   ├── Middleware/
│   │   └── Requests/
│   │
│   ├── Models/
│   └── Services/
│
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
│
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
│       ├── layouts/
│       ├── components/
│       ├── auth/
│       ├── admin/
│       ├── survey/
│       └── laporan/
│
├── routes/
│   └── web.php
│
├── public/
├── storage/
│
├── docs/
│   ├── DATABASE.md
│   ├── ERD.md
│   ├── SETUP.md
│   ├── WORKFLOW.md
│   ├── STRUKTUR_PROJECT.md
│   └── PEMBAGIAN_TUGAS.md
│
├── .env.example
├── README.md
├── composer.json
├── package.json
└── vite.config.js
```

---

# 5. Penjelasan Folder

## `app/`

Berisi kode utama aplikasi.

### `app/Models/`

Digunakan untuk membuat model yang berhubungan dengan database.

Model yang direncanakan:

```text
User.php
JenisLayanan.php
PeriodeSurvei.php
UnsurPelayanan.php
Responden.php
Survei.php
JawabanSurvei.php
SaranKritik.php
HasilIkm.php
HasilIkmDetail.php
```

### `app/Http/Controllers/`

Mengatur alur request dari user.

Pembagian:

```text
Auth/
Admin/
Survey/
Laporan/
```

### `app/Http/Requests/`

Tempat validasi input/form.

Contoh:

```text
LoginRequest.php
RespondenRequest.php
SurveyRequest.php
```

### `app/Services/`

Digunakan untuk logic yang kompleks.

Contoh:

```text
SkmCalculatorService.php
```

Perhitungan IKM sebaiknya diletakkan di Service, bukan ditulis panjang di Controller.

---

# 6. Struktur Modul

Project dibagi menjadi beberapa modul supaya pekerjaan tim tidak tumpang tindih.

## Modul 1 — Authentication

Fitur:

- Login
- Logout
- Role
- Status user
- Middleware admin

File utama:

```text
app/Http/Controllers/Auth/
app/Http/Middleware/
app/Models/User.php
resources/views/auth/
```

Branch:

```text
feature/auth
```

---

## Modul 2 — Master Data

Mengelola:

- Jenis layanan
- Periode survei
- Unsur pelayanan

File utama:

```text
app/Http/Controllers/Admin/
app/Models/
resources/views/admin/
database/migrations/
```

Branch:

```text
feature/master-data
```

---

## Modul 3 — Survey

Mengelola proses survei masyarakat:

- Data responden
- Pemilihan layanan
- Pengisian survei
- Jawaban
- Kritik dan saran
- Submit survei

File utama:

```text
app/Http/Controllers/Survey/
app/Models/
resources/views/survey/
```

Branch:

```text
feature/survey
```

---

## Modul 4 — Perhitungan IKM

Mengelola:

- Perhitungan nilai IKM/SKM
- Hasil IKM
- Detail hasil IKM
- Mutu pelayanan
- Kinerja pelayanan

File utama:

```text
app/Services/SkmCalculatorService.php
app/Models/HasilIkm.php
app/Models/HasilIkmDetail.php
```

Branch:

```text
feature/perhitungan-ikm
```

---

## Modul 5 — Dashboard & Laporan

Mengelola:

- Dashboard
- Rekap hasil
- Detail hasil
- Grafik
- Filter
- Laporan
- PDF/Excel jika dibutuhkan

File utama:

```text
app/Http/Controllers/Laporan/
resources/views/laporan/
```

Branch:

```text
feature/laporan
```

---

# 7. Struktur Database

Berdasarkan rancangan database awal, tabel utama adalah:

```text
users
jenis_layanan
periode_survei
unsur_pelayanan
responden
survei
jawaban_survei
saran_kritik
hasil_ikm
hasil_ikm_detail
```

> **Catatan:** Nama tabel dan kolom harus disepakati tim sebelum migration final dibuat. Gunakan penamaan yang konsisten dengan Laravel dan snake_case.

Contoh:

```text
jenis_kelamin
id_responden
id_jenis_layanan
tanggal_isi
```

Hindari penamaan campuran seperti:

```text
Jenis_Kelamin
Survei
priode_survei
```

---

# 8. Aturan Database

Database project harus dibuat menggunakan **Laravel Migration**.

Jangan membuat perubahan struktur database hanya di phpMyAdmin tanpa membuat migration.

Jika ingin menambah kolom:

```bash
php artisan make:migration add_xxx_to_nama_table
```

Kemudian commit migration tersebut ke Git.

Anggota lain cukup menjalankan:

```bash
php artisan migrate
```

---

# 9. Setup Project untuk Anggota Baru

## 9.1 Clone Repository

```bash
git clone URL_REPOSITORY
cd e-skm
```

---

## 9.2 Install Dependency Laravel

```bash
composer install
```

---

## 9.3 Install Dependency Frontend

Jika project menggunakan pnpm:

```bash
pnpm install
```

---

## 9.4 Buat File `.env`

Copy:

```text
.env.example
```

menjadi:

```text
.env
```

Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

---

## 9.5 Generate Application Key

```bash
php artisan key:generate
```

---

## 9.6 Buat Database MySQL

Buat database:

```sql
CREATE DATABASE e_skm;
```

Kemudian sesuaikan `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=e_skm
DB_USERNAME=root
DB_PASSWORD=
```

Sesuaikan username/password dengan MySQL masing-masing.

---

## 9.7 Jalankan Migration

```bash
php artisan migrate
```

Jika project sudah memiliki Seeder:

```bash
php artisan db:seed
```

Untuk reset database development:

```bash
php artisan migrate:fresh --seed
```

> Jangan menjalankan `migrate:fresh` pada database production karena akan menghapus tabel beserta datanya.

---

## 9.8 Jalankan Project

Terminal 1:

```bash
php artisan serve
```

Terminal 2:

```bash
pnpm dev
```

Kemudian buka:

```text
http://127.0.0.1:8000
```

---

# 10. Workflow Git Tim

Branch utama:

```text
main
  |
  +-- develop
        |
        +-- feature/auth
        +-- feature/master-data
        +-- feature/survey
        +-- feature/perhitungan-ikm
        +-- feature/laporan
```

## Fungsi Branch

### `main`

Versi project yang sudah stabil.

**Jangan coding langsung di main.**

### `develop`

Tempat penggabungan fitur sebelum masuk ke main.

### `feature/*`

Tempat masing-masing anggota mengerjakan fitur.

---

# 11. Sebelum Mulai Coding

Selalu update branch terlebih dahulu:

```bash
git checkout develop
git pull origin develop
```

Kemudian buat branch fitur:

```bash
git checkout -b feature/nama-fitur
```

Contoh:

```bash
git checkout -b feature/survey
```

---

# 12. Setelah Selesai Coding

Cek perubahan:

```bash
git status
```

Tambahkan file:

```bash
git add .
```

Commit:

```bash
git commit -m "feat: menambahkan form survei"
```

Push:

```bash
git push origin feature/survey
```

Kemudian buat **Pull Request** ke:

```text
feature/survey → develop
```

---

# 13. Aturan Commit

Gunakan format sederhana:

```text
feat: fitur baru
fix: memperbaiki bug
refactor: merapikan kode
style: perubahan tampilan
docs: perubahan dokumentasi
chore: konfigurasi/perawatan project
```

Contoh:

```text
feat: menambahkan login admin
feat: menambahkan CRUD jenis layanan
feat: menambahkan form survei
fix: memperbaiki validasi responden
fix: memperbaiki perhitungan IKM
style: memperbaiki tampilan dashboard
docs: memperbarui panduan setup
```

Hindari:

```text
update
fix
coba
test
perbaikan
aaa
```

karena tidak menjelaskan perubahan yang dilakukan.

---

# 14. Aturan Pull Request

Sebelum Pull Request:

- Pastikan project tidak error.
- Jalankan migration jika ada perubahan database.
- Test fitur sendiri.
- Pastikan tidak mengubah fitur anggota lain tanpa koordinasi.
- Pastikan tidak ada file `.env` yang ikut di-commit.

Pull Request harus menjelaskan:

```text
## Fitur
Form survei

## Perubahan
- Menambahkan form responden
- Menambahkan pertanyaan survei
- Menambahkan penyimpanan jawaban

## Testing
- [x] Form responden
- [x] Validasi
- [x] Submit survei
- [x] Data masuk database
```

---

# 15. Aturan Kerja Database

Karena database dikerjakan bersama, ikuti aturan berikut.

### Jangan

- Menghapus migration milik anggota lain sembarangan.
- Mengubah struktur tabel tanpa koordinasi.
- Mengubah nama kolom tanpa memberi tahu tim.
- Membuat tabel hanya secara manual di phpMyAdmin.

### Jika perlu perubahan database

Buat migration baru.

Contoh:

```bash
php artisan make:migration add_no_hp_to_respondens_table
```

Kemudian beri tahu tim:

```text
Saya menambahkan migration:
add_no_hp_to_respondens_table
```

Anggota lain:

```bash
git pull origin develop
php artisan migrate
```

---

# 16. Alur Coding Fitur

Setiap fitur sebisa mungkin mengikuti alur:

```text
Database
   ↓
Migration
   ↓
Model
   ↓
Request / Validation
   ↓
Controller
   ↓
Route
   ↓
Blade
   ↓
Testing
```

Contoh CRUD Jenis Layanan:

```text
create_jenis_layanan_table
          ↓
JenisLayanan.php
          ↓
JenisLayananController.php
          ↓
JenisLayananRequest.php
          ↓
routes/web.php
          ↓
views/admin/jenis-layanan/
          ↓
Testing
```

---

# 17. Aturan Frontend

Frontend menggunakan:

```text
Blade
Tailwind CSS
Alpine.js
```

Gunakan Blade untuk struktur halaman.

Gunakan Tailwind untuk styling.

Gunakan Alpine.js untuk interaksi sederhana seperti:

- Modal
- Dropdown
- Toggle
- Alert
- Tab
- Show/hide
- Filter sederhana

Tidak perlu menggunakan React/Vue untuk project ini kecuali ada keputusan baru dari tim.

---

# 18. Komponen Blade

Jika sebuah UI digunakan berkali-kali, buat component.

Contoh:

```text
resources/views/components/
├── navbar.blade.php
├── sidebar.blade.php
├── alert.blade.php
├── modal.blade.php
└── pagination.blade.php
```

Jangan membuat navbar berbeda-beda untuk setiap halaman.

Gunakan satu komponen bersama.

---

# 19. Alur Pengisian Survei

Alur utama masyarakat:

```text
Halaman Awal
    ↓
Mulai Survei
    ↓
Data Responden
    ↓
Pilih Jenis Layanan
    ↓
Pertanyaan Survei
    ↓
Jawaban Setiap Unsur
    ↓
Kritik & Saran
    ↓
Konfirmasi
    ↓
Submit
    ↓
Selesai
```

Setiap proses harus diuji sebelum fitur dianggap selesai.

---

# 20. Alur Perhitungan IKM

Perhitungan tidak ditulis langsung di Blade.

Gunakan:

```text
Controller
    ↓
SkmCalculatorService
    ↓
Perhitungan
    ↓
Hasil IKM
    ↓
Hasil IKM Detail
    ↓
Dashboard/Laporan
```

Dengan cara ini logic perhitungan dapat digunakan kembali oleh:

- Dashboard
- Laporan
- Grafik
- PDF

---

# 21. Checklist Sebelum Fitur Dianggap Selesai

Setiap anggota menggunakan checklist:

```text
[x] Migration selesai
[ ] Model selesai
[ ] Relationship selesai
[ ] Validation selesai
[ ] Controller selesai
[ ] Route selesai
[ ] Blade selesai
[ ] Tailwind sudah sesuai
[ ] Alpine.js jika diperlukan
[ ] CRUD/fitur sudah dites
[ ] Tidak ada error Laravel
[ ] Tidak ada error browser
[ ] Database sudah dicek
[ ] Commit sudah dibuat
[ ] Push ke branch
[ ] Pull Request dibuat
```

---

# 22. Pembagian Tugas Tim

Contoh pembagian untuk 4 anggota:

| Anggota | Modul | Branch |
|---|---|---|
| Anggota 1 | Authentication + User | `feature/auth` |
| Anggota 2 | Master Data | `feature/master-data` |
| Anggota 3 | Survey | `feature/survey` |
| Anggota 4 | IKM + Laporan | `feature/perhitungan-ikm` |

### Anggota 1 — Authentication

Mengurus:

- Login
- Logout
- User
- Role
- Middleware

### Anggota 2 — Master Data

Mengurus:

- Jenis layanan
- Periode survei
- Unsur pelayanan

### Anggota 3 — Survey

Mengurus:

- Responden
- Survei
- Jawaban survei
- Kritik & saran

### Anggota 4 — IKM & Laporan

Mengurus:

- Perhitungan IKM
- Hasil IKM
- Detail IKM
- Dashboard hasil
- Grafik
- Laporan

> Pembagian ini dapat disesuaikan dengan jumlah anggota tim.

---

# 23. Dokumentasi Project

Dokumentasi disimpan di folder:

```text
docs/
```

Minimal:

```text
docs/
├── DATABASE.md
├── ERD.md
├── SETUP.md
├── WORKFLOW.md
├── STRUKTUR_PROJECT.md
└── PEMBAGIAN_TUGAS.md
```

Jika ada keputusan penting dalam project, dokumentasikan agar anggota lain tidak perlu menebak.

---

# 24. File yang Tidak Boleh Di-commit

Jangan commit:

```text
.env
/node_modules
/vendor
```

`.env` berisi konfigurasi lokal dan credential.

Gunakan:

```text
.env.example
```

sebagai template konfigurasi.

---

# 25. Masalah Umum

## Error database

Cek:

```text
.env
```

Pastikan:

```env
DB_DATABASE=e_skm
DB_USERNAME=root
DB_PASSWORD=
```

Kemudian:

```bash
php artisan config:clear
php artisan migrate
```

---

## Error dependency

Backend:

```bash
composer install
```

Frontend:

```bash
pnpm install
```

---

## Error Vite/Tailwind

Jalankan:

```bash
pnpm install
pnpm dev
```

---

## Perubahan migration belum masuk

Jalankan:

```bash
php artisan migrate
```

Untuk database development yang ingin di-reset:

```bash
php artisan migrate:fresh --seed
```

---

# 26. Prinsip Utama Project

Semua anggota tim harus mengikuti prinsip berikut:

1. **Jangan langsung coding tanpa memahami modul.**
2. **Jangan mengubah database tanpa koordinasi.**
3. **Jangan push langsung ke `main`.**
4. **Setiap fitur menggunakan branch sendiri.**
5. **Gunakan Pull Request.**
6. **Gunakan migration untuk perubahan database.**
7. **Gunakan Model untuk database.**
8. **Gunakan Controller untuk alur request.**
9. **Gunakan Request untuk validasi.**
10. **Gunakan Service untuk logic/perhitungan kompleks.**
11. **Gunakan Blade untuk tampilan.**
12. **Gunakan Tailwind untuk styling.**
13. **Gunakan Alpine.js untuk interaksi sederhana.**
14. **Dokumentasikan perubahan penting.**
15. **Test fitur sebelum membuat Pull Request.**

---

# 27. Urutan Pengerjaan Project

Jangan mengerjakan semua modul secara acak.

Urutan yang disarankan:

```text
FASE 1
Setup Laravel
      ↓
FASE 2
Database + Migration
      ↓
FASE 3
Authentication
      ↓
FASE 4
Master Data
      ↓
FASE 5
Survey
      ↓
FASE 6
Perhitungan IKM
      ↓
FASE 7
Dashboard
      ↓
FASE 8
Laporan
      ↓
FASE 9
Testing
      ↓
FASE 10
Deployment
```

---

# 28. Definition of Done

Project/fitur dianggap selesai apabila:

```text
✓ Kode sudah dibuat
✓ Tidak ada error
✓ Database berjalan
✓ Validation berjalan
✓ Tampilan sudah dicek
✓ Fitur sudah diuji
✓ Tidak merusak fitur lain
✓ Sudah di-commit
✓ Sudah di-push
✓ Pull Request sudah direview
✓ Sudah masuk develop
```

---

# 29. Catatan Penting untuk Tim

ERD yang digunakan saat ini adalah **rancangan awal**.

Sebelum migration final dibuat, tim wajib menyepakati:

- Nama tabel.
- Nama kolom.
- Primary key.
- Foreign key.
- Tipe data.
- Nullable/tidak.
- Relasi antar tabel.
- Aturan perhitungan IKM.

Jangan mengubah struktur database secara sepihak.

Jika ada perubahan, komunikasikan terlebih dahulu kepada seluruh anggota tim dan dokumentasikan di:

```text
docs/DATABASE.md
```

---

# 30. Quick Start

Untuk anggota yang sudah pernah setup project:

```bash
git checkout develop
git pull origin develop

composer install
pnpm install

php artisan key:generate
php artisan migrate

php artisan serve
```

Terminal lain:

```bash
pnpm dev
```

Buka:

```text
http://127.0.0.1:8000
```

---

## Status Project

```text
[ ] Setup Laravel
[ ] Setup Tailwind
[ ] Setup Alpine.js
[ ] Setup GitHub
[ ] Finalisasi ERD
[ ] Finalisasi database
[ ] Migration
[ ] Authentication
[ ] Master Data
[ ] Survey
[ ] Perhitungan IKM
[ ] Dashboard
[ ] Laporan
[ ] Testing
[ ] Deployment
```

---

## Maintainer

Project ini dikerjakan secara kolaboratif oleh tim.

Setiap anggota bertanggung jawab terhadap modul yang telah diberikan dan wajib mengikuti workflow Git serta standar project yang telah ditentukan.

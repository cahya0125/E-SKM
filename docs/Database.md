# Dokumentasi Database — Aplikasi E-SKM BPBD

Dokumen ini menjelaskan struktur database aplikasi E-SKM (Survei Kepuasan Masyarakat) untuk BPBD, sesuai ERD yang sudah diimplementasikan (prefix tabel: `e_skm_`).

---

## Daftar Tabel

| No | Nama Tabel | Fungsi Singkat |
|---|---|---|
| 1 | `e_skm_users` | Akun pengguna/admin sistem |
| 2 | `e_skm_priode_surveis` | Periode pelaksanaan survei |
| 3 | `e_skm_jenis_layanans` | Jenis layanan BPBD yang disurvei |
| 4 | `e_skm_unsur_pelayanans` | Master 9 unsur/indikator pertanyaan SKM |
| 5 | `e_skm_respondens` | Data diri responden/warga |
| 6 | `e_skm_surveis` | Header transaksi pengisian survei |
| 7 | `e_skm_jawaban_surveis` | Nilai jawaban per unsur per survei |
| 8 | `e_skm_saran_kritiks` | Saran/kritik/pengaduan bebas dari responden *(tabel tambahan)* |
| 9 | `e_skm_hasil_ikms` | Rekap hasil akhir IKM per periode |
| 10 | `e_skm_hasil_ikm_details` | Rincian nilai per unsur per periode |

Tabel tambahan bawaan framework (Laravel): `cache_locks`, `password_reset_tokens`, `sessions`, `migrations`, `job_batches` — bukan bagian dari logika bisnis SKM, dipakai internal sistem (autentikasi, cache, antrian job).

---

## 1. e_skm_users
**Fungsi:** Menyimpan akun yang bisa login ke sistem (admin/petugas) untuk mengelola data master, memantau survei, dan melihat laporan hasil IKM.

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint unsigned (PK) | ID unik user |
| name | varchar(255) | Nama lengkap user |
| username | varchar(255) | Username login |
| email_verified_at | timestamp | Waktu verifikasi email (fitur Laravel) |
| password | varchar(255) | Password (hash) |
| role | enum('admin','petugas') | Hak akses pengguna |
| status | enum('active','inactive') | Status keaktifan akun |
| remember_token | varchar(100) | Token "ingat saya" saat login |
| created_at / updated_at | timestamp | Jejak waktu |

---

## 2. e_skm_priode_surveis
**Fungsi:** Mengelompokkan seluruh data survei berdasarkan rentang waktu tertentu (misal per triwulan). Semua hasil IKM dihitung dan dilaporkan berdasarkan periode ini.

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint unsigned (PK) | ID periode |
| nama_priode | varchar(255) | Contoh: "Triwulan I 2026" |
| tanggal_mulai | date | Tanggal mulai periode |
| tanggal_selesai | date | Tanggal selesai periode |
| status | enum('active','inactive') | Status periode berjalan/tidak |
| created_at / updated_at | timestamp | Jejak waktu |

---

## 3. e_skm_jenis_layanans
**Fungsi:** Master daftar jenis layanan yang diberikan BPBD (mis. bantuan logistik, info peringatan dini, dsb), agar tiap survei bisa dikaitkan dengan layanan spesifik yang dinilai.

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint unsigned (PK) | ID jenis layanan |
| nama_layanan | varchar(255) | Nama layanan |
| deskripsi | varchar(255) | Penjelasan singkat layanan |
| status | enum('active','inactive') | Status aktif/tidak |
| created_at / updated_at | timestamp | Jejak waktu |

---

## 4. e_skm_unsur_pelayanans
**Fungsi:** Master 9 unsur/indikator penilaian SKM (persyaratan, prosedur, waktu, biaya, dll). Menyimpan redaksi pertanyaan yang akan dijawab responden — cukup diubah di satu tempat jika ada revisi kuesioner.

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint unsigned (PK) | ID unsur pelayanan |
| nama_unsur | varchar(255) | Nama unsur (mis. "Kecepatan Pelayanan") |
| pertanyaan | varchar(255) | Redaksi pertanyaan kuesioner |
| status | enum('active','inactive') | Status aktif/tidak |
| created_at / updated_at | timestamp | Jejak waktu |

---

## 5. e_skm_respondens
**Fungsi:** Menyimpan profil demografis warga yang mengisi survei, untuk keperluan analisis (mis. kepuasan berbeda antar usia/pendidikan/pekerjaan).

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint unsigned (PK) | ID responden |
| nama | varchar(255) | Nama responden |
| jenis_kelamin | enum('L','P') | Jenis kelamin |
| usia | varchar(255) | Usia responden |
| pendidikan | varchar(255) | Pendidikan terakhir |
| pekerjaan | varchar(255) | Pekerjaan |
| no_hp | varchar(255) | Nomor HP |
| created_at / updated_at | timestamp | Jejak waktu |

---

## 6. e_skm_surveis
**Fungsi:** Tabel "induk"/header setiap transaksi pengisian survei — mencatat siapa mengisi, untuk layanan apa, di periode mana. Semua jawaban detail menempel ke tabel ini.

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint unsigned (PK) | ID survei |
| priode_survei_id | bigint unsigned (FK) | Relasi ke `e_skm_priode_surveis` |
| jenis_layanan_id | bigint unsigned (FK) | Relasi ke `e_skm_jenis_layanans` |
| responden_id | bigint unsigned (FK) | Relasi ke `e_skm_respondens` |
| nama_responden | varchar(255) | Nama responden (disalin saat submit, untuk histori) |
| alamat_responden | varchar(255) | Alamat responden saat submit |
| created_at / updated_at | timestamp | Jejak waktu pengisian |

---

## 7. e_skm_jawaban_surveis
**Fungsi:** Menyimpan nilai mentah yang diberikan responden untuk tiap unsur pertanyaan. Satu baris = satu nilai jawaban untuk satu unsur dari satu survei. Ini sumber data mentah yang nanti diolah jadi rata-rata (NRR).

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint unsigned (PK) | ID jawaban |
| survei_id | bigint unsigned (FK) | Relasi ke `e_skm_surveis` |
| unsur_pelayanan_id | bigint unsigned (FK) | Relasi ke `e_skm_unsur_pelayanans` |
| nilai | double | Nilai jawaban (skala 1–5) |
| created_at / updated_at | timestamp | Jejak waktu |

---

## 8. e_skm_saran_kritiks *(tabel tambahan — belum ada di ERD)*
**Fungsi:** Menampung masukan bebas berupa teks (saran, kritik, atau pengaduan) dari responden, di luar skor angka pada kuesioner. Penting untuk BPBD karena laporan kualitatif sering memuat informasi kondisi lapangan yang tidak tertangkap lewat nilai numerik.

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint unsigned (PK) | ID saran |
| survei_id | bigint unsigned (FK) | Relasi ke `e_skm_surveis` |
| isi_saran | text | Isi saran/kritik/pengaduan responden |
| created_at / updated_at | timestamp | Jejak waktu |

> **Migrasi Laravel untuk tabel ini:**
> ```php
> Schema::create('e_skm_saran_kritiks', function (Blueprint $table) {
>     $table->id();
>     $table->foreignId('survei_id')->constrained('e_skm_surveis')->cascadeOnDelete();
>     $table->text('isi_saran');
>     $table->timestamps();
> });
> ```

---

## 9. e_skm_hasil_ikms
**Fungsi:** Menyimpan hasil akhir perhitungan Indeks Kepuasan Masyarakat per periode — ini yang menjadi laporan resmi. Disimpan sebagai ringkasan (bukan dihitung ulang tiap saat) agar dashboard/laporan cepat ditampilkan.

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint unsigned (PK) | ID hasil |
| priode_survei_id | bigint unsigned (FK) | Relasi ke `e_skm_priode_surveis` |
| nilai_skm | double | Total NRR tertimbang (contoh: 4.497) |
| nilai_ikm | double | nilai_skm × 20 (contoh: 89.94) |
| mutu_pelayanan | enum('A','B','C','D') | Kategori mutu keseluruhan |
| kinerja_pelayanan | varchar(255) | Label kinerja (mis. "Sangat Baik") |
| created_at / updated_at | timestamp | Jejak waktu kalkulasi |

---

## 10. e_skm_hasil_ikm_details
**Fungsi:** Menyimpan rincian nilai per unsur pelayanan (9 baris per periode) — dipakai untuk analisis lebih dalam, misalnya melihat unsur mana yang paling lemah meski nilai IKM total sudah bagus.

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint unsigned (PK) | ID detail |
| hasil_ikm_id | bigint unsigned (FK) | Relasi ke `e_skm_hasil_ikms` |
| unsur_pelayanan_id | bigint unsigned (FK) | Relasi ke `e_skm_unsur_pelayanans` |
| jumlah_responden | int | Jumlah responden yang menjawab unsur ini |
| nilai_rata_rata | double | NRR (Nilai Rata-Rata) per unsur |
| bobot_nilai | double | Bobot (selalu 0.111, yaitu 1/9 unsur) |
| nrr_tertimbang | double | nilai_rata_rata × bobot_nilai |
| mutu_unsur | enum('A','B','C','D') | Kategori mutu per unsur |
| created_at / updated_at | timestamp | Jejak waktu |

---

## Relasi Antar Tabel (ERD)

```
e_skm_priode_surveis  1───N  e_skm_surveis
e_skm_jenis_layanans  1───N  e_skm_surveis
e_skm_respondens      1───N  e_skm_surveis

e_skm_surveis         1───N  e_skm_jawaban_surveis
e_skm_surveis         1───N  e_skm_saran_kritiks

e_skm_unsur_pelayanans 1───N e_skm_jawaban_surveis
e_skm_unsur_pelayanans 1───N e_skm_hasil_ikm_details

e_skm_priode_surveis  1───N  e_skm_hasil_ikms
e_skm_hasil_ikms      1───N  e_skm_hasil_ikm_details
```

## Alur Kerja Data

1. **Input** — Responden mengisi survei → data masuk ke `e_skm_surveis`, `e_skm_jawaban_surveis`, dan opsional `e_skm_saran_kritiks`.
2. **Kalkulasi** — Di akhir periode, sistem menghitung rata-rata nilai per unsur dari `e_skm_jawaban_surveis` → hasilnya disimpan ke `e_skm_hasil_ikm_details`.
3. **Rekap** — Total dari seluruh unsur tertimbang dijumlahkan → disimpan sebagai Nilai SKM & Nilai IKM di `e_skm_hasil_ikms`.
4. **Laporan** — Dashboard/laporan cukup membaca `e_skm_hasil_ikms` dan `e_skm_hasil_ikm_details`, tanpa perlu menghitung ulang dari ribuan baris data mentah.
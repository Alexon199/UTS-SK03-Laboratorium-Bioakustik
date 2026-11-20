# Lab Bioakustik: Aplikasi Administrasi Rekaman Lapangan

Aplikasi **Laravel 12 + MySQL** untuk mendata metadata rekaman suara lapangan: kode dan nama rekaman, habitat, titik perekaman, dan anotasi suara. Satu rekaman memiliki **satu** titik perekaman dan **banyak** anotasi. Unggah audio dan klasifikasi otomatis tidak termasuk (sesuai soal).

## Status verifikasi (baca dulu)

Kode ini ditulis di lingkungan yang tidak bisa mengunduh Laravel, jadi **aplikasi penuh belum pernah dijalankan oleh penulisnya**. Yang sudah dibuktikan:

| Diperiksa | Hasil |
|---|---|
| Sintaks semua file PHP (`php -l`) | lulus |
| Tes unit murni `ChartBuilder` dan `Fmt` (9 tes, 33 asersi) | lulus, dijalankan sungguhan |
| Seluruh view Blade dirender dengan data contoh lewat Blade mini, lalu dicek di browser (terang, gelap, ponsel) | lulus, tanpa galat JavaScript dan tanpa scroll horizontal di 390 px |

Yang **belum** dijalankan: `php artisan migrate --seed` dan tes fitur yang memakai database (`tests/Feature`, 22 tes). Jalankan langkah 3 di bawah di komputermu dan perbaiki bila ada galat sebelum dikumpulkan.

## Fitur

| Fitur | Keterangan |
|---|---|
| **Dashboard** | 4 angka ringkas (hitung naik), grafik area rekaman per bulan, donat sebaran habitat, plot titik perekaman (lintang/bujur), batang jenis suara, batang spesies teratas, daftar rekaman terbaru |
| **CRUD rekaman** | Satu form menyimpan rekaman + titik + banyak anotasi sekaligus (baris anotasi dinamis) |
| **Pencarian** | Kode, nama, nama lokasi, dan nama spesies pada anotasi |
| **Filter** | Habitat, jenis suara, rentang tanggal; chip filter aktif yang bisa dilepas satu per satu |
| **Urutan & halaman** | 5 pilihan urutan, 10/25/50 baris per halaman, filter terbawa saat pindah halaman |
| **Validasi** | Form Request berbahasa Indonesia: waktu selesai > mulai, frekuensi berpasangan, kode unik |
| **Detail rekaman** | Ringkasan (jumlah anotasi, spesies, keyakinan rata-rata, durasi), **spektrogram** waktu × frekuensi yang tersambung ke tabel anotasi (hover saling menyorot), grafik jenis suara, navigasi rekaman sebelum/sesudah |
| **Rekap spesies** | Jumlah anotasi, jumlah rekaman, keyakinan rata-rata per spesies; bisa dicari dan diurutkan |
| **Ekspor CSV** | Mengikuti pencarian dan filter yang aktif |
| **Tampilan** | Sidebar, tema terang/gelap (mengikuti sistem, bisa diganti), animasi (hormati `prefers-reduced-motion`), dialog konfirmasi hapus, notifikasi, responsif sampai ponsel |

## 1. Instalasi

Prasyarat: PHP ≥ 8.2, Composer, MySQL/MariaDB. Tidak perlu Node/npm (CSS dan JS sudah siap di `public/`).

### Otomatis

```bash
./install.sh            # membuat ../bioakustik-app
```

### Manual

```bash
composer create-project laravel/laravel:^12.0 bioakustik-app
# salin folder app, database, lang, public, resources, routes, tests ke dalam bioakustik-app (timpa)
rm bioakustik-app/tests/Feature/ExampleTest.php      # bawaan Laravel; "/" sekarang dashboard yang butuh database
cp .env.example bioakustik-app/.env
cd bioakustik-app && php artisan key:generate
```

## 2. Database

```sql
CREATE DATABASE bioakustik CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Sesuaikan `DB_USERNAME` dan `DB_PASSWORD` di `.env`.

## 3. Jalankan

```bash
php artisan migrate --seed   # tabel + 6 rekaman contoh + 12 rekaman acak (tanggal 5 bulan terakhir)
php artisan test             # tes unit + 22 tes fitur (SQLite in-memory, bukan MySQL)
php artisan serve            # http://localhost:8000
```

Ulang dari awal: `php artisan migrate:fresh --seed`.

> Font Plus Jakarta Sans dimuat dari Google Fonts. Tanpa internet, tampilan tetap utuh dengan font sistem.

---

## 4. Struktur dan peta materi

| Materi | Lokasi |
|---|---|
| Arsitektur Laravel (service container, injeksi) | `RecordingController` menerima `RecordingService` lewat constructor; `DashboardController` menerima `DashboardService` lewat parameter method |
| Routing & controller | `routes/web.php`, `app/Http/Controllers/*` (`RecordingController` = Resource Controller + `export`) |
| Blade templating | `resources/views/layouts/app.blade.php` (`@extends/@section/@yield/@stack`), `recordings/*`, `dashboard/*`, `species/*`, `charts/*` (`@include` partial) |
| Database engineering | `database/migrations/*` (FK, unique, index, cascade), `database/seeders/*`, `database/factories/*` |
| Eloquent ORM | `app/Models/*` (fillable, casts, accessor, **query scope** `filter` dan `orderedBy`) |
| Database relationship | `Recording` ↔ `RecordingPoint` (hasOne/belongsTo, 1:1), `Recording` ↔ `Annotation` (hasMany/belongsTo, 1:N) |
| Form Request | `app/Http/Requests/RecordingRequest.php` (store + update), `RecordingFilterRequest.php`, `SpeciesFilterRequest.php` |
| Service & transaksi | `app/Services/RecordingService.php` (`DB::transaction`), `DashboardService.php`, `SpeciesService.php` |
| Logika tampilan murni | `app/Support/ChartBuilder.php` (geometri grafik), `Fmt.php` (format angka), `Catalog.php` (habitat, jenis suara) |
| Tes | `tests/Feature/RecordingCrudTest.php`, `PagesTest.php`; `tests/Unit/ChartBuilderTest.php` |

```
app/
├─ Http/Controllers/   Dashboard, Recording (resource + export), Species
├─ Http/Requests/      RecordingRequest, RecordingFilterRequest, SpeciesFilterRequest
├─ Models/             Recording, RecordingPoint, Annotation
├─ Services/           RecordingService, DashboardService, SpeciesService
└─ Support/            Catalog, ChartBuilder, Fmt        (tanpa dependensi Laravel)
resources/views/       layouts, partials/icons, dashboard, recordings, species, charts/*, vendor/pagination
public/                css/app.css, js/app.js            (tanpa build step)
```

## 5. Desain database

```
recordings                       recording_points                 annotations
──────────                       ────────────────                 ───────────
id            PK                 id             PK                id             PK
code          UNIQUE             recording_id   FK UNIQUE ──┐     recording_id   FK ──┐
name                             location_name               │     species_name  INDEX
habitat       INDEX              latitude, longitude         │     call_type
recorded_at   INDEX              altitude, weather           │     start_time / end_time
notes                            timestamps                  │     low_freq_hz / high_freq_hz
timestamps                                                   │     confidence, notes, timestamps
     └───────────── 1 : 1 ───────────────────────────────────┘
     └───────────── 1 : N ─────────────────────────────────────────────────────────┘
```

- **Satu rekaman, satu titik**: `recording_points.recording_id` diberi `UNIQUE`, jadi database sendiri menolak titik kedua.
- **Satu rekaman, banyak anotasi**: `annotations.recording_id` tidak unique.
- Kedua FK memakai `cascadeOnDelete()`: menghapus rekaman tidak meninggalkan data yatim.

## 6. Alur kerja (untuk dijelaskan saat demo)

### a. Menambah rekaman

```
GET  /recordings/create ─► RecordingController@create ─► form kosong + saran nama spesies (datalist)

POST /recordings
  1. Route::resource memetakan ke RecordingController@store
  2. RecordingRequest::prepareForValidation()  → kode di-uppercase
  3. RecordingRequest::rules() + after()       → gagal? redirect kembali + pesan error + old input
  4. store() memanggil RecordingService::create($request->validated())
  5. DB::transaction {
         Recording::create(...)                → INSERT recordings
         $recording->point()->create(...)      → INSERT recording_points (recording_id otomatis)
         syncAnnotations(...)                  → INSERT annotations (0..n baris)
     }                                         → satu langkah gagal = ROLLBACK semuanya
  6. redirect ke recordings.show + flash "berhasil" (tampil sebagai notifikasi)
```

### b. Mengubah rekaman

Setiap baris anotasi lama membawa `id` tersembunyi. `syncAnnotations()`:
- baris **dengan id** milik rekaman ini → **update**
- baris **tanpa id** → **insert**
- anotasi lama yang **tidak dikirim lagi** → **delete**

### c. Pencarian, filter, urutan

`GET /recordings?q=kuntul&habitat=mangrove&sort=annotations&per_page=25`

`RecordingFilterRequest` memvalidasi (nilai `sort` dan `per_page` hanya dari daftar putih) → `RecordingService::paginate()` → scope `filter()` memakai `when()` sehingga tiap kondisi hanya aktif bila diisi; `q` juga mencari lewat relasi (`orWhereHas`) → scope `orderedBy()` memetakan kunci urutan ke kolom lewat `match` (input pengguna tidak pernah masuk ke ORDER BY) → `withQueryString()` menjaga filter saat pindah halaman.

### d. Dashboard dan grafik

```
DashboardController ─► DashboardService::overview()   query GROUP BY (habitat, jenis suara, spesies), hitung per bulan
                    ─► ChartBuilder::donut/bars/area/map   ubah angka jadi geometri (titik, jalur, sudut)
                    ─► view dashboard.index + charts/_*.blade.php   hanya mengulang data menjadi elemen SVG
```

Pemisahan ini disengaja: angka dihitung di service, geometri di `ChartBuilder` (murni, bisa dites tanpa database), dan Blade tidak berisi rumus. Animasi memakai CSS (`@keyframes`) dan satu skrip kecil `public/js/app.js` untuk hitung naik, tema, dialog hapus, dan sorot spektrogram.

Catatan: "peta" di dashboard adalah **plot koordinat** (garis lintang/bujur), bukan peta dengan garis pantai.

### e. Menghapus rekaman

`DELETE /recordings/{id}` → dialog konfirmasi (`data-confirm`) → `RecordingService::delete()` menghapus anotasi, titik, lalu rekaman dalam satu transaksi.

### f. Ekspor CSV

`GET /recordings/export?...filter yang sama` → `streamDownload` menulis baris demi baris dari generator `exportRows()`. Route ini **harus didaftarkan sebelum** `Route::resource`, kalau tidak `/recordings/export` dianggap `show` dengan id "export" (ada tesnya).

## 7. Konsep kunci

- **Controller tipis, service tebal**: controller menerima request dan mengembalikan response; aturan bisnis dan transaksi ada di service.
- **Form Request** memisahkan validasi dari controller; `Rule::unique(...)->ignore($this->route('recording'))` membuat kode rekaman sendiri tidak dianggap duplikat saat update.
- **Eager loading** (`with('point')`, `withCount('annotations')`) mencegah N+1 query di tabel daftar.
- **Query scope** (`filter`, `orderedBy`) membuat query yang bisa dipakai ulang oleh daftar *dan* ekspor.
- **Mass assignment**: `$fillable` + `$request->validated()`, bukan `$request->all()`.
- **Route model binding**: `{recording}` otomatis menjadi objek `Recording` (404 bila tidak ada).
- **Daftar putih** untuk `sort` dan `per_page`: mencegah input liar masuk ke SQL.

## 8. Skenario demo (±7 menit)

1. **Dashboard**: tunjukkan angka yang menghitung naik, grafik bulanan, donat habitat, plot titik; klik satu titik.
2. **Rekaman**: cari `kuntul`, filter habitat Mangrove, lepas chip, urutkan "Anotasi terbanyak", klik Ekspor CSV.
3. **Detail** `HP-GP-001`: arahkan kursor ke kotak spektrogram dan lihat baris tabelnya menyala.
4. **Tambah rekaman**: tambah 2 anotasi; sengaja kosongkan lokasi dan isi waktu selesai < mulai untuk menunjukkan validasi.
5. **Ubah**: hapus satu anotasi, tambah satu, simpan.
6. **Hapus**: dialog konfirmasi; jelaskan transaksi dan cascade.
7. **Spesies**; opsional `php artisan test`.

## 9. Pertanyaan yang sering diajukan penguji

- **Mengapa memakai service?** Satu aksi menyentuh tiga tabel dan perlu transaksi; controller tetap tipis dan logika bisa dites sendiri.
- **Apa yang terjadi bila insert anotasi gagal?** `DB::transaction` melakukan rollback; rekaman dan titik ikut dibatalkan (`test_transaction_rolls_back_when_annotation_insert_fails`).
- **Bagaimana one-to-one dijamin?** Di Eloquent (`hasOne`) dan di database (`UNIQUE` pada `recording_id`).
- **Mengapa `validated()` bukan `all()`?** Hanya data yang lolos aturan yang diteruskan; input tambahan tidak ikut tersimpan.
- **Apa itu N+1?** Tanpa eager loading, tiap baris tabel memicu query titik dan hitung anotasi sendiri; `with()` dan `withCount()` menggabungkannya.
- **Mengapa `sort` tidak langsung dipakai di `orderBy`?** Itu input pengguna; `match` memetakan kunci yang diizinkan ke kolom tetap.
- **Mengapa grafik dihitung di `ChartBuilder`?** Agar Blade tidak berisi rumus, dan geometri bisa dites tanpa database.
- **Mengapa route `export` sebelum `resource`?** Urutan pendaftaran menentukan pencocokan; `{recording}` akan menelan kata "export".

## 10. Pengembangan lanjutan (di luar soal)

Unggah audio (`Storage`), autentikasi + policy, tabel master spesies (menggantikan teks bebas pada anotasi), peta sungguhan (Leaflet/OpenStreetMap), dan impor CSV.

## Dokumentasi

Diagram rancangan ada di folder `docs/`: `erd.png` / `erd.pdf` dan `class-diagram.png` / `class-diagram.pdf`.

@extends('layouts.app')

@section('title', 'Panduan')

@section('content')
    <div class="page-head rise">
        <div>
            <p class="eyebrow">Dokumentasi singkat</p>
            <h1>Panduan aplikasi</h1>
            <p class="lead">Apa yang dicatat aplikasi ini, bagaimana datanya berhubungan, dan ke mana sebuah permintaan mengalir di dalam Laravel.</p>
        </div>
    </div>

    <div class="stack">
        <section class="card rise" style="--d: 1">
            <div class="card-head"><h2>Tentang aplikasi</h2></div>
            <div class="prose">
                <p>Laboratorium Bioakustik merekam suara di lapangan. Aplikasi ini mencatat <strong>metadata</strong> setiap rekaman: kode dan nama, habitat, titik perekaman, dan anotasi suara yang ditemukan (spesies, jenis suara, waktu, rentang frekuensi, tingkat keyakinan).</p>
                <p>Berkas audio tidak diunggah dan tidak ada klasifikasi otomatis. Semua anotasi diisi manual oleh peneliti.</p>
            </div>
        </section>

        <section class="card rise" style="--d: 2">
            <div class="card-head"><div><h2>Model data</h2><p>Tiga tabel. Dua relasi menghubungkannya.</p></div></div>
            <div class="erd">
                <div class="erd-card c-0">
                    <header><span>recordings</span><span>induk</span></header>
                    <ul>
                        <li>id <span>PK</span></li>
                        <li>code <span>unik</span></li>
                        <li>name</li>
                        <li>habitat <span>indeks</span></li>
                        <li>recorded_at</li>
                        <li>notes</li>
                    </ul>
                </div>
                <div class="erd-link"><span><b>1 : 1</b>hasOne / belongsTo<br>recording_id UNIQUE</span></div>
                <div class="erd-card c-1">
                    <header><span>recording_points</span><span>detail</span></header>
                    <ul>
                        <li>id <span>PK</span></li>
                        <li>recording_id <span>FK unik</span></li>
                        <li>location_name</li>
                        <li>latitude, longitude</li>
                        <li>altitude, weather</li>
                    </ul>
                </div>
            </div>
            <div class="erd" style="margin-top:1rem">
                <div class="erd-card c-0">
                    <header><span>recordings</span><span>induk</span></header>
                    <ul><li>id <span>PK</span></li><li>…</li></ul>
                </div>
                <div class="erd-link"><span><b>1 : N</b>hasMany / belongsTo<br>recording_id (tidak unik)</span></div>
                <div class="erd-card c-3">
                    <header><span>annotations</span><span>anak</span></header>
                    <ul>
                        <li>id <span>PK</span></li>
                        <li>recording_id <span>FK</span></li>
                        <li>species_name <span>indeks</span></li>
                        <li>call_type</li>
                        <li>start_time, end_time</li>
                        <li>low_freq_hz, high_freq_hz</li>
                        <li>confidence, notes</li>
                    </ul>
                </div>
            </div>
            <div class="prose" style="margin-top:1rem">
                <p>Kedua foreign key memakai <code>cascadeOnDelete()</code>: menghapus rekaman otomatis menghapus titik dan anotasinya, sehingga tidak ada data yatim.</p>
            </div>
        </section>

        <section class="card rise" style="--d: 3">
            <div class="card-head"><div><h2>Alur sebuah permintaan</h2><p>Contoh: menyimpan rekaman baru (<code>POST /recordings</code>).</p></div></div>
            <ol class="flow">
                <li><strong>Route</strong><p><code>Route::resource</code> memetakan POST /recordings ke <code>RecordingController@store</code>.</p></li>
                <li><strong>Form Request</strong><p><code>RecordingRequest</code> merapikan kode, lalu memvalidasi. Gagal: kembali ke form dengan pesan dan isian lama.</p></li>
                <li><strong>Controller</strong><p>Menerima data bersih lewat <code>validated()</code>, memanggil service, lalu mengalihkan halaman.</p></li>
                <li><strong>Service + transaksi</strong><p><code>RecordingService::create</code> membungkus tiga penyimpanan dalam <code>DB::transaction</code>.</p></li>
                <li><strong>Eloquent</strong><p><code>Recording::create</code>, <code>point()-&gt;create</code>, dan <code>annotations()-&gt;create</code> menyimpan lewat relasi.</p></li>
                <li><strong>Database</strong><p>MySQL menjaga unik, foreign key, dan cascade. Satu langkah gagal berarti rollback semuanya.</p></li>
                <li><strong>Redirect</strong><p>Pengguna dialihkan ke halaman detail dengan notifikasi berhasil (flash session).</p></li>
                <li><strong>Blade</strong><p>Detail dirender: controller menyiapkan angka, <code>ChartBuilder</code> menyiapkan geometri, view menggambar SVG.</p></li>
            </ol>
        </section>

        <section class="card rise" style="--d: 4">
            <div class="card-head"><h2>Fitur</h2></div>
            <ul class="feature-list">
                <li><strong>CRUD rekaman</strong><span>Satu form menyimpan rekaman, titik, dan banyak anotasi sekaligus.</span></li>
                <li><strong>Pencarian</strong><span>Kode, nama, lokasi, dan nama spesies pada anotasi.</span></li>
                <li><strong>Filter</strong><span>Habitat, jenis suara, dan rentang tanggal, dengan chip yang bisa dilepas satu per satu.</span></li>
                <li><strong>Urutan &amp; halaman</strong><span>Lima pilihan urutan dan 10/25/50 baris per halaman.</span></li>
                <li><strong>Validasi</strong><span>Aturan per kolom, waktu selesai lebih besar dari mulai, frekuensi berpasangan.</span></li>
                <li><strong>Ekspor CSV</strong><span>Mengikuti pencarian dan filter yang sedang aktif.</span></li>
                <li><strong>Dashboard</strong><span>Angka ringkas, rekaman per bulan, sebaran habitat, jenis suara, spesies teratas, dan plot titik.</span></li>
                <li><strong>Spektrogram</strong><span>Sketsa waktu × frekuensi per rekaman, tersambung dengan tabel anotasi.</span></li>
                <li><strong>Rekap spesies</strong><span>Jumlah anotasi, jumlah rekaman, dan keyakinan rata-rata per spesies.</span></li>
            </ul>
        </section>

        <section class="card rise" style="--d: 5">
            <div class="card-head"><h2>Cara memakai</h2></div>
            <div class="prose">
                <ol>
                    <li>Buka <a href="{{ route('recordings.create') }}">Rekaman baru</a>, isi kode, nama, habitat, dan titik perekaman.</li>
                    <li>Tambahkan anotasi lewat tombol “Tambah anotasi”. Satu baris untuk satu suara yang ditemukan.</li>
                    <li>Simpan. Halaman detail menampilkan spektrogram dan ringkasan.</li>
                    <li>Gunakan <a href="{{ route('recordings.index') }}">daftar rekaman</a> untuk mencari, menyaring, mengurutkan, dan mengekspor.</li>
                    <li>Pantau gambaran besarnya di <a href="{{ route('dashboard') }}">dashboard</a>.</li>
                </ol>
            </div>
        </section>
    </div>
@endsection

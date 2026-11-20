<?php

/*
 * Terjemahan pesan validasi (bahasa Indonesia). Hanya aturan yang dipakai aplikasi ini;
 * aturan lain otomatis memakai pesan bawaan (en) milik framework.
 */
return [
    'required' => ':attribute wajib diisi.',
    'string' => ':attribute harus berupa teks.',
    'numeric' => ':attribute harus berupa angka.',
    'integer' => ':attribute harus berupa bilangan bulat.',
    'array' => ':attribute tidak valid.',
    'date' => ':attribute bukan tanggal yang valid.',
    'in' => ':attribute yang dipilih tidak valid.',
    'unique' => ':attribute sudah digunakan.',
    'regex' => ':attribute hanya boleh berisi huruf, angka, titik, strip, dan garis bawah.',
    'before_or_equal' => ':attribute tidak boleh di masa depan.',
    'after_or_equal' => ':attribute tidak boleh lebih awal dari :date.',

    'max' => [
        'numeric' => ':attribute tidak boleh lebih dari :max.',
        'string' => ':attribute maksimal :max karakter.',
        'array' => ':attribute maksimal :max item.',
    ],
    'min' => [
        'numeric' => ':attribute minimal :min.',
        'string' => ':attribute minimal :min karakter.',
        'array' => ':attribute minimal :min item.',
    ],
    'between' => [
        'numeric' => ':attribute harus di antara :min dan :max.',
        'string' => ':attribute harus di antara :min dan :max karakter.',
        'array' => ':attribute harus memiliki :min sampai :max item.',
    ],
    'gt' => [
        'numeric' => ':attribute harus lebih besar dari :value.',
        'string' => ':attribute harus lebih panjang dari :value karakter.',
        'array' => ':attribute harus memiliki lebih dari :value item.',
    ],

    'attributes' => [
        'code' => 'Kode rekaman',
        'name' => 'Nama rekaman',
        'habitat' => 'Habitat',
        'recorded_at' => 'Tanggal rekam',
        'notes' => 'Catatan',

        'point' => 'Titik perekaman',
        'point.location_name' => 'Nama lokasi',
        'point.latitude' => 'Lintang',
        'point.longitude' => 'Bujur',
        'point.altitude' => 'Ketinggian',
        'point.weather' => 'Cuaca',

        'annotations' => 'Anotasi',
        'annotations.*.species_name' => 'Nama spesies',
        'annotations.*.call_type' => 'Jenis suara',
        'annotations.*.start_time' => 'Waktu mulai',
        'annotations.*.end_time' => 'Waktu selesai',
        'annotations.*.low_freq_hz' => 'Frekuensi bawah',
        'annotations.*.high_freq_hz' => 'Frekuensi atas',
        'annotations.*.confidence' => 'Tingkat keyakinan',
        'annotations.*.notes' => 'Catatan anotasi',

        'q' => 'Kata kunci',
        'call_type' => 'Jenis suara',
        'from' => 'Tanggal awal',
        'to' => 'Tanggal akhir',
    ],
];

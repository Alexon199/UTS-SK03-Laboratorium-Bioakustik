<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RecordingController;
use App\Http\Controllers\SpeciesController;
use Illuminate\Support\Facades\Route;

// Dashboard (halaman awal): ringkasan + grafik.
Route::get('/', DashboardController::class)->name('dashboard');

// PENTING: route 'export' harus didaftarkan SEBELUM Route::resource.
// Kalau tidak, /recordings/export akan dianggap /recordings/{recording} dengan id "export".
Route::get('recordings/export', [RecordingController::class, 'export'])->name('recordings.export');

// Satu baris ini mendaftarkan 7 route CRUD:
//   GET    /recordings                   recordings.index
//   GET    /recordings/create            recordings.create
//   POST   /recordings                   recordings.store
//   GET    /recordings/{recording}       recordings.show
//   GET    /recordings/{recording}/edit  recordings.edit
//   PUT    /recordings/{recording}       recordings.update
//   DELETE /recordings/{recording}       recordings.destroy
Route::resource('recordings', RecordingController::class);

// Rekap spesies.
Route::get('species', [SpeciesController::class, 'index'])->name('species.index');


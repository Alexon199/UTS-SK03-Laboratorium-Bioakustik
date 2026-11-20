@extends('layouts.app')

@section('title', 'Rekaman baru')

@section('content')
    <div class="page-head rise">
        <div>
            <p class="eyebrow">Tambah data</p>
            <h1>Rekaman baru</h1>
            <p class="lead">Isi data rekaman dan titik perekaman, lalu tambahkan anotasi suara bila ada.</p>
        </div>
        <a class="btn btn-ghost" href="{{ route('recordings.index') }}"><svg class="ico"><use href="#i-left"/></svg>Kembali</a>
    </div>

    @include('recordings._form', [
        'action' => route('recordings.store'),
        'method' => 'POST',
        'submitLabel' => 'Simpan rekaman',
    ])
@endsection

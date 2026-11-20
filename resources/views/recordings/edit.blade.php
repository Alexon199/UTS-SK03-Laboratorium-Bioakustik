@extends('layouts.app')

@section('title', 'Ubah '.$recording->code)

@section('content')
    <div class="page-head rise">
        <div>
            <p class="eyebrow">Ubah data</p>
            <h1>Ubah rekaman</h1>
            <p class="lead"><span class="code">{{ $recording->code }}</span> · {{ $recording->name }}</p>
        </div>
        <a class="btn btn-ghost" href="{{ route('recordings.show', $recording) }}"><svg class="ico"><use href="#i-left"/></svg>Batal</a>
    </div>

    @include('recordings._form', [
        'action' => route('recordings.update', $recording),
        'method' => 'PUT',
        'submitLabel' => 'Simpan perubahan',
    ])
@endsection

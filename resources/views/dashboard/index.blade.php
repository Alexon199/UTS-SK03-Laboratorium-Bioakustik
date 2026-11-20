@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="page-head rise">
        <div>
            <p class="eyebrow">Ringkasan lab</p>
            <h1>Dashboard</h1>
            <p class="lead">Gambaran data rekaman lapangan: berapa banyak sesi, di habitat apa, spesies apa yang paling sering terdeteksi, dan di mana titiknya.</p>
        </div>
        <div class="head-actions">
            <a class="btn btn-ghost" href="{{ route('recordings.export') }}"><svg class="ico"><use href="#i-download"/></svg>Ekspor CSV</a>
            <a class="btn btn-primary" href="{{ route('recordings.create') }}"><svg class="ico"><use href="#i-plus"/></svg>Rekaman baru</a>
        </div>
    </div>

    <section class="kpis" aria-label="Angka ringkas">
        @foreach ($cards as $i => $card)
            <div class="card kpi c-{{ $card['tone'] }} rise" style="--d: {{ $i + 1 }}">
                <div class="kpi-top">
                    <span class="kpi-icon"><svg class="ico"><use href="#i-{{ $card['icon'] }}"/></svg></span>
                    {{ $card['label'] }}
                </div>
                <div class="kpi-value"><span data-count="{{ $card['value'] }}">{{ \App\Support\Fmt::num($card['value']) }}</span>@if (! empty($card['suffix']))<small>{{ $card['suffix'] }}</small>@endif</div>
                <div class="kpi-hint">{{ $card['hint'] }}</div>
            </div>
        @endforeach
    </section>

    <div class="board">
        <x-card title="Rekaman per bulan" subtitle="Enam bulan terakhir, berdasarkan tanggal rekam." span="col-8" :d="5">
            <x-slot:actions><span class="count">{{ $monthly['total'] }} rekaman</span></x-slot:actions>
            @include('charts._area', ['chart' => $monthly])
        </x-card>

        <x-card title="Sebaran habitat" :subtitle="$habitatCount . ' habitat tercakup.'" span="col-4" :d="6">
            @include('charts._donut', ['chart' => $habitat, 'caption' => 'rekaman'])
        </x-card>

        <x-card title="Sebaran titik perekaman" subtitle="Setiap titik adalah satu rekaman, diplot menurut lintang dan bujur. Klik titik untuk membuka detailnya." span="col-8" :d="7">
            @include('charts._map', ['chart' => $map])
        </x-card>

        <x-card title="Jenis suara" subtitle="Jumlah anotasi per jenis." span="col-4" :d="8">
            @include('charts._bars', ['items' => $callTypes])
        </x-card>

        <x-card title="Spesies paling sering" subtitle="Delapan teratas menurut jumlah anotasi." span="col-6" :d="9">
            <x-slot:actions><a class="btn btn-ghost btn-sm" href="{{ route('species.index') }}">Semua spesies</a></x-slot:actions>
            @include('charts._bars', ['items' => $topSpecies])
        </x-card>

        <x-card title="Rekaman terbaru" subtitle="Lima sesi dengan tanggal rekam terbaru." span="col-6" :d="10">
            <x-slot:actions><a class="btn btn-ghost btn-sm" href="{{ route('recordings.index') }}">Lihat semua</a></x-slot:actions>
            <ul class="recent">
                @forelse ($recent as $recording)
                    <li>
                        <a href="{{ route('recordings.show', $recording) }}">
                            <span class="badge c-{{ \App\Support\Catalog::habitatColor($recording->habitat) }}">{{ $recording->code }}</span>
                            <span>
                                <strong>{{ $recording->name }}</strong>
                                <small>{{ $recording->point?->location_name ?? 'Titik belum diisi' }} · {{ $recording->annotations_count }} anotasi</small>
                            </span>
                            <span class="when">{{ $recording->recorded_at?->translatedFormat('d M Y') ?? '—' }}</span>
                        </a>
                    </li>
                @empty
                    <li class="empty-inline">Belum ada rekaman. <a href="{{ route('recordings.create') }}">Tambahkan yang pertama</a>.</li>
                @endforelse
            </ul>
        </x-card>
    </div>
@endsection

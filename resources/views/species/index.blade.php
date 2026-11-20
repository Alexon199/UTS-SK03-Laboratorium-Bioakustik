@extends('layouts.app')

@section('title', 'Spesies')

@section('content')
    <div class="page-head rise">
        <div>
            <p class="eyebrow">Rekap anotasi</p>
            <h1>Spesies</h1>
            <p class="lead">Semua nama spesies dari anotasi, dikelompokkan: berapa kali muncul, di berapa rekaman, dan rata-rata keyakinan identifikasinya.</p>
        </div>
    </div>

    <form method="GET" action="{{ route('species.index') }}" class="card filters rise" style="--d: 1">
        <div class="field f-q">
            <label for="q">Cari spesies</label>
            <div class="search-wrap">
                <svg class="ico"><use href="#i-search"/></svg>
                <input type="search" id="q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nama umum atau nama ilmiah">
            </div>
        </div>
        <div class="field f-sort">
            <label for="sort">Urutkan</label>
            <select id="sort" name="sort" data-autosubmit>
                @foreach ($sorts as $key => $label)
                    <option value="{{ $key }}" @selected(($filters['sort'] ?? 'annotations') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="f-actions">
            <button type="submit" class="btn btn-primary">Cari</button>
            <a href="{{ route('species.index') }}" class="btn btn-ghost">Reset</a>
        </div>
    </form>

    <section class="card table-card rise" style="--d: 2">
        @if ($species->isEmpty())
            <div class="empty">
                <svg class="ico"><use href="#i-inbox"/></svg>
                <p><strong>Tidak ada spesies yang cocok.</strong></p>
                <p class="muted">Spesies muncul di sini setelah ada anotasi pada sebuah rekaman.</p>
            </div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Spesies</th>
                        <th class="num">Anotasi</th>
                        <th>Porsi dari semua anotasi</th>
                        <th class="num">Rekaman</th>
                        <th class="num">Keyakinan rata-rata</th>
                        <th class="num">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($species as $i => $row)
                        @php
                            $names = \App\Support\Fmt::species($row->species_name);
                            $share = $totalAnnotations > 0 ? round($row->annotations_count / $totalAnnotations * 100, 1) : 0;
                        @endphp
                        <tr style="--i: {{ $i }}">
                            <td>
                                <strong>{{ $names['common'] }}</strong>
                                @if ($names['latin'])<span class="sub"><em>{{ $names['latin'] }}</em></span>@endif
                            </td>
                            <td class="num"><b>{{ $row->annotations_count }}</b></td>
                            <td>
                                <span class="meter" style="width: 140px"><i style="--w: {{ min(100, $share * 3) }}%; --i: {{ $i }}"></i></span>
                                <span class="muted small">{{ \App\Support\Fmt::num($share, 1) }}%</span>
                            </td>
                            <td class="num">{{ $row->recordings_count }}</td>
                            <td class="num">{{ $row->avg_confidence !== null ? $row->avg_confidence.'%' : '—' }}</td>
                            <td>
                                <div class="row-actions">
                                    <a class="btn btn-ghost btn-sm" href="{{ route('recordings.index', ['q' => $row->species_name]) }}">Lihat rekaman</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="table-foot">
                <span class="muted">Menampilkan {{ $species->firstItem() }}–{{ $species->lastItem() }} dari {{ $species->total() }} spesies</span>
                {{ $species->links() }}
            </div>
        @endif
    </section>
@endsection

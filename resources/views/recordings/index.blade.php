@extends('layouts.app')

@section('title', 'Rekaman')

@section('content')
    <div class="page-head rise">
        <div>
            <p class="eyebrow">Data lapangan</p>
            <h1>Rekaman</h1>
            <p class="lead">Cari, saring, urutkan, ekspor, dan kelola metadata rekaman beserta titik dan anotasinya.</p>
        </div>
        <div class="head-actions">
            <a class="btn btn-ghost" href="{{ route('recordings.export', $filters) }}"><svg class="ico"><use href="#i-download"/></svg>Ekspor CSV</a>
            <a class="btn btn-primary" href="{{ route('recordings.create') }}"><svg class="ico"><use href="#i-plus"/></svg>Rekaman baru</a>
        </div>
    </div>

    <div class="mini-stats rise" style="--d: 1">
        <span><b>{{ \App\Support\Fmt::num($stats['recordings']) }}</b> rekaman</span>
        <span><b>{{ \App\Support\Fmt::num($stats['annotations']) }}</b> anotasi</span>
        <span><b>{{ \App\Support\Fmt::num($stats['species']) }}</b> spesies</span>
    </div>

    @if ($errors->any())
        <div class="alert" role="alert">{{ $errors->first() }}</div>
    @endif

    <form method="GET" action="{{ route('recordings.index') }}" class="card filters rise" style="--d: 2">
        <div class="field f-q">
            <label for="q">Cari</label>
            <div class="search-wrap">
                <svg class="ico"><use href="#i-search"/></svg>
                <input type="search" id="q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Kode, nama, lokasi, atau spesies">
            </div>
        </div>
        <div class="field f-sel">
            <label for="habitat">Habitat</label>
            <select id="habitat" name="habitat">
                <option value="">Semua habitat</option>
                @foreach ($habitats as $key => $label)
                    <option value="{{ $key }}" @selected(($filters['habitat'] ?? '') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="field f-sel">
            <label for="call_type">Jenis suara</label>
            <select id="call_type" name="call_type">
                <option value="">Semua jenis</option>
                @foreach ($callTypes as $key => $label)
                    <option value="{{ $key }}" @selected(($filters['call_type'] ?? '') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="field f-date">
            <label for="from">Dari tanggal</label>
            <input type="date" id="from" name="from" value="{{ $filters['from'] ?? '' }}">
        </div>
        <div class="field f-date">
            <label for="to">Sampai tanggal</label>
            <input type="date" id="to" name="to" value="{{ $filters['to'] ?? '' }}">
        </div>
        <div class="field f-sort">
            <label for="sort">Urutkan</label>
            <select id="sort" name="sort" data-autosubmit>
                @foreach ($sorts as $key => $label)
                    <option value="{{ $key }}" @selected(($filters['sort'] ?? 'newest') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="field f-per">
            <label for="per_page">Per halaman</label>
            <select id="per_page" name="per_page" data-autosubmit>
                @foreach ($perPageOptions as $option)
                    <option value="{{ $option }}" @selected((int) ($filters['per_page'] ?? $perPageOptions[0]) === $option)>{{ $option }}</option>
                @endforeach
            </select>
        </div>
        <div class="f-actions">
            <button type="submit" class="btn btn-primary">Terapkan</button>
            <a href="{{ route('recordings.index') }}" class="btn btn-ghost">Reset</a>
        </div>
    </form>

    @if (count($chips))
        <div class="chips" aria-label="Filter aktif">
            <span class="muted small">Filter aktif:</span>
            @foreach ($chips as $chip)
                <a class="chip" href="{{ $chip['url'] }}" title="Lepas filter ini">{{ $chip['label'] }}<svg class="ico"><use href="#i-x"/></svg></a>
            @endforeach
        </div>
    @endif

    <section class="card table-card rise" style="--d: 3">
        @if ($recordings->isEmpty())
            <div class="empty">
                <svg class="ico"><use href="#i-inbox"/></svg>
                <p><strong>Tidak ada rekaman yang cocok.</strong></p>
                <p class="muted">Ubah kata kunci atau filter, atau tambahkan rekaman baru.</p>
                <a class="btn btn-ghost" href="{{ route('recordings.index') }}">Reset filter</a>
            </div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Rekaman</th>
                        <th>Habitat</th>
                        <th>Titik perekaman</th>
                        <th>Tanggal</th>
                        <th class="num">Anotasi</th>
                        <th class="num">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($recordings as $i => $recording)
                        <tr style="--i: {{ $i }}">
                            <td><a class="code" href="{{ route('recordings.show', $recording) }}">{{ $recording->code }}</a></td>
                            <td><strong>{{ $recording->name }}</strong></td>
                            <td><span class="badge c-{{ \App\Support\Catalog::habitatColor($recording->habitat) }}">{{ $recording->habitat_label }}</span></td>
                            <td>
                                @if ($recording->point)
                                    {{ $recording->point->location_name }}
                                    <span class="sub">{{ \App\Support\Fmt::num($recording->point->latitude, 4) }}, {{ \App\Support\Fmt::num($recording->point->longitude, 4) }}</span>
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>
                            <td class="nowrap">{{ $recording->recorded_at?->translatedFormat('d M Y') ?? '—' }}</td>
                            <td class="num">
                                <span class="meter"><i style="--w: {{ $maxAnnotations > 0 ? round($recording->annotations_count / $maxAnnotations * 100) : 0 }}%; --i: {{ $i }}"></i></span>{{ $recording->annotations_count }}
                            </td>
                            <td>
                                <div class="row-actions">
                                    <a class="btn btn-ghost btn-icon" href="{{ route('recordings.show', $recording) }}" title="Detail" aria-label="Detail {{ $recording->code }}"><svg class="ico"><use href="#i-eye"/></svg></a>
                                    <a class="btn btn-ghost btn-icon" href="{{ route('recordings.edit', $recording) }}" title="Ubah" aria-label="Ubah {{ $recording->code }}"><svg class="ico"><use href="#i-edit"/></svg></a>
                                    <form method="POST" action="{{ route('recordings.destroy', $recording) }}"
                                          data-confirm="Rekaman {{ $recording->code }} beserta titik dan seluruh anotasinya akan dihapus permanen.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-icon" title="Hapus" aria-label="Hapus {{ $recording->code }}"><svg class="ico"><use href="#i-trash"/></svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="table-foot">
                <span class="muted">Menampilkan {{ $recordings->firstItem() }}–{{ $recordings->lastItem() }} dari {{ $recordings->total() }} rekaman</span>
                {{ $recordings->links() }}
            </div>
        @endif
    </section>
@endsection

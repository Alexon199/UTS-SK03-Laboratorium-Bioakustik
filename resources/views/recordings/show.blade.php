@extends('layouts.app')

@section('title', $recording->code)

@section('content')
    @php
        $point = $recording->point;
        $fmt = fn ($value, int $decimals = 2) => \App\Support\Fmt::num($value, $decimals);
    @endphp

    <div class="page-head rise">
        <div>
            <p class="eyebrow">{{ $recording->habitat_label }}</p>
            <h1>{{ $recording->name }}</h1>
            <p class="lead"><span class="code">{{ $recording->code }}</span> · {{ $recording->recorded_at ? 'direkam '.$recording->recorded_at->translatedFormat('d F Y') : 'tanggal rekam belum diisi' }}</p>
        </div>
        <div class="head-actions">
            <a class="btn btn-ghost" href="{{ route('recordings.index') }}"><svg class="ico"><use href="#i-left"/></svg>Daftar</a>
            <a class="btn btn-primary" href="{{ route('recordings.edit', $recording) }}"><svg class="ico"><use href="#i-edit"/></svg>Ubah</a>
            <form method="POST" action="{{ route('recordings.destroy', $recording) }}"
                  data-confirm="Rekaman {{ $recording->code }} beserta titik dan seluruh anotasinya akan dihapus permanen.">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger"><svg class="ico"><use href="#i-trash"/></svg>Hapus</button>
            </form>
        </div>
    </div>

    <section class="kpis" aria-label="Ringkasan rekaman">
        <div class="card kpi c-0 rise" style="--d: 1">
            <div class="kpi-top"><span class="kpi-icon"><svg class="ico"><use href="#i-tag"/></svg></span>Anotasi</div>
            <div class="kpi-value"><span data-count="{{ $summary['annotations'] }}">{{ $summary['annotations'] }}</span></div>
            <div class="kpi-hint">penanda suara</div>
        </div>
        <div class="card kpi c-3 rise" style="--d: 2">
            <div class="kpi-top"><span class="kpi-icon"><svg class="ico"><use href="#i-feather"/></svg></span>Spesies</div>
            <div class="kpi-value"><span data-count="{{ $summary['species'] }}">{{ $summary['species'] }}</span></div>
            <div class="kpi-hint">nama berbeda</div>
        </div>
        <div class="card kpi c-2 rise" style="--d: 3">
            <div class="kpi-top"><span class="kpi-icon"><svg class="ico"><use href="#i-check"/></svg></span>Keyakinan rata-rata</div>
            <div class="kpi-value">
                @if ($summary['confidence'] !== null)<span data-count="{{ $summary['confidence'] }}">{{ $summary['confidence'] }}</span><small>%</small>@else<span class="muted">—</span>@endif
            </div>
            <div class="kpi-hint">dari anotasi yang diisi</div>
        </div>
        <div class="card kpi c-1 rise" style="--d: 4">
            <div class="kpi-top"><span class="kpi-icon"><svg class="ico"><use href="#i-wave"/></svg></span>Total durasi anotasi</div>
            <div class="kpi-value"><span data-count="{{ $summary['duration'] }}" data-decimals="1">{{ $fmt($summary['duration'], 1) }}</span><small>dtk</small></div>
            <div class="kpi-hint">jumlah lama semua penanda</div>
        </div>
    </section>

    {{-- data-ann-scope: kotak spektrogram dan baris tabel dengan data-ann yang sama saling menyorot --}}
    <div class="board" data-ann-scope>
        <section class="card col-8 rise" style="--d: 5">
            <div class="card-head">
                <div>
                    <h2>Spektrogram anotasi</h2>
                    <p>Kotak = satu anotasi. Arahkan kursor untuk menyorot barisnya di tabel.</p>
                </div>
            </div>
            @include('charts._spectrogram', ['chart' => $spectro])
        </section>

        <div class="col-4 stack">
            <section class="card rise" style="--d: 6">
                <div class="card-head"><h2>Titik perekaman</h2></div>
                @if ($point)
                    <dl class="dl">
                        <div><dt>Lokasi</dt><dd>{{ $point->location_name }}</dd></div>
                        <div><dt>Koordinat</dt><dd>{{ $fmt($point->latitude, 5) }}, {{ $fmt($point->longitude, 5) }}</dd></div>
                        <div><dt>Ketinggian</dt><dd>{{ $point->altitude !== null ? \App\Support\Fmt::num($point->altitude).' mdpl' : '—' }}</dd></div>
                        <div><dt>Cuaca</dt><dd>{{ $point->weather ?: '—' }}</dd></div>
                    </dl>
                    <p style="margin-top:.9rem">
                        <a class="btn btn-ghost btn-sm" target="_blank" rel="noopener"
                           href="https://www.openstreetmap.org/?mlat={{ $point->latitude }}&amp;mlon={{ $point->longitude }}#map=12/{{ $point->latitude }}/{{ $point->longitude }}">
                            <svg class="ico"><use href="#i-pin"/></svg>Lihat di peta<svg class="ico"><use href="#i-external"/></svg>
                        </a>
                    </p>
                @else
                    <p class="muted">Titik perekaman belum diisi.</p>
                @endif
            </section>

            <section class="card rise" style="--d: 7">
                <div class="card-head"><h2>Jenis suara</h2></div>
                @include('charts._bars', ['items' => $typeBars])
            </section>
        </div>

        <section class="card col-12 rise" style="--d: 8">
            <div class="card-head">
                <h2 class="card-title">Daftar anotasi <span class="count">{{ $summary['annotations'] }}</span></h2>
            </div>

            @if ($recording->annotations->isEmpty())
                <p class="empty-inline">Rekaman ini belum memiliki anotasi. <a href="{{ route('recordings.edit', $recording) }}">Tambahkan sekarang</a>.</p>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Spesies</th>
                            <th>Jenis suara</th>
                            <th class="num">Waktu (detik)</th>
                            <th class="num">Durasi</th>
                            <th class="num">Frekuensi (Hz)</th>
                            <th class="num">Keyakinan</th>
                            <th>Catatan</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($recording->annotations as $i => $annotation)
                            @php $names = \App\Support\Fmt::species($annotation->species_name); @endphp
                            <tr data-ann="{{ $i }}" style="--i: {{ $i }}">
                                <td>{{ $i + 1 }}</td>
                                <td>
                                    <strong>{{ $names['common'] }}</strong>
                                    @if ($names['latin'])<span class="sub"><em>{{ $names['latin'] }}</em></span>@endif
                                </td>
                                <td><span class="badge c-{{ \App\Support\Catalog::callColor($annotation->call_type) }}">{{ $annotation->call_type_label }}</span></td>
                                <td class="num">{{ $fmt($annotation->start_time) }} – {{ $fmt($annotation->end_time) }}</td>
                                <td class="num">{{ $fmt($annotation->duration) }} dtk</td>
                                <td class="num">
                                    @if ($annotation->low_freq_hz !== null && $annotation->high_freq_hz !== null)
                                        {{ \App\Support\Fmt::num($annotation->low_freq_hz) }} – {{ \App\Support\Fmt::num($annotation->high_freq_hz) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="num">
                                    @if ($annotation->confidence !== null)
                                        <span class="meter"><i style="--w: {{ $annotation->confidence }}%; --i: {{ $i }}"></i></span>{{ $annotation->confidence }}%
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $annotation->notes ?: '—' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="card col-12 rise" style="--d: 9">
            <div class="card-head"><h2>Catatan &amp; riwayat</h2></div>
            <p>{{ $recording->notes ?: 'Tidak ada catatan untuk rekaman ini.' }}</p>
            <p class="muted small">Dibuat {{ $recording->created_at?->diffForHumans() }} · terakhir diubah {{ $recording->updated_at?->diffForHumans() }}</p>
            <div class="pager">
                @if ($neighbors['prev'])
                    <a class="btn btn-ghost btn-sm" href="{{ route('recordings.show', $neighbors['prev']) }}"><svg class="ico"><use href="#i-left"/></svg>{{ $neighbors['prev']->code }}</a>
                @endif
                @if ($neighbors['next'])
                    <a class="btn btn-ghost btn-sm" href="{{ route('recordings.show', $neighbors['next']) }}">{{ $neighbors['next']->code }}<svg class="ico"><use href="#i-right"/></svg></a>
                @endif
            </div>
        </section>
    </div>
@endsection

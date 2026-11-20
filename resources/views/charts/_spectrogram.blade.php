{{--
    Sketsa spektrogram. $chart dari ChartBuilder::spectrogram().
    Tiap kotak = satu anotasi (X = waktu, Y = frekuensi). data-ann menghubungkannya dengan baris tabel.
--}}
@if ($chart['plotted'] === 0)
    <p class="empty-inline">Belum ada anotasi dengan batas frekuensi, jadi sketsa belum bisa digambar.</p>
@else
    @php
        // Legenda: satu entri per jenis suara yang benar-benar muncul di kotak
        $legend = [];
        foreach ($chart['boxes'] as $box) {
            $legend[$box['type']] = ['label' => $box['type_label'], 'color' => $box['color']];
        }
    @endphp
    <figure class="spectro" style="margin:0">
        <svg class="chart-svg" viewBox="0 0 {{ $chart['width'] }} {{ $chart['height'] }}" role="img"
             aria-label="Sketsa waktu dan frekuensi dari {{ $chart['plotted'] }} anotasi">
            <rect class="sp-plot" x="{{ $chart['left'] }}" y="{{ $chart['top'] }}" width="{{ $chart['plotW'] }}" height="{{ $chart['plotH'] }}"/>

            @foreach ($chart['yTicks'] as $tick)
                <line class="sp-grid" x1="{{ $chart['left'] }}" x2="{{ $chart['left'] + $chart['plotW'] }}" y1="{{ $tick['y'] }}" y2="{{ $tick['y'] }}"/>
                <text class="sp-tick" x="{{ $chart['left'] - 8 }}" y="{{ $tick['y'] + 4 }}" text-anchor="end">{{ $tick['label'] }}</text>
            @endforeach
            @foreach ($chart['xTicks'] as $tick)
                <line class="sp-grid" x1="{{ $tick['x'] }}" x2="{{ $tick['x'] }}" y1="{{ $chart['top'] }}" y2="{{ $chart['top'] + $chart['plotH'] }}"/>
                <text class="sp-tick" x="{{ $tick['x'] }}" y="{{ $chart['top'] + $chart['plotH'] + 18 }}" text-anchor="middle">{{ $tick['label'] }}</text>
            @endforeach

            <text class="sp-tick" x="{{ $chart['left'] + $chart['plotW'] / 2 }}" y="{{ $chart['height'] - 4 }}" text-anchor="middle">Waktu (detik)</text>
            <text class="sp-tick" transform="translate(12 {{ $chart['top'] + $chart['plotH'] / 2 }}) rotate(-90)" text-anchor="middle">Frekuensi (kHz)</text>

            @foreach ($chart['boxes'] as $box)
                <rect class="sp-box c-{{ $box['color'] }}" data-ann="{{ $box['idx'] }}" style="--i: {{ $box['idx'] }}"
                      x="{{ $box['x'] }}" y="{{ $box['y'] }}" width="{{ $box['w'] }}" height="{{ $box['h'] }}" rx="3">
                    <title>{{ $box['label'] }} · {{ $box['type_label'] }} · {{ \App\Support\Fmt::num($box['start'], 1) }}–{{ \App\Support\Fmt::num($box['end'], 1) }} dtk · {{ \App\Support\Fmt::num($box['low']) }}–{{ \App\Support\Fmt::num($box['high']) }} Hz</title>
                </rect>
            @endforeach
        </svg>

        <figcaption class="spectro-legend">
            @foreach ($legend as $item)
                <span class="c-{{ $item['color'] }}"><i class="dot"></i>{{ $item['label'] }}</span>
            @endforeach
            @if ($chart['skipped'] > 0)
                <span>{{ $chart['skipped'] }} anotasi tanpa data frekuensi tidak digambar.</span>
            @endif
        </figcaption>
    </figure>
@endif

{{--
    Sketsa "spektrogram": sumbu X = waktu (detik), sumbu Y = frekuensi (kHz).
    Tiap anotasi yang punya batas frekuensi digambar sebagai kotak.
    Variabel: $annotations (koleksi Annotation)
--}}
@php
    $plotted = $annotations->filter(fn ($a) => $a->low_freq_hz !== null && $a->high_freq_hz !== null);
    $skipped = $annotations->count() - $plotted->count();
@endphp

@if ($plotted->isNotEmpty())
    @php
        $W = 760; $H = 270; $left = 52; $right = 12; $top = 12; $bottom = 32;
        $plotW = $W - $left - $right;
        $plotH = $H - $top - $bottom;

        $maxTime = (float) $annotations->map(fn ($a) => (float) $a->end_time)->max();
        $maxTime = max(5, ceil($maxTime / 5) * 5);
        $maxFreq = max(1000, ceil(((int) $plotted->max('high_freq_hz')) / 1000) * 1000);

        $x = fn ($t) => $left + ((float) $t / $maxTime) * $plotW;
        $y = fn ($f) => $top + $plotH - ((float) $f / $maxFreq) * $plotH;
        $n = fn ($v) => number_format((float) $v, 1, '.', '');

        $usedTypes = $plotted->pluck('call_type')->unique()->values();
    @endphp

    <figure class="spectrogram">
        <svg viewBox="0 0 {{ $W }} {{ $H }}" role="img"
             aria-label="Sketsa waktu dan frekuensi anotasi suara">
            <rect class="sp-plot" x="{{ $left }}" y="{{ $top }}" width="{{ $plotW }}" height="{{ $plotH }}"/>

            @for ($k = 0; $k <= 4; $k++)
                @php $freq = $maxFreq / 4 * $k; @endphp
                <line class="sp-grid" x1="{{ $left }}" x2="{{ $left + $plotW }}" y1="{{ $n($y($freq)) }}" y2="{{ $n($y($freq)) }}"/>
                <text class="sp-tick" x="{{ $left - 8 }}" y="{{ $n($y($freq) + 4) }}" text-anchor="end">{{ number_format($freq / 1000, 1, ',', '.') }}</text>
            @endfor

            @for ($k = 0; $k <= 5; $k++)
                @php $time = $maxTime / 5 * $k; @endphp
                <line class="sp-grid" x1="{{ $n($x($time)) }}" x2="{{ $n($x($time)) }}" y1="{{ $top }}" y2="{{ $top + $plotH }}"/>
                <text class="sp-tick" x="{{ $n($x($time)) }}" y="{{ $top + $plotH + 18 }}" text-anchor="middle">{{ number_format($time, 0, ',', '.') }}</text>
            @endfor

            <text class="sp-axis" x="{{ $left + $plotW / 2 }}" y="{{ $H - 2 }}" text-anchor="middle">Waktu (detik)</text>
            <text class="sp-axis" transform="translate(12 {{ $top + $plotH / 2 }}) rotate(-90)" text-anchor="middle">kHz</text>

            @foreach ($plotted as $a)
                @php
                    $bx = $x($a->start_time);
                    $bw = max(3, $x($a->end_time) - $bx);
                    $by = $y($a->high_freq_hz);
                    $bh = max(3, $y($a->low_freq_hz) - $by);
                @endphp
                <rect class="sp-box sp-{{ $a->call_type }}" x="{{ $n($bx) }}" y="{{ $n($by) }}" width="{{ $n($bw) }}" height="{{ $n($bh) }}" rx="2">
                    <title>{{ $a->species_name }} · {{ $a->call_type_label }} · {{ number_format((float) $a->start_time, 2, ',', '.') }}–{{ number_format((float) $a->end_time, 2, ',', '.') }} s · {{ number_format($a->low_freq_hz, 0, ',', '.') }}–{{ number_format($a->high_freq_hz, 0, ',', '.') }} Hz</title>
                </rect>
            @endforeach
        </svg>

        <figcaption class="legend">
            @foreach ($usedTypes as $type)
                <span class="legend-item"><i class="legend-dot sp-{{ $type }}"></i>{{ \App\Models\Annotation::CALL_TYPES[$type] ?? $type }}</span>
            @endforeach
            @if ($skipped > 0)
                <span class="muted small">{{ $skipped }} anotasi tanpa data frekuensi tidak digambar.</span>
            @endif
        </figcaption>
    </figure>
@endif

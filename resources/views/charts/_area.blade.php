{{-- Grafik area per bulan. $chart dari ChartBuilder::area(). --}}
<svg class="chart-svg" viewBox="0 0 {{ $chart['width'] }} {{ $chart['height'] }}" role="img"
     aria-label="Grafik jumlah rekaman per bulan, total {{ $chart['total'] }}">
    <defs>
        <linearGradient id="area-grad" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0" style="stop-color: var(--brand); stop-opacity: .38"/>
            <stop offset="1" style="stop-color: var(--brand); stop-opacity: 0"/>
        </linearGradient>
    </defs>

    @foreach ($chart['yTicks'] as $tick)
        <line class="ax-grid" x1="{{ $chart['left'] }}" x2="{{ $chart['right'] }}" y1="{{ $tick['y'] }}" y2="{{ $tick['y'] }}"/>
        <text class="ax-text" x="{{ $chart['left'] - 10 }}" y="{{ $tick['y'] + 4 }}" text-anchor="end">{{ $tick['label'] }}</text>
    @endforeach

    @if ($chart['area'] !== '')
        <path class="area-fill" d="{{ $chart['area'] }}"/>
        <path class="area-line" d="{{ $chart['line'] }}" pathLength="1"/>
    @endif

    @foreach ($chart['dots'] as $i => $dot)
        <circle class="area-dot" cx="{{ $dot['x'] }}" cy="{{ $dot['y'] }}" r="5" style="--i: {{ $i }}">
            <title>{{ $dot['label'] }}: {{ $dot['value'] }} rekaman</title>
        </circle>
        <text class="area-val" x="{{ $dot['x'] }}" y="{{ $dot['y'] - 12 }}" style="--i: {{ $i }}">{{ $dot['value'] }}</text>
        <text class="ax-text" x="{{ $dot['x'] }}" y="{{ $chart['baseY'] + 20 }}" text-anchor="middle">{{ $dot['label'] }}</text>
    @endforeach
</svg>

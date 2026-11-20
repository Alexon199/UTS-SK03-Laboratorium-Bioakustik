{{--
    Plot titik perekaman. $chart dari ChartBuilder::map().
    Ini plot koordinat (lintang/bujur), bukan peta dengan garis pantai.
--}}
<div class="map-box">
    <svg class="chart-svg" viewBox="0 0 {{ $chart['width'] }} {{ $chart['height'] }}" role="img"
         aria-label="Sebaran {{ count($chart['dots']) }} titik perekaman berdasarkan koordinat" style="overflow:hidden">
        @foreach ($chart['vertical'] as $line)
            <line class="map-line" x1="{{ $line['x'] }}" x2="{{ $line['x'] }}" y1="0" y2="{{ $chart['height'] }}"/>
            <text class="map-text" x="{{ $line['x'] + 4 }}" y="{{ $chart['height'] - 6 }}">{{ $line['label'] }}</text>
        @endforeach
        @foreach ($chart['horizontal'] as $line)
            <line class="map-line {{ $line['label'] === '0°' ? 'map-equator' : '' }}" x1="0" x2="{{ $chart['width'] }}" y1="{{ $line['y'] }}" y2="{{ $line['y'] }}"/>
            <text class="map-text" x="6" y="{{ $line['y'] - 5 }}">{{ $line['label'] }}</text>
        @endforeach

        @foreach ($chart['dots'] as $dot)
            <a href="{{ $dot['href'] ?? '#' }}">
                <g class="map-dot c-{{ $dot['color'] ?? 0 }}" style="--i: {{ $dot['i'] }}">
                    <circle class="pulse" cx="{{ $dot['x'] }}" cy="{{ $dot['y'] }}" r="7"/>
                    <circle class="core" cx="{{ $dot['x'] }}" cy="{{ $dot['y'] }}" r="5"/>
                    <title>{{ $dot['code'] ?? '' }} · {{ $dot['label'] }} · {{ $dot['place'] ?? '' }}</title>
                </g>
            </a>
        @endforeach
    </svg>
</div>

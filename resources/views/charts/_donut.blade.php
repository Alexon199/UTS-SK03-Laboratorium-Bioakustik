{{-- Donat beranimasi. $chart dari ChartBuilder::donut(); $caption = teks kecil di tengah. --}}
@if (empty($chart['segments']))
    <p class="empty-inline">Belum ada data.</p>
@else
    <div class="donut-wrap">
        <svg viewBox="0 0 200 200" role="img" aria-label="Diagram donat: {{ $caption }}">
            <g transform="rotate(-90 100 100)">
                @foreach ($chart['segments'] as $i => $seg)
                    <circle class="donut-seg c-{{ $seg['idx'] }}" cx="100" cy="100" r="{{ $chart['radius'] }}"
                            stroke-width="{{ $chart['stroke'] }}" stroke-dashoffset="{{ $seg['offset'] }}"
                            style="--len: {{ $seg['length'] }}; --rest: {{ $seg['rest'] }}; --c-total: {{ $chart['circumference'] }}; --i: {{ $i }}">
                        <title>{{ $seg['label'] }}: {{ $seg['value'] }} ({{ $seg['pct'] }}%)</title>
                    </circle>
                @endforeach
            </g>
            <text class="donut-num" x="100" y="104">{{ \App\Support\Fmt::num($chart['total']) }}</text>
            <text class="donut-cap" x="100" y="122">{{ $caption }}</text>
        </svg>

        <ul class="legend">
            @foreach ($chart['segments'] as $seg)
                <li class="c-{{ $seg['idx'] }}">
                    <i></i>
                    <span>{{ $seg['label'] }}</span>
                    <span><b>{{ \App\Support\Fmt::num($seg['value']) }}</b><span class="pct">{{ \App\Support\Fmt::num($seg['pct'], 0) }}%</span></span>
                </li>
            @endforeach
        </ul>
    </div>
@endif

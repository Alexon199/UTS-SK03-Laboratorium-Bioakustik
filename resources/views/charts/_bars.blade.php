{{--
    Batang horizontal beranimasi. $items dari ChartBuilder::bars():
    tiap item punya label, value, pct, idx, dan opsional color / href.
--}}
<ul class="bars">
    @forelse ($items as $i => $item)
        <li style="--i: {{ $i }}">
            <div class="bars-label">
                @if (! empty($item['href']))
                    <a href="{{ $item['href'] }}" title="{{ $item['label'] }}">{{ $item['label'] }}</a>
                @else
                    <span>{{ $item['label'] }}</span>
                @endif
                <b>{{ \App\Support\Fmt::num($item['value']) }}</b>
            </div>
            <div class="bars-track" role="img" aria-label="{{ $item['label'] }}: {{ $item['value'] }}">
                <i class="bars-fill c-{{ $item['color'] ?? $item['idx'] }}" style="--w: {{ $item['pct'] }}%; --i: {{ $i }}"></i>
            </div>
        </li>
    @empty
        <li class="empty-inline">Belum ada data.</li>
    @endforelse
</ul>

@if ($paginator->hasPages())
    <nav class="pagination" role="navigation" aria-label="Navigasi halaman">
        @if ($paginator->onFirstPage())
            <span class="page-btn disabled" aria-disabled="true">&laquo;</span>
        @else
            <a class="page-btn" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Sebelumnya">&laquo;</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="page-btn disabled">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="page-btn active" aria-current="page">{{ $page }}</span>
                    @else
                        <a class="page-btn" href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a class="page-btn" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Berikutnya">&raquo;</a>
        @else
            <span class="page-btn disabled" aria-disabled="true">&raquo;</span>
        @endif
    </nav>
@endif
